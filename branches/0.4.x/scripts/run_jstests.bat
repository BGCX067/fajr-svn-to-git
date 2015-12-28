@echo off
REM See http://code.google.com/p/js-test-driver/ on how to write tests
setlocal

set SCRIPT_PATH=%~dp0
set TEST_PATH=%SCRIPT_PATH%..\tests
set JSDRIVER_BIN=%SCRIPT_PATH%..\third_party\jstestdriver\JsTestDriver-1.2.2.utf8patch.jar

cd %TEST_PATH%
java -jar %JSDRIVER_BIN% --verbose --tests all
