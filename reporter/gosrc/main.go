package main

import (
	"flag"
	"sync"
	"github.com/hashicorp/consul/api"
	"os"
	"log"
)

type ServiceInfo struct {
	ServiceID      string
	IP             string
	Port           int
	Load1          float64 `json:"Load1"`
	Load5          float64 `json:"Load5"`
	Load15         float64 `json:"float64"`
	TotalMem       uint64  `json:"TotalMem"`
	FreeMem        uint64  `json:"FreeMem"`
	UsedPercentMem float64 `json:"UsedPercentMem"`
	Timestamp      int     `json:"ts"`
}

type ServiceList []ServiceInfo

type KVData struct {
	Load1          float64 `json:"Load1"`
	Load5          float64 `json:"Load5"`
	Load15         float64 `json:"float64"`
	TotalMem       uint64  `json:"TotalMem"`
	FreeMem        uint64  `json:"FreeMem"`
	UsedPercentMem float64 `json:"UsedPercentMem"`
	Timestamp      int     `json:"ts"`
}

type ResponseOfMethods struct {
	Code int               `json:"code"`
	Data map[string]string `json:"data"`
}
type MyHandler map[string]string

var (
	servics_map   = make(map[string]ServiceList)
	HashMethodMap = make(map[string]string)

	service_locker = new(sync.Mutex)
	methodLocker   = new(sync.Mutex)
	consul_client  *api.Client
	my_service_id  string
	my_kv_key      string
)

const (
	UrlHash   = "UrlHash"
	IPHash    = "IPHash"
	RandHash  = "RandHash"
	LoadRound = "LoadRound"
)

var detect_uri string

func main() {
	var status_monitor_addr, service_name, service_ip, consul_addr, proxy_addr string
	var service_port int
	var error_log_file string
	var clientOnly bool
	flag.BoolVar(&clientOnly, "client_mode", false, "client_mode ")
	flag.StringVar(&consul_addr, "consul_addr", "localhost:8500", "host:port of the service stuats monitor interface")
	flag.StringVar(&status_monitor_addr, "monitor_addr", "127.0.0.1:54321", "host:port of the service stuats monitor interface")
	flag.StringVar(&service_name, "service_name", "worker", "name of the service")
	flag.StringVar(&service_ip, "ip", "127.0.0.1", "service serve ip")

	flag.StringVar(&proxy_addr, "proxy_addr", "127.0.0.1:8080", "start a proxy and transfer to backend")
	flag.IntVar(&service_port, "port", 4300, "service serve port")
	flag.StringVar(&error_log_file, "error_log", "/data/service.error.log", "log file")
	flag.StringVar(&detect_uri, "detect_uri", "/__status", "specific the url to detect to check the server is online")
	flag.Parse()

	HashMethodMap["ws.z.12zan.net"] = UrlHash
	f, err := os.OpenFile(error_log_file, os.O_RDWR|os.O_CREATE|os.O_APPEND, 0755)
	if err != nil {
		log.Fatalf("error opening file: %v,%v", error_log_file, err)
	}
	defer f.Close()

	log.SetOutput(f)

	go WaitToUnRegisterService()
	go HandleOsKill()

	/**
	注册服务
	 */
	DoRegisterService(consul_addr, status_monitor_addr, service_name, service_ip, service_port)

	if clientOnly {
		log.Println("client mode");

		/**
		每隔一会汇报一下负载
		汇报服务也不需要不断的汇报负载
		 */
		go DoUpdateKeyValue(consul_addr, service_ip, service_port)

	} else {
		log.Println("proxy mode");
		/**
		开启Proxy
		 */
		go StartProxyService(proxy_addr)
		/**
		* 发现服务[只有proxy服务需要不断的发现服务，汇报服务是不需要不断的发现服务的;]
 		*/
		go DoDiscover(consul_addr)
	}
	select {}
}
