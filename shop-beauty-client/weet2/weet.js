const misc = require("./lib/misc.js");
const wxwrapper = require("./lib/wxwrapper.js");
const util = require("./lib/util.js");
const md5 = require("./lib/md5.js");
const event = require("./lib/event.js");
const countDown = require('./lib/countDown.js');
let exports = {
    formatTime: util.formatTime,
    generateUIDNotMoreThan1million: util.generateUIDNotMoreThan1million,
    formatNumber: util.formatNumber,
    getUuid: util.getUuid,
    onError: misc.onError,
    reportEvent: misc.reportEvent,
    switchPopUp: misc.switchPopUp,
    launch: misc.launch,
    login: misc.login,
    consoleLog: misc.consoleLog,
    XUrl: wxwrapper.XUrl,
    XUpload: wxwrapper.XUpload,
    md5: md5,
    event: event,
    misc: misc,
    util: util,
    wxwrapper: wxwrapper,
    zan: require("./lib/zan.js"),
    jump: misc.jump,
    countDown: countDown.countDown
};


module.exports = exports;
