#敏感词过滤服务
---

基于词典的敏感词过滤程序

程序敏感词词典使用Trie树存储， 提供HTTP API访问

## 使用

```
go get github.com/huayuego/wordfilter

cd $GOPATH/src/github.com/huayuego/wordfilter/service

// 启动HTTP服务
go run main.go 127.0.0.1:8080
```

然后访问 http://127.0.0.1:8080/v1/query?q=文本内容

## API

## 敏感词

### 1.查找敏感词
输入一段文本，返回敏感词及敏感词替换为*号后的文本

* **Request:**  /v1/query
* **Request Method:** GET or POST 
* **Params**:

| Name | Type | Requried | Example | Desc. |
| ---- | ---- | -------- | ------- | ----- |
| q | string | yes | | 需要检查的文本内容 |

*  **Response:**
```
{
  "code": 1,
  "error": "", // 当code=0时，返回的错误消息
  "keywords": ["k1","k2"], //敏感词
  "text": "" //将敏感词替换为*号后的文本
}
```

### 2.添加敏感词

添加一组敏感词

* **Request:**  /v1/black_words 
* **Request Method:** POST 
* **Params**:

| Name | Type | Requried | Example | Desc. |
| ---- | ---- | -------- | ------- | ----- |
| q    | string | yes  | 你大爷,走私 | 敏感词，多个之间与逗号相隔 |

*  **Response:**
```
{
  "code": 1,
  "error": "", // 当code=0时，返回的错误消息
}
```

### 3.删除敏感词

删除一组敏感词

* **Request:**  /v1/black_words 
* **Request Method:** DELETE 
* **Params**:

| Name | Type | Requried | Example | Desc. |
| ---- | ---- | -------- | ------- | ----- |
| q    | string | yes  | 你大爷,走私 | 敏感词，多个之间与逗号相隔 |

*  **Response:**
```
{
  "code": 1,
  "error": "", // 当code=0时，返回的错误消息
}
```

### 4.查看所有敏感词

* **Request:**  /v1/black_words 
* **Request Method:** GET
* **Response:**
```
陪睡
陪聊
```

## 白名单

### 1.添加白名单（前缀）词组

* **Request:**  /v1/white_prefix_words
* **Request Method:** POST 
* **Params**:

| Name | Type | Requried | Example | Desc. |
| ---- | ---- | -------- | ------- | ----- |
| q    | string | yes  | 路口,司机 | 词组，多个之间与逗号相隔 |

*  **Response:**
```
{
  "code": 1,
  "error": "", // 当code=0时，返回的错误消息
}
```

### 2.添加白名单（后缀）词组

* **Request:**  /v1/white_suffix_words 
* **Request Method:** POST 
* **Params**:

| Name | Type | Requried | Example | Desc. |
| ---- | ---- | -------- | ------- | ----- |
| q    | string | yes  | 路口,司机 | 词组，多个之间与逗号相隔 |

*  **Response:**
```
{
  "code": 1,
  "error": "", // 当code=0时，返回的错误消息
}
```

### 3.查看白名单（前缀）词组

* **Request:**  /v1/white_prefix_words 
* **Request Method:** GET
* **Response:**
```
路口
司机
```

### 4.查看白名单（后缀）词组

* **Request:**  /v1/white_suffix_words 
* **Request Method:** GET
* **Response:**
```
路口
司机
```

## 词库说明
敏感词词库在 dicts 目录里
每个敏感词独立一行。

- dicts/black/default 默认载入的敏感词词典

- dicts/black/exclude 默认载入的敏感词词典中需要删除的字词
  如black/default中有”情色“, 在black/exclude中也有”情色“, 则表示排除掉了”情色“这个词,不会过滤这个词了

- dicts/white 白名单
- dicts/white/prefix 白名单(前缀)
- dicts/white/suffix 白名单(后缀)

  对于敏感词 "口交"，”机8", 如果原文是 “xx路口交通事故”， ”阿司机82岁“ 之类的，会误判
  故，需要建议白名单机制：
  在prefix/default.txt中写  "司机"，
  在suffix/default.txt中写  "交通事故"
  就能解决此问题

