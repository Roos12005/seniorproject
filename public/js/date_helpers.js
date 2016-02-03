function DateHelper() {   
}

DateHelper.prototype.toDateFormat = function(date) {
    return date.substr(0,4) + '/' + date.substr(4,2) + '/' + date.substr(6,2);
}