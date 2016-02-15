function Validator() {   
}

Validator.prototype.validateMinRange = function(n) {
    if(n > 2147483647) {
        return 2147483647;
    } else if(n < 0 || n == "") {
        return 0;
    }
    return parseInt(n);
}

Validator.prototype.validateMaxRange = function(n) {
    if(n > 2147483647) {
        return 2147483647;
    } else if(n < 0 || n == "") {
        return -1;
    }
    return parseInt(n);
}

Validator.prototype.validateMinTime = function(t) {
    if(parseFloat(t) > 24.00) {
        return 24.00;
    } else if(t < 0 || t == "") {
        return 0.00;
    }
    return parseFloat(t);
}

Validator.prototype.validateMaxTime = function(t) {
    if(parseFloat(t) > 24.00) {
        return 24.00;
    } else if(t < 0 || t == "") {
        return -1;
    }
    return parseFloat(t);
} 

