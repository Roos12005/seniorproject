/**
 *  @file   graph_highlight.js
 *
 *  @brief  Graph Highlight Script
 *
 *  This file contains methods for highlight node and edge with condition. 
 *  Color and size defined in each method. 
 */

function colorByDefaultNode() {
    if(currentHighlightNode == 'default') return;
    hilightButton('#h-defaultNode','Node');
    document.getElementById('highlightNode').innerHTML = 'Default';
    document.getElementById('highlightNodeSize').innerHTML = '';
    document.getElementById('highlightNodeColor').innerHTML = '';
    var maxMember = 1;
    if(flag['compute_com']){
        setMaxNodeSize(7 * zoomScale['current']);
        console.log("rendering community");
        if(flag['compute_com'] && !flag['clickListenerComOfCom']){
            s.graph.nodes().forEach(function(node) {
                if(parseInt(node['attributes']['Member']) > maxMember) {
                    maxMember = node['attributes']['Member'];
                }
                s.graph.nodes().forEach(function(node) {
                    node.color = node.communityColor;
                    node.size = 10  * zoomScale['current'] * node['attributes']['Member']/maxMember;
                    node.color = node.communityColor;
                });
            });
        }
        else {
            setMaxNodeSize(1 * zoomScale['current']);
            s.graph.nodes().forEach(function(node) {
                node.color = '#a5adb0';
                node.size = node.defaultSize * zoomScale['current'];
            });  
        }
        currentHighlightNode = 'default';
        s.refresh();
    }
}

function colorByCommunity() {
    setMaxNodeSize(1 * zoomScale['current']);
    if(currentHighlightNode == 'community') return;
    colorByDefaultNode();
    hilightButton('#h-community','Node');
    document.getElementById('highlightNode').innerHTML = 'Community';
    document.getElementById('highlightNodeSize').innerHTML = '';
    document.getElementById('highlightNodeColor').innerHTML = 'Color by Community';
    s.graph.nodes().forEach(function(node) {
        node.color = node.communityColor;
    });
    currentHighlightNode = 'community';
    s.refresh();
}

function colorByCentrality() {
    setMaxNodeSize(3 * zoomScale['current']);
    if(currentHighlightNode == 'centrality') return;
    document.getElementById('highlightNode').innerHTML = 'Centrality';
    document.getElementById('highlightNodeSize').innerHTML = 'Size by Closeness Centrality';
    document.getElementById('highlightNodeColor').innerHTML = 'Color by Betweenness Centrality';
    hilightButton('#h-centrality','Node');
    var maxBC = 0.1;
    var maxCC = 0.1;
    s.graph.nodes().forEach(function(node) {
        if(node['attributes']['Modularity Class'] == selectedCom || selectedCom == 'null') {
            if(parseFloat(node['attributes']['Betweenness Centrality']) > maxBC) {
                maxBC = parseFloat(node['attributes']['Betweenness Centrality']);
            }
            if(parseFloat(node['attributes']['Closeness Centrality']) > maxCC) {
                maxCC = parseFloat(node['attributes']['Closeness Centrality']);
            }
        }
    });

    s.graph.nodes().forEach(function(node) {
        if(node['attributes']['Modularity Class'] == selectedCom || selectedCom == 'null') {
            var colorScale =  node['attributes']['Betweenness Centrality'] == 0 ? 0 : 255 * (Math.log(node['attributes']['Betweenness Centrality'])/Math.log(maxBC));
            
            var hexString = parseInt(colorScale).toString(16);
            hexString = hexString.length == 1? '0' + hexString : hexString;
            node.color = '#' + hexString + "0000";
            // console.log(colorScale + " --> " + node.color);
            node.size = 3 * zoomScale['current'] * parseFloat(node['attributes']['Closeness Centrality'])/maxCC;
        }
    });
    currentHighlightNode = 'centrality';
    s.refresh();
}

function colorByCarrier() {
    var ais = ["AIS","3GPre-paid","3GPost-paid","3GHybrid-Post","GSM","AWN"];
    var trueh = ["TRUE","RFT","CATCDA"];
    var dtac = ["DTAC","DTN"]; 
    var tot = ["TOT","TOT3G"];
    setMaxNodeSize(1 * zoomScale['current']);
    if(currentHighlightNode == 'carrier') return;
    colorByDefaultNode();
    document.getElementById('highlightNode').innerHTML = 'Carrier';
    document.getElementById('highlightNodeSize').innerHTML = '';
    document.getElementById('highlightNodeColor').innerHTML = 'AIS - Green , TRUE - RED , DTAC - Blue , Other - GREY';
    hilightButton('#h-carrier','Node');
    s.graph.nodes().forEach(function(node) {
        node.color = trueh.indexOf(node['attributes']['Carrier']) >= 0 ? "#e74c3c" : (ais.indexOf(node['attributes']['Carrier']) >= 0 ? "#40d47e" : (dtac.indexOf(node['attributes']['Carrier']) >= 0 ? "#3498db" : (tot.indexOf(node['attributes']['Carrier']) >= 0 ? '#abdeea': '#000000')));
    });
    s.refresh();
    currentHighlightNode = 'carrier';
}

function colorByAIS() {
    var ais = ["AIS","3GPre-paid","3GPost-paid","3GHybrid-Post","GSM","AWN"];
    setMaxNodeSize(1 * zoomScale['current']);
    if(currentHighlightNode == 'ais') return;
    colorByDefaultNode();
    hilightButton('#h-ais','Node');
    document.getElementById('highlightNode').innerHTML = 'AIS Only';
    document.getElementById('highlightNodeSize').innerHTML = '';
    document.getElementById('highlightNodeColor').innerHTML = 'Only AIS - Green , Other - GREY';
    s.graph.nodes().forEach(function(node) {
        node.color = ais.indexOf(node['attributes']['Carrier']) >= 0 ? "#40d47e" : '#bdc3c7';
    });
    s.refresh();
    currentHighlightNode = 'ais';
}

function colorByArpu() {
    setMaxNodeSize(1.5 * zoomScale['current']);
    if(currentHighlightNode == 'arpu') return;
    colorByDefaultNode();
    hilightButton('#h-arpu','Node');
    document.getElementById('highlightNode').innerHTML = 'ARPU';
    document.getElementById('highlightNodeSize').innerHTML = '';
    document.getElementById('highlightNodeColor').innerHTML = 'Coloy by ARPU';
    var maxARPU = 0.1;
    s.graph.nodes().forEach(function(node) {
        if(parseInt(node['attributes']['Arpu']) > maxARPU) {
            maxARPU = parseInt(node['attributes']['Arpu']);
        }
    });

    var ARPU = 0;
    s.graph.nodes().forEach(function(node) {
        ARPU = parseInt(node['attributes']['Arpu']);
        var colorScale =  255 * ARPU/maxARPU;
        var hexString = parseInt(colorScale).toString(16);
        hexString = hexString.length == 1? '0' + hexString : hexString;
        node.color = '#' + hexString + "0000";
    });
    currentHighlightNode = 'arpu';
    s.refresh();
}

function colorByDegree() {
    setMaxNodeSize(5 * zoomScale['current']);
    if(currentHighlightNode == 'degree') return;
    colorByDefaultNode();
    hilightButton('#h-degree','Node');
    document.getElementById('highlightNode').innerHTML = 'Degree';
    document.getElementById('highlightNodeSize').innerHTML = 'Size by Number of Incoming and Outgoing Calls';
    document.getElementById('highlightNodeColor').innerHTML = '';
    var maxDegree = 0;
    s.graph.nodes().forEach(function(node) {
        if(node['attributes']['Modularity Class'] == selectedCom || selectedCom == 'null') {
            if(parseInt(node['attributes']['NoOfIncoming']) + parseInt(node['attributes']['NoOfOutgoing']) > maxDegree) {
                maxDegree = parseInt(node['attributes']['NoOfIncoming']) + parseInt(node['attributes']['NoOfOutgoing']);
            }
        }
    });
    s.graph.nodes().forEach(function(node) {
        if(node['attributes']['Modularity Class'] == selectedCom || selectedCom == 'null') {

            node.size = 5 * zoomScale['current'] * Math.log(parseInt(node['attributes']['NoOfIncoming']) + parseInt(node['attributes']['NoOfOutgoing']))/Math.log(maxDegree);
            node.color = '#000000';
        }
    });
    currentHighlightNode = 'degree';
    s.refresh();
}

function colorByDegreeIn() {
    setMaxNodeSize(1.5 * zoomScale['current']);
    if(currentHighlightNode == 'degreeIn') return;
    colorByDefaultNode();
    hilightButton('#h-degreeIn','Node');
    document.getElementById('highlightNode').innerHTML = 'Degree In';
    document.getElementById('highlightNodeSize').innerHTML = '';
    document.getElementById('highlightNodeColor').innerHTML = 'Color by Number of Receive';
    var maxDegreeIn = 0;
    s.graph.nodes().forEach(function(node) {
        if(node['attributes']['Modularity Class'] == selectedCom || selectedCom == 'null') {
            if(parseInt(node['attributes']['NoOfIncoming']) > maxDegreeIn) {
                maxDegreeIn = parseInt(node['attributes']['NoOfIncoming']);
            }
        }
    });
    s.graph.nodes().forEach(function(node) {
        if(node['attributes']['Modularity Class'] == selectedCom || selectedCom == 'null') {
            var colorScale =  255 * Math.pow(1.1,node['attributes']['NoOfIncoming'])/Math.pow(1.1,maxDegreeIn);
            if(node['attributes']['NoOfIncoming'] == 0){
                colorScale = 0;
            }
            var hexString = parseInt(colorScale).toString(16);
            hexString = hexString.length == 1? '0' + hexString : hexString;
            node.color = '#' + hexString + "0000";
        }
    });
    currentHighlightNode = 'degreeIn';
    s.refresh();
}

function colorByDegreeOut() {
    setMaxNodeSize(1.5 * zoomScale['current']);
    if(currentHighlightNode == 'degreeOut') return;
    colorByDefaultNode();
    hilightButton('#h-degreeOut','Node');
    document.getElementById('highlightNode').innerHTML = 'Degree Out';
    document.getElementById('highlightNodeSize').innerHTML = '';
    document.getElementById('highlightNodeColor').innerHTML = 'Color by Number of Call';
    var maxDegreeOut = 0;
    s.graph.nodes().forEach(function(node) {
        if(node['attributes']['Modularity Class'] == selectedCom || selectedCom == 'null') {
            if(parseInt(node['attributes']['NoOfOutgoing']) > maxDegreeOut) {
                maxDegreeOut = parseInt(node['attributes']['NoOfOutgoing']);
            }
        }
    });
    s.graph.nodes().forEach(function(node) {
        if(node['attributes']['Modularity Class'] == selectedCom || selectedCom == 'null') {
            var colorScale =  255 * Math.pow(1.02,node['attributes']['NoOfOutgoing'])/Math.pow(1.02,maxDegreeOut);
            if(node['attributes']['NoOfOutgoing'] == 0){
                colorScale = 0;
            }
            var hexString = parseInt(colorScale).toString(16);
            hexString = hexString.length == 1? '0' + hexString : hexString;
            node.color = '#' + hexString + "0000";
        }
    });
    currentHighlightNode = 'degreeOut';
    s.refresh();
}

function colorByDefaultEdge() {
    hilightButton('#h-defaultEdge','Edge');
    document.getElementById('highlightEdge').innerHTML = 'Default';
    document.getElementById('highlightEdgeColor').innerHTML = '';
    s.graph.nodes().forEach(function(node){
        s.graph.edges().forEach(function(edge) {
            if(edge['source'] == node.id){
                edge.color = node.communityColor;
            }
        });
    });

    currentHighlightEdge = 'default';
    s.refresh();
}

function colorByDayNight() {
    if(currentHighlightEdge == 'daynight') return;
    hilightButton('#h-daynight','Edge');
    document.getElementById('highlightEdge').innerHTML = 'Day / Night';
    document.getElementById('highlightEdgeColor').innerHTML = 'Color by Number of Day & Night Call';
    s.graph.edges().forEach(function(edge) {
        console.log(edge['attributes']['noDayTime'] + "   " + edge['attributes']['noNightTime']);
        var red = (Math.log(edge['attributes']['noDayTime'])/Math.log((edge['attributes']['noDayTime']+edge['attributes']['noNightTime']))) * 252;
        var green = (Math.log(edge['attributes']['noDayTime'])/Math.log((edge['attributes']['noDayTime']+edge['attributes']['noNightTime']))) * 212;
        var blue = (Math.log(edge['attributes']['noDayTime'])/Math.log((edge['attributes']['noDayTime']+edge['attributes']['noNightTime']))) * 64;
        edge.color = '#'+ parseInt(red).toString(16)+ parseInt(green).toString(16)+parseInt(blue).toString(16);
    });
    s.refresh();
    currentHighlightEdge = 'daynight';
}

function colorByDuration() {
    if(currentHighlightEdge == 'duration') return;
    hilightButton('#h-duration','Edge');
    document.getElementById('highlightEdge').innerHTML = 'Duration';
    document.getElementById('highlightEdgeColor').innerHTML = 'Color by Call Duration';
    var maxDuration = 1;
    s.graph.edges().forEach(function(edge) {
        if(parseInt(edge['attributes']['duration']) > maxDuration) {
            maxDuration = parseInt(edge['attributes']['duration']);
        }
    });

    s.graph.edges().forEach(function(edge) {
        console.log(edge['attributes']['duration']);
        var colorScale =  255 * Math.log(edge['attributes']['duration'])/Math.log(maxDuration);
        var hexString = parseInt(colorScale).toString(16);
        hexString = hexString.length == 1? '0' + hexString : hexString;
        edge.color = '#00' + hexString + '00';
    });
    s.refresh();
    currentHighlightEdge = 'duration';
}

function hilightButton(name,type) {
    if(type == "Node") {
        $('.hilightNode').removeClass('h-on');
    } else {
        $('.hilightEdge').removeClass('h-on');
    }
    $(name).addClass('h-on');
}

function addHilightListener() {
    document.getElementById('h-defaultNode').addEventListener('click', colorByDefaultNode);
    document.getElementById('h-community').addEventListener('click', colorByCommunity);
    document.getElementById('h-centrality').addEventListener('click', colorByCentrality);
    document.getElementById('h-carrier').addEventListener('click', colorByCarrier);
    document.getElementById('h-ais').addEventListener('click', colorByAIS);
    document.getElementById('h-arpu').addEventListener('click', colorByArpu);
    document.getElementById('h-degree').addEventListener('click', colorByDegree);
    document.getElementById('h-degreeIn').addEventListener('click', colorByDegreeIn);
    document.getElementById('h-degreeOut').addEventListener('click', colorByDegreeOut);

    document.getElementById('h-defaultEdge').addEventListener('click', colorByDefaultEdge);
    document.getElementById('h-daynight').addEventListener('click', colorByDayNight);
    document.getElementById('h-duration').addEventListener('click', colorByDuration);
}