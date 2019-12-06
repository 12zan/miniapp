#!/usr/bin/env bash
template="ledis"
. ../../env.sh
TAG=${REPO}:${template}
platform=`uname -s`
git clone https://gitee.com/cassss/ledisdb.git src/github.com/siddontang/ledisdb
$sudo docker build -t ${TAG} .
if [ ${platform} = "Linux" ]; then
    sudo docker push $TAG
fi
