linux:
	CGO_ENABLED=0 GOOS=linux GOARCH=amd64 go build -o ./service-linux-64 ./main.go ./lib.go

current:
	go build -o ./service ./main.go ./lib.go
