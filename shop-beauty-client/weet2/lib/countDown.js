let countDown = function(time){
    let s = (time % 60).toString();
    let m = (time / 60 % 60).toString();
    let h = (time / 3600).toString();
    s = parseInt(s) < 10 ? "0" + s : s;
    m = parseInt(m) < 10 ? "0" + m.substr(0, 1) : m.substr(0, 2);
    h = parseInt(h) > 99 ? h.substr(0, 3) : parseInt(h) < 10 ? "0" + h.substr(0, 1) : h.substr(0, 2);
    var cd = h + ':' + m + ':' + s;
    return {
        cd
    }
}
module.exports = {
    countDown
}