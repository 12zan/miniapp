package main

// #cgo CFLAGS: -I/www/acfilter/src/
// #cgo LDFLAGS: -L/www/acfilter/src/ -lacseg
/*
#include <stdio.h>
#include <stdlib.h>
#include <errno.h>
#include "acseg_tree.h"
#include "acseg_rbtree.h"
#include "acseg_util.h"
static acseg_index_t * Acseg_new(){
	acseg_index_t * acseg_index;
	acseg_index = acseg_index_init();
	return acseg_index;
}
static void Acfilter_load_dict(acseg_index_t * acseg_index,char *dict_file_path){
	acseg_index=acseg_index_load(acseg_index,dict_file_path);
	if(acseg_index==NULL){
		errno = 2;
	}
}
static void Acfilter_prepare(acseg_index_t * acseg_index){
	acseg_index_fix(acseg_index);
}
static char *  Acfilter_check_text(acseg_index_t * acseg_index,char * otext,int text_len,long max_seek){
const char * text = (unsigned char * ) otext;
	acseg_result_t * seg_result;
	acseg_str_t acseg_text;
	acseg_text.data=otext;
	acseg_text.len=text_len;
	seg_result = acseg_full_seg(acseg_index, &acseg_text,max_seek);
	acseg_str_t *phrase;
	acseg_list_item_t *result_item;
	result_item = seg_result->list->first;

	char * ret;
	ret=malloc(sizeof(char)*2*text_len);
	bzero(ret,sizeof(char)*2*text_len);
	if(!ret){
		return NULL;
		//php_error(E_ERROR,"acfilter:can't alloc enough memory");
	}
	while (result_item) {
		phrase = (acseg_str_t *) result_item->data;
		result_item = result_item->next;
		strncat(ret,phrase->data,phrase->len);
		strcat(ret,"|");
	}
	acseg_destory_result(&seg_result);
	//printf("%s",ret);
	return ret;
}

static void FreeResult(char * result){
	if(result!=NULL){
		//printf("freeResult called %s","");
		free(result);
	}
}
static  char * Acfilter_add_word(acseg_index_t * acseg_index,char * oword,int word_len){
	unsigned char * word = (unsigned char * ) oword;
	acseg_str_t  phrase;
	phrase.data=word;
	phrase.len=word_len;
	acseg_index=acseg_index_add(acseg_index,&phrase);
	if(acseg_index==NULL){
		errno = 3;
	}
	return oword;
}


*/
import "C"
import (
	"encoding/json"
	"fmt"
	"io/ioutil"
	"net/http"
	"net/url"
	"os"
	"strings"
	"time"
	"./trie"
	"bufio"
	"io"
	"log"

	//"./dirtyfilter"
	//"./dirtyfilter/store"
	"flag"
)

type router struct {
}

var acseg *C.struct_acseg_index_s = C.Acseg_new()
//var filterManage   *filter.DirtyManager

func (ro *router) ServeHTTP(w http.ResponseWriter, r *http.Request) {
	switch r.URL.Path {
	case "/":
		apiHelper(w)
	case "/__status":
		statusResp(w)
	case "/v1/query": // 查找敏感词
		queryWords(w, r)
	case "/v1/black_words": // 敏感词
		blackWords(w, r)
	case "/v1/white_prefix_words": // 白名单（前缀）
		whitePrefixWords(w, r)
	case "/v1/white_suffix_words": // 白名单（后缀）
		whiteSuffixWords(w, r)
	default:
		notFound(w)
	}
}

func notFound(w http.ResponseWriter) {
	w.WriteHeader(http.StatusNotFound)
}

func statusResp(w http.ResponseWriter){
	w.WriteHeader(200)
	w.Write([]byte("status ok!"))
}

func apiHelper(w http.ResponseWriter) {
	help := make(map[string]string)
	help["/v1/query?q={text} [GET,POST] "] = "查找敏感词"

	help["/v1/black_words [GET]"] = "查看敏感词"
	help["/v1/black_words [POST]"] = "添加敏感词"
	help["/v1/black_words [DELETE]"] = "删除敏感词"

	help["/v1/white_prefix_words [GET]"] = "查看白名单（前缀）词组"
	help["/v1/white_prefix_words [POST]"] = "添加白名单（前缀）词组"

	help["/v1/white_suffix_words [GET]"] = "查看白名单（后缀）词组"
	help["/v1/white_suffix_words [POST]"] = "添加白名单（后缀）词组"

	serveJSON(w, help)
}

func QuerySuperBadWords (testing string) ([]string){
	result := AcfilterTest(acseg,testing)
	resultCleand := strings.TrimRight(result,"|")
	//C.FreeResult()
	return strings.Split(resultCleand,"|")
}
func queryWords(w http.ResponseWriter, r *http.Request) {
	paramName := "q"

	type resp struct {
		Code     int      `json:"code"`
		Error    string   `json:"error,omitempty"`
		Keywords []string `json:"keywords,omitempty"`
		SuperSearch []string `json:"superSearch,omitempty"`
		Text     string   `json:"text,omitempty"`
	}

	text := ""
	if r.Method == "GET" {
		params, err := url.ParseQuery(r.URL.RawQuery)
		if err == nil {
			if q, ok := params[paramName]; ok {
				text = q[0]
			}
		} else {
			fmt.Println(err)
		}

	} else if r.Method == "POST" {
		text = r.FormValue(paramName)
	}

	res := resp{
		Keywords: []string{},
		SuperSearch:[]string{},
	}

	if text != "" {
		res.Code = 1

		superBad := QuerySuperBadWords(text)
		res.SuperSearch = superBad

		ok, keyword, newText := trie.BlackTrie().Query(text)


		//result, err := filterManage.Filter().Filter(text, '*', '@')
		//fmt.Print(result)

		if ok  {
			//var target []string
			//for c :=  range keyword {
			//	target = append(target,keyword[c])
			//}
			//for c :=  range result {
			//	target = append(target,result[c])
			//}
			res.Keywords = keyword
			res.Text = newText
		}
	} else {
		res.Code = 0
		res.Error = "参数" + paramName + "不能为空"
	}
	serveJSON(w, res)
}

func blackWords(w http.ResponseWriter, r *http.Request) {
	if r.Method == "GET" {
		showBlackWords(w, r)
	} else if r.Method == "POST" {
		addBlackWords(w, r)
	} else if r.Method == "DELETE" {
		deleteBlackWords(w, r)
	}
}

func addBlackWords(w http.ResponseWriter, r *http.Request) {
	resp := make(map[string]interface{})
	q := r.FormValue("q")

	if q == "" {
		resp["code"] = 0
		resp["error"] = "参数q不能为空"
	} else {
		i := 0
		words := strings.Split(q, ",")
		for _, s := range words {
			trie.BlackTrie().Add(strings.Trim(s, " "))
			i++
		}

		resp["code"] = 1
		resp["mess"] = fmt.Sprintf("共添加了%d个敏感词", i)
	}

	serveJSON(w, resp)
}

func deleteBlackWords(w http.ResponseWriter, r *http.Request) {
	resp := make(map[string]interface{})

	q := r.FormValue("q")
	if q == "" {
		body, err := ioutil.ReadAll(r.Body)
		if err == nil {
			data := make(map[string]string)
			err = json.Unmarshal(body, &data)
			if err == nil {
				if qq, ok := data["q"]; ok {
					q = qq
				}
			}
		}
	}

	if q == "" {
		resp["code"] = 0
		resp["error"] = "参数q不能为空"
	} else {
		i := 0
		words := strings.Split(q, ",")
		for _, s := range words {
			trie.BlackTrie().Del(strings.Trim(s, " "))
			i++
		}

		resp["code"] = 1
		resp["mess"] = fmt.Sprintf("共删除了%d个敏感词", i)
	}
	serveJSON(w, resp)
}

func showBlackWords(w http.ResponseWriter, r *http.Request) {
	words := trie.BlackTrie().ReadAll()
	str := strings.Join(words, "\n")
	w.Header().Set("Server", "goo")
	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	w.WriteHeader(200)
	w.Write([]byte(str))
}

func whitePrefixWords(w http.ResponseWriter, r *http.Request) {
	if r.Method == "GET" {
		words := trie.WhitePrefixTrie().ReadAll()
		str := strings.Join(words, "\n")
		w.Header().Set("Server", "goo")
		w.Header().Set("Content-Type", "text/html; charset=utf-8")
		w.WriteHeader(200)
		w.Write([]byte(str))

	} else if r.Method == "POST" {
		resp := make(map[string]interface{})
		q := r.FormValue("q")
		op := r.FormValue("type")
		if op == "init" {
			trie.ClearWhitePrefixTrie()
		}

		if q == "" {
			resp["code"] = 0
			resp["error"] = "参数q不能为空"
		} else {
			i := 0
			words := strings.Split(q, ",")
			for _, s := range words {
				trie.WhitePrefixTrie().Add(strings.Trim(s, " "))
				i++
			}

			resp["code"] = 1
			resp["mess"] = fmt.Sprintf("共添加了%d个白名称前缀词", i)
		}

		serveJSON(w, resp)
	}
}

func whiteSuffixWords(w http.ResponseWriter, r *http.Request) {
	if r.Method == "GET" {
		words := trie.WhiteSuffixTrie().ReadAll()
		str := strings.Join(words, "\n")
		w.Header().Set("Server", "goo")
		w.Header().Set("Content-Type", "text/html; charset=utf-8")
		w.WriteHeader(200)
		w.Write([]byte(str))

	} else if r.Method == "POST" {
		resp := make(map[string]interface{})
		q := r.FormValue("q")
		op := r.FormValue("type")
		if op == "init" {
			trie.ClearWhiteSuffixTrie()
		}
		if q == "" {
			resp["code"] = 0
			resp["error"] = "参数q不能为空"
		} else {
			i := 0
			words := strings.Split(q, ",")
			for _, s := range words {
				trie.WhiteSuffixTrie().Add(strings.Trim(s, " "))
				i++
			}

			resp["code"] = 1
			resp["mess"] = fmt.Sprintf("共添加了%d个白名称后缀词", i)
		}

		serveJSON(w, resp)
	}
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

func loadDict(path string) []string {
	badwords := []string{}
	f, err := os.Open(path)
	if err != nil {
		panic(fmt.Sprintf("fail to open file %s %s", path, err.Error()))
	}

	defer f.Close()

	log.Printf("%s Load dict: %s", time.Now().Local().Format("2006-01-02 15:04:05 -0700"), path)

	buf := bufio.NewReader(f)
	for {
		line, isPrefix, e := buf.ReadLine()
		if e != nil {
			if e != io.EOF {
				err = e
			}
			break
		}
		if isPrefix {
			continue
		}

		if word := strings.TrimSpace(string(line)); word != "" {
			badwords = append(badwords,word)
		}
	}
	return badwords
}
func main() {
	ipAddr := ":8080"
	if len(os.Args) > 1 {
		ipAddr = os.Args[1]
	}

	trie.InitAllTrie()
	var dictPath  = flag.String("dictPath", "./dicts/super/dict.txt", "dict file for super bad words")

	C.Acfilter_load_dict(acseg,C.CString(*dictPath))
	C.Acfilter_prepare(acseg)

	//badwords := loadDict("../dicts/super/dict.txt")
	//
	//memStore, err := store.NewMemoryStore(store.MemoryConfig{
	//	DataSource: badwords,
	//})
	//if err != nil {
	//	panic(err)
	//}
	//jsonStr,er := json.Marshal(badwords)
	//if( er!=nil ){
	//	log.Panic(er)
	//}
	//fmt.Println(string(jsonStr))
	//filterManage = filter.NewDirtyManager(memStore)
	//filterText := "我要测试法轮功"
	//result, err := filterManage.Filter().Filter(filterText, '*', '@')
	//fmt.Print(result)

	t := time.Now().Local().Format("2006-01-02 15:04:05 -0700")
	fmt.Printf("%s Listen %s\n", t, ipAddr)
	http.ListenAndServe(ipAddr, &router{})
}



func AcfilterTest( acseg *C.struct_acseg_index_s,test string) (result string ){
	var max C.long = 12
	var length C.int
	length = C.int(len(test))
	var x *C.char = C.Acfilter_check_text(acseg, C.CString(test), length,max)
	//fmt.Println("now is the result 2")
	//fmt.Println(C.GoString(x))
	var temp string = C.GoString(x)
	C.FreeResult(x)
	return temp
}


func mainp() {
	//fmt.Println(C.count)
	//C.foo()
	var ps C.long = 32
	fmt.Println(ps)
	var test = "中华人民共和国"
	fmt.Print(len(test))
	 var dictPath  = flag.String("dictPath", "./dict.txt", "dict file ")

	var acseg *C.struct_acseg_index_s = C.Acseg_new()

	z1,x1 := C.Acfilter_add_word(acseg, C.CString("中华"), 6)

	fmt.Printf("add-world 1:%q\n",*z1)
	if( x1 !=nil ){
		fmt.Println("got some error while add 中华")
		fmt.Println(x1)

	}
	z2,x2 := C.Acfilter_add_word(acseg, C.CString("hello world"), 11)
	fmt.Printf("add-world 2:%q\n",*z2)
	if (x2 !=nil ){
		fmt.Println("got some error while add hello world")
		fmt.Println(x2)

	}
	C.Acfilter_load_dict(acseg,C.CString(*dictPath))

	//fmt.Printf("length of zhongguo falun gong:%d\n",len("中国法轮功"))
	C.Acfilter_prepare(acseg)
	//var max C.long = 6
	//var p *C.char = C.acfilter_check_text(acseg, C.CString("中国法-轮功"), 16, max)
	////fmt.Printf("%s",p)
	//fmt.Println("now is the result")
	//fmt.Printf("now is the result:%s\n",C.GoString(p))
	//
	//
	//var x *C.char = C.acfilter_check_text(acseg, C.CString("Hey hello world"), 15, max)
	//fmt.Println("now is the result 2")
	//fmt.Println(C.GoString(x))
	//
	//var t1 *C.char  = C.CString("中国人民")
	//var t2 * C.char  = C.test(t1)
	//fmt.Println(C.GoString(t2))


	testing := "中华人民共和国，法-轮**功,赵家紫阳"
	result := AcfilterTest(acseg,testing)
	fmt.Printf(result)
}
