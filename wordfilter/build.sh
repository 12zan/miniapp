#!/usr/bin/env bash
template="wordfilter"
. ../env.sh
TAG=${REPO}:${template}
cp ../gosrc/service-linux-64 ./bin/
platform=`uname -s`
$sudo docker build -t ${TAG} .
if [ ${platform} = "Linux" ]; then
    $sudo docker push $TAG
fi