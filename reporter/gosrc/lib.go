package main

import (
	"fmt"
	"strings"
	"os"
	"os/signal"
	"syscall"
	"time"
	"strconv"
	"encoding/json"
	"github.com/hashicorp/consul/api"
	"log"
	"math/rand"
	"net/http"
	"net"
	"io"
	"github.com/shirou/gopsutil/mem"
	Load "github.com/shirou/gopsutil/load"
	"hash/crc32"
	//"github.com/siddontang/go-mysql/server"
)

func FileExists(name string) bool {
	if _, err := os.Stat(name); err != nil {
		if os.IsNotExist(err) {
			return false
		}
	}
	return true
}

func CreateFile(name string) error {
	fo, err := os.Create(name)
	if err != nil {
		return err
	}
	defer func() {
		fo.Close()
	}()
	return nil
}


func DoRegisterService(consul_addr string, monitor_addr string, service_name string, ip string, port int) {
	my_service_id = service_name + "-" + ip + ":" + fmt.Sprintf("%d",port)
	var tags []string
	tags = append(tags,"backend")
	service := &api.AgentServiceRegistration{
		ID:      my_service_id,
		Name:    service_name,
		Port:    port,
		Address: ip,
		Tags:    tags,
		Check: &api.AgentServiceCheck{
			HTTP:     "http://" + monitor_addr + detect_uri,
			Interval: "3s",
			Timeout:  "2s",
		},
	}

	config := api.DefaultConfig()
	config.Address = consul_addr
	client, err := api.NewClient(config)
	if err != nil {
		log.Fatal(err)
	}
	consul_client = client
	if err := consul_client.Agent().ServiceRegister(service); err != nil {
		log.Fatal(err)
	}
	log.Printf("Registered service %q in consul with tags %q;service_id:%q", service_name, strings.Join(tags, ","), my_service_id)
}

func Quit(){
	if consul_client == nil {
		os.Exit(0)
		return
	}
	if err := consul_client.Agent().ServiceDeregister(my_service_id); err != nil {
		log.Fatal(err)
	}
	os.Exit(0)
}
func HandleOsKill(){
	quit := make(chan os.Signal, 1)
	signal.Notify(quit,os.Kill,os.Interrupt)
	<-quit
	fmt.Println("killing signal")
	Quit()
}

func WaitToUnRegisterService() {
	quit := make(chan os.Signal, 1)
	signal.Notify(quit, os.Interrupt,syscall.SIGINT,syscall.SIGTERM)
	<-quit
	fmt.Println("interrupt signal")
	Quit()
}

func DoDiscover(consul_addr string) {
	DiscoverServices(consul_addr, true)
	t := time.NewTicker(time.Second * 3)
	for {
		select {
		case <-t.C:
			DiscoverServices(consul_addr, true)
		}
	}
}

func DiscoverServices(addr string, healthyOnly bool) {
	consulConf := api.DefaultConfig()
	consulConf.Address = addr
	client, err := api.NewClient(consulConf)
	CheckErr(err)

	services, _, err := client.Catalog().Services(&api.QueryOptions{})
	CheckErr(err)

	//fmt.Println("--do discover ---:", addr)



	temp_map     := make(map[string]ServiceList)

	for name := range services {
		servicesData, _, err := client.Health().Service(name, "backend", healthyOnly,
			&api.QueryOptions{})
		CheckErr(err)
		for _, entry := range servicesData {
			//if service_name != entry.Service.Service {
			//	continue
			//}
			for _, health := range entry.Checks {
				//if health.ServiceName != service_name {
				//	continue
				//}
				//fmt.Println("  health nodeid:", health.Node, " service_name:", health.ServiceName, " service_id:", health.ServiceID, " status:", health.Status, " ip:", entry.Service.Address, " port:", entry.Service.Port)

				var node ServiceInfo
				node.IP = entry.Service.Address
				node.Port = entry.Service.Port
				node.ServiceID = health.ServiceID

				//get data from kv store
				s := GetKeyValue(node.IP, node.Port)
				if len(s) > 0 {
					var data KVData
					err = json.Unmarshal([]byte(s), &data)
					if err == nil {
						node.Load1 = data.Load1
						node.Load5 = data.Load5
						node.Load15 = data.Load15
						node.UsedPercentMem = data.UsedPercentMem
						node.TotalMem = data.TotalMem
						node.FreeMem = data.FreeMem
						node.Timestamp = data.Timestamp
					}
				}
				serverList := temp_map[health.ServiceName]
				if serverList!=nil {
					serverList = append(serverList,node)
				}else{
					var sers ServiceList
					serverList = append(sers,node)
				}


				temp_map[health.ServiceName] = serverList


				//fmt.Println("service node updated ip:", node.IP, " port:", node.Port, " serviceid:", node.ServiceID, " load:", node.Load, " ts:", node.Timestamp)
			}
		}
	}

	service_locker.Lock()
	servics_map = temp_map
	service_locker.Unlock()

}

func DoUpdateKeyValue(consul_addr string, ip string, port int) {
	t := time.NewTicker(time.Second * 13)
	for {
		select {
		case <-t.C:
			StoreKeyValue(consul_addr, ip, port)
		}
	}
}

func StoreKeyValue(consul_addr string, ip string, port int) {


	my_kv_key =   "backend/" + ip + ":" + strconv.Itoa(port)

	memV,_ := mem.VirtualMemory()
	loadInfo ,_ := Load.Avg()
	var data KVData
	data.Load1 = loadInfo.Load1
	data.Load5 = loadInfo.Load5
	data.Load15 = loadInfo.Load15

	data.UsedPercentMem = memV.UsedPercent
	data.FreeMem = memV.Free
	data.TotalMem = memV.Total
	data.Timestamp = int(time.Now().Unix())
	bys, _ := json.Marshal(&data)

	kv := &api.KVPair{
		Key:   my_kv_key,
		Flags: 0,
		Value: bys,
	}

	_, err := consul_client.KV().Put(kv, nil)
	CheckErr(err)
	//fmt.Println(" store data key:", kv.Key, " value:", string(bys))
}

func GetKeyValue( ip string, port int) string {
	key :=   "backend/" + ip + ":" + strconv.Itoa(port)

	kv, _, err := consul_client.KV().Get(key, nil)
	if kv == nil {
		return ""
	}
	CheckErr(err)

	return string(kv.Value)
}



func CheckErr(err error) {
	if err != nil {
		log.Printf("error: %v", err)
		os.Exit(1)
	}
}
func StatusHandler(w http.ResponseWriter, r *http.Request) {
	fmt.Println("check status.")
	fmt.Fprint(w, "status ok!")
}

func StartProxyService(addr string) {

	//http.HandleFunc("/",ProxyToBackend)
	fmt.Println("start listen..."+ addr)

	handler := MyHandler{}
	err := http.ListenAndServe(addr, handler)
	CheckErr(err)
}

func GetAllBackends(hostname string) string {
	data := servics_map["backend-"+hostname]
	if(data==nil){
		return ""
	}
	if len(data) == 0 {
		return ""
	}
	jsonString ,er := json.Marshal(data)
	if  er== nil {
		return string(jsonString)
	}
	return ""
}
func GetBackendServerByHostName(hostname string, ip string, path string) string {
	data := servics_map["backend-"+hostname]
	if(data==nil){
		log.Println("map item backend-"+hostname+" is null")
		return ""
	}
	if len(data) == 0 {
		log.Println("map lenth of  backend-"+hostname+" is 0")
		return ""
	}

	method, ok := HashMethodMap[hostname]
	if !ok {
		method = RandHash
	}
	var server ServiceInfo
	/**
	随机分一台
	 */
	if method == RandHash {
		idx := rand.Intn(len(data))
		server = data[idx]
	}
	/**
	找出负载最低的那一台;
	 */
	if method == LoadRound {
		maxLoad := float64(1000000)
		for i := 0; i < len(data); i++ {
			if data[i].Load1 < maxLoad {
				server = data[i]
				maxLoad = data[i].Load1
			}
		}
	}
	/**
	根据IP或是UrlHash Hash一台出来；
	 */
	if method == IPHash || method == UrlHash {
		var seed string
		if method == IPHash {
			seed = ip
		}
		if method == UrlHash {
			seed = path
		}
		crc32q := crc32.MakeTable(0xD5828281)
		checkSum := crc32.Checksum([]byte(seed), crc32q)
		idx := checkSum % uint32(len(data))
		server = data[idx]
	}
	return fmt.Sprintf("%s:%d", server.IP, server.Port)
}

func showHashMethodsHandle(w http.ResponseWriter, r *http.Request) {
	var response ResponseOfMethods
	response.Data = HashMethodMap
	response.Code = 200
	jsonTxt, er := json.Marshal(response)
	if er != nil {
		w.Write([]byte("{'code':200,'msg':'json encode failed'}"))
		return
	} else {
		w.Write([]byte(jsonTxt))
	}
}
func updateHashHandle(w http.ResponseWriter, r *http.Request) {
	r.ParseForm()
	domain := ""
	method := ""
	if r.Form["domain"] != nil {
		domain = strings.Join(r.Form["domain"], "")
	}
	if (r.Form["method"] != nil ) {
		method = strings.Join(r.Form["method"], "")
	}

	if method != RandHash && method != IPHash && method != UrlHash && method != LoadRound {
		w.Write([]byte("{'code':200,'msg':'method invalid'}"))
		return
	}
	if domain != "" && method != "" {
		methodLocker.Lock()
		HashMethodMap[domain] = method
		methodLocker.Unlock()
	}
	w.Write([]byte("{'code':200}"))
}


func GetBackendsHandle(w http.ResponseWriter,r *http.Request){
	path := r.URL.Path
	runes := []rune(path)
	start := len("/__backend/")
	queryHost :=  string(runes[start:len(path)])
	backends := GetAllBackends(queryHost)
	w.Write([]byte(backends))
}

func HandleAllBackends(w http.ResponseWriter,r *http.Request){
	_data,er := json.Marshal(servics_map)
	if er != nil {
		msg := "{'code':401,msg:'cna't get message'}"
		w.Write([]byte (msg))
		return
	}
	w.Write(_data)
}

func (self MyHandler)  ServeHTTP(w http.ResponseWriter, r *http.Request) {
	hostSeg := r.Host
	idx := strings.Index(hostSeg,":")
	if idx<0 {
		idx = 0
	}
	runes := []rune(hostSeg)
	queryHost :=  string(runes[0:idx])
	if queryHost=="" {
		queryHost = hostSeg
	}
	if strings.HasPrefix(r.URL.Path,"/__backends") {
		HandleAllBackends(w,r)
		return
	}
	if strings.HasPrefix(r.URL.Path,"/__status") {
		StatusHandler(w,r)
		return
	}
	if strings.HasPrefix(r.URL.Path,"/__backend/") {
		GetBackendsHandle(w,r)
		return
	}

	if strings.HasPrefix(r.URL.Path, "/__hashMethods") {
		showHashMethodsHandle(w, r)
		return
	}
	if strings.HasPrefix(r.URL.Path, "/__hashMethod") {
		updateHashHandle(w, r)
		return
	}

	var ip string

	if (len(r.Header["X-Real-Ip"]) < 1) {
		log.Printf("without X-Real-Ip,")
		ip = ""
	} else {
		ip = r.Header["X-Real-Ip"][0]
	}

	log.Printf("query backend for host:" + queryHost + ",ip:" + ip + ",path:" + r.URL.Path)
	backend := GetBackendServerByHostName(queryHost, ip, r.URL.Path)
	if backend == "" {
		w.WriteHeader(504)
		return
	}

	peer, err := net.Dial("tcp", backend)
	if err != nil {
		log.Printf("dial upstream error:%v", err)
		w.WriteHeader(503)
		return
	}
	if err := r.Write(peer); err != nil {
		log.Printf("write request to upstream error :%v", err)
		w.WriteHeader(502)
		return
	}

	hj, ok := w.(http.Hijacker)
	if !ok {
		w.WriteHeader(500)
		return
	}
	conn, _, err := hj.Hijack()
	if err != nil {
		w.WriteHeader(500)
		return
	}
	log.Printf(
		"serving %s < %s <-> %s > %s ",
		peer.RemoteAddr(), peer.LocalAddr(),
		conn.RemoteAddr(), conn.LocalAddr(),
	)

	go func() {
		defer peer.Close()
		defer conn.Close()
		io.Copy(peer, conn)
	}()
	go func() {
		defer peer.Close()
		defer conn.Close()
		io.Copy(conn, peer)
	}()
}

