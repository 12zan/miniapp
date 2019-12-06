package main

import (
	"encoding/json"
	"errors"
	"flag"
	"fmt"
	"github.com/aliyun/aliyun-oss-go-sdk/oss"
	"math/rand"
	"net/http"
	"net/url"
	"os/exec"
	"strings"
	"time"
)

type router struct {
}

var addr string
var bucket, key, secret, endPoint string

const (
	argumentMissing = 1
	notExists       = 2
	bucketFailed    = 3
	putFileFailed   = 4
	downFileFailed  = 5
)

type Resp struct {
	Code int    `json:"code"`
	Msg  string `json:"msg,omitempty"`
}

func apiHelper(w http.ResponseWriter) {
	help := make(map[string]string)
	help["/v1/watermark/add?original_path={original_path}&watermark_path={watermark_path}&result_path={result_path}&alpha={alpha} [GET,POST] "] = "添加水印"
	help["/v1/watermark/read?original_path={original_path}&picture_path={picture_path}&result_path={result_path}&alpha={alpha} [GET,POST] "] = "读出水印"

	serveJSON(w, help)
}

func serveJSON(w http.ResponseWriter, data interface{}) {
	w.Header().Set("Server", "goo")
	w.Header().Set("Content-Type", "application/json; charset=utf-8")
	w.WriteHeader(200)

	content, err := json.Marshal(data)
	if err == nil {
		w.Write(content)
	} else {
		w.Write([]byte(`{"code":0, "error":"解析JSON出错"}`))
	}
}

func statusResp(w http.ResponseWriter) {
	w.WriteHeader(200)
	w.Write([]byte("status ok!"))
}

func (ro *router) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	switch r.URL.Path {
	case "/":
		apiHelper(w)
	case "/__status":
		statusResp(w)
	case "/v1/watermark/add": // 查找敏感词
		httpAddWM(w, r)
	case "/v1/watermark/read": // 敏感词
		httpReadWM(w, r)
	default:
		notFound(w)
	}
}

func notFound(w http.ResponseWriter) {
	w.WriteHeader(http.StatusNotFound)
}

func httpReadWM(w http.ResponseWriter, r *http.Request) {
	original_path_param_name := "original_path"
	checking_picture_path_param_name := "picture_path"
	result_path_param_name := "result_path"
	alpha_param_name := "alpha"

	original_path := ""
	checking_path := ""
	result_path := ""
	alpha := ""
	if r.Method == "GET" {
		params, err := url.ParseQuery(r.URL.RawQuery)
		if err == nil {
			if q, ok := params[original_path_param_name]; ok {
				original_path = q[0]
			}
			if q, ok := params[checking_picture_path_param_name]; ok {
				checking_path = q[0]
			}
			if q, ok := params[result_path_param_name]; ok {
				result_path = q[0]
			}
			if q, ok := params[alpha_param_name]; ok {
				alpha = q[0]
			}
		} else {
			fmt.Println(err)
		}

	} else if r.Method == "POST" {
		original_path = r.FormValue(original_path_param_name)
		result_path = r.FormValue(result_path_param_name)
		checking_path = r.FormValue(checking_picture_path_param_name)
		alpha = r.FormValue(alpha_param_name)
	}

	prefix := "/tmp/" + fmt.Sprintf("%d.%d.", time.Now().Unix()*1000, rand.Intn(100))

	original := prefix + "original.png"
	result := prefix + "result.png"
	checking := prefix + "checking.png"
	errOfDownloadOriginal := downloadFile(original_path, original)
	if errOfDownloadOriginal != nil {
		serveJSON(w, &Resp{
			Code: 502,
			Msg:  fmt.Sprintf("download  original file  error:%s", errOfDownloadOriginal.Error())})
		return
	}

	errOfDOwnloadChecking := downloadFile(checking_path, checking)
	if errOfDOwnloadChecking != nil {
		serveJSON(w, &Resp{
			Code: 502,
			Msg:  fmt.Sprintf("download  checking file  error:%s", errOfDOwnloadChecking.Error())})
		return
	}

	commandStr := "/usr/local/bin/python /app/decode.py --image " + checking + " --original " + original + " --result " + result + " --alpha " + alpha
	cmd := exec.Command("sh",
		"-c",
		commandStr)
	out, err := cmd.CombinedOutput()
	if err != nil {
		returnValue := &Resp{
			Code: 500,
			Msg:  string(err.Error()) + ";out:" + string(out) + ",commandStr:" + commandStr}
		serveJSON(w, returnValue)
		return
	}

	if "OK" == (strings.TrimSpace(string(out))) {

		errOfUpload := uploadFile(result_path, result)

		if errOfUpload == nil {

			serveJSON(w, &Resp{
				Code: 200,
				Msg:  ""})
			return
		} else {

			serveJSON(w, &Resp{
				Code: 502,
				Msg:  fmt.Sprintf("upload result file  error:%s", err.Error())})
			return
		}

	} else {

		serveJSON(w, &Resp{
			Code: 502,
			Msg:  "return not OK:[" + string(out) + "]"})
		return
	}
}

func httpAddWM(w http.ResponseWriter, r *http.Request) {
	original_path_param_name := "original_path"
	watermark_path_param_name := "watermark_path"
	result_path_param_name := "result_path"
	alpha_param_name := "alpha"

	original_path := ""
	watermark_path := ""
	result_path := ""
	alpha := ""
	if r.Method == "GET" {
		params, err := url.ParseQuery(r.URL.RawQuery)
		if err == nil {
			if q, ok := params[original_path_param_name]; ok {
				original_path = q[0]
			}
			if q, ok := params[watermark_path_param_name]; ok {
				watermark_path = q[0]
			}
			if q, ok := params[result_path_param_name]; ok {
				result_path = q[0]
			}
			if q, ok := params[alpha_param_name]; ok {
				alpha = q[0]
			}
		} else {
			fmt.Println(err)
		}

	} else if r.Method == "POST" {
		original_path = r.FormValue(original_path_param_name)
		result_path = r.FormValue(result_path_param_name)
		watermark_path = r.FormValue(watermark_path_param_name)
		alpha = r.FormValue(alpha_param_name)
	}

	prefix := "/tmp/" + fmt.Sprintf("%d.%d.", time.Now().Unix()*1000, rand.Intn(100))

	original := prefix + "original.png"
	result := prefix + "result.png"
	watermark := prefix + "wm.png"
	errOfDownloadOriginal := downloadFile(original_path, original)
	if errOfDownloadOriginal != nil {
		serveJSON(w, &Resp{
			Code: 502,
			Msg:  fmt.Sprintf("download  original file  error:%s", errOfDownloadOriginal.Error())})
		return
	}

	errOfDOwnloadWatermark := downloadFile(watermark_path, watermark)
	if errOfDOwnloadWatermark != nil {
		serveJSON(w, &Resp{
			Code: 502,
			Msg:  fmt.Sprintf("download  watermark file  error:%s", errOfDOwnloadWatermark.Error())})
		return
	}

	commandStr := "/usr/local/bin/python /app/encode.py --image " + original + " --watermark " + watermark + " --result " + result + " --alpha " + alpha
	cmd := exec.Command("sh",
		"-c",
		commandStr)
	out, err := cmd.CombinedOutput()
	if err != nil {
		returnValue := &Resp{
			Code: 500,
			Msg:  string(err.Error()) + ";out:" + string(out) + ",commandStr:" + commandStr}
		serveJSON(w, returnValue)
		return
	}
	if "OK" == (strings.TrimSpace(string(out))) {
		errOfUpload := uploadFile(result_path, result)
		if errOfUpload == nil {
			serveJSON(w, &Resp{
				Code: 200,
				Msg:  ""})
			return
		} else {
			serveJSON(w, &Resp{
				Code: 502,
				Msg:  fmt.Sprintf("upload result file  error:%s", err.Error())})
			return
		}

	} else {
		serveJSON(w, &Resp{
			Code: 502,
			Msg:  "return not OK:[" + string(out) + "]"})
		return
	}
}

func uploadFile(remotePath string, localFile string) (err error) {
	bucketObject, err := NewBucketSample(endPoint, key, secret, bucket)
	if err != nil {
		return errors.New(fmt.Sprintf("can't connect to endpoint %s", endPoint))
	}

	err = bucketObject.PutObjectFromFile(remotePath, localFile)
	if err != nil {
		fmt.Println("upload file error:%v", err)
		return errors.New(fmt.Sprintf("can't uploadfile file :%s", remotePath))
	}
	return nil
}
func downloadFile(remotePath string, localFile string) (err error) {

	bucketObject, err := NewBucketSample(endPoint, key, secret, bucket)
	if err != nil {
		return errors.New(fmt.Sprintf("can't connect to endpoint %s", endPoint))
	}

	err = bucketObject.GetObjectToFile(remotePath, localFile)
	if err != nil {
		fmt.Println("download file error:%v", err)
		return errors.New(fmt.Sprintf("can't download file :%s", remotePath))
	}
	return nil
}

func NewBucketSample(endpoint string, accessID string, accessKey string, bucketName string) (returnBucket *oss.Bucket, err error) {
	// New Client
	client, err := oss.New(endpoint, accessID, accessKey)
	if err != nil {
		return nil, err
	}
	//// Create Bucket
	//err = client.CreateBucket(bucketName)
	//if err != nil {
	//	return nil,err
	//}

	// New Bucket
	bucket, err := client.Bucket(bucketName)
	if err != nil {
		return nil, err
	}
	return bucket, nil
}
func main() {

	flag.StringVar(&endPoint, "endPoint", "oss-cn-beijing-internal.aliyuncs.com", "the endpoint of oss")
	flag.StringVar(&bucket, "bucket", "-", " name of the bucket")
	flag.StringVar(&key, "accessId", "-", "the Access Key Id")
	flag.StringVar(&secret, "accessSecret", "-", "the Access Key Secret")
	flag.StringVar(&addr, "addr", ":8030", "the listen address ")
	flag.Parse()

	t := time.Now().Local().Format("2006-01-02 15:04:05 -0700")
	fmt.Printf("%s Listen %s\n", t, addr)
	http.ListenAndServe(addr, &router{})

}
