package main

import (
	"bufio"
	"fmt"
	"io"
	"os"
	"path/filepath"
	"strings"
	"time"
)

// 将敏感词词典中重复的关键字清除掉
func main() {
	path := "../dicts/black/default"

	var loadAllDictWalk filepath.WalkFunc = func(path string, f os.FileInfo, err error) error {
		if f == nil {
			return err
		}
		if f.IsDir() {
			return nil
		}
		loadByLine(path)
		return nil
	}

	err := filepath.Walk(path, loadAllDictWalk)
	if err != nil {
		panic(err)
	}
}

func loadByLine(path string) (err error) {
	f, err := os.Open(path)
	if err != nil {
		fmt.Printf("fail to open file %s %s", path, err.Error())
		return
	}

	defer f.Close()

	fmt.Printf("%s Load dict: %s\n", time.Now().Local().Format("2006-01-02 15:04:05 -0700"), path)

	keywords := make(map[string]int)

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
			if s == "" {
				continue
			}

			if _, ok := keywords[s]; !ok {
				keywords[s] = 1
			} else {
				keywords[s]++
			}
		}
	}

	tmpKw := [50000][]string{}
	l := 0
	for k := range keywords {
		l = len([]rune(k))
		tmpKw[l] = append(tmpKw[l], k)
	}

	ff, err := os.Create(path + ".txt")
	defer ff.Close()

	for _, kw := range tmpKw {
		for _, k := range kw {
			ff.WriteString(k + "\n")
		}
	}

	return
}
