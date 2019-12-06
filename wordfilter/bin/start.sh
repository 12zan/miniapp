#!/usr/bin/env bash
LD_LIBRARY_PATH=/www/acfilter/src/ nohup /www/entry &
/usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
#no hup /www/bin/service-linux-64 -consul_addr=${CONSUL_ADDR} -service_name=${SERVICE_NAME} --monitor_addr=${STATUS_ADDR} -port=${PORT} -ip=${IP} -proxy_addr=127.0.0.1:9999 >> /data/command.log
#/var/opt/bin/service-linux-64 -consul_addr=${CONSUL_ADDR} -service_name=${SERVICE_NAME} --monitor_addr=${STATUS_ADDR} -port=${PORT} -ip=${IP} -proxy_addr=127.0.0.1:9999