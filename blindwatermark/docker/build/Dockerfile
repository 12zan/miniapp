FROM jjanzic/docker-python3-opencv

RUN apt-get update
RUN apt-get install -y supervisor
ADD ./blind-watermark/ /app/
WORKDIR /app/
COPY ./conf/service.conf /etc/supervisor/conf.d/
ADD bin/ /var/opt/bin/
CMD ["/var/opt/bin/start.sh"]