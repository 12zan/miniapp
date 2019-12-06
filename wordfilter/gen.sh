#!/usr/bin/env bash

OBJECTS="acseg_util.o acseg_rbtree.o acseg_tree.o mem_collector.o"

#echo "go get github.com/antlinker/go-cmap"
#go get github.com/antlinker/go-cmap
#echo "go get gopkg.in/mgo.v2"
#go get gopkg.in/mgo.v2

cd /www
cd acfilter
cd src
#rm *.o
gcc -c -fPIC *.c
gcc -shared -fPIC -o libacseg.so ${OBJECTS}
cd /www/
go build -o ./entry ./main.go
chmod a+x ./entry


