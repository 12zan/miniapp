package main
import (
	"bytes"
	"encoding/base64"
	"encoding/json"
	"flag"
	"fmt"
	"github.com/siddontang/go-log/log"
	"github.com/yuanfenxi/tunnel"
	"io"
	"net"
	"net/http"
	"net/url"
	"os"
	"os/signal"
	"strings"
	"sync"
	"syscall"
	"time"
)
var lock4Connections sync.RWMutex
type TunnelClient struct {
	Id   string
	Conn net.Conn
	Busy bool
	Addr string
}
type TunnelConnRoute struct {
	Address string
	Id      string
	Conn    net.Conn
}
type TunnelClients map[string]map[string]TunnelClient

var tunnelClients TunnelClients
var version = "1.0.0"
var secret = "Ilove9527"
var httpAddr = ":8888"
var secureData bool
var brokenConn chan TunnelConnRoute

func main() {
	var tunnelAddr string
	var proxyAddr string
	tunnelClients = make(TunnelClients)
	brokenConn = make(chan TunnelConnRoute)
	log.SetLevel(log.LevelError)
	var level string
	flag.StringVar(&level, "level", "error", "log level ,may be: DEBUG/INFO/WARN/ERROR/FATAL")
	flag.StringVar(&proxyAddr, "proxyAddr", ":22222", "http proxy address\t default value is :22222")
	flag.StringVar(&tunnelAddr, "tunnelAddr", ":2345", "tunnel address\t default value is :2345")
	flag.StringVar(&httpAddr, "httpAddr", ":8888", " http address\t default value is :8888 ")
	flag.StringVar(&secret, "secret", "Ilove9527", "the secret when create tunnel")
	flag.BoolVar(&secureData, "secure", false, "secure transfer,default value:false")
	flag.Parse()

	level = strings.ToUpper(level)
	switch level {
	case "DEBUG":
		log.SetLevel(log.LevelDebug)
		break
	case "INFO":
		log.SetLevel(log.LevelInfo)
		break
	case "WARN":
		log.SetLevel(log.LevelWarn)
		break
	case "ERROR":
		log.SetLevel(log.LevelError)
		break
	case "FATAL":
		log.SetLevel(log.LevelFatal)
		break
	}
	go httpServe(httpAddr)
	pta, err := net.ResolveTCPAddr("tcp", tunnelAddr)
	if err != nil {
		log.Fatalf("resolve frontend error:%v\n", err)
		return
	}

	ppa, err := net.ResolveTCPAddr("tcp", proxyAddr)
	if err != nil {
		log.Fatalf("resolve backend error:%s\n", err.Error())
		return
	}
	lta, err := net.ListenTCP("tcp", pta)
	if err != nil {
		log.Fatalf("can't listen at pt %s\n", tunnelAddr)
		return
	}
	fmt.Println("listening at ...", tunnelAddr)

	defer lta.Close()
	ltp, err := net.ListenTCP("tcp", ppa)
	if err != nil {
		log.Fatalf("cant listen at pp:%v\n", proxyAddr)
		return
	}

	defer ltp.Close()

	go WaitTunnelConn(lta)
	go waitProxyConn(ltp)
	go waitBrokenConn()
	for {
		signChan := make(chan os.Signal, 1)
		signal.Notify(signChan)
		for sig := range signChan {
			if sig == syscall.SIGINT || sig == syscall.SIGTERM {
				log.Errorf("temrinated by signal %v\n", sig)
				os.Exit(0)
			} else {
				log.Errorf("received :%v,ignore\n", sig)
			}
		}
	}

}
func waitBrokenConn() {
	var conn TunnelConnRoute
	for {
		conn = <-brokenConn
		log.Warnf("some tunnel connection closed!:%v\n", conn)
		lock4Connections.Lock()
		_, ok := tunnelClients[conn.Address]
		if ok {
			_, found := tunnelClients[conn.Address][conn.Id]
			if found {
				delete(tunnelClients[conn.Address], conn.Id)
			}
		}
		lock4Connections.Unlock()
	}
}
func httpServe(addr string) {
	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		w.Write([]byte("Pt server " + version + "," + r.RequestURI))
	})
	http.HandleFunc("/status", func(writer http.ResponseWriter, request *http.Request) {
		data, err := json.Marshal(tunnelClients)
		if err != nil {
			writer.Write([]byte("{'code':500,'msg':'json encode failed'}"))
			return
		}
		writer.Write(data)
	})
	err := http.ListenAndServe(addr, nil)
	if err != nil {
		log.Fatal("ListenAndServe: ", err)
	}
}
func WaitTunnelConn(lta *net.TCPListener) {
	i := 0
	for {
		aconn, err := lta.AcceptTCP()
		if err != nil {
			fmt.Println("accept agent error:", err)
			continue
		}
		log.Debugf("new agent connection accpet\n")
		i++
		go handleAgent(aconn)
	}
}

func waitProxyConn(ltp *net.TCPListener) {
	for {
		pconn, err := ltp.AcceptTCP()
		if err != nil {
			fmt.Println("accepct public error:", err)
			continue
		}
		fmt.Println("new proxy connect accept")
		go handleProxy(pconn)
	}
}
func handleAgent(conn net.Conn) {
	log.Debugf("new tunnel connection got!\n")

	var b [1024]byte
	n, err := conn.Read(b[:])
	if err != nil {
		conn.Close()
		return
	}
	log.Debugf("agent thread read %d bytes\n", n)
	var key, id, address string
	fmt.Sscanf(string(b[:bytes.IndexByte(b[:], '\n')]), "%s%s%s", &key, &id, &address)
	if key == "SIGN" {
		conn.Write([]byte("SIGN_OK " + id + "\n"))
		if strings.Index(address, ":") == -1 {
			address = address + ":80"
		}
		tunnelClient := TunnelClient{
			Id:   id,
			Conn: conn,
			Addr: conn.RemoteAddr().String(),
			Busy: false}
		lock4Connections.Lock()
		_, exists := tunnelClients[address]
		if exists {
			tunnelClients[address][id] = tunnelClient
		} else {
			tunnelClients[address] = make(map[string]TunnelClient)
			tunnelClients[address][id] = tunnelClient
		}
		lock4Connections.Unlock()
	} else {
		conn.Write([]byte("ERR invalid protocol\n"))
		conn.Close()
	}

	log.Debugf("thread handle Agent ready:%s,address:%s\n", id, conn.RemoteAddr())
	timer1 := time.NewTimer(600 * time.Second)
	<-timer1.C
	log.Debugf("timeout!\t \n")
	lock4Connections.Lock()
	if !tunnelClients[address][id].Busy {
		delete(tunnelClients[address], id)
	}
	lock4Connections.Unlock()
	conn.Close()
}
func handleProxy(conn net.Conn) {
	defer conn.Close()
	var b [8192]byte
	n, err := conn.Read(b[:])
	if err != nil {
		log.Error(err)
		return
	}
	log.Debugf("read bytes:%d\n", n)
	var method, host, address string
	fmt.Sscanf(string(b[:bytes.IndexByte(b[:], '\n')]), "%s%s", &method, &host)
	hostPortUrl, err := url.Parse(host)
	if err != nil {
		log.Error(err)
		return
	}
	if hostPortUrl.Opaque == "443" {
		address = hostPortUrl.Scheme + ":443"
	} else {
		if strings.Index(hostPortUrl.Host, ":") == -1 {
			address = hostPortUrl.Host + ":80"
		} else {
			address = hostPortUrl.Host
		}
	}

	cipher := tunnel.NewCipher("aes256cfb", []byte(secret))

	log.Debugf("connect to address:%v \n", address)
	log.Debugf("read:%v\n\n", string(b[:]))
	lock4Connections.Lock()
	var tunnelClient TunnelClient
	var found = false
	for k, v := range tunnelClients[address] {
		if v.Busy == false {
			tunnelClient = v
			v.Busy = true
			tunnelClients[address][k] = v
			found = true
			break
		}
	}
	lock4Connections.Unlock()
	log.Debugf("choose %s to serve\n", tunnelClient.Id)
	if !found {
		if method == "CONNECT" {
			fmt.Fprint(conn, "HTTP/1.1 504 Connection refused\r\n\r\n")
			conn.Close()
		} else {
			fmt.Fprint(conn, "HTTP/1.1 504 Connection refused\r\n\r\n")
			conn.Close()
		}
		return
	}

	if method == "CONNECT" {
		fmt.Fprint(conn, "HTTP/1.1 200 Connection established\r\n\r\n")
		log.Debugf("echo to proxy client ")
	} else {
		log.Warnf("request head info(before encode):%d bytes,%s\n", n, string(b[:n]))
		if secureData {
			cipher.Encrypt(b[:n], b[:n])
		}
		log.Warnf("request head info(after encode):%d bytes,%s\n", n, base64.StdEncoding.EncodeToString(b[:n]))
		writeN, err := tunnelClient.Conn.Write(b[:n])
		log.Warnf("write %d bytes request data\n", writeN)
		if err != nil {
			log.Errorf("cant write data to tunnel connection:%v\n", err)
			netConn := TunnelConnRoute{
				Id:      tunnelClient.Id,
				Address: address,
				Conn:    tunnelClient.Conn}
			brokenConn <- netConn
			//这时就直接返回，并且通知，该tunnel 不在了;
			return
		} else {
			log.Debugf("write to tunnel done\n")
		}
	}
	//找到对应的
	readChan := make(chan int64)
	writeChan := make(chan int64)
	var readBytes, writeBytes int64
	go func() {
		n, _, eW, eR := PPipe(conn, tunnelClient.Conn, time.Millisecond*1000, "R", cipher, address, tunnelClient.Id)
		writeChan <- n
		log.Debugf(" while read from tunnel and write to proxy:\t ew:%v \t er:%v\n", eW, eR)
	}()

	go func() {
		n, _, eW, eR := PPipe(tunnelClient.Conn, conn, time.Millisecond*300, "W", cipher, address, tunnelClient.Id)
		readChan <- n
		log.Debugf(" while read from proxy and write to tunnel:\t ew:%v \t er:%v\n", eW, eR)
	}()
	//log.Debugf("t1 time:%d\n",time.Now().String())

	//log.Debugf("t2 time:%d\n",time.Now().String())

	writeBytes = <-writeChan
	if writeBytes < 0 {
		log.Errorf("there was error:%s ;when read from proxy side.\n", tunnelClient.Id)
	}

	readBytes = <-readChan
	if readBytes < 0 {
		log.Errorf("there was error:%s ;when read from proxy side.\n", tunnelClient.Id)
	}

	log.Warnf("read %d ,write %d of address:%s ,id:%s \n", readBytes, writeBytes, address, tunnelClient.Id)
	conn.Close()
	lock4Connections.Lock()
	tunnelClient.Busy = false
	tunnelClients[address][tunnelClient.Id] = tunnelClient
	lock4Connections.Unlock()

	log.Debugf("rollback client status of id %s to new status:%v\n", tunnelClient.Id, tunnelClient.Busy)
}

//func pipe(dst, src net.Conn, c chan int64,id string,msg string) {
//	n, err := io.Copy(dst, src)
//	if err != nil {
//		log.Errorf("Error while io.Copy: %v,id:%s,msg:%s\n",err,id,msg)
//		n = -1
//	}
//	c <- n
//}

func PPipe(dst net.Conn, src net.Conn, duration time.Duration, m string, cipher *tunnel.Cipher, adderss string, id string) (written int64, writer net.Conn, errorRead error, errorWrite error) {
	var buf []byte
	size := 16 * 1024
	buf = make([]byte, size)
	log.Debugf("m:%s\n", m)
	errorRead = nil
	errorWrite = nil

	for {
		src.SetReadDeadline(time.Now().Add(duration))
		nr, er := src.Read(buf)
		log.Debugf("read again from :%s \n", src.LocalAddr())
		if nr > 0 {
			//临时创建写入端的Connection
			if secureData {
				log.Debugf("content before decrypt:%s\n", string(buf[0:nr]))
				if m == "R" {
					cipher.Decrypt(buf[0:nr], buf[0:nr])
				}
				if m == "W" {
					cipher.Encrypt(buf[0:nr], buf[0:nr])
				}
			}
			nw, ew := dst.Write(buf[0:nr])
			log.Debugf("dst write:%d bytes\n", nw)
			log.Debugf("content after decode():%s\n", string(buf[0:nr]))
			if nw > 0 {
				written += int64(nw)
			}
			if ew != nil {
				errorWrite = ew
				break
			}
			if nr != nw {
				errorWrite = io.ErrShortWrite
				break
			}
		}
		if er != nil {
			if er != io.EOF {
				errorRead = er
			} else {
				log.Errorf("GOT EOF! model:%s\n", m)
				errorRead = er
				if m == "R" {
					route := TunnelConnRoute{
						Id:      id,
						Conn:    src,
						Address: adderss}
					brokenConn <- route
				}
			}
			break
		}
	}
	return written, dst, errorRead, errorWrite
}
