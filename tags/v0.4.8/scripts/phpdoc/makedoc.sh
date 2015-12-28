#!/bin/sh
if [ $# -ne 2 ] ; then
 echo "usage $0 source_dir title";
else
echo "Making documentation Source:$1 Title:$2";
cd $1
if [ $? -eq 0 ] ; then
   rm -rf ./report/documentation/*
   phpdoc -d . -t ./report/documentation -o HTML:frames:DOM/earthli -s -ti "$2" -pp
   chmod a+w -R ./report/documentation

   phpdoc -d src/libfajr/pub -t ./report/doc_public -o HTML:frames:DOM/earthli -s -ti "$2"
   chmod a+w -R ./report/doc_public
  else
    echo "Problems entering directory $1, exitting";
  fi;
fi
