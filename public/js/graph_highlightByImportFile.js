/**
 *  @file   graph_highlightByImportFile.js
 *
 *  @brief  Graph Highlight by Import File Script
 *
 *  This file contains a script for highlighting graph by new import file. File must contains
 *  only two columns consist of number and value of attribute.
 */

function isAPIAvailable() {
    // Check for the various File API support.
    if (window.File && window.FileReader && window.FileList && window.Blob) {
        // Great success! All the File APIs are supported.
        return true;
    } else {
        // source: File API availability - http://caniuse.com/#feat=fileapi
        // source: <output> availability - http://html5doctor.com/the-output-element/
        document.writeln('The HTML5 APIs used in this form are only available in the following browsers:<br />');
        // 6.0 File API & 13.0 <output>
        document.writeln(' - Google Chrome: 13.0 or later<br />');
        // 3.6 File API & 6.0 <output>
        document.writeln(' - Mozilla Firefox: 6.0 or later<br />');
        // 10.0 File API & 10.0 <output>
        document.writeln(' - Internet Explorer: Not supported (partial support expected in 10.0)<br />');
        // ? File API & 5.1 <output>
        document.writeln(' - Safari: Not supported<br />');
        // ? File API & 9.2 <output>
        document.writeln(' - Opera: Not supported');
        return false;
    }
}

function colorByAttribute() {
    if(currentHighlightNode == 'attribute') return;
    document.getElementById('highlightNode').innerHTML = 'Attribute by File';
    document.getElementById('highlightNodeSize').innerHTML = '';
    document.getElementById('highlightNodeColor').innerHTML = 'Category';

    s.graph.nodes().forEach(function(node) {
        for (var value in category){
            if(category[value]['number'].indexOf(node['label']) >= 0){
                node.color = category[value]["color"];
                break;
            }
        }
    });
    currentHighlightNode = 'attribute';
    s.refresh();
} 

function checkColorCode(value) {
    if(value < 16){
        return "0" + value.toString(16).substr(-4);
    } else {
        return value.toString(16).substr(-4);
    }
}

function randomColor() {
    var r = Math.floor((Math.random() * 255 + Math.random() * 255) / 2);
    var g = Math.floor((Math.random() * 255 + Math.random() * 255) / 2);
    var b = Math.floor((Math.random() * 255 + Math.random() * 255) / 2);

    var hex = "#" + checkColorCode(r) + checkColorCode(g) + checkColorCode(b);
    return hex;
}

function handleFileSelect(evt) {
    var files = evt.target.files; // FileList object
    var file = files[0];

    var reader = new FileReader();
    reader.readAsText(file);
    reader.onload = function(event){
        var csv = event.target.result;
        var data = $.csv.toArrays(csv);

        var firstRow = true;
        var html = '';
        if(!flag['canImport']){
            alert('Show calling graph first !') 
        } else {
            for(var row in data) {
                if(firstRow){
                    firstRow = false;
                    $('#dynamic-table thead').html('<tr><th>'+ data[row][1] +'</th><th>Color</th></tr>');
                    continue;
                }
                if( category[data[row][1]] === undefined ) {
                    var color = randomColor();
                    var colorBox = '<div style="width: 100%; height: 10px; background-color:'+color+';"></div>'

                    html += '<tr>\r\n<td>' + data[row][1] + '</td>\r\n';
                    html += '<td>' + colorBox + '</td>\r\n</tr>\r\n';
                    category[data[row][1]] = {"color" : color,"number":new Array()};
                }
                category[data[row][1]]["number"].push(data[row][0]);
            }
            $('#dynamic-table tbody').html(html);
            tableInit();
            colorByAttribute();
        }
    };
    reader.onerror = function(){ alert('Unable to read ' + file.fileName); };
}