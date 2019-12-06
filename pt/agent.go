package main

import (
	"bytes"
	"encoding/base64"
	"flag"
	"fmt"
	"github.com/siddontang/go-log/log"
	"github.com/yuanfenxi/tunnel"
	"io"
	"net"
	"os"
	"os/signal"
	"syscall"
	"time"
	"strings"
)

var agentSecret string

const DirectionTunnel = 1
const DirectionBackend = 2

var threads int
var secure = false

func handleSig() {
	for {
		signChan := make(chan os.Signal, 1)
		signal.Notify(signChan)
		for sig := range signChan {
			if sig == syscall.SIGINT || sig == syscall.SIGTERM {
				log.Printf("terminated by signal %v\n", sig)
				os.Exit(0)
			} else {
				log.Printf("received :%v,ignore\n", sig)
			}
		}
	}

}
func main() {
	var tunnelAddr = "127.0.0.1:8188"
	var backendAddr = "127.0.0.1:9944"
	log.SetLevel(log.LevelError)
	var level string
	flag.StringVar(&level, "level", "error", "log level ,may be: DEBUG/INFO/WARN/ERROR/FATAL")
	flag.StringVar(&agentSecret, "secret", "Ilove9527", "the secret when create tunnel")
	flag.StringVar(&tunnelAddr, "tunnelAddr", "127.0.0.1:2345", "tunnel Server Address\n default is 127.0.0.1:2345")
	flag.StringVar(&backendAddr, "backendAddr", "127.0.0.1:80", "the real backend server address,default for 127.0.0.1:80")
	flag.BoolVar(&secure, "secure", false, "secure transfer,default value:false")
	flag.IntVar(&threads, "threads", 8, "how many tunnel channel created!")
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

	for i := 0; i < threads; i++ {
		go loop(tunnelAddr, fmt.Sprintf("id-%d-%s", i, backendAddr), backendAddr)
	}
	handleSig()
}
func loop(pAddr string, id string, bAddr string) {
	log.Debugf("loop %s start!\n", id)
	for {
		time.Sleep(time.Millisecond * 20)
		singleLoop(pAddr, id, bAddr)
	}
	log.Debugf("loop %s done!\n", id)
}
func singleLoop(pAddr string, id string, bAddr string) {
	log.Debugf("%s re-Dialed \n", id)
	//var id = "vjt.test:80"
	tunnelConn, err := net.Dial("tcp", pAddr)
	if err != nil {
		log.Printf("can't connect to tunnel :%v\n", pAddr)
		time.Sleep(time.Millisecond * 300)
		return
	}
	defer tunnelConn.Close()
	_, err = tunnelConn.Write([]byte("SIGN " + id + " " + bAddr + "\n"))
	if err != nil {
		log.Errorf("can't sign in tunnel server:%v\n", err)
		return
	}
	var b [1024]byte
	_, err = tunnelConn.Read(b[:])
	if err != nil {
		log.Errorf("can't read echo info of sign in with tunnel server.%v\n", err)
		return
	}
	var command, echo_id string
	fmt.Sscanf(string(b[:bytes.IndexByte(b[:], '\n')]), "%s%s%s", &command, &echo_id)
	if command != "SIGN_OK" {
		log.Error("do not receive SIGN_OK from server\n")
		return
	}
	if echo_id != id {
		log.Error("tunnel server do not echo with correct id\n")
		return
	}
	log.Debugf("id %s ready\n", id)
	for i := 0; i < 100; i++ {
		log.Debug("\n\n\n------------" +
			"n\n\n")
		written, backendConn, readError, writeError := Pipe(nil, tunnelConn, bAddr, time.Millisecond*400, "R", agentSecret)
		if readError != nil {
			if written == 0 {
				log.Debugf("error while read from tunnel:read nothing\n", readError)
				continue
			}
		}
		if writeError != nil {
			log.Debugf("error while write to backend:%v\n", writeError)
			if backendConn != nil {
				backendConn.Close()
			}
			continue
		}
		log.Debugf("read from tunnel and write to backend:%d bytes done!\n", written)
		if written <= 0 {
			if backendConn != nil {
				backendConn.Close()
			}
			log.Debugf("write failed!\n")
			continue
		}
		log.Debugf("now read the backend and write to tunnel\n")
		written, _, readError, writeError = Pipe(tunnelConn, backendConn, "", time.Millisecond*2000, "W", agentSecret)
		if readError != nil {
			log.Debugf("error while read from backend;%v\n", readError)
			continue
		}
		if writeError != nil {
			log.Debugf("error while write to tunnel:%v\n", writeError)
			continue
		}
		if backendConn != nil {
			backendConn.Close()
		}
		if written > 0 {
			log.Debugf("write to tunnel succeed!\n")
		}
	}

}

func Pipe(dst net.Conn, src net.Conn, dstAddr string, duration time.Duration, m string, secret string) (written int64, writer net.Conn, errorRead error, errorWrite error) {
	var buf []byte
	size := 16 * 1024
	buf = make([]byte, size)

	errorRead = nil
	errorWrite = nil
	cipher := tunnel.NewCipher("aes256cfb", []byte(agentSecret))

	for {
		//if m != "R" {
		src.SetReadDeadline(time.Now().Add(duration))

		nr, er := src.Read(buf)
		log.Debugf("read again\n")
		if nr > 0 {
			//临时创建写入端的Connection

			if dstAddr != "" && dst == nil {
				log.Debugf("backend reconnecting...\n")
				var err error
				dst, err = net.Dial("tcp", dstAddr)
				if err != nil {
					log.Debugf("can't connect to writer :%v\n", dstAddr)
					return 0, nil, nil, err
				}
				log.Debugf("backend connected!\n")
			}
			if secure {
				if m == "R" {
					log.Warnf("read request data(secured):%s\n", base64.StdEncoding.EncodeToString(buf[:nr]))
					cipher.Decrypt(buf[0:nr], buf[0:nr])
					log.Warnf("read request data(text):%s\n", string(buf[0:nr]))
				}
				if m == "W" {
					log.Warnf("response: %d bytes before encrypt:%s\n", nr, string(buf[0:nr]))
					cipher.Encrypt(buf[0:nr], buf[0:nr])
					log.Warnf("response after encrypt:%s\n", base64.StdEncoding.EncodeToString(buf[0:nr]))
				}
			}

			nw, ew := dst.Write(buf[0:nr])
			log.Debugf("dst write:%d bytes\n", nw)
			if nw > 0 {
				written += int64(nw)
			}
			if ew != nil {
				if m == "R" {
					log.Warnf("write to backend failed:%v\n", ew)
				} else {
					log.Warnf("write to tunnel failed:%v\n", ew)
				}
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
				log.Errorf("GOT EOF! while %s mode\n", m)
				errorRead = er
			}
			break
		}
	}
	return written, dst, errorRead, errorWrite
}
