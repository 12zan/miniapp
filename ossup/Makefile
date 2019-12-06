linux:
	CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o ./ossup-linux-64 ./main.go


test:
	./ossup -accessId="LTAIk***" -accessSecret="*******"  -file=`pwd`/main.go -path="golang/main.go" -bucket=chengyubaba -endPoint=oss-cn-beijing.aliyuncs.com
	echo $?
