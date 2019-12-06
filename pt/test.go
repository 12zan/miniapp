package main

import (
	"./tunnel"
	"fmt"
	"encoding/base64"
	"github.com/siddontang/go-log/log"
)
func main() {
	cipher := tunnel.NewCipher("aes256cfb",[]byte("Ilove9527"))

	old := make([]byte,1024)

	buf,er := base64.StdEncoding.DecodeString("cEWr3UvvoM10hofKfh7Me/lA3xoZUWJrIqanA4Qa9RdwhY/rdyadIlZVGUkSpKMErhZPQxEcJzRLp9SnMjvq4H96rcyiOKVi1cSPsAW6AUfeAMASGMUiHdKLYqtxaln5tB61cLeN3mbIMIRmJxNgbxKLwXtq5iFW9NCrDddA0gajRw==")
	if er!=nil {
		log.Panic("can't decode")
	}
	fmt.Printf("parsed len:%d\n",len(buf))
	copy(old,buf)
	//old[130]='a'
	//old[131]='b'
	//old[132]='c'
	fmt.Printf("parsed len of old:%d\n",len(old))
	//old := []byte("Wo are family")

	//cipher.Encrypt(old,old)
	//fmt.Printf("encoded:%s\n",base64.StdEncoding.EncodeToString(old))

	cipher.Decrypt(old[:],old[:len(buf)])
	fmt.Printf("decode:%s\n",string(old))

}