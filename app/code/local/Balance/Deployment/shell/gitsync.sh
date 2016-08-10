#!/bin/bash
# synchronize source code
#
#################################
cd $1
echo "update source code in: "$1
echo `git reset --hard`
echo `git pull --rebase origin master`
#echo `git log -n 1 --pretty=format:"%H"`