/*
 * Copyright 2009 Google Inc.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 * 
 * http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
package com.google.jstestdriver;

import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.google.jstestdriver.BrowserCaptureEvent.Event;

import java.io.IOException;
import java.io.PrintWriter;
import java.util.Collection;
import java.util.Date;
import java.util.LinkedHashSet;
import java.util.Map;
import java.util.Observable;
import java.util.Observer;
import java.util.Set;
import java.util.HashSet;
import java.util.UUID;
import java.util.concurrent.ConcurrentHashMap;

import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import java.io.UnsupportedEncodingException;
import java.nio.charset.CharsetEncoder;
import java.nio.charset.CharsetDecoder;
import java.nio.charset.Charset;
import java.nio.CharBuffer;
import java.nio.ByteBuffer;
import java.io.IOException;

/**
 * @author jeremiele@google.com (Jeremie Lenfant-Engelmann)
 */
public class FileSetServlet extends HttpServlet implements Observer {

  private static final long serialVersionUID = -5224290018208979639L;
  private static final int HEARTBEAT_TIMEOUT = 2000;

  private final Gson gson = new Gson();  
  private final Map<String, Lock> locks = new ConcurrentHashMap<String, Lock>();

  private final CapturedBrowsers capturedBrowsers;
  private final FileSetCacheStrategy strategy = new FileSetCacheStrategy();

  // Shared with the TestResourceServlet
  private final FilesCache filesCache;

  public FileSetServlet(CapturedBrowsers capturedBrowsers, FilesCache filesCache) {
    this.capturedBrowsers = capturedBrowsers;
    this.filesCache = filesCache;
    this.capturedBrowsers.addObserver(this);
  }

  @Override
  protected void doGet(HttpServletRequest req, HttpServletResponse resp) throws IOException {
    String id = req.getParameter("id");
    String session = req.getParameter("session");
    String sessionId = req.getParameter("sessionId");

    if (session == null && sessionId != null) {
      sessionHeartBeat(id, sessionId);
    } else { 
      if (session.equals("start")) {
        startSession(id, resp.getWriter());
      } else if (session.equals("stop")) {
        stopSession(id, sessionId, resp.getWriter());
      }
    }
  }

  private void sessionHeartBeat(String id, String sessionId) {
    Lock lock = locks.get(id);

    if (lock.getSessionId().equals(sessionId)) {
      lock.setLastHeartBeat(new Date().getTime());
    } else {
      // who are you??
    }
  }

  public void stopSession(String id, String sessionId, PrintWriter writer) {
    Lock lock = locks.get(id);

    try {
      lock.unlock(sessionId);
    } finally {
      writer.flush();
    }
  }

  public void startSession(String id, PrintWriter writer) {
    SlaveBrowser browser = capturedBrowsers.getBrowser(id);
    Lock lock = locks.get(id);
    String sessionId = UUID.randomUUID().toString();

    if (lock.tryLock(sessionId)) {
      writer.write(sessionId);
    } else {
      // session is probably staled
      if ((!browser.isCommandRunning() && browser.peekCommand() == null) ||
          ((new Date().getTime() - lock.getLastHeartBeat()) > HEARTBEAT_TIMEOUT)) {
        lock.forceUnlock();
        SlaveBrowser slaveBrowser = capturedBrowsers.getBrowser(id);

        slaveBrowser.clearCommandRunning();
        slaveBrowser.clearResponseQueue();
        filesCache.clear();
        writer.write(lock.tryLock(sessionId) ? sessionId : "FAILED");
      } else {
        writer.write("FAILED");
      }
    }
    writer.flush();
  }

  @Override
  protected void doPost(HttpServletRequest req, HttpServletResponse resp) throws IOException {
    String data = req.getParameter("data");
    String id = req.getParameter("id");

    if (data != null) {
      uploadFiles(id, data);
    } else {
      checkFileSet(req.getParameter("fileSet"), id, resp.getWriter());
    }
  }

  public void checkFileSet(String fileSet, String browserId, PrintWriter writer) {
    Collection<FileInfo> clientFileSet =
      gson.fromJson(fileSet, new TypeToken<Collection<FileInfo>>() {}.getType());
    SlaveBrowser browser = capturedBrowsers.getBrowser(browserId);
    // Hack by ppershing: always reload all files, as there might be dependencies
    // between sources and tests which must be loaded in order!
    Set<FileInfo> browserFileSet = new HashSet<FileInfo>(); //browser.getFileSet();
    Set<FileInfo> filesToRequest = strategy.createExpiredFileSet(clientFileSet, browserFileSet);
    if (!filesToRequest.isEmpty()) {
      if (browser.getBrowserInfo().getName().contains("Safari")
          || browser.getBrowserInfo().getName().contains("Opera")
          || browser.getBrowserInfo().getName().contains("Konqueror")) {
        filesToRequest.clear();
        for (FileInfo info : clientFileSet) {
          filesToRequest.add(info);
        }
      }
      Set<FileInfo> filteredFilesToRequest = filterServeOnlyFiles(filesToRequest);

      writer.write(gson.toJson(filteredFilesToRequest));
    }
    writer.flush();
  }

  private Set<FileInfo> filterServeOnlyFiles(Set<FileInfo> filesToRequest) {
    Set<FileInfo> filteredFilesToRequest = new LinkedHashSet<FileInfo>();
    Set<String> cachedFiles = filesCache.getAllFileNames();

    for (FileInfo fileInfo : filesToRequest) {
      if (!fileInfo.isServeOnly()
          || !cachedFiles.contains(fileInfo.getFileName())
          || filesCache.getFileInfo(fileInfo.getFileName()).getTimestamp() < fileInfo
          .getTimestamp()) {
        filteredFilesToRequest.add(fileInfo);
      }
    }
    return filteredFilesToRequest;
  }

  public void update(Observable o, Object arg) {
    BrowserCaptureEvent captureEvent = (BrowserCaptureEvent) arg;
    if (captureEvent.event == Event.CONNECTED) {
      locks.put(captureEvent.getBrowser().getId(), new Lock());
    }
  }

  public void uploadFiles(String id, String data) {
    Collection<FileInfo> filesData = gson.fromJson(data, new TypeToken<Collection<FileInfo>>()
        {}.getType());

    for (FileInfo f : filesData) {
      String path = f.getFileName();
      try {
        // Big hack, don't ask my why, but this works on fks.sk
        // Problem is that gson.fromJson mess up encoding.
        CharsetEncoder encoder = Charset.forName("UTF-8").newEncoder();
        CharsetDecoder decoder = Charset.forName("ISO-8859-1").newDecoder();

        ByteBuffer bbuf = encoder.encode(CharBuffer.wrap(f.getData()));
        CharBuffer cbuf = decoder.decode(bbuf);

        String utf = cbuf.toString();
        f.setData(utf);
        filesCache.addFile(path, f);
      } catch (IOException e) {
      }
    }
  }
}
