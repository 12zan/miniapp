const XUrl = function(url,data,app){
    return new Promise(function(resolve, reject) {
        let options = {};
        options["url"] = url;
        options["data"] = data;
        options["dataType"] = "json";
        options.success =  res =>{
            if (res.statusCode != 200) {
                app.log("请求错误",res);
                app.reportEvent("requestFailed",app.name);
                reject({msg:"服务器返回异常",data:res});
                return;
            }
            if (res.data.code != 200) {
                app.log("服务器返回错误",res.data);
                app.reportEvent("requestFailed",app.name);
                reject({msg:(res.data.data||"服务器返回异常"),data:{}});
                return;
            }
            app.reportEvent("requestSucceed",app.name);
            resolve(res.data);
        };
        options.fail=function(msg={}){
            app.log("发送请求失败",msg);
            app.reportEvent("requestFailed",app.name);
            reject({msg:"网络异常",data:{reason:"wx.request.fail"}});
        };
        wx.request(options);
    });
};
const XUpload = function(url,filepath,app,progressCallback,data={}){
    return new Promise(function(resolve, reject) {
        let options = {};
        options["url"] = url;
        options["data"] = data;
        options["dataType"] = "json";
        options["filePath"] = filepath;
        options["name"] = "file";
        options["header"] = { "Content-Type": "multipart/form-data" };
        options["formData"] = data;

        options.success =  res =>{
            if (res.statusCode != 200) {
                app.log("请求错误",res);
                app.reportEvent("uploadFileFailed",app.name);
                reject({msg:"服务器返回异常",data:res});
                return;
            }
            let data = JSON.parse(res.data);
            if (data.code != 200) {
                app.log("服务器返回错误",data);
                app.reportEvent("uploadFileFailed",app.name);
                reject({msg:(data.msg||"服务器返回异常"),data:{}});
                return;
            }
            app.reportEvent("uploadFileSucceed",app.name);
            resolve(data);
        };
        options.fail=function(msg={}){
            app.reportEvent("uploadFileFailed",app.name);
            reject({msg:"网络异常",data:{reason:"wx.request.fail"}});
        };
        let uploadTask = wx.uploadFile(options);
        uploadTask.onProgressUpdate(progressCallback);
    });
};
module.exports = {
    XUrl:XUrl,
    XUpload:XUpload
};