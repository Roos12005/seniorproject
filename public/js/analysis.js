function fullGraphClick(){


        $.ajax({
            url: 'http://localhost/seniorproject/public/runMaven',
            type: 'GET',

            success: function(e)
            {
                $.ajax(
                {   
                    url: 'http://localhost/seniorproject/public/getCDR',
                    type: 'GET',
                    data: {},
                    dataType: 'json',
                    success: function(data)
                    {
                        setDate();
                        setStatus();
                        var user_array = new Array();
                        var communities = new Array();
                        $.each(data.nodes, function(index, user_info) {
                            if(!isInArray(user_info['attributes']['Modularity Class'],communities)){
                                communities.push(user_info['attributes']['Modularity Class']);
                            }
                            user_array.push(user_info);
                        });
                        console.log(communities);
                        document.getElementById('unique_numbers').innerHTML = user_array.length;
                        document.getElementById('communities').innerHTML = communities.length;
                    }
                });

            },
            error : function(rs, e) {
                console.log(rs.responseText);
            }
        });
}

function setDate(){
    var year = 0;
    document.getElementById('date').innerHTML = year;
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

function isInArray(value, array) {
  return array.indexOf(value) > -1;
}

