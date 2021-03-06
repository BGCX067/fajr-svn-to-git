#!/usr/bin/python
# Copyright (c) 2006-2009 The Chromium Authors. All rights reserved.
# Use of this source code is governed by a BSD-style license that can be
# found in the LICENSE file.

"""\
Wrapper script around Rietveld's upload.py that simplifies working with groups
of files.
"""

import getpass
import os
import random
import re
import string
import subprocess
import sys
import tempfile
import time
from third_party import upload
import urllib2

__pychecker__ = 'unusednames=breakpad'
import breakpad
__pychecker__ = ''

# gcl now depends on gclient.
from scm import SVN
import gclient_utils

__version__ = '1.2'


CODEREVIEW_SETTINGS = {
  # To make gcl send reviews to a server, check in a file named
  # "codereview.settings" (see |CODEREVIEW_SETTINGS_FILE| below) to your
  # project's base directory and add the following line to codereview.settings:
  # CODE_REVIEW_SERVER: codereview.yourserver.org
}

# globals that store the root of the current repository and the directory where
# we store information about changelists.
REPOSITORY_ROOT = ""

# Filename where we store repository specific information for gcl.
CODEREVIEW_SETTINGS_FILE = "codereview.settings"
CODEREVIEW_SETTINGS_FILE_NOT_FOUND = (
    'No %s file found. Please add one.' % CODEREVIEW_SETTINGS_FILE)

# Warning message when the change appears to be missing tests.
MISSING_TEST_MSG = "Change contains new or modified methods, but no new tests!"

# Global cache of files cached in GetCacheDir().
FILES_CACHE = {}

# Valid extensions for files we want to lint.
DEFAULT_LINT_REGEX = r"(.*\.cpp|.*\.cc|.*\.h)"
DEFAULT_LINT_IGNORE_REGEX = r"$^"


def CheckHomeForFile(filename):
  """Checks the users home dir for the existence of the given file.  Returns
  the path to the file if it's there, or None if it is not.
  """
  home_vars = ['HOME']
  if sys.platform in ('cygwin', 'win32'):
    home_vars.append('USERPROFILE')
  for home_var in home_vars:
    home = os.getenv(home_var)
    if home != None:
      full_path = os.path.join(home, filename)
      if os.path.exists(full_path):
        return full_path
  return None


def UnknownFiles():
  """Runs svn status and returns unknown files."""
  return [item[1] for item in SVN.CaptureStatus([]) if item[0][0] == '?']


def GetRepositoryRoot():
  """Returns the top level directory of the current repository.

  The directory is returned as an absolute path.
  """
  global REPOSITORY_ROOT
  if not REPOSITORY_ROOT:
    REPOSITORY_ROOT = SVN.GetCheckoutRoot(os.getcwd())
    if not REPOSITORY_ROOT:
      raise gclient_utils.Error("gcl run outside of repository")
  return REPOSITORY_ROOT


def GetInfoDir():
  """Returns the directory where gcl info files are stored."""
  return os.path.join(GetRepositoryRoot(), '.svn', 'gcl_info')


def GetChangesDir():
  """Returns the directory where gcl change files are stored."""
  return os.path.join(GetInfoDir(), 'changes')


def GetCacheDir():
  """Returns the directory where gcl change files are stored."""
  return os.path.join(GetInfoDir(), 'cache')


def GetCachedFile(filename, max_age=60*60*24*3, use_root=False):
  """Retrieves a file from the repository and caches it in GetCacheDir() for
  max_age seconds.

  use_root: If False, look up the arborescence for the first match, otherwise go
            directory to the root repository.

  Note: The cache will be inconsistent if the same file is retrieved with both
        use_root=True and use_root=False. Don't be stupid.
  """
  if filename not in FILES_CACHE:
    # Don't try to look up twice.
    FILES_CACHE[filename] = None
    # First we check if we have a cached version.
    try:
      cached_file = os.path.join(GetCacheDir(), filename)
    except gclient_utils.Error:
      return None
    if (not os.path.exists(cached_file) or
        (time.time() - os.stat(cached_file).st_mtime) > max_age):
      dir_info = SVN.CaptureInfo('.')
      repo_root = dir_info['Repository Root']
      if use_root:
        url_path = repo_root
      else:
        url_path = dir_info['URL']
      while True:
        # Look in the repository at the current level for the file.
        for _ in range(5):
          content = None
          try:
            # Take advantage of the fact that svn won't output to stderr in case
            # of success but will do in case of failure so don't mind putting
            # stderr into content_array.
            content_array = []
            svn_path = url_path + '/' + filename
            args = ['svn', 'cat', svn_path]
            if sys.platform != 'darwin':
              # MacOSX 10.5.2 has a bug with svn 1.4.4 that will trigger the
              # 'Can\'t get username or password' and can be fixed easily.
              # The fix doesn't work if the user upgraded to svn 1.6.x. Bleh.
              # I don't have time to fix their broken stuff.
              args.append('--non-interactive')
            gclient_utils.CheckCallAndFilter(
                args, cwd='.', filter_fn=content_array.append)
            # Exit the loop if the file was found. Override content.
            content = '\n'.join(content_array)
            break
          except gclient_utils.Error:
            if content_array[0].startswith(
                'svn: Can\'t get username or password'):
              ErrorExit('Your svn credentials expired. Please run svn update '
                        'to fix the cached credentials')
            if content_array[0].startswith('svn: Can\'t get password'):
              ErrorExit('If are using a Mac and svn --version shows 1.4.x, '
                  'please hack gcl.py to remove --non-interactive usage, it\'s'
                  'a bug on your installed copy')
            if not content_array[0].startswith('svn: File not found:'):
              # Try again.
              continue
        if content:
          break
        if url_path == repo_root:
          # Reached the root. Abandoning search.
          break
        # Go up one level to try again.
        url_path = os.path.dirname(url_path)
      if content is not None or filename != CODEREVIEW_SETTINGS_FILE:
        # Write a cached version even if there isn't a file, so we don't try to
        # fetch it each time. codereview.settings must always be present so do
        # not cache negative.
        gclient_utils.FileWrite(cached_file, content or '')
    else:
      content = gclient_utils.FileRead(cached_file, 'r')
    # Keep the content cached in memory.
    FILES_CACHE[filename] = content
  return FILES_CACHE[filename]


def GetCodeReviewSetting(key):
  """Returns a value for the given key for this repository."""
  # Use '__just_initialized' as a flag to determine if the settings were
  # already initialized.
  if '__just_initialized' not in CODEREVIEW_SETTINGS:
    settings_file = GetCachedFile(CODEREVIEW_SETTINGS_FILE)
    if settings_file:
      for line in settings_file.splitlines():
        if not line or line.startswith('#'):
          continue
        if not ':' in line:
          raise gclient_utils.Error(
              '%s is invalid, please fix. It\'s content:\n\n%s' %
                  (CODEREVIEW_SETTINGS_FILE, settings_file))
        k, v = line.split(':', 1)
        CODEREVIEW_SETTINGS[k.strip()] = v.strip()
    CODEREVIEW_SETTINGS.setdefault('__just_initialized', None)
  return CODEREVIEW_SETTINGS.get(key, "")


def Warn(msg):
  print >> sys.stderr, msg


def ErrorExit(msg):
  print >> sys.stderr, msg
  sys.exit(1)


def RunShellWithReturnCode(command, print_output=False):
  """Executes a command and returns the output and the return code."""
  p = gclient_utils.Popen(command, stdout=subprocess.PIPE,
                          stderr=subprocess.STDOUT, universal_newlines=True)
  if print_output:
    output_array = []
    while True:
      line = p.stdout.readline()
      if not line:
        break
      if print_output:
        print line.strip('\n')
      output_array.append(line)
    output = "".join(output_array)
  else:
    output = p.stdout.read()
  p.wait()
  p.stdout.close()
  return output, p.returncode


def RunShell(command, print_output=False):
  """Executes a command and returns the output."""
  return RunShellWithReturnCode(command, print_output)[0]


def FilterFlag(args, flag):
  """Returns True if the flag is present in args list.

  The flag is removed from args if present.
  """
  if flag in args:
    args.remove(flag)
    return True
  return False


class ChangeInfo(object):
  """Holds information about a changelist.

    name: change name.
    issue: the Rietveld issue number or 0 if it hasn't been uploaded yet.
    patchset: the Rietveld latest patchset number or 0.
    description: the description.
    files: a list of 2 tuple containing (status, filename) of changed files,
           with paths being relative to the top repository directory.
    local_root: Local root directory
  """

  _SEPARATOR = "\n-----\n"
  # The info files have the following format:
  # issue_id, patchset\n   (, patchset is optional)
  # _SEPARATOR\n
  # filepath1\n
  # filepath2\n
  # .
  # .
  # filepathn\n
  # _SEPARATOR\n
  # description

  def __init__(self, name, issue, patchset, description, files, local_root,
               needs_upload=False):
    self.name = name
    self.issue = int(issue)
    self.patchset = int(patchset)
    self.description = description
    if files is None:
      files = []
    self._files = files
    self.patch = None
    self._local_root = local_root
    self.needs_upload = needs_upload

  def NeedsUpload(self):
    return self.needs_upload

  def GetFileNames(self):
    """Returns the list of file names included in this change."""
    return [f[1] for f in self._files]

  def GetFiles(self):
    """Returns the list of files included in this change with their status."""
    return self._files

  def GetLocalRoot(self):
    """Returns the local repository checkout root directory."""
    return self._local_root

  def Exists(self):
    """Returns True if this change already exists (i.e., is not new)."""
    return (self.issue or self.description or self._files)

  def _NonDeletedFileList(self):
    """Returns a list of files in this change, not including deleted files."""
    return [f[1] for f in self.GetFiles()
            if not f[0].startswith("D")]

  def _AddedFileList(self):
    """Returns a list of files added in this change."""
    return [f[1] for f in self.GetFiles() if f[0].startswith("A")]

  def Save(self):
    """Writes the changelist information to disk."""
    if self.NeedsUpload():
      needs_upload = "dirty"
    else:
      needs_upload = "clean"
    data = ChangeInfo._SEPARATOR.join([
        "%d, %d, %s" % (self.issue, self.patchset, needs_upload),
        "\n".join([f[0] + f[1] for f in self.GetFiles()]),
        self.description])
    gclient_utils.FileWrite(GetChangelistInfoFile(self.name), data)

  def Delete(self):
    """Removes the changelist information from disk."""
    os.remove(GetChangelistInfoFile(self.name))

  def CloseIssue(self):
    """Closes the Rietveld issue for this changelist."""
    data = [("description", self.description),
            ("xsrf_token", GetXsrfToken())]
    ctype, body = upload.EncodeMultipartFormData(data, [])
    SendToRietveld("/%d/close" % self.issue, body, ctype)

  def UpdateRietveldDescription(self):
    """Sets the description for an issue on Rietveld."""
    data = [("description", self.description),]
    ctype, body = upload.EncodeMultipartFormData(data, [])
    SendToRietveld("/%d/description" % self.issue, body, ctype)

  def MissingTests(self):
    """Returns True if the change looks like it needs unit tests but has none.

    A change needs unit tests if it contains any new source files or methods.
    """
    SOURCE_SUFFIXES = [".cc", ".cpp", ".c", ".m", ".mm"]
    # Ignore third_party entirely.
    files = [f for f in self._NonDeletedFileList()
             if f.find("third_party") == -1]
    added_files = [f for f in self._AddedFileList()
                   if f.find("third_party") == -1]

    # If the change is entirely in third_party, we're done.
    if len(files) == 0:
      return False

    # Any new or modified test files?
    # A test file's name ends with "test.*" or "tests.*".
    test_files = [test for test in files
                  if os.path.splitext(test)[0].rstrip("s").endswith("test")]
    if len(test_files) > 0:
      return False

    # Any new source files?
    source_files = [item for item in added_files
                    if os.path.splitext(item)[1] in SOURCE_SUFFIXES]
    if len(source_files) > 0:
      return True

    # Do the long test, checking the files for new methods.
    return self._HasNewMethod()

  def _HasNewMethod(self):
    """Returns True if the changeset contains any new functions, or if a
    function signature has been changed.

    A function is identified by starting flush left, containing a "(" before
    the next flush-left line, and either ending with "{" before the next
    flush-left line or being followed by an unindented "{".

    Currently this returns True for new methods, new static functions, and
    methods or functions whose signatures have been changed.

    Inline methods added to header files won't be detected by this. That's
    acceptable for purposes of determining if a unit test is needed, since
    inline methods should be trivial.
    """
    # To check for methods added to source or header files, we need the diffs.
    # We'll generate them all, since there aren't likely to be many files
    # apart from source and headers; besides, we'll want them all if we're
    # uploading anyway.
    if self.patch is None:
      self.patch = GenerateDiff(self.GetFileNames())

    definition = ""
    for line in self.patch.splitlines():
      if not line.startswith("+"):
        continue
      line = line.strip("+").rstrip(" \t")
      # Skip empty lines, comments, and preprocessor directives.
      # TODO(pamg): Handle multiline comments if it turns out to be a problem.
      if line == "" or line.startswith("/") or line.startswith("#"):
        continue

      # A possible definition ending with "{" is complete, so check it.
      if definition.endswith("{"):
        if definition.find("(") != -1:
          return True
        definition = ""

      # A { or an indented line, when we're in a definition, continues it.
      if (definition != "" and
          (line == "{" or line.startswith(" ") or line.startswith("\t"))):
        definition += line

      # A flush-left line starts a new possible function definition.
      elif not line.startswith(" ") and not line.startswith("\t"):
        definition = line

    return False

  @staticmethod
  def Load(changename, local_root, fail_on_not_found, update_status):
    """Gets information about a changelist.

    Args:
      fail_on_not_found: if True, this function will quit the program if the
        changelist doesn't exist.
      update_status: if True, the svn status will be updated for all the files
        and unchanged files will be removed.

    Returns: a ChangeInfo object.
    """
    info_file = GetChangelistInfoFile(changename)
    if not os.path.exists(info_file):
      if fail_on_not_found:
        ErrorExit("Changelist " + changename + " not found.")
      return ChangeInfo(changename, 0, 0, '', None, local_root,
                        needs_upload=False)
    split_data = gclient_utils.FileRead(info_file, 'r').split(
        ChangeInfo._SEPARATOR, 2)
    if len(split_data) != 3:
      ErrorExit("Changelist file %s is corrupt" % info_file)
    items = split_data[0].split(', ')
    issue = 0
    patchset = 0
    needs_upload = False
    if items[0]:
      issue = int(items[0])
    if len(items) > 1:
      patchset = int(items[1])
    if len(items) > 2:
      needs_upload = (items[2] == "dirty")
    files = []
    for line in split_data[1].splitlines():
      status = line[:7]
      filename = line[7:]
      files.append((status, filename))
    description = split_data[2]
    save = False
    if update_status:
      for item in files:
        filename = os.path.join(local_root, item[1])
        status_result = SVN.CaptureStatus(filename)
        if not status_result or not status_result[0][0]:
          # File has been reverted.
          save = True
          files.remove(item)
          continue
        status = status_result[0][0]
        if status != item[0]:
          save = True
          files[files.index(item)] = (status, item[1])
    change_info = ChangeInfo(changename, issue, patchset, description, files,
                             local_root, needs_upload)
    if save:
      change_info.Save()
    return change_info


def GetChangelistInfoFile(changename):
  """Returns the file that stores information about a changelist."""
  if not changename or re.search(r'[^\w-]', changename):
    ErrorExit("Invalid changelist name: " + changename)
  return os.path.join(GetChangesDir(), changename)


def LoadChangelistInfoForMultiple(changenames, local_root, fail_on_not_found,
                                  update_status):
  """Loads many changes and merge their files list into one pseudo change.

  This is mainly usefull to concatenate many changes into one for a 'gcl try'.
  """
  changes = changenames.split(',')
  aggregate_change_info = ChangeInfo(changenames, 0, 0, '', None, local_root,
                                     needs_upload=False)
  for change in changes:
    aggregate_change_info._files += ChangeInfo.Load(change,
                                                    local_root,
                                                    fail_on_not_found,
                                                    update_status).GetFiles()
  return aggregate_change_info


def GetCLs():
  """Returns a list of all the changelists in this repository."""
  cls = os.listdir(GetChangesDir())
  if CODEREVIEW_SETTINGS_FILE in cls:
    cls.remove(CODEREVIEW_SETTINGS_FILE)
  return cls


def GenerateChangeName():
  """Generate a random changelist name."""
  random.seed()
  current_cl_names = GetCLs()
  while True:
    cl_name = (random.choice(string.ascii_lowercase) +
               random.choice(string.digits) +
               random.choice(string.ascii_lowercase) +
               random.choice(string.digits))
    if cl_name not in current_cl_names:
      return cl_name


def GetModifiedFiles():
  """Returns a set that maps from changelist name to (status,filename) tuples.

  Files not in a changelist have an empty changelist name.  Filenames are in
  relation to the top level directory of the current repository.  Note that
  only the current directory and subdirectories are scanned, in order to
  improve performance while still being flexible.
  """
  files = {}

  # Since the files are normalized to the root folder of the repositary, figure
  # out what we need to add to the paths.
  dir_prefix = os.getcwd()[len(GetRepositoryRoot()):].strip(os.sep)

  # Get a list of all files in changelists.
  files_in_cl = {}
  for cl in GetCLs():
    change_info = ChangeInfo.Load(cl, GetRepositoryRoot(),
                                  fail_on_not_found=True, update_status=False)
    for status, filename in change_info.GetFiles():
      files_in_cl[filename] = change_info.name

  # Get all the modified files.
  status_result = SVN.CaptureStatus(None)
  for line in status_result:
    status = line[0]
    filename = line[1]
    if status[0] == "?":
      continue
    if dir_prefix:
      filename = os.path.join(dir_prefix, filename)
    change_list_name = ""
    if filename in files_in_cl:
      change_list_name = files_in_cl[filename]
    files.setdefault(change_list_name, []).append((status, filename))

  return files


def GetFilesNotInCL():
  """Returns a list of tuples (status,filename) that aren't in any changelists.

  See docstring of GetModifiedFiles for information about path of files and
  which directories are scanned.
  """
  modified_files = GetModifiedFiles()
  if "" not in modified_files:
    return []
  return modified_files[""]

# Newer versions of Rietveld require us to pass an XSRF token to POST, so
# we fetch it from the server.  (The version used by Chromium has been
# modified so the token isn't required when closing an issue.)
def GetXsrfToken(timeout=None):
  server = GetCodeReviewSetting("CODE_REVIEW_SERVER")
  def GetUserCredentials():
    """Prompts the user for a username and password."""
    email = upload.GetEmail("Email (login for uploading to %s)" % server)
    password = getpass.getpass("Password for %s: " % email)
    return email, password
  rpc_server = upload.HttpRpcServer(server,
                                    GetUserCredentials,
                                    save_cookies=True)
  try:
    return rpc_server.Send('/xsrf_token',
                           extra_headers={'X-Requesting-XSRF-Token': '1'})
  except urllib2.URLError as e:
    return None

def SendToRietveld(request_path, payload=None,
                   content_type="application/octet-stream", timeout=None):
  """Send a POST/GET to Rietveld.  Returns the response body."""
  server = GetCodeReviewSetting("CODE_REVIEW_SERVER")
  if not server:
    ErrorExit(CODEREVIEW_SETTINGS_FILE_NOT_FOUND)
  def GetUserCredentials():
    """Prompts the user for a username and password."""
    email = upload.GetEmail("Email (login for uploading to %s)" % server)
    password = getpass.getpass("Password for %s: " % email)
    return email, password
  rpc_server = upload.HttpRpcServer(server,
                                    GetUserCredentials,
                                    save_cookies=True)

  try:
    return rpc_server.Send(request_path, payload, content_type, timeout)
  except urllib2.URLError as e:
    print "'%s'" % server
    print "'%s'" % request_path
    print e
    if timeout is None:
      ErrorExit("Error accessing url %s" % request_path)
    else:
      return None


def GetIssueDescription(issue):
  """Returns the issue description from Rietveld."""
  return SendToRietveld("/%d/description" % issue)


def ListFiles(show_unknown_files):
  files = GetModifiedFiles()
  cl_keys = files.keys()
  cl_keys.sort()
  for cl_name in cl_keys:
    if not cl_name:
      continue
    note = ""
    change_info = ChangeInfo.Load(cl_name, GetRepositoryRoot(),
                                  fail_on_not_found=True, update_status=False)
    if len(change_info.GetFiles()) != len(files[cl_name]):
      note = " (Note: this changelist contains files outside this directory)"
    print "\n--- Changelist " + cl_name + note + ":"
    for filename in files[cl_name]:
      print "".join(filename)
  if show_unknown_files:
    unknown_files = UnknownFiles()
  if (files.get('') or (show_unknown_files and len(unknown_files))):
    print "\n--- Not in any changelist:"
    for item in files.get('', []):
      print "".join(item)
    if show_unknown_files:
      for filename in unknown_files:
        print "?      %s" % filename
  return 0


def GetEditor():
  editor = os.environ.get("SVN_EDITOR")
  if not editor:
    editor = os.environ.get("EDITOR")

  if not editor:
    if sys.platform.startswith("win"):
      editor = "notepad"
    else:
      editor = "vi"

  return editor


def GenerateDiff(files, root=None):
  return SVN.GenerateDiff(files, root=root)


def OptionallyDoPresubmitChecks(change_info, committing, args):
  if FilterFlag(args, "--no_presubmit") or FilterFlag(args, "--force"):
    return True
  return DoPresubmitChecks(change_info, committing, True)


def defer_attributes(a, b):
  """Copy attributes from an object (like a function) to another."""
  for x in dir(a):
    if not getattr(b, x, None):
      setattr(b, x, getattr(a, x))


def need_change(function):
  """Converts args -> change_info."""
  def hook(args):
    if not len(args) == 1:
      ErrorExit("You need to pass a change list name")
    change_info = ChangeInfo.Load(args[0], GetRepositoryRoot(), True, True)
    return function(change_info)
  defer_attributes(function, hook)
  hook.need_change = True
  hook.no_args = True
  return hook


def need_change_and_args(function):
  """Converts args -> change_info."""
  def hook(args):
    if not args:
      ErrorExit("You need to pass a change list name")
    change_info = ChangeInfo.Load(args.pop(0), GetRepositoryRoot(), True, True)
    return function(change_info, args)
  defer_attributes(function, hook)
  hook.need_change = True
  return hook


def no_args(function):
  """Make sure no args are passed."""
  def hook(args):
    if args:
      ErrorExit("Doesn't support arguments")
    return function()
  defer_attributes(function, hook)
  hook.no_args = True
  return hook


def attrs(**kwargs):
  """Decorate a function with new attributes."""
  def decorate(function):
    for k in kwargs:
      setattr(function, k, kwargs[k])
    return function
  return decorate


@no_args
def CMDopened():
  """Lists modified files in the current directory down."""
  return ListFiles(False)


@no_args
def CMDstatus():
  """Lists modified and unknown files in the current directory down."""
  return ListFiles(True)


@need_change_and_args
@attrs(usage='[--no_presubmit] [--no_watchlists]')
def CMDupload(change_info, args):
  """Uploads the changelist to the server for review.

  This does not submit a try job; use gcl try to submit a try job.
  """
  if '-s' in args or '--server' in args:
    ErrorExit('Don\'t use the -s flag, fix codereview.settings instead')
  if not change_info.GetFiles():
    print "Nothing to upload, changelist is empty."
    return 0
  if not OptionallyDoPresubmitChecks(change_info, False, args):
    return 1
  no_watchlists = (FilterFlag(args, "--no_watchlists") or
                   FilterFlag(args, "--no-watchlists"))

  # Map --send-mail to --send_mail
  if FilterFlag(args, "--send-mail"):
    args.append("--send_mail")

  upload_arg = ["upload.py", "-y"]
  server = GetCodeReviewSetting("CODE_REVIEW_SERVER")
  if not server:
    ErrorExit(CODEREVIEW_SETTINGS_FILE_NOT_FOUND)
  upload_arg.append("--server=%s" % server)
  upload_arg.extend(args)

  desc_file = ""
  if change_info.issue:  # Uploading a new patchset.
    found_message = False
    for arg in args:
      if arg.startswith("--message") or arg.startswith("-m"):
        found_message = True
        break

    if not found_message:
      upload_arg.append("--message=''")

    upload_arg.append("--issue=%d" % change_info.issue)
  else: # First time we upload.
    handle, desc_file = tempfile.mkstemp(text=True)
    os.write(handle, change_info.description)
    os.close(handle)

    # Watchlist processing -- CC people interested in this changeset
    # http://dev.chromium.org/developers/contributing-code/watchlists
    if not no_watchlists:
      import watchlists
      watchlist = watchlists.Watchlists(change_info.GetLocalRoot())
      watchers = watchlist.GetWatchersForPaths(change_info.GetFileNames())

    cc_list = GetCodeReviewSetting("CC_LIST")
    if not no_watchlists and watchers:
      # Filter out all empty elements and join by ','
      cc_list = ','.join(filter(None, [cc_list] + watchers))
    if cc_list:
      upload_arg.append("--cc=" + cc_list)
    upload_arg.append("--description_file=" + desc_file + "")
    if change_info.description:
      subject = change_info.description[:77]
      if subject.find("\r\n") != -1:
        subject = subject[:subject.find("\r\n")]
      if subject.find("\n") != -1:
        subject = subject[:subject.find("\n")]
      if len(change_info.description) > 77:
        subject = subject + "..."
      upload_arg.append("--message=" + subject)

    if GetCodeReviewSetting("PRIVATE") == "True":
      upload_arg.append("--private")

  # Change the current working directory before calling upload.py so that it
  # shows the correct base.
  previous_cwd = os.getcwd()
  os.chdir(change_info.GetLocalRoot())

  # If we have a lot of files with long paths, then we won't be able to fit
  # the command to "svn diff".  Instead, we generate the diff manually for
  # each file and concatenate them before passing it to upload.py.
  if change_info.patch is None:
    change_info.patch = GenerateDiff(change_info.GetFileNames())
  issue, patchset = upload.RealMain(upload_arg, change_info.patch)
  if issue and patchset:
    change_info.issue = int(issue)
    change_info.patchset = int(patchset)
    change_info.Save()

  if desc_file:
    os.remove(desc_file)

  # Do background work on Rietveld to lint the file so that the results are
  # ready when the issue is viewed.
  SendToRietveld("/lint/issue%s_%s" % (issue, patchset), timeout=0.5)

  # Move back before considering try, so GetCodeReviewSettings is
  # consistent.
  os.chdir(previous_cwd)

  print "*** Upload does not submit a try; use gcl try to submit a try. ***"

  return 0


@need_change
def CMDpresubmit(change_info):
  """Runs presubmit checks on the change.

  The actual presubmit code is implemented in presubmit_support.py and looks
  for PRESUBMIT.py files."""
  if not change_info.GetFiles():
    print "Nothing to presubmit check, changelist is empty."
    return 0

  print "*** Presubmit checks for UPLOAD would report: ***"
  result = DoPresubmitChecks(change_info, False, False)

  print "\n*** Presubmit checks for COMMIT would report: ***"
  result &= DoPresubmitChecks(change_info, True, False)
  return not result


def TryChange(change_info, args, swallow_exception):
  """Create a diff file of change_info and send it to the try server."""
  try:
    import trychange
  except ImportError:
    if swallow_exception:
      return 1
    ErrorExit("You need to install trychange.py to use the try server.")

  trychange_args = []
  if change_info:
    trychange_args.extend(['--name', change_info.name])
    if change_info.issue:
      trychange_args.extend(["--issue", str(change_info.issue)])
    if change_info.patchset:
      trychange_args.extend(["--patchset", str(change_info.patchset)])
    trychange_args.extend(args)
    file_list = change_info.GetFileNames()
  else:
    trychange_args.extend(args)
    file_list = None
  return trychange.TryChange(
      trychange_args,
      file_list=file_list,
      swallow_exception=swallow_exception,
      prog='gcl try',
      extra_epilog='\n'
          'When called from gcl, use the format gcl try <change_name>.\n')


@need_change_and_args
@attrs(usage='[--no_presubmit]')
def CMDcommit(change_info, args):
  """Commits the changelist to the repository."""
  if not change_info.GetFiles():
    print "Nothing to commit, changelist is empty."
    return 1
  if not OptionallyDoPresubmitChecks(change_info, True, args):
    return 1

  # We face a problem with svn here: Let's say change 'bleh' modifies
  # svn:ignore on dir1\. but another unrelated change 'pouet' modifies
  # dir1\foo.cc. When the user `gcl commit bleh`, foo.cc is *also committed*.
  # The only fix is to use --non-recursive but that has its issues too:
  # Let's say if dir1 is deleted, --non-recursive must *not* be used otherwise
  # you'll get "svn: Cannot non-recursively commit a directory deletion of a
  # directory with child nodes". Yay...
  commit_cmd = ["svn", "commit"]
  if change_info.issue:
    # Get the latest description from Rietveld.
    change_info.description = GetIssueDescription(change_info.issue)

  commit_message = change_info.description.replace('\r\n', '\n')
  if change_info.issue:
    server = GetCodeReviewSetting("CODE_REVIEW_SERVER")
    if not server:
      ErrorExit(CODEREVIEW_SETTINGS_FILE_NOT_FOUND)
    if not server.startswith("http://") and not server.startswith("https://"):
      server = "http://" + server
    commit_message += ('\nReview URL: %s/%d' % (server, change_info.issue))

  handle, commit_filename = tempfile.mkstemp(text=True)
  os.write(handle, commit_message)
  os.close(handle)

  handle, targets_filename = tempfile.mkstemp(text=True)
  os.write(handle, "\n".join(change_info.GetFileNames()))
  os.close(handle)

  commit_cmd += ['--file=' + commit_filename]
  commit_cmd += ['--targets=' + targets_filename]
  # Change the current working directory before calling commit.
  previous_cwd = os.getcwd()
  os.chdir(change_info.GetLocalRoot())
  output = RunShell(commit_cmd, True)
  os.remove(commit_filename)
  os.remove(targets_filename)
  if output.find("Committed revision") != -1:
    change_info.Delete()

    if change_info.issue:
      revision = re.compile(".*?\nCommitted revision (\d+)",
                            re.DOTALL).match(output).group(1)
      viewvc_url = GetCodeReviewSetting("VIEW_VC")
      change_info.description = change_info.description + '\n'
      if viewvc_url:
        change_info.description += "\nCommitted: " + viewvc_url + revision
      change_info.CloseIssue()
  os.chdir(previous_cwd)
  return 0


def CMDchange(args):
  """Creates or edits a changelist.

  Only scans the current directory and subdirectories."""
  if len(args) == 0:
    # Generate a random changelist name.
    changename = GenerateChangeName()
  elif args[0] == '--force':
    changename = GenerateChangeName()
  else:
    changename = args[0]
  change_info = ChangeInfo.Load(changename, GetRepositoryRoot(), False, True)
  silent = FilterFlag(args, "--silent")

  # Verify the user is running the change command from a read-write checkout.
  svn_info = SVN.CaptureInfo('.')
  if not svn_info:
    ErrorExit("Current checkout is unversioned.  Please retry with a versioned "
              "directory.")

  if len(args) == 2:
    f = open(args[1], 'rU')
    override_description = f.read()
    f.close()
  else:
    override_description = None

  if change_info.issue and not change_info.NeedsUpload():
    try:
      description = GetIssueDescription(change_info.issue)
    except urllib2.HTTPError, err:
      if err.code == 404:
        # The user deleted the issue in Rietveld, so forget the old issue id.
        description = change_info.description
        change_info.issue = 0
        change_info.Save()
      else:
        ErrorExit("Error getting the description from Rietveld: " + err)
  else:
    if override_description:
      description = override_description
    else:
      description = change_info.description

  other_files = GetFilesNotInCL()

  # Edited files (as opposed to files with only changed properties) will have
  # a letter for the first character in the status string.
  file_re = re.compile(r"^[a-z].+\Z", re.IGNORECASE)
  affected_files = [x for x in other_files if file_re.match(x[0])]
  unaffected_files = [x for x in other_files if not file_re.match(x[0])]

  separator1 = ("\n---All lines above this line become the description.\n"
                "---Repository Root: " + change_info.GetLocalRoot() + "\n"
                "---Paths in this changelist (" + change_info.name + "):\n")
  separator2 = "\n\n---Paths modified but not in any changelist:\n\n"
  text = (description + separator1 + '\n' +
          '\n'.join([f[0] + f[1] for f in change_info.GetFiles()]))

  if change_info.Exists():
    text += (separator2 +
            '\n'.join([f[0] + f[1] for f in affected_files]) + '\n')
  else:
    text += ('\n'.join([f[0] + f[1] for f in affected_files]) + '\n' +
            separator2)
  text += '\n'.join([f[0] + f[1] for f in unaffected_files]) + '\n'

  handle, filename = tempfile.mkstemp(text=True)
  os.write(handle, text)
  os.close(handle)

  if not silent:
    os.system(GetEditor() + " " + filename)

  result = gclient_utils.FileRead(filename, 'r')
  os.remove(filename)

  if not result:
    return 0

  split_result = result.split(separator1, 1)
  if len(split_result) != 2:
    ErrorExit("Don't modify the text starting with ---!\n\n" + result)

  # Update the CL description if it has changed.
  new_description = split_result[0]
  cl_files_text = split_result[1]
  if new_description != description or override_description:
    change_info.description = new_description
    change_info.needs_upload = True

  new_cl_files = []
  for line in cl_files_text.splitlines():
    if not len(line):
      continue
    if line.startswith("---"):
      break
    status = line[:7]
    filename = line[7:]
    new_cl_files.append((status, filename))

  if (not len(change_info._files)) and (not change_info.issue) and \
      (not len(new_description) and (not new_cl_files)):
    ErrorExit("Empty changelist not saved")

  change_info._files = new_cl_files
  change_info.Save()
  if svn_info.get('URL', '').startswith('http:'):
    Warn("WARNING: Creating CL in a read-only checkout.  You will not be "
         "able to commit it!")

  print change_info.name + " changelist saved."
  if change_info.MissingTests():
    Warn("WARNING: " + MISSING_TEST_MSG)

  # Update the Rietveld issue.
  if change_info.issue and change_info.NeedsUpload():
    change_info.UpdateRietveldDescription()
    change_info.needs_upload = False
    change_info.Save()
  return 0


@need_change_and_args
def CMDlint(change_info, args):
  """Runs cpplint.py on all the files in the change list.

  Checks all the files in the changelist for possible style violations.
  """
  try:
    import cpplint
  except ImportError:
    ErrorExit("You need to install cpplint.py to lint C++ files.")
  # Change the current working directory before calling lint so that it
  # shows the correct base.
  previous_cwd = os.getcwd()
  os.chdir(change_info.GetLocalRoot())

  # Process cpplints arguments if any.
  filenames = cpplint.ParseArguments(args + change_info.GetFileNames())

  white_list = GetCodeReviewSetting("LINT_REGEX")
  if not white_list:
    white_list = DEFAULT_LINT_REGEX
  white_regex = re.compile(white_list)
  black_list = GetCodeReviewSetting("LINT_IGNORE_REGEX")
  if not black_list:
    black_list = DEFAULT_LINT_IGNORE_REGEX
  black_regex = re.compile(black_list)
  for filename in filenames:
    if white_regex.match(filename):
      if black_regex.match(filename):
        print "Ignoring file %s" % filename
      else:
        cpplint.ProcessFile(filename, cpplint._cpplint_state.verbose_level)
    else:
      print "Skipping file %s" % filename

  print "Total errors found: %d\n" % cpplint._cpplint_state.error_count
  os.chdir(previous_cwd)
  return 1


def DoPresubmitChecks(change_info, committing, may_prompt):
  """Imports presubmit, then calls presubmit.DoPresubmitChecks."""
  # Need to import here to avoid circular dependency.
  import presubmit_support
  root_presubmit = GetCachedFile('PRESUBMIT.py', use_root=True)
  change = presubmit_support.SvnChange(change_info.name,
                                       change_info.description,
                                       change_info.GetLocalRoot(),
                                       change_info.GetFiles(),
                                       change_info.issue,
                                       change_info.patchset)
  result = presubmit_support.DoPresubmitChecks(change=change,
                                               committing=committing,
                                               verbose=False,
                                               output_stream=sys.stdout,
                                               input_stream=sys.stdin,
                                               default_presubmit=root_presubmit,
                                               may_prompt=may_prompt)
  if not result and may_prompt:
    print "\nPresubmit errors, can't continue (use --no_presubmit to bypass)"
  return result


@no_args
def CMDchanges():
  """Lists all the changelists and their files."""
  for cl in GetCLs():
    change_info = ChangeInfo.Load(cl, GetRepositoryRoot(), True, True)
    print "\n--- Changelist " + change_info.name + ":"
    for filename in change_info.GetFiles():
      print "".join(filename)
  return 0


@no_args
def CMDdeleteempties():
  """Delete all changelists that have no files."""
  print "\n--- Deleting:"
  for cl in GetCLs():
    change_info = ChangeInfo.Load(cl, GetRepositoryRoot(), True, True)
    if not len(change_info._files):
      print change_info.name
      change_info.Delete()
  return 0


@no_args
def CMDnothave():
  """Lists files unknown to Subversion."""
  for filename in UnknownFiles():
    print "?      " + "".join(filename)
  return 0


@attrs(usage='<svn options>')
def CMDdiff(args):
  """Diffs all files in the changelist or all files that aren't in a CL."""
  files = None
  if args:
    change_info = ChangeInfo.Load(args.pop(0), GetRepositoryRoot(), True, True)
    files = change_info.GetFileNames()
  else:
    files = [f[1] for f in GetFilesNotInCL()]

  root = GetRepositoryRoot()
  cmd = ['svn', 'diff']
  cmd.extend([os.path.join(root, x) for x in files])
  cmd.extend(args)
  return RunShellWithReturnCode(cmd, print_output=True)[1]


@no_args
def CMDsettings():
  """Prints code review settings for this checkout."""
  # Force load settings
  GetCodeReviewSetting("UNKNOWN")
  del CODEREVIEW_SETTINGS['__just_initialized']
  print '\n'.join(("%s: %s" % (str(k), str(v))
                    for (k,v) in CODEREVIEW_SETTINGS.iteritems()))
  return 0


@need_change
def CMDdescription(change_info):
  """Prints the description of the specified change to stdout."""
  print change_info.description
  return 0


@need_change
def CMDdelete(change_info):
  """Deletes a changelist."""
  change_info.Delete()
  return 0


def CMDtry(args):
  """Sends the change to the tryserver to do a test run on your code.

  To send multiple changes as one path, use a comma-separated list of
  changenames. Use 'gcl help try' for more information!"""
  # When the change contains no file, send the "changename" positional
  # argument to trychange.py.
  # When the command is 'try' and --patchset is used, the patch to try
  # is on the Rietveld server.
  if not args:
    ErrorExit("You need to pass a change list name")
  if args[0].find(',') != -1:
    change_info = LoadChangelistInfoForMultiple(args[0], GetRepositoryRoot(),
                                                True, True)
  else:
    change_info = ChangeInfo.Load(args[0], GetRepositoryRoot(),
                                  False, True)
  if change_info.GetFiles():
    args = args[1:]
  else:
    change_info = None
  return TryChange(change_info, args, swallow_exception=False)


@attrs(usage='<old-name> <new-name>')
def CMDrename(args):
  """Renames an existing change."""
  if len(args) != 2:
    ErrorExit("Usage: gcl rename <old-name> <new-name>.")
  src, dst = args
  src_file = GetChangelistInfoFile(src)
  if not os.path.isfile(src_file):
    ErrorExit("Change '%s' does not exist." % src)
  dst_file = GetChangelistInfoFile(dst)
  if os.path.isfile(dst_file):
    ErrorExit("Change '%s' already exists; pick a new name." % dst)
  os.rename(src_file, dst_file)
  print "Change '%s' renamed '%s'." % (src, dst)
  return 0


def CMDpassthru(args):
  """Everything else that is passed into gcl we redirect to svn.

  It assumes a change list name is passed and is converted with the files names.
  """
  args = ["svn", args[0]]
  if len(args) > 1:
    root = GetRepositoryRoot()
    change_info = ChangeInfo.Load(args[1], root, True, True)
    args.extend([os.path.join(root, x) for x in change_info.GetFileNames()])
  return RunShellWithReturnCode(args, print_output=True)[1]


def Command(name):
  return getattr(sys.modules[__name__], 'CMD' + name, None)


def GenUsage(command):
  """Modify an OptParse object with the function's documentation."""
  obj = Command(command)
  display = command
  more = getattr(obj, 'usage', '')
  if command == 'help':
    display = '<command>'
  need_change = ''
  if getattr(obj, 'need_change', None):
    need_change = ' <change_list>'
  options = ' [options]'
  if getattr(obj, 'no_args', None):
    options = ''
  res = 'Usage: gcl %s%s%s %s\n\n' % (display, need_change, options, more)
  res += re.sub('\n  ', '\n', obj.__doc__)
  return res


def CMDhelp(args):
  """Prints this help or help for the given command."""
  if args and 'CMD' + args[0] in dir(sys.modules[__name__]):
    print GenUsage(args[0])

    # These commands defer to external tools so give this info too.
    if args[0] == 'try':
      TryChange(None, ['--help'], swallow_exception=False)
    if args[0] == 'upload':
      upload.RealMain(['upload.py', '--help'])
    return 0

  print GenUsage('help')
  print sys.modules[__name__].__doc__
  print 'version ' + __version__ + '\n'

  print('Commands are:\n' + '\n'.join([
        '  %-12s %s' % (fn[3:], Command(fn[3:]).__doc__.split('\n')[0].strip())
        for fn in dir(sys.modules[__name__]) if fn.startswith('CMD')]))
  return 0


def main(argv):
  if not argv:
    argv = ['help']
  command = Command(argv[0])
  # Help can be run from anywhere.
  if command == CMDhelp:
    return command(argv[1:])

  try:
    GetRepositoryRoot()
  except gclient_utils.Error:
    print('To use gcl, you need to be in a subversion checkout.')
    return 1

  # Create the directories where we store information about changelists if it
  # doesn't exist.
  try:
    if not os.path.exists(GetInfoDir()):
      os.mkdir(GetInfoDir())
    if not os.path.exists(GetChangesDir()):
      os.mkdir(GetChangesDir())
    if not os.path.exists(GetCacheDir()):
      os.mkdir(GetCacheDir())

    if command:
      return command(argv[1:])
    # Unknown command, try to pass that to svn
    return CMDpassthru(argv)
  except gclient_utils.Error, e:
    print('Got an exception')
    print(str(e))

if __name__ == "__main__":
  sys.exit(main(sys.argv[1:]))
