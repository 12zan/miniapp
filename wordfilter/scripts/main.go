package main


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
	"../trie"
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
	return strings.Split(result,"|")
}
func queryWords(w http.ResponseWriter, r *http.Request) {
	paramName := "q"

	type resp struct {
		Code     int      `json:"code"`
		Error    string   `json:"error,omitempty"`
		Keywords []string `json:"keywords,omitempty"`
		SuperSearch []string `json:"keywords,omitempty"`
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
			var target []string
			for c :=  range keyword {
				target = append(target,keyword[c])
			}
			//for c :=  range result {
			//	target = append(target,result[c])
			//}
			res.Keywords = target
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
