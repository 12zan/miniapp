#!/usr/bin/env bash
docker run -ti --rm -p 9965:8080 -v `pwd`/:/www/ gobase /bin/bash