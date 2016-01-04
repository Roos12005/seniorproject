function fullGraphClick(){
        $.ajax(
        {   
            url: '/getCDR',
            type: 'GET',
            data: {},
            dataType: 'json',
            success: function(data)
            {
                setDate();
                setStatus();
                var user_array = new Array();
                $.each(data.cdr_list, function(index, user_info) {
                    user_array.push(user_info);
                });
                document.getElementById('unique_numbers').innerHTML = user_array.length;
            }
        });
}

function setDate(){
    document.getElementById('date').innerHTML = document.getElementById('e1').value;
}

function setStatus(){
    var weekdays = document.getElementById('weekdays').checked;
    var weekends = document.getElementById('weekends').checked;
    var dayTime = document.getElementById('dayTime').checked;
    var nightTime = document.getElementById('nightTime').checked;
    var ans = "";
    if(weekdays && !weekends) ans = ans + "Weekdays |";
    else if(!weekdays && weekends) ans = ans + "Weekends |";
    else ans = ans + "Everyday |";
    if(dayTime && !nightTime) ans = ans + " Day Time";
    else if(!dayTime && nightTime) ans = ans + " Night Time";
    else ans = ans + " All Time";

    document.getElementById('filter').innerHTML = ans;
}

