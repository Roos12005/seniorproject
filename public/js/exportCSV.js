!function(){

    var graphStatus = {
        'export-data' : 0,
    }

    function addCommunity(){
        $.ajax(
        {
            url: 'http://localhost/seniorproject/public/getCommunities',
            type: 'GET',
            data: {},
            dataType: 'json',
            success: function(e)
            {
                console.log(e);
                $.each(e, function(val, text) {
                     $('#e2').append($('<option></option>').val(val).html("Community  " + val));
                });
            }
        });
    }

    function ajaxSetup(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    function processData() {
        if(graphStatus['export-data'] == 0) {
            graphStatus['export-data'] = 1;
            $('#export-data').removeClass('btn-default').addClass('btn-warning');
            $('#export-data i').removeClass('fa-times').addClass('fa-refresh');
            ajaxSetup();

            var selectedCommunities = [];    
            $("#e2 :selected").each(function(){
                selectedCommunities.push($(this).val());
            });

            var export_communities = new Array();

            $.ajax({
                type: "GET",
                url: "http://localhost/seniorproject/public/getNodeCommunity",
                data : {"senddata":selectedCommunities},
                success: function(e){
                    console.log(e);
                    $('#export-data').removeClass('btn-warning').addClass('btn-success');
                    $('#export-data i').removeClass('fa-refresh').addClass('fa-check');

                    for(var i in selectedCommunities){
                        export_communities = export_communities.concat(e[selectedCommunities[i]]);
                    }
                    console.log(export_communities);
                    JSONToCSVConvertor(export_communities, "Call Detail Records", true);
                    // TODO : trigger button
                    graphStatus['export-data'] = 1;
                    discardExport();
            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            },
        })
        } else if(graphStatus['export-data'] == 1) {
            alert('The graph is already been exported.');
        }
        
     }

    function JSONToCSVConvertor(JSONData, ReportTitle, ShowLabel) {
        //If JSONData is not an object then JSON.parse will parse the JSON string in an Object
        var arrData = typeof JSONData != 'object' ? JSON.parse(JSONData) : JSONData;
        
        var CSV = '';    
        //Set Report title in first row or line
        
        CSV += ReportTitle + '\r\n\n';

        //This condition will generate the Label/Header
        if (ShowLabel) {
            var row = "";
            
            //This loop will extract the label from 1st index of on array
            for (var index in arrData[0]) {
                console.log('pass' + index);
                //Now convert each value to string and comma-seprated
                row += index + ',';
            }

            row = row.slice(0, -1);
            
            //append Label row with line break
            CSV += row + '\r\n';
        }
        
        //1st loop is to extract each row
        for (var i = 0; i < arrData.length; i++) {
            var row = "";
            
            //2nd loop will extract each column and convert it in string comma-seprated
            for (var index in arrData[i]) {
                row += '"' + arrData[i][index] + '",';
            }

            row.slice(0, row.length - 1);
            
            //add a line break after each row
            CSV += row + '\r\n';
        }

        if (CSV == '') {        
            alert("Invalid data");
            return;
        }   

        console.log(CSV);
        
        //Generate a file name
        var fileName = "MyReport_";
        //this will remove the blank-spaces from the title and replace it with an underscore
        fileName += ReportTitle.replace(/ /g,"_");   
        
        //Initialize file format you want csv or xls
        var uri = 'data:text/csv;charset=utf-8,' + escape(CSV);
        
        // Now the little tricky part.
        // you can use either>> window.open(uri);
        // but this will not work in some browsers
        // or you will not get the correct file extension    
        
        //this trick will generate a temp <a /> tag
        var link = document.createElement("a");    
        link.href = uri;
        
        //set the visibility hidden so it will not effect on your web-layout
        link.style = "visibility:hidden";
        link.download = fileName + ".csv";
        
        //this part will append the anchor tag and remove it after automatic click
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function discardExport() {
        $('#exportCSVModal').modal('hide');  
     }

    !function(undefined){
        document.getElementById('export-data').addEventListener('click', addCommunity);
        document.getElementById('exportCSV-export').addEventListener('click', processData);
        document.getElementById('exportCSV-cancel').addEventListener('click', discardExport);
    }();

}();

