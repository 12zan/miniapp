let formatTime = function (date) {
    var date = new Date(date);
    const Y = formatNumber(date.getFullYear());
    const M = formatNumber(date.getMonth() + 1);
    const D = formatNumber(date.getDate());
    const h = formatNumber(date.getHours());
    const m = formatNumber(date.getMinutes());
    const s = formatNumber(date.getSeconds());
    return {
        Y,M,D,h,m,s
    };
}

let formatNumber = function (n) {
    n = n.toString();
    return n[1] ? n : '0' + n;
}

module.exports = {
    formatTime, formatNumber,
    generateUIDNotMoreThan1million: function (n) {
        let str = "";
        for (let i = 0; i < n; i++) {
            str = str + ("0000)" +
                (
                    Math.floor(Math.random() * Math.pow(36, 5))
                ).toString(36)).slice(-5);
        }
        return str;
    },
    getUuid: options => {
        let rid = (options.rid);
        try {
            if (!options.rid && ((typeof options.scene) == "string") && options.scene.length == 32) {
                rid = options.scene;
            }
        } catch (e) {
        }
        return rid;
    }
};