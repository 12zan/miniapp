FROM ubuntu:latest


RUN sed -i 's/archive.ubuntu.com/mirrors.ustc.edu.cn/g' /etc/apt/sources.list

RUN apt-get update && apt-get install -y \
    apt-utils libsnappy-dev librocksdb-dev make golang

ADD ./src /root/go/src/
ADD ./bin /etc/bin

WORKDIR /root/go/src/github.com/siddontang/ledisdb

RUN /bin/bash -c "source ./dev.sh"
RUN make clean && make
ADD ./conf /etc/conf

EXPOSE 6380
EXPOSE 11181

CMD ["sh", "/etc/bin/start.sh"]
