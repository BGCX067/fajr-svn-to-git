#!/bin/bash

# See http://code.google.com/p/js-test-driver/ on how to write tests

SCRIPT_PATH=`dirname $0`
SCRIPT_PATH=`readlink -f $SCRIPT_PATH`
TEST_PATH=`readlink -f $SCRIPT_PATH/../tests`
JSDRIVER_BIN=`readlink -f $SCRIPT_PATH/../third_party/jstestdriver/JsTestDriver-1.2.2.utf8patch.jar`
REPORT_PATH=`readlink -f $SCRIPT_PATH/../report/tests/`
REPORT_PATH_XML=`readlink -f $SCRIPT_PATH/../report/tests/jstests_xml`

mkdir -p $REPORT_PATH;

rm $REPORT_PATH_XML/*;
mkdir -p $REPORT_PATH_XML;

cd $TEST_PATH && java -jar $JSDRIVER_BIN \
  --testOutput $REPORT_PATH_XML --verbose --tests all $@ | \
  tee $REPORT_PATH/js_tests.txt | \
  sed -u -e 's/^\(\[PASSED\].*\)/\o033[0;32m\1\o033[0m/' | \
  sed -u -e 's/^\(\[FAILED\].*\)/\o033[0;31m\1\o033[0m/' | \
  sed -u -e 's/^\(\[ERROR\].*\)/\o033[0;31m\1\o033[0m/'
