all:
	./golangexecapi -addr=":8030" -accessId="****" -accessSecret="***********" -bucket=yuanli-cdn -endPoint=oss-cn-beijing.aliyuncs.com

build:
	go build .

linux:
	CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build .
