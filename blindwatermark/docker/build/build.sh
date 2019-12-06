#!/usr/bin/env bash
template="watermark"
cp ../../gosrc/service-linux-64 ./bin/
cp ../../golangexecapi/golangexecapi ./blind-watermark/
. ../../env.sh
TAG=${REPO}:${template}
platform=`uname -s`
echo $sudo docker build -t ${TAG} .
$sudo docker build -t ${TAG} .
if [ ${platform} = "Linux" ]; then
    sudo docker push $TAG
fi
