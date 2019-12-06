package trie

import (
	"bufio"
	"fmt"
	"io"
	"log"
	"os"
	"path/filepath"
	"strings"
	"time"
)

var blackTrie *Trie
var whitePrefixTrie *Trie
var whiteSuffixTrie *Trie

// InitAllTrie 初始化三种Trie
func InitAllTrie() {
	BlackTrie()
	WhitePrefixTrie()
	WhiteSuffixTrie()
}

// BlackTrie 返回黑名单Trie树
func BlackTrie() *Trie {
	if blackTrie == nil {
		blackTrie = NewTrie()
		blackTrie.CheckWhiteList = true

		loadDict(blackTrie, "add", "./dicts/black/default")
		loadDict(blackTrie, "del", "./dicts/black/exclude")
	}
	return blackTrie
}

// WhitePrefixTrie 返回白名单前缀Trie树
func WhitePrefixTrie() *Trie {
	if whitePrefixTrie == nil {
		whitePrefixTrie = NewTrie()
		loadDict(whitePrefixTrie, "add", "./dicts/white/prefix")
	}
	return whitePrefixTrie
}

// ClearWhitePrefixTrie 清空白名单前缀Trie树
func ClearWhitePrefixTrie() {
	whitePrefixTrie = NewTrie()
}

// WhiteSuffixTrie 返回白名单后缀Trie树
func WhiteSuffixTrie() *Trie {
	if whiteSuffixTrie == nil {
		whiteSuffixTrie = NewTrie()
		loadDict(whiteSuffixTrie, "add", "./dicts/white/suffix")
	}
	return whiteSuffixTrie
}

// ClearWhiteSuffixTrie 清空白名单后缀Trie树
func ClearWhiteSuffixTrie() {
	whiteSuffixTrie = NewTrie()
}

func loadDict(trieHandle *Trie, op, path string) {

	var loadAllDictWalk filepath.WalkFunc = func(path string, f os.FileInfo, err error) error {
		if f == nil {
			return err
		}
		if f.IsDir() {
			return nil
		}

		initTrie(trieHandle, op, path)

		return nil
	}

	err := filepath.Walk(path, loadAllDictWalk)
	if err != nil {
		panic(err)
	}
}

func initTrie(trieHandle *Trie, op, path string) (err error) {
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
			tmp := strings.Split(word, " ")
			s := strings.Trim(tmp[0], " ")

			if "add" == op {
				trieHandle.Add(s)

			} else if "del" == op {
				trieHandle.Del(s)
			}
		}
	}

	return
}
