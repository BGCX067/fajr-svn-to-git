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

import static java.lang.String.format;

import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.google.jstestdriver.JsonCommand.CommandType;
import com.google.jstestdriver.Response.ResponseType;
import com.google.jstestdriver.browser.BrowserPanicException;
import com.google.jstestdriver.util.StopWatch;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import java.io.File;
import java.util.ArrayList;
import java.util.Collection;
import java.util.LinkedHashMap;
import java.util.LinkedHashSet;
import java.util.LinkedList;
import java.util.List;
import java.util.Map;
import java.util.Set;

/**
 * Handles the communication of a command to the JsTestDriverServer from the
 * JsTestDriverClient.
 * 
 * @author jeremiele@google.com (Jeremie Lenfant-Engelmann)
 */
public class CommandTask {

  private static final Logger logger = LoggerFactory.getLogger(CommandTask.class);

  private static final List<String> EMPTY_ARRAYLIST = new ArrayList<String>();
  private static final long WAIT_INTERVAL = 500L;
  public static final int CHUNK_SIZE = 50;

  private final Gson gson = new Gson();

  private final JsTestDriverFileFilter filter;
  private final ResponseStream stream;
  private final Set<FileInfo> fileSet;
  private final String baseUrl;
  private final Server server;
  private final Map<String, String> params;
  private final HeartBeatManager heartBeatManager;
  private final FileLoader fileLoader;
  private final boolean upload;

  private final StopWatch stopWatch;

  public CommandTask(JsTestDriverFileFilter filter, ResponseStream stream, Set<FileInfo> fileSet,
      String baseUrl, Server server, Map<String, String> params, HeartBeatManager heartBeatManager,
      FileLoader fileLoader, boolean upload, StopWatch stopWatch) {
    this.filter = filter;
    this.stream = stream;
    this.fileSet = fileSet;
    this.baseUrl = baseUrl;
    this.server = server;
    this.params = params;
    this.heartBeatManager = heartBeatManager;
    this.fileLoader = fileLoader;
    this.upload = upload;
    this.stopWatch = stopWatch;
  }

  private String startSession() {
    String browserId = params.get("id");
    String sessionId = server.startSession(baseUrl, browserId);

    if ("FAILED".equals(sessionId)) {
      while ("FAILED".equals(sessionId)) {
        try {
          Thread.sleep(WAIT_INTERVAL);
        } catch (InterruptedException e) {
          logger.error("Could not create session for browser: " + browserId);
          return "";
        }
        sessionId = server.startSession(baseUrl, browserId);
      }
    }
    return sessionId;
  }

  private void stopSession(String sessionId) {
    server.stopSession(baseUrl, params.get("id"), sessionId);
  }

  /**
   * Throws an exception if the expected browser is not available for this task.
   */
  private void checkBrowser() {
    String alive = server.fetch(baseUrl + "/heartbeat?id=" + params.get("id"));

    if (!alive.equals("OK")) {
      throw new FailureException(
          format("Browser is not available\n {} \nfor\n {}", alive, params));
    }
  }

  // TODO(corysmith): remove this function once FileInfo is used exclusively.
  // Hate static crap.
  public static FileSource fileInfoToFileSource(FileInfo info) {
    if (info.getFileName().startsWith("http://")) {
      return new FileSource(info.getFileName(), info.getTimestamp());
    }
    return new FileSource("/test/" + info.getFileName(), info.getTimestamp());
  }

  /**
   * @param response The response that might be a panic.
   * @param during A string that indicates when the browser paniced.
   */
  private void shouldPanic(Response response, String during) {
    if (response.getResponseType() == ResponseType.BROWSER_PANIC) {
      BrowserPanic panic = gson.fromJson(response.getResponse(),
                                         response.getResponseType().type);
      BrowserPanicException exception =
          new BrowserPanicException(panic.getBrowserInfo(),
                                    during);
      logger.error("Browser not found : {}\n during: {} \n Exception: {}",
                   new Object[]{response.getResponse(),
                                during,
                                exception});
      throw exception;
    }
  }

  private void uploadFileSet() {
    Map<String, String> fileSetParams = new LinkedHashMap<String, String>();

    fileSetParams.put("id", params.get("id"));
    fileSetParams.put("fileSet", gson.toJson(fileSet));
    String postResult = server.post(baseUrl + "/fileSet", fileSetParams);

    if (postResult.length() > 0) {
      Collection<FileInfo> filesToUpload =
          gson.fromJson(postResult, new TypeToken<Collection<FileInfo>>() {}.getType());
      // should reset if the files are the same, because there could be other files on
      // the server.
      boolean shouldReset = sameFiles(filesToUpload, fileSet);
      Set<FileInfo> finalFilesToUpload = new LinkedHashSet<FileInfo>();

      if (shouldReset) {
        JsonCommand cmd = new JsonCommand(CommandType.RESET, EMPTY_ARRAYLIST);
        Map<String, String> resetParams = new LinkedHashMap<String, String>();

        resetParams.put("id", params.get("id"));
        resetParams.put("data", gson.toJson(cmd));
        server.post(baseUrl + "/cmd", resetParams);

        logger.debug("Starting File Upload Refresh for {}", params.get("id"));
        String jsonResponse = server.fetch(baseUrl + "/cmd?id=" + params.get("id"));
        StreamMessage message = gson.fromJson(jsonResponse, StreamMessage.class);
        Response response = message.getResponse();
        shouldPanic(response, "File upload reset");
        logger.debug("Finished File Upload Refresh for {}", params.get("id"));

        finalFilesToUpload.addAll(filesToUpload);
      } else {
        for (FileInfo file : filesToUpload) {
          finalFilesToUpload.addAll(findDependencies(file));
        }
      }
      List<FileInfo> loadedfiles = fileLoader.loadFiles(finalFilesToUpload, shouldReset);
      Map<String, String> uploadFileParams = new LinkedHashMap<String, String>();

      uploadFileParams.put("id", params.get("id"));
      uploadFileParams.put("data", gson.toJson(loadedfiles));
      server.post(baseUrl + "/fileSet", uploadFileParams);
      List<FileSource> filesSrc = new LinkedList<FileSource>(filterFilesToLoad(loadedfiles));
      int numberOfFilesToLoad = filesSrc.size();

      for (int i = 0; i < numberOfFilesToLoad; i += CHUNK_SIZE) {
        int chunkEndIndex = Math.min(i + CHUNK_SIZE, numberOfFilesToLoad);
        List<String> loadParameters = new LinkedList<String>();
        List<FileSource> filesToLoad = filesSrc.subList(i, chunkEndIndex);

        loadParameters.add(gson.toJson(filesToLoad));
        loadParameters.add("false");
        JsonCommand cmd = new JsonCommand(CommandType.LOADTEST, loadParameters);
        Map<String, String> loadFileParams = new LinkedHashMap<String, String>();

        loadFileParams.put("id", params.get("id"));
        loadFileParams.put("data", gson.toJson(cmd));
        logger.debug("Sending LOADTEST for {}", params.get("id"));
        server.post(baseUrl + "/cmd", loadFileParams);
        String jsonResponse = server.fetch(baseUrl + "/cmd?id=" + params.get("id"));
        StreamMessage message = gson.fromJson(jsonResponse, StreamMessage.class);
        Response response = message.getResponse();
        logger.debug("LOADTEST finished for ({}) {}", params.get("id"), response.getBrowser());

        shouldPanic(response, "Loading files into the browser.");
        stream.stream(response);
      }
    }
  }

  private Collection<FileInfo> findDependencies(FileInfo file) {
    List<FileInfo> deps = new LinkedList<FileInfo>();

    // TODO(jeremiele): replace filter with a plugin
    for (String fileName : filter.resolveFilesDeps(file.getFileName())) {
      deps.add(new FileInfo(fileName, new File(fileName).lastModified(), false, false, null));
    }
    return deps;
  }

  private List<FileSource> filterFilesToLoad(Collection<FileInfo> fileInfos) {
    List<FileSource> filteredFileSources = new LinkedList<FileSource>();

    for (FileInfo fileInfo : fileInfos) {
      if (!fileInfo.isServeOnly()) {
        filteredFileSources.add(fileInfoToFileSource(fileInfo));
      }
    }
    return filteredFileSources;
  }

  public void run() {
    heartBeatManager.startTimer();
    String browserId = params.get("id");
    String sessionId = null;

    try {
      sessionId = startSession();

      if (!"".equals(sessionId)) {
        heartBeatManager.startHeartBeat(baseUrl, browserId, sessionId);
      } else {
        throw new FailureException("Can't start a session on the server!" + params);
      }
      checkBrowser();

      logger.debug("Starting upload for {}", browserId);
      if (upload) {
        stopWatch.start("upload");
        uploadFileSet();
        stopWatch.stop("upload");
      }
      logger.debug("Finished upload for {}", browserId);
      server.post(baseUrl + "/cmd", params);
      StreamMessage streamMessage = null;

      stopWatch.start("Command %s", params.get("data"));
      logger.debug("Starting {} for {}", params.get("data"), browserId);
      do {
        String response = server.fetch(baseUrl + "/cmd?id=" + browserId);
        streamMessage = gson.fromJson(response, StreamMessage.class);
        Response resObj = streamMessage.getResponse();

        shouldPanic(resObj, "execution of command");
        stream.stream(resObj);
      } while (!streamMessage.isLast());
      stopWatch.stop("Command %s", params.get("data"));
    } finally {
      heartBeatManager.cancelTimer();
      stopSession(sessionId);
      logger.debug("finished {} for {}", params.get("data"), browserId);
    }
  }

  private boolean sameFiles(Collection<FileInfo> filesToUpload, Collection<FileInfo> fileSet) {
    return true; // hack by ppershing, force browser to realod files (load them in correct order)
    /*
    for (FileInfo info : fileSet) {
      if (!info.isServeOnly() && !filesToUpload.contains(info)) {
        return false;
      }
    }
    return true;
    */
  }
}
