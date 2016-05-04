function DateHelper() {   
}

DateHelper.prototype.toDateFormat = function(date) {
    return date.substr(0,4) + '/' + date.substr(4,2) + '/' + date.substr(6,2);
}

DateHelper.prototype.toReadable = function(ms) {
    var seconds = Math.floor((ms/1000)%60);
    var minutes = Math.floor((ms/(1000*60))%60);
    var hours = Math.floor((ms/(1000*60*60))%24);
    return (hours > 0 ? hours + 'h ' : '') + (minutes > 0? minutes + 'm ' : '' ) + (seconds > 0 ? seconds : '1') + 's';
}