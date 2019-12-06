FROM golang

RUN echo "go get github.com/antlinker/go-cmap"
RUN go get github.com/antlinker/go-cmap
RUN echo "go get gopkg.in/mgo.v2"
RUN go get gopkg.in/mgo.v2
RUN apt-get update && apt-get install -y  supervisor vim
ENV APP_ENV=production
COPY ./conf/service.conf /etc/supervisor/conf.d/
WORKDIR /www/
ADD ./ /www/
RUN /www/gen.sh
EXPOSE 8080
CMD ["/www/bin/start.sh"]
