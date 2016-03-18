/**
 *  @file   graph_main.js
 *
 *  @brief  Graph Script
 *
 *  This file contains a script for generating graph using 
 *  SigmaJS library (http://sigmajs.org) and method for 
 *  additional functions on the graph.
 *
 *  @author Thanaphoom Pungchaichan (pperfectionist)
 *  @bug    Scroll to Zoom is not working fine!
 *
 */

!function(){
    'use strict';
    var did = $('meta[name="data-id"]').attr('content');
    var graphData = [];
    var communityData = [];
    var colors = [];
    var numIDMapper = {};
    var s;
    var currentHighlightNode = 'null';
    var currentHighlightEdge = 'null';
    var flag = {
        compute_com : false,
        activateClick : false,
        clickListenerComOfCom : false
    }

    var graphStatus = {
        'full-graph' : 0,
        'community-group' : 0,
        'community-profile' : 0,
    }

    /**
     *  @brief  Instantiate Sigma object.
     *
     *  Initializing Sigma object and setup the object configurations
     *
     *  @return instantiated Sigma object
     */    
    function initSigma() {
        s = new sigma({
            renderers: [{
                container: document.getElementById('container'),
               // type: 'canvas'
            }]

            /* 
             *  TODO : May add some other setting here... 
             *  Visit https://github.com/jacomyal/sigma.js/wiki/Settings for more setting available
             *
            */
        });
        s.settings({
            defaultEdgeType: "curvedArrow",
            minEdgeSize : 0.2,
            maxEdgeSize : 0.5,
            minNodeSize : 2,
            maxNodeSize : 5,
            zoomMin : 0.75,
            zoomMax : 20,
            edgeColor : 'default',
            defaultEdgeArrow: 'source'
            // autoResize: false
            // zoomingRatio : 1
        });

    }

    /**
     *  @brief  Add node to Sigma object.
     *
     *  Adding the input node to the Sigma object with the given node setting,
     *  including id, label, x, y, size and color.
     *
     *  @param  n  Input node
     *  @return void
     */
    function addNode(n) {
        /* TODO : May add some conditional check or calculation here... */
        if(s.graph.nodes(n.id) !== undefined) {
            throw 'Node#' + n.id + '(' + n.label + ')' + ' is duplicated.';
        }
        
        s.graph.addNode({
            id: n.id,
            label: n.label,
            x: n.x,
            y: n.y,
            size: n.size,
            color: '#a5adb0',
            communityColor: n.color,
            defaultSize: n.size,
            attributes: n.attributes
        })
    }

    /**
     *  @brief  Add edge to Sigma object.
     *
     *  Adding the input edge to the Sigma object with the given edge setting,
     *  including id, source, target, color.
     *
     *  @param  e  Input edge
     *  @return void
     */
    function addEdge(e) {
        /* TODO : May add some conditional check or calculation here... */

        // Throw an exception if source or target node does not exist.
        if(s.graph.nodes(e.source) == undefined) {
            throw 'Node#' + e.source + ' is undefined.';
        }
        if(s.graph.nodes(e.target) == undefined) {
            throw 'Node#' + e.target + ' is undefined.';
        }

        s.graph.addEdge({
            id: e.id,
            source: e.source,
            target: e.target,
            attributes : e.attributes,
            color: '#a5adb0',
            type: "arrow"
        })
    }


    function removeNode(n) {
        s.graph.dropNode(n.id);
    }

    function clearGraph() {
        s.graph.clear();
    }

    /**
     *  @brief  Basic setuo for AJAX call.
     *
     *  This function must be called everytime before using ajax call.
     *  This function contains CSRF generator which will generate
     *  a CSRF token from page cookie.
     *
     *  Note that CSRF must be placed in HTML file that includes this script.
     *  Otherwise, backend side will reject any AJAX call.
     *
     *  @return void
     */
    function ajaxSetup(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    /**  
     *  @brief  Fetch all data
     *
     *  Send Ajax request to server-side to fetch all graph data,
     *  including all nodes and all edges with thier properties.
     *
     *  @param  ???
     *  @return JSON object - contains graph data
     */
    function fetchData(){
        
        var preparedData = [];
        var carrier = [0,0,0,0];
        var com_data = new Array();
        var color_data = new Array();
        var communities = new Array();
        var user_num = 0;
        ajaxSetup();
        $.ajax({
            type: "GET",
            url: "http://localhost/seniorproject/public/getCDR/" + did,
            data : {},
            success: function(e){
                console.log(e);
                preparedData = e;

                user_num = e['nodes'].length;

                $.each(e.nodes, function(index, user_info) {
                    if(user_info['attributes']['Carrier'] == "AIS"){
                        carrier[0] += 1;
                    }
                    else if(user_info['attributes']['Carrier'] == "DTAC"){
                        carrier[1] += 1;
                    }
                    else if(user_info['attributes']['Carrier'] == "TRUE"){
                        carrier[2] += 1;
                    }
                    else {
                        carrier[3] += 1;
                    }
                    if (!communities[user_info['attributes']['Modularity Class']]) {
                        communities[user_info['attributes']['Modularity Class']] = 1;
                        color_data[user_info['attributes']['Modularity Class']] = user_info['color'];
                    }
                    else {
                        communities[user_info['attributes']['Modularity Class']] += 1;
                    }
                });

                for (var i in communities) {
                    com_data.push({value: communities[i], label: 'Community ID ' + i, formatted: communities[i] + ' users : ' + (communities[i]/user_num * 100).toFixed(0) + " %"});
                }

                document.getElementById('unique_numbers').innerHTML = user_num;
                document.getElementById('communities').innerHTML = communities.length;
                document.getElementById('transactions').innerHTML = e['edges'].length;
            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            },
            async: false,
        })

        $.ajax({
            type: "GET",
            url: "http://localhost/seniorproject/public/getCarrier/" + did,
            data : {},
            success: function(e){
                console.log(e);
                var user_num = e['all'];

                carrier[0] = e['ais'];
                carrier[1] = e['true'];
                carrier[2] = e['dtac'];
                carrier[3] = e['all']-e['ais']-e['true']-e['dtac'];

                document.getElementById('unique_numbers').innerHTML = e['all'];
                document.getElementById('transactions').innerHTML = e['calls'];
            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            },
            async: false,
        })
        
        createPieChart(com_data,color_data,carrier,user_num);

        return preparedData;
    }

    function fetchCommunityData(){
        
        var preparedData = [];
        var carrier = [0,0,0,0];
        var com_data = new Array();
        var color_data = new Array();
        var communities = new Array();
        var node_num = 0;
        ajaxSetup();
        $.ajax({
            type: "GET",
            url: "http://localhost/seniorproject/public/getCommunityOfCommunity/" + did,
            data : {},
            success: function(e){
                console.log(e);
                preparedData = e;

                $.each(e.nodes, function(index, community_info) {
                     communities[community_info['attributes']['Modularity Class']] = community_info['attributes']['Member'];
                     color_data[community_info['attributes']['Modularity Class']] = community_info['color'];
                     node_num += community_info['attributes']['Member'];
                });

                for (var i in communities) {
                    com_data.push({value: communities[i], label: 'Community ID ' + i, formatted: communities[i] + ' users : ' + (communities[i]/node_num * 100).toFixed(0) + " %"});
                }

                document.getElementById('communities').innerHTML = communities.length;
            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            },
            async: false,
        })

        $.ajax({
            type: "GET",
            url: "http://localhost/seniorproject/public/getCarrier/" + did,
            data : {},
            success: function(e){
                console.log(e);
                var user_num = e['all'];

                carrier[0] = e['ais'];
                carrier[1] = e['true'];
                carrier[2] = e['dtac'];
                carrier[3] = e['all']-e['ais']-e['true']-e['dtac'];

                document.getElementById('unique_numbers').innerHTML = e['all'];
                document.getElementById('transactions').innerHTML = e['calls'];
            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            },
            async: false,
        })
        
        createPieChart(com_data,color_data,carrier,node_num);

        return preparedData;
    }

    function createPieChart(com_data, color_data, carrier, node_num){
        Morris.Donut({
            element: 'graph-donut',
            data: com_data,
            backgroundColor: '#fff',
            labelColor: '#1fb5ac',
            colors: color_data,
            formatter: function (x, data) { return data.formatted; }
        });
        
        Morris.Donut({
            element: 'graph-donut2',
            data: [
                {value: carrier[0], label: 'AIS', formatted: carrier[0] + ' users : ' + (carrier[0]/node_num * 100).toFixed(0) + "%" },
                {value: carrier[1], label: 'DTAC', formatted: carrier[1] + ' users : ' + (carrier[1]/node_num * 100).toFixed(0) + "%" },
                {value: carrier[2], label: 'TRUE', formatted: carrier[2] + ' users : ' + (carrier[2]/node_num * 100).toFixed(0) + "%" },
                {value: carrier[3], label: 'OTHER', formatted: carrier[3] + ' users : ' + (carrier[3]/node_num * 100).toFixed(0) + "%" }
            ],
            backgroundColor: '#fff',
            labelColor: '#1fb5ac',
            colors: [
                '#66CC66','#FF0000','#00CCFF','#DDDDDD'
            ],
            formatter: function (x, data) { return data.formatted; }
        });
    }

    /**  
     *  @brief  Plot Full Graph
     *
     *  First, fetch all graph data from server-side and
     *  then add all nodes and edges to the sigma object.
     *  All listener for nodes and edges are also setup here.
     *  Finally, ask sigma object to display graph.
     *
     *  @return void
     */
    function plotFullGraph(){
        numIDMapper = {};
        // Add all returned nodes to sigma object
        graphData.nodes.forEach(function(node) {
            addNode(node);
            numIDMapper[node.label] = node.id;
        });

        // Add all return edges to sigma object
        graphData.edges.forEach(function(edge) {
            addEdge(edge);
        });

        // Add Click Listener to all Nodes
        if(!flag['activateClick']){
            s.bind('clickNode', clickNodeListener);
            s.bind('doubleClickNode', doubleClickNodeListener);
            flag['activateClick'] = true;
        }

        // Display Graph using sigma object
        s.startForceAtlas2({});
        setTimeout(function () {
            s.killForceAtlas2();
        }, 1000);
        colorByDefaultNode();
        colorByDefaultEdge();
    }

    /**  
     *  @brief  Plot Partial Graph
     *
     *  
     *
     *  @param  nodes   array of selected nodes
     *  @return void
     */
    function plotPartialGraph(nodes){
        nodes.forEach(function(node) {
            removeNode(node);
        });

        s.startForceAtlas2({});
        setTimeout(function () {
            s.killForceAtlas2();
        }, 500);
        s.refresh();
    }

    /**  
     *  @brief  Scripting Zoom Button
     *
     *  Add listeners to all three zoom buttons,
     *  zoom-in, zoom-out, refresh-zoom, by recalculating
     *  camera position of sigma object
     *
     *  @return void
     */
     function addZoomListener() {
        // Zoom in Button
        document.getElementById("zoomin").addEventListener("click", function(){
            s.camera.goTo({x:s.camera.x, y:s.camera.y, ratio: 0.9 * s.camera.ratio});
        });

        // Zoom out Button
        document.getElementById("zoomout").addEventListener("click", function(){
            s.camera.goTo({x:s.camera.x, y:s.camera.y, ratio: 1.1 * s.camera.ratio});
        });

        // Refresh Zoom Button
        document.getElementById("nozoom").addEventListener("click", function(){
            s.camera.goTo({x:0, y:0, ratio: 1});
        });
     }

     /**  
     *  @brief  Scripting Search Box
     *
     *  This function will add listener on search box
     *  for searching specific number and focus on 
     *  that node, also, display node data on right column
     *
     *  @return void
     */
     function addSearchBoxListener() {
        document.getElementById("searchbox").addEventListener("keypress", function(key){
            // Detect only "Enter" key - keyCode = 13
            if (key.keyCode === 13) {
                //  Move camera to entered node
                var input = document.getElementById("searchbox").value;
                var node = s.graph.nodes(numIDMapper[input]);
                
                if(node == undefined) {
                    alert("Number " + input + " is not found. Please check your input number again.");
                    return;
                }

                s.camera.goTo({
                    x: node['read_cam0:x'], 
                    y: node['read_cam0:y'], 
                    ratio: 0.1
                });

                updateInformation(node);
            }
        });
     }

     /**  
     *  @brief  Listener on clicking node
     *
     *  Handling event when clicking on node ???
     *
     *  @param  node   clicked node
     *  @return void
     */
     function clickNodeListener(node) {
        console.log(node.data.node.label);
        var nodeData = updateInformation(node);
     }

     function doubleClickNodeListener(node) {
        // TODO : Display only selected community
        var nodeData = updateInformation(node);
        // Show back button on the top right of the div
        document.getElementsByClassName('back-section')[0].style.display = 'block';
        if(flag['compute_com']){
            var selectedCommunity = nodeData['attributes']['Modularity Class'];
            clearGraph();
            console.log("Community");

            ajaxSetup();
            $.ajax({
                type: "GET",
                url: "http://localhost/seniorproject/public/getNodeInSelectedCommunity/" + did,
                data : {"senddata":selectedCommunity},
                success: function(e){
                     console.log(e);
                     communityData = e;

                     numIDMapper = {};
                    // Add all returned nodes to sigma object
                     communityData.nodes.forEach(function(n) {
                        addNode(n);
                        numIDMapper[n.label] = n.id;
                     });
                    // Add all return edges to sigma object
                    communityData.edges.forEach(function(edge) {
                        addEdge(edge);
                    });

                    s.startForceAtlas2({});
                    setTimeout(function () {
                        s.killForceAtlas2();
                    }, 500);

                    s.refresh();
                    flag['clickListenerComOfCom'] = true;
                },
                async: false
            });        
        } else { 
            var filteredNodes = [];
            console.log("Node");
            graphData.nodes.forEach(function(n) {
                if(nodeData['attributes']['Modularity Class'] !== n['attributes']['Modularity Class']) {
                    filteredNodes.push(n);
                }
            });

            plotPartialGraph(filteredNodes);
        }
     }

     /**  
     *  @brief  Update Right column information
     *
     *  Triggered by clicking on searching node on the graph.
     *  This function will update the displayed values to
     *  the values of selected node.
     *
     *  @param  node   clicked node
     *  @return void
     */
     function updateInformation(node) {
        var nodeData = undefined;
        var nodeID = node.id == undefined ? node.data.node.id : node.id; 
        var communities = new Array();
        var communityRank = new Array();
        var bc = new Array();
        var cc = new Array();

        if(!flag['clickListenerComOfCom']){
            graphData.nodes.forEach(function(n) {
                if(n.id == nodeID) {
                    nodeData = n;
                }
                bc.push(parseFloat(n.attributes['Betweenness Centrality']));
                cc.push(parseFloat(n.attributes['Closeness Centrality']));
                if(flag['compute_com']){
                    communityRank[n.attributes['Modularity Class']] = n.attributes['Member'];
                } else {
                    if (!communities[n.attributes['Modularity Class']]) {
                       communities[n.attributes['Modularity Class']] = 1;
                       communityRank[n.attributes['Modularity Class']] = 1;
                    }
                    else {
                        communities[n.attributes['Modularity Class']] += 1;
                        communityRank[n.attributes['Modularity Class']] += 1;
                    }
                }
            });
        } else {
            communityData.nodes.forEach(function(n) {
                if(n.id == nodeID) {
                    nodeData = n;
                }
                bc.push(parseFloat(n.attributes['Betweenness Centrality']));
                cc.push(parseFloat(n.attributes['Closeness Centrality']));
                if(flag['compute_com']){
                    communityRank[n.attributes['Modularity Class']] = n.attributes['Member'];
                } else {
                    if (!communities[n.attributes['Modularity Class']]) {
                       communities[n.attributes['Modularity Class']] = 1;
                       communityRank[n.attributes['Modularity Class']] = 1;
                    }
                    else {
                        communities[n.attributes['Modularity Class']] += 1;
                        communityRank[n.attributes['Modularity Class']] += 1;
                    }
                }
            });
        }

        bc.sort(function(a, b) {
          return b - a;
        });

        cc.sort(function(a, b) {
          return b - a;
        });

        communityRank.sort(function(a, b) {
          return b - a;
        });

        if(nodeData == undefined) {
            alert('Can\'t get node data');
            return;
        }
        
        // TODO : Update right column
        document.getElementById('cage').innerHTML = nodeData.attributes['Age'];
        document.getElementById('cnumber').innerHTML = nodeData.label;
        document.getElementById('carpu').innerHTML = nodeData.attributes['Arpu'];
        document.getElementById('cpromotion').innerHTML = nodeData.attributes['Promotion'];
        document.getElementById('ccarrier').innerHTML = nodeData.attributes['Carrier'];
        document.getElementById('cgender').innerHTML = nodeData.attributes['Gender'];
        document.getElementById('cnoOfCall').innerHTML = nodeData.attributes['NoOfOutgoing'];
        document.getElementById('cnoOfReceive').innerHTML = nodeData.attributes['NoOfIncoming'];
        document.getElementById('cc').innerHTML = cc.indexOf(nodeData.attributes['Closeness Centrality']) + ' (' + parseFloat(nodeData.attributes['Closeness Centrality']).toFixed(3) + ')';
        document.getElementById('bc').innerHTML = bc.indexOf(nodeData.attributes['Betweenness Centrality']) + ' (' + parseFloat(nodeData.attributes['Betweenness Centrality']).toFixed(3) + ')';
        document.getElementById('comid').innerHTML = nodeData.attributes['Modularity Class'];
        if(flag['compute_com']){
            document.getElementById('comrank').innerHTML = communityRank.indexOf(nodeData.attributes['Member']) + 1;
            document.getElementById('comsize').innerHTML = nodeData.attributes['Member'];
            document.getElementById('comnum').innerHTML = nodeData.attributes['Member'];
            document.getElementById('memberProfileInfor').innerHTML = nodeData.attributes['Member Profile'];
            document.getElementById('aisRatioProfileInfor').innerHTML = nodeData.attributes['Ais Ratio Profile'];
            document.getElementById('daytimeNighttimeProfileInfor').innerHTML = nodeData.attributes['Daytime Nighttime Profile'];
            document.getElementById('weekdayWeekendProfileInfor').innerHTML = nodeData.attributes['Weekday Weekend Profile'];
            document.getElementById('callOtherCarrierProfileInfor').innerHTML = nodeData.attributes['Call Other Carrier Profile'];
            document.getElementById('averageNoOfCallProfileInfor').innerHTML = nodeData.attributes['Average No Of Call Profile'];
            document.getElementById('averageArpuProfileInfor').innerHTML = nodeData.attributes['Average Arpu Profile'];
            document.getElementById('averageDurationProfileInfor').innerHTML = nodeData.attributes['Average Duration Profile'];
        } else {
            document.getElementById('comrank').innerHTML = communityRank.indexOf(communities[nodeData.attributes['Modularity Class']]) + 1;
            document.getElementById('comsize').innerHTML = communities[nodeData.attributes['Modularity Class']];
            document.getElementById('comnum').innerHTML = communities[nodeData.attributes['Modularity Class']];
       }
        return nodeData;
     }

     /**  
     *  @brief  Listener on clicking back button
     *
     *  Hide the back button and change the displayed graph
     *  to the full one
     *
     *  @return void
     */
     function addBackButtonListener() {
        document.getElementById('back').addEventListener('click', function() {
            document.getElementsByClassName('back-section')[0].style.display = 'none';
            // TODO : Change displayed graph back to the full one
            clearGraph();
            s.stopForceAtlas2();
            plotFullGraph();
            flag['clickListenerComOfCom'] = false;
        });
     }

    function colorByDefaultNode() {
        if(currentHighlightNode == 'default') return;
        hilightButton('#h-defaultNode','Node');
        document.getElementById('highlightNode').innerHTML = 'Default';
        document.getElementById('highlightNodeSize').innerHTML = '';
        document.getElementById('highlightNodeColor').innerHTML = '';
        s.graph.nodes().forEach(function(node) {
            node.color = '#a5adb0';
            node.size = node.defaultSize;
        });


        currentHighlightNode = 'default';
        s.refresh();
    }

    function colorByCommunity() {
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

        if(currentHighlightNode == 'centrality') return;
        colorByDefaultNode();
        document.getElementById('highlightNode').innerHTML = 'Centrality';
        document.getElementById('highlightNodeSize').innerHTML = 'Size by Closeness Centrality';
        document.getElementById('highlightNodeColor').innerHTML = 'Color by Betweenness Centrality';
        hilightButton('#h-centrality','Node');
        var maxBC = 0.1;
        var maxCC = 0.1;
        s.graph.nodes().forEach(function(node) {
            if(node['attributes']['Betweenness Centrality'] > maxBC) {
                maxBC = node['attributes']['Betweenness Centrality'];
            }
            if(node['attributes']['Closeness Centrality'] > maxCC) {
                maxCC = node['attributes']['Closeness Centrality'];
            }
        });

        s.graph.nodes().forEach(function(node) {
            var colorScale =  255 * Math.pow(1.008,node['attributes']['Betweenness Centrality'])/Math.pow(1.008,maxBC);

            var hexString = parseInt(colorScale).toString(16);
            hexString = hexString.length == 1? '0' + hexString : hexString;
            node.color = '#' + hexString + "0000";

            node.size = 10 * node['attributes']['Closeness Centrality']/maxCC;
        });
        currentHighlightNode = 'centrality';
        s.refresh();
    }

    function colorByCarrier() {
        if(currentHighlightNode == 'carrier') return;
        colorByDefaultNode();
        document.getElementById('highlightNode').innerHTML = 'Carrier';
        document.getElementById('highlightNodeSize').innerHTML = '';
        document.getElementById('highlightNodeColor').innerHTML = 'AIS - Green , TRUE - RED , DTAC - Blue , Other - GREY';
        hilightButton('#h-carrier','Node');
        s.graph.nodes().forEach(function(node) {
            node.color = node['attributes']['Carrier'] == 'TRUE' ? "#e74c3c" : (node['attributes']['Carrier'] == 'AIS' ? "#40d47e" : (node['attributes']['RnCode'] == 'DTAC' ? "#3498db" : '#000000'));
        });
        s.refresh();
        currentHighlightNode = 'carrier';
    }

    function colorByAIS() {
        if(currentHighlightNode == 'ais') return;
        colorByDefaultNode();
        hilightButton('#h-ais','Node');
        document.getElementById('highlightNode').innerHTML = 'AIS Only';
        document.getElementById('highlightNodeSize').innerHTML = '';
        document.getElementById('highlightNodeColor').innerHTML = 'Only AIS - Green , Other - GREY';
        s.graph.nodes().forEach(function(node) {
            node.color = node['attributes']['RnCode'] == 'AIS' ? "#40d47e" : '#bdc3c7';
        });
        s.refresh();
        currentHighlightNode = 'ais';
    }

    function colorByArpu() {
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
        if(currentHighlightNode == 'degree') return;
        colorByDefaultNode();
        hilightButton('#h-degree','Node');
        document.getElementById('highlightNode').innerHTML = 'Degree';
        document.getElementById('highlightNodeSize').innerHTML = 'Size by Number of Call';
        document.getElementById('highlightNodeColor').innerHTML = 'Color by Number of Receive';
        var maxDegreeIn = 0;
        var maxDegreeOut = 0;
        s.graph.nodes().forEach(function(node) {
            if(node['attributes']['NoOfIncoming'] > maxDegreeIn) {
                maxDegreeIn = node['attributes']['NoOfIncoming'];
            }
            if(node['attributes']['NoOfOutgoing'] > maxDegreeOut) {
                maxDegreeOut = node['attributes']['NoOfOutgoing'];
            }
        });
        s.graph.nodes().forEach(function(node) {
            var colorScale =  255 * Math.pow(1.25,node['attributes']['NoOfIncoming'])/Math.pow(1.25,maxDegreeIn);

            var hexString = parseInt(colorScale).toString(16);
            hexString = hexString.length == 1? '0' + hexString : hexString;
            node.color = '#' + hexString + "0000";

            node.size = 10 * node['attributes']['NoOfOutgoing']/maxDegreeOut;
        });
        currentHighlightNode = 'degree';
        s.refresh();
    }

    function colorByDegreeIn() {
        if(currentHighlightNode == 'degreeIn') return;
        colorByDefaultNode();
        hilightButton('#h-degreeIn','Node');
        document.getElementById('highlightNode').innerHTML = 'Degree In';
        document.getElementById('highlightNodeSize').innerHTML = '';
        document.getElementById('highlightNodeColor').innerHTML = 'Color by Number of Receive';
        var maxDegreeIn = 0;
        s.graph.nodes().forEach(function(node) {
            if(node['attributes']['NoOfIncoming'] > maxDegreeIn) {
                maxDegreeIn = node['attributes']['NoOfIncoming'];
            }
        });
        s.graph.nodes().forEach(function(node) {
            var colorScale =  255 * Math.pow(1.25,node['attributes']['NoOfIncoming'])/Math.pow(1.25,maxDegreeIn);

            var hexString = parseInt(colorScale).toString(16);
            hexString = hexString.length == 1? '0' + hexString : hexString;
            node.color = '#' + hexString + "0000";
        });
        currentHighlightNode = 'degreeIn';
        s.refresh();
    }

    function colorByDegreeOut() {
        if(currentHighlightNode == 'degreeOut') return;
        colorByDefaultNode();
        hilightButton('#h-degreeOut','Node');
        document.getElementById('highlightNode').innerHTML = 'Degree Out';
        document.getElementById('highlightNodeSize').innerHTML = '';
        document.getElementById('highlightNodeColor').innerHTML = 'Color by Number of Call';
        var maxDegreeOut = 0;
        s.graph.nodes().forEach(function(node) {
            if(node['attributes']['NoOfOutgoing'] > maxDegreeOut) {
                maxDegreeOut = node['attributes']['NoOfOutgoing'];
            }
        });
        s.graph.nodes().forEach(function(node) {
            var colorScale =  255 * Math.pow(1.25,node['attributes']['NoOfOutgoing'])/Math.pow(1.25,maxDegreeOut);

            var hexString = parseInt(colorScale).toString(16);
            hexString = hexString.length == 1? '0' + hexString : hexString;
            node.color = '#' + hexString + "0000";
        });
        currentHighlightNode = 'degreeOut';
        s.refresh();
    }

    function colorByDefaultEdge() {
        if(currentHighlightEdge == 'default') return;
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
            var red = (edge['attributes']['noDayTime']/(edge['attributes']['noDayTime']+edge['attributes']['noNightTime'])) * 252;
            var green = (edge['attributes']['noDayTime']/(edge['attributes']['noDayTime']+edge['attributes']['noNightTime'])) * 212;
            var blue = (edge['attributes']['noDayTime']/(edge['attributes']['noDayTime']+edge['attributes']['noNightTime'])) * 64;
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
            var colorScale =  255 * edge['attributes']['duration']/maxDuration;
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

    /**  
     *  @brief  Listener on clicking back button
     *
     *  Hide the back button and change the displayed graph
     *  to the full one
     *
     *  @return void
     */
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

     function resetButton(button) {
        graphStatus[button] = 0;

        $('#' + button).removeClass('btn-warning').removeClass('btn-success').addClass('btn-default');
        $('#' + button +' i').removeClass('fa-refresh').removeClass('fa-check').addClass('fa-times');
     }

     function processData() {
        if(graphStatus['full-graph'] == 0) {
            flag['compute_com'] = false;
            currentHighlightNode = 'null';
            currentHighlightEdge = 'null';
            resetButton('community-group');
            resetButton('community-profile');
            runGraph();
            graphStatus['full-graph'] = 1;
            $('#full-graph').removeClass('btn-default').addClass('btn-success');
            $('#full-graph i').removeClass('fa-times').addClass('fa-check');
        } else if(graphStatus['full-graph'] == 1) {
            alert('The graph is already been shown.');
        }
        
     }

     function processCommunityData() {
        if(graphStatus['community-group'] == 0) {
            flag['compute_com'] = true;
            currentHighlightNode = 'null';
            currentHighlightEdge = 'null';
            resetButton('full-graph');
            resetButton('community-profile');
            runGraph();
            graphStatus['community-group'] = 1;
            $('#community-group').removeClass('btn-default').addClass('btn-success');
            $('#community-group i').removeClass('fa-times').addClass('fa-check');
        } else if(graphStatus['community-group'] == 1) {
            alert('The graph is already been shown.');
        }
     }

     function runGraph() {
        clearGraph();
        if(flag['compute_com']){
            graphData = fetchCommunityData();
        } else {
            graphData = fetchData();
        }
        plotFullGraph();
        addZoomListener();
        addSearchBoxListener();
        addBackButtonListener();
        addHilightListener();
     }

    function processCommunityProfile() {
        if(graphStatus['community-profile'] == 0 && graphStatus['community-group'] == 1) {

            var existValue = false;
            var data = {};

            var memberSelected = [];    
            $("#memberProfile :selected").each(function(){
                memberSelected.push($(this).val());
            });
            var aisRatioSelected = [];    
            $("#aisRatioProfile :selected").each(function(){
                aisRatioSelected.push($(this).val());
            });
            var daytimeNighttimeSelected = [];    
            $("#daytimeNighttimeProfile :selected").each(function(){
                daytimeNighttimeSelected.push($(this).val());
            });
            var weekdayWeekendSelected = [];    
            $("#weekdayWeekendProfile :selected").each(function(){
                weekdayWeekendSelected.push($(this).val());
            });
            var callOtherCarrierSelected = [];    
            $("#callOtherCarrierProfile :selected").each(function(){
                callOtherCarrierSelected.push($(this).val());
            });
            var averageNoOfCallSelected = [];    
            $("#averageNoOfCallProfile :selected").each(function(){
                averageNoOfCallSelected.push($(this).val());
            });
            var averageArpuSelected = [];    
            $("#averArpuProfile :selected").each(function(){
                averageArpuSelected.push($(this).val());
            });
            var averageDurationSelected = [];    
            $("#averageDurationProfile :selected").each(function(){
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
                graphStatus['community-profile'] = 1;
                $('#community-profile').removeClass('btn-default').addClass('btn-warning');
                $('#community-profile i').removeClass('fa-times').addClass('fa-refresh');
                ajaxSetup();

                $.ajax({
                    type: "GET",
                    url: "http://localhost/seniorproject/public/getNodeCommunityProfile/" + did,
                    data : {"sendprofile":data},
                    success: function(e){
                        console.log(e);
                        $('#community-profile').removeClass('btn-warning').addClass('btn-success');
                        $('#community-profile i').removeClass('fa-refresh').addClass('fa-check');
                    
                        var filteredNodes = [];
                        graphData.nodes.forEach(function(n) {
                            for (var i = 0; i < e.length; i++) {
                                if(n['attributes']['Modularity Class'] !== e[i]){
                                    if(filteredNodes.indexOf(n) < 0 && i == e.length-1){
                                        delete graphData.nodes[n['attributes']['Modularity Class']];
                                        for (var j = 0; j < graphData.edges.length; j++){
                                            if(graphData.edges[j] === undefined) {
                                                continue;
                                            }
                                            if(graphData.edges[j]['source'] == n['id']){
                                                delete graphData.edges[j];
                                                break;
                                            }
                                            else if(graphData.edges[j]['target'] == n['id']){
                                                delete graphData.edges[j];
                                                break;
                                            }
                                        }
                                        filteredNodes.push(n); 
                                    }
                                    continue;
                                } else if(n['attributes']['Modularity Class'] == e[i]){
                                    break;
                                }
                            }
                        });
                        $('#communityProfileModal').modal('hide');  
                        plotPartialGraph(filteredNodes);
                        resetButton('community-group');
                        graphStatus['community-profile'] = 2;
                    },
                    error: function(rs, e){
                        console.log(rs.responseText);
                        alert('Problem occurs during fetch data.');
                    },
                })
            } else alert("Please fill some community profile.");
        } else if(graphStatus['community-profile'] == 1) {
            alert('The graph is in processing.');
        } else if(graphStatus['community-profile'] == 2) {
            alert('The graph is already been shown.');
        }   
    }

    /**
     *  @brief Main function of this file
     *
     *  @param undefined
     *  @return void
     */
    !function(undefined){
        initSigma();

        document.getElementById('community-group').addEventListener('click', processCommunityData);
        document.getElementById('full-graph').addEventListener('click', processData);
        document.getElementById('communityProfile-filter').addEventListener('click', processCommunityProfile);
    }();

}();

