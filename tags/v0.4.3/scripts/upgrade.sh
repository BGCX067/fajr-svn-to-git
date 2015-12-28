#!/bin/bash
cd "`dirname $0`/.."
umask 0002
svn up "$@"
./scripts/make_version.sh
