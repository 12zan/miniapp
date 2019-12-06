package main

import (
	"flag"
	"os"
	"fmt"
	"github.com/aliyun/aliyun-oss-go-sdk/oss"
)
const (
	argumentMissing =1
	notExists       =2
	bucketFailed    = 3
	putFileFailed   = 4
	downFileFailed  = 5
)
func main()  {
	var bucket, key, secret, localFile, remotePath, endPoint string
	var download bool
	flag.StringVar(&endPoint,"endPoint","oss-cn-beijing-internal.aliyuncs.com","the endpoint of oss")
	flag.StringVar(&bucket,"bucket","-"," name of the bucket")
	flag.StringVar(&key,"accessId","-","the Access Key Id")
	flag.StringVar(&secret,"accessSecret","-","the Access Key Secret")
	flag.StringVar(&localFile, "file", "src.jpg", "the local file path")
	flag.StringVar(&remotePath, "path", "/dest.online.jpg", "the remote oss server path")
	flag.BoolVar(&download, "download", false, "download file instead")
	flag.Parse()

	if bucket == "-" {
		fmt.Printf("please specific [-bucket] argument")
		os.Exit(argumentMissing)
	}

	if key == "-" {
		fmt.Printf("please specific [-accessId] argument")
		os.Exit(argumentMissing)
	}

	if secret == "-" {
		fmt.Printf("please specific [-accessSecret] argument")
		os.Exit(argumentMissing)
	}

	if !download {
		if _, err := os.Stat(localFile); os.IsNotExist(err) {
			fmt.Printf("local file not exists:%v", localFile)
			os.Exit(notExists);
		}
	}

	bucketObject, err := NewBucketSample(endPoint,key,secret,bucket)
	if err!=nil {
		fmt.Println("create Bucket object failed")
		os.Exit(bucketFailed)
	}
	// 场景4：上传本地文件，不需要打开文件。
	if download {
		err = bucketObject.GetObjectToFile(remotePath, localFile)
		if err != nil {
			fmt.Println("download file error:%v", err)
			os.Exit(downFileFailed)
		} else {
			fmt.Println("OK")
		}
	} else {
		err = bucketObject.PutObjectFromFile(remotePath, localFile)
		if err != nil {
			fmt.Println("put file error:%v", err)
			os.Exit(putFileFailed)
		} else {
			fmt.Println("OK")
		}
	}

	//bucketObject.AppendObject()
}

func NewBucketSample(endpoint string,accessID string, accessKey string,bucketName string) (returnBucket *oss.Bucket,err error)  {
	// New Client
	client, err := oss.New(endpoint, accessID, accessKey)
	if err != nil {
		return nil,err
	}
	//// Create Bucket
	//err = client.CreateBucket(bucketName)
	//if err != nil {
	//	return nil,err
	//}

	// New Bucket
	bucket, err := client.Bucket(bucketName)
	if err != nil {
		return nil,err
	}
	return bucket,nil
}