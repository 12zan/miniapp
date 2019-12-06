#!/usr/bin/env bash
template="watermark"
. ../env.sh
TAG=${REPO}:${template}
domain=wm.z.12zan.net
INST_NAME="wm"
port=8844
pwd=`pwd`

$sudo docker stop ${INST_NAME}
$sudo docker rm ${INST_NAME}
[ -d data_${INST_NAME} ] || mkdir ${pwd}/data_${INST_NAME}
[ -d config_${INST_NAME} ] || mkdir ${pwd}/config_${INST_NAME}

echo "starting watermark"
CONSUL_ADDR="${IP}:8500"

echo docker run  -d --name="${INST_NAME}" -v ${pwd}/data_${INST_NAME}:/data/ -p ${port}:${port} -e IP=${IP} -e PORT=${port} -e CONSUL_ADDR=${CONSUL_ADDR} -e SERVICE_NAME=backend-${domain} -e STATUS_ADDR=${IP}:${port} ${TAG}

$sudo docker run  -d --name="${INST_NAME}" -v ${pwd}/data_${INST_NAME}:/data/ -p ${port}:${port} -e IP=${IP} -e PORT=${port} -e CONSUL_ADDR=${CONSUL_ADDR} -e SERVICE_NAME=backend-${domain} -e STATUS_ADDR=${IP}:${port} ${TAG}
