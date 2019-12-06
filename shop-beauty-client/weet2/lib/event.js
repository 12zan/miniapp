const misc = require("./misc.js");
const md5 = require("./md5.js");
module.exports = {

    /**
     *  调用的时候在page的Onload方法里:Weet.page.onLoad.call(getApp(),this,options,"someEventData",0)
     * @param page
     * @param options
     * @param eventData
     * @param eventDataNum
     */
    onLoad:function(page,options,eventData,eventDataNum){
        page.options = options;
        page.rn = md5(Math.random()+new Date()+"secretYuanli");
        page.pageUrl = page.route;
        page.pageId = page.pageId|"";
        eventData = eventData||"";
        eventDataNum = eventDataNum||0;
        //this.rn = rn;
        misc.reportEvent(this,page,"onLoad",eventData,eventDataNum);
    },
    /**
     * 这里的options,是app启动的options
     * 需要将this绑定为App
     * @param options
     */
    onLaunch:function(options){
        try{
            misc.launch.call(this,options);
            misc.reportEvent(this,{},"onLaunch","",0);
        }catch(e){

        }
    },
    onShow:function () {
       // this.rn  = md5(Math.random()+new Date()+"secretYuanli");
        //misc.reportEvent(app,this,);
    },

};