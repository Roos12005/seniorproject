!function(){

    var did = $('meta[name="data-id"]').attr('content');
    var graphStatus = {
        'export-data' : 0,
    }

    function addCommunity(){
        $.ajax(
        {
            url: 'http://localhost/seniorproject/public/getCommunities/' + did,
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

            var existValue = false;

            var selectedCommunities = [];    
            $("#e2 :selected").each(function(){
                existValue = true;
                selectedCommunities.push($(this).val());
            });

            var data = {};

            var memberSelected = [];    
            $("#memberProfileExport :selected").each(function(){
                memberSelected.push($(this).val());
            });
            var aisRatioSelected = [];    
            $("#aisRatioProfileExport :selected").each(function(){
                aisRatioSelected.push($(this).val());
            });
            var daytimeNighttimeSelected = [];    
            $("#daytimeNighttimeProfileExport :selected").each(function(){
                daytimeNighttimeSelected.push($(this).val());
            });
            var weekdayWeekendSelected = [];    
            $("#weekdayWeekendProfileExport :selected").each(function(){
                weekdayWeekendSelected.push($(this).val());
            });
            var callOtherCarrierSelected = [];    
            $("#callOtherCarrierProfileExport :selected").each(function(){
                callOtherCarrierSelected.push($(this).val());
            });
            var averageNoOfCallSelected = [];    
            $("#averageNoOfCallProfileExport :selected").each(function(){
                averageNoOfCallSelected.push($(this).val());
            });
            var averageArpuSelected = [];    
            $("#averArpuProfileExport :selected").each(function(){
                averageArpuSelected.push($(this).val());
            });
            var averageDurationSelected = [];    
            $("#averageDurationProfileExport :selected").each(function(){
                averageDurationSelected.push($(this).val());
            });

            if(memberSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(memberSelected);
                data['MemberProfile'] = myJsonString;
            }
            if(aisRatioSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(aisRatioSelected);
                data['AisRatioProfile'] = myJsonString;
            }
            if(daytimeNighttimeSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(daytimeNighttimeSelected);
                data['DaytimeNighttimeProfile'] = myJsonString;
            }
            if(weekdayWeekendSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(weekdayWeekendSelected);
                data['WeekdayWeekendProfile'] = myJsonString;
            }
            if(callOtherCarrierSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(callOtherCarrierSelected);
                data['CallOtherCarrierProfile'] = myJsonString;
            }
            if(averageNoOfCallSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(averageNoOfCallSelected);
                data['AverageNoOfCallProfile'] = myJsonString;
            }
            if(averageArpuSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(averageArpuSelected);
                data['AverageArpuProfile'] = myJsonString;
            }
            if(averageDurationSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(averageDurationSelected);
                data['AverageDurationProfile'] = myJsonString;
            }

            if(existValue){
                graphStatus['export-data'] = 1;
                $('#export-data').removeClass('btn-default').addClass('btn-warning');
                $('#export-data i').removeClass('fa-times').addClass('fa-refresh');
                ajaxSetup();

                var export_communities = new Array();

                $.ajax({
                    type: "GET",
                    url: "http://localhost/seniorproject/public/getNodeCommunity/" + did,
                    data : {"exportdata":selectedCommunities, "exportprofile":data},
                    success: function(e){
                        console.log(e);
                        $('#export-data').removeClass('btn-warning').addClass('btn-success');
                        $('#export-data i').removeClass('fa-refresh').addClass('fa-check');

                        for(var i in e){
                            export_communities = export_communities.concat(e[i]);
                        }
                        console.log(export_communities);
                        JSONToCSVConvertor(export_communities, "Call Detail Records", true);

                        // TODO : trigger button
                        graphStatus['export-data'] = 1;
                    },
                    error: function(rs, e){
                        console.log(rs.responseText);
                        alert('Problem occurs during fetch data.');
                    },
                })
            } else alert("Please fill in the box.");
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

    !function(undefined){
        document.getElementById('export-data').addEventListener('click', addCommunity);
        document.getElementById('exportCSV-export').addEventListener('click', processData);
    }();

}();

