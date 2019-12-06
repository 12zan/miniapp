#!/usr/bin/env bash
template="ledis"
app=${INST_NAME}
domain=${DOMAIN_NAME}
echo "APP:${app},INST_NAME:${INST_NAME},DOMAIN_NAME:${DOMAIN_NAME},TAG:${TAG}"
. ../env.sh
echo "IP is:${IP}"
TAG=${REPO}:${template}
$sudo docker pull ${TAG}
echo "The Tag is :" $TAG
$sudo docker stop  ${app}

pwd=`pwd`
port=${PORT}
$sudo docker rm ${app}
CONSUL_ADDR="${IP}:8500"

[ -d data_${app} ] || mkdir ${pwd}/data_${app}
[ -d log_${app} ] || mkdir ${pwd}/log_${app}

$sudo docker run -d  --restart=always  -h ${INST_NAME}.${domain}  --name="${app}"  -v ${pwd}/data_${app}:/data/ -v ${pwd}/log_${app}:/log/ -p ${port}:6380 -e IP=${IP} -e PORT=${port} -e CONSUL_ADDR=${CONSUL_ADDR} -e SERVICE_NAME=backend-${domain} -e STATUS_ADDR=${IP}:${port} ${TAG}


