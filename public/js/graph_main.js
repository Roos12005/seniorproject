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

    var graphData = [];
    var colors = [];
    var numIDMapper = {};
    var s;
    var currentHilight = 'default';
    var initForce = false;
    var compute_com = false;
    var activateClick = false;

    var filter = {
        startDate : [19700101, 21000101],
        callDay : ['.+'],
        startTime : [0.0, 24.00],
        duration : [0, 99999],
        rnCode : ['.+'],
        ComOfCom : 0
    };

    var graphStatus = {
        'full-graph' : 0,
        'community-group' : 0,
        'export-data' : 0,
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
            }]

            /* 
             *  TODO : May add some other setting here... 
             *  Visit https://github.com/jacomyal/sigma.js/wiki/Settings for more setting available
             *
            */
        });
        s.settings({
            defaultEdgeType: "curve",
            minEdgeSize : 0.2,
            maxEdgeSize : 0.5,
            minNodeSize : 1,
            maxNodeSize : 7,
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
        ajaxSetup();
        $.ajax({
            type: "GET",
            url: "http://localhost/seniorproject/public/getCDR",
            data : {},
            success: function(e){
                console.log(e);
                preparedData = e;
                setDate();
                var user_array = new Array();
                var communities = new Array();

                $.each(e.nodes, function(index, user_info) {
                    if(user_info['attributes']['RnCode'] == "AIS"){
                        carrier[0] += 1;
                    }
                    else if(user_info['attributes']['RnCode'] == "DTAC"){
                        carrier[1] += 1;
                    }
                    else if(user_info['attributes']['RnCode'] == "TRUE"){
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
                    user_array.push(user_info);
                });

                carrier[0] = (carrier[0]/user_array.length * 100).toFixed(1);
                carrier[1] = (carrier[1]/user_array.length * 100).toFixed(1);
                carrier[2] = (carrier[2]/user_array.length * 100).toFixed(1);
                carrier[3] = (carrier[3]/user_array.length * 100).toFixed(1);

                for (var i in communities) {
                    com_data.push({value: communities[i], label: 'Community ID ' + i, formatted: communities[i] + ' members'});
                }

                document.getElementById('unique_numbers').innerHTML = user_array.length;
                document.getElementById('communities').innerHTML = communities.length;
            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            },
            async: false,
        })
        
        createPieChart(com_data,color_data,carrier);

        return preparedData;
    }

    function fetchCommunityData(){
        
        var preparedData = [];
        var carrier = [0,0,0,0];
        var com_data = new Array();
        var color_data = new Array();
        ajaxSetup();
        $.ajax({
            type: "GET",
            url: "http://localhost/seniorproject/public/getCommunityOfCommunity",
            data : {},
            success: function(e){
                console.log(e);
                preparedData = e;
                setDate();
                var communities = new Array();

                $.each(e.nodes, function(index, community_info) {
                     communities[community_info['attributes']['Modularity Class']] = community_info['attributes']['Member'];
                     color_data[community_info['attributes']['Modularity Class']] = community_info['color'];
                });

                for (var i in communities) {
                    com_data.push({value: communities[i], label: 'Community ID ' + i, formatted: communities[i] + ' members'});
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
            url: "http://localhost/seniorproject/public/getCarrier",
            data : {},
            success: function(e){
                console.log(e);
                var user_num = e['all'];

                carrier[0] = (e['ais']/e['all'] * 100).toFixed(1);
                carrier[1] = (e['true']/e['all'] * 100).toFixed(1);
                carrier[2] = (e['dtac']/e['all'] * 100).toFixed(1);
                carrier[3] = ((e['all']-e['ais']-e['true']-e['dtac'])/e['all'] * 100).toFixed(1);

                document.getElementById('unique_numbers').innerHTML = e['all'];
            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            },
            async: false,
        })
        
        createPieChart(com_data,color_data,carrier);

        return preparedData;
    }

    function createPieChart(com_data, color_data, carrier){
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
                {value: carrier[0], label: 'AIS', formatted: 'at least ' + carrier[0] + "%" },
                {value: carrier[1], label: 'DTAC', formatted: 'approx. ' + carrier[1] + "%" },
                {value: carrier[2], label: 'TRUE', formatted: 'approx. ' + carrier[2] + "%" },
                {value: carrier[3], label: 'OTHER', formatted: 'at most ' + carrier[3] + "%" }
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
        if(!activateClick){
            s.bind('clickNode', clickNodeListener);
            if(compute_com){
                s.bind('doubleClickNode', doubleClickCommunityListener);
            } else {
                s.bind('doubleClickNode', doubleClickNodeListener);
            }
            activateClick = true;
        }

        // Display Graph using sigma object
        s.startForceAtlas2({});
        setTimeout(function () {
            s.killForceAtlas2();
        }, 500);
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
        var nodeData = updateInformation(node);
     }

     function doubleClickNodeListener(node) {
        // TODO : Display only selected community
        var nodeData = updateInformation(node);
        // Show back button on the top right of the div
        document.getElementsByClassName('back-section')[0].style.display = 'block';
        var filteredNodes = [];
        graphData.nodes.forEach(function(n) {
            if(nodeData['attributes']['Modularity Class'] !== n['attributes']['Modularity Class']) {
                filteredNodes.push(n);
            }
        });

        plotPartialGraph(filteredNodes);
     }

     function doubleClickCommunityListener(node) {
        // TODO : Display only selected community
        var nodeData = updateInformation(node);
        // Show back button on the top right of the div
        document.getElementsByClassName('back-section')[0].style.display = 'block';
        var selectedCommunity = nodeData['attributes']['Modularity Class'];
        var communityData = [];
        clearGraph();

        ajaxSetup();
        $.ajax({
            type: "GET",
            url: "http://localhost/seniorproject/public/getNodeInSelectedCommunity",
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
            }
        });        
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
        graphData.nodes.forEach(function(n) {
            if(n.id == nodeID) {
                nodeData = n;
            }
            bc.push(parseFloat(n.attributes['Betweenness Centrality']));
            cc.push(parseFloat(n.attributes['Closeness Centrality']));
            if(compute_com){
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
        // document.getElementById('cname').innerHTML = 'Unknown';
        document.getElementById('cage').innerHTML = nodeData.attributes['Age'];
        document.getElementById('cnumber').innerHTML = nodeData.label;
        document.getElementById('cpromotion').innerHTML = nodeData.attributes['Promotion'];
        document.getElementById('ccarrier').innerHTML = nodeData.attributes['RnCode'];
        document.getElementById('cgender').innerHTML = nodeData.attributes['Gender'];
        document.getElementById('cc').innerHTML = cc.indexOf(nodeData.attributes['Closeness Centrality']) + ' (' + parseFloat(nodeData.attributes['Closeness Centrality']).toFixed(3) + ')';
        document.getElementById('bc').innerHTML = bc.indexOf(nodeData.attributes['Betweenness Centrality']) + ' (' + parseFloat(nodeData.attributes['Betweenness Centrality']).toFixed(3) + ')';
        document.getElementById('comid').innerHTML = nodeData.attributes['Modularity Class'];
        if(compute_com){
            document.getElementById('comrank').innerHTML = communityRank.indexOf(nodeData.attributes['Member']) + 1;
            document.getElementById('comsize').innerHTML = nodeData.attributes['Member'];
            document.getElementById('comnum').innerHTML = nodeData.attributes['Member'];
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
        });
     }

    function colorByDefault() {
        if(currentHilight == 'default') return;
        hilightButton('#h-default');
        s.graph.nodes().forEach(function(node) {
            node.color = '#a5adb0';
            node.size = node.defaultSize;
        });
        s.graph.edges().forEach(function(edge) {
            edge.color = '#999999';
        });
        currentHilight = 'default';
        s.refresh();
    }

    function colorByCommunity() {
        if(currentHilight == 'community') return;
        colorByDefault();
        hilightButton('#h-community');
        s.graph.nodes().forEach(function(node) {
            node.color = node.communityColor;
        });
        currentHilight = 'community';
        s.refresh();
    }

    function colorByCentrality() {

        if(currentHilight == 'centrality') return;
        colorByDefault();
        hilightButton('#h-centrality');
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
        currentHilight = 'centrality';
        s.refresh();
    }

    function colorByCarrier() {
        if(currentHilight == 'carrier') return;
        colorByDefault();
        hilightButton('#h-carrier');
        s.graph.nodes().forEach(function(node) {
            node.color = node['attributes']['RnCode'] == 'TRUE' ? "#e74c3c" : (node['attributes']['RnCode'] == 'AIS' ? "#40d47e" : (node['attributes']['RnCode'] == 'DTAC' ? "#3498db" : '#000000'));
        });
        s.refresh();
        currentHilight = 'carrier';
    }

    function colorByAIS() {
        if(currentHilight == 'ais') return;
        colorByDefault();
        hilightButton('#h-ais');
        s.graph.nodes().forEach(function(node) {
            node.color = node['attributes']['RnCode'] == 'AIS' ? "#40d47e" : '#bdc3c7';
        });
        s.refresh();
        currentHilight = 'ais';
    }

    function colorByDayNight() {
        if(currentHilight == 'daynight') return;
        hilightButton('#h-daynight');
        var color = "";
        s.graph.edges().forEach(function(edge) {
            if(edge['attributes']['startTime'] <= 17 &&  edge['attributes']['startTime'] >= 5) {
                color = "#FF9900";
            }
            else color = "#000000";
            edge.color = color;
        });
        s.refresh();
        currentHilight = 'daynight';
    }

    function colorByPromotion() {
        if(currentHilight == 'promotion') return;
        colorByDefault();
        hilightButton('#h-promotion');
        var maxARPU = 0.1;
        s.graph.nodes().forEach(function(node) {
            if(parseInt((node['attributes']['Promotion'].split(' '))[0]) > maxARPU) {
                maxARPU = parseInt((node['attributes']['Promotion'].split(' '))[0]);
            }
        });

        var ARPU = 0;
        s.graph.nodes().forEach(function(node) {
            ARPU = parseInt((node['attributes']['Promotion'].split(' '))[0]);
            var colorScale =  255 * ARPU/maxARPU;
            var hexString = parseInt(colorScale).toString(16);
            hexString = hexString.length == 1? '0' + hexString : hexString;
            node.color = '#' + hexString + "0000";
        });
        currentHilight = 'promotion';
        s.refresh();
    }

    function colorByDegree() {
        alert('Coming Soon ...');
    }

    function hilightButton(name) {
        $('.hilight').removeClass('h-on');
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
        document.getElementById('h-default').addEventListener('click', colorByDefault);
        document.getElementById('h-community').addEventListener('click', colorByCommunity);
        document.getElementById('h-centrality').addEventListener('click', colorByCentrality);
        document.getElementById('h-carrier').addEventListener('click', colorByCarrier);
        document.getElementById('h-ais').addEventListener('click', colorByAIS);
        document.getElementById('h-promotion').addEventListener('click', colorByPromotion);
        document.getElementById('h-degree').addEventListener('click', colorByDegree);
        document.getElementById('h-daynight').addEventListener('click', colorByDayNight);
     }

     function initFilter() {
        document.getElementById('filter-save').addEventListener('click', saveFilter);
        document.getElementById('filter-cancel').addEventListener('click', discardFilter);
        
        $('.time-filter').mask('99.99');
     }


     function saveFilter() {
        resetButton();

        var day = [];
        $.each($('.day-checkbox:checked'), function() {
            day.push($(this).val());
        });

        var carrier = [];
        $.each($('.carrier-checkbox:checked'), function() {
            carrier.push($(this).val());
        });
    
        filter = {
            startDate : [$('#e1').val(),$('#e1').val().substr(6,2) == '01'? $('#e1').val().substr(0,6) + '15' : $('#e1').val().substr(0,6) + '31'],
            callDay : day.length == 0? ['.+'] : day,
            startTime : $('#callPeriodFrom').val() == ''? [0.0, 24.00] : [$('#callPeriodFrom').val(), $('#callPeriodTo').val()],
            duration : $('#callDurationFrom').val() == ''? [1, 99999] : [$('#callDurationFrom').val(), $('#callDurationTo').val()],
            // noOfCall : $('#noOfCallFrom').val() == ''? [] : [$('#noOfCallFrom').val(), $('#noOfCallTo').val()],
            rnCode : carrier.length == 0? ['.+'] : carrier,
            ComOfCom : 0
        }

        $.each(filter, function(k,e) {
            if(e != '' && e.length != 0 && e[0] != '.+' && e[0] != undefined) {
                $('#' + k + '-button').removeClass('btn-default').addClass('btn-primary');
            } else {
                $('#' + k + '-button').removeClass('btn-primary').addClass('btn-default');
            }
        });

        $('#filterModal').modal('hide');
     }

     function discardFilter() {
        $('#filterModal').modal('hide');  
     }

     function resetButton() {
        graphStatus['full-graph'] = 0;
        graphStatus['community-group'] = 0;
        graphStatus['export-data'] = 0;

        $('#full-graph').removeClass('btn-warning').removeClass('btn-success').addClass('btn-default');
        $('#full-graph i').removeClass('fa-refresh').removeClass('fa-check').addClass('fa-times');

        $('#community-group').removeClass('btn-warning').removeClass('btn-success').addClass('btn-default');
        $('#community-group i').removeClass('fa-refresh').removeClass('fa-check').addClass('fa-times');

        $('#export-data').removeClass('btn-warning').removeClass('btn-success').addClass('btn-default');
        $('#export-data i').removeClass('fa-refresh').removeClass('fa-check').addClass('fa-times');

     }

     function processData() {
        if(graphStatus['full-graph'] == 0) {
            graphStatus['full-graph'] = 1;
            $('#full-graph').removeClass('btn-default').addClass('btn-warning');
            $('#full-graph i').removeClass('fa-times').addClass('fa-refresh');
            ajaxSetup();
            $.ajax({
                type: "POST",
                url: "http://localhost/seniorproject/public/processData",
                data : filter,
                success: function(e){
                    console.log(e);
                    $('#full-graph').removeClass('btn-warning').addClass('btn-success');
                    $('#full-graph i').removeClass('fa-refresh').addClass('fa-check');
                    // TODO : trigger button
                    graphStatus['full-graph'] = 2;
                },
                error: function(rs, e){
                    console.log(rs.responseText);
                }
            });
        } else if(graphStatus['full-graph'] == 1) {
            alert('Graph is processing ...'); 
        } else if(graphStatus['full-graph'] == 2) {
            runGraph();
            graphStatus['full-graph'] = 3;
        } else if(graphStatus['full-graph'] == 3) {
            alert('The graph is already been shown.');
        }
        
     }

     function processCommunityData() {
        if(graphStatus['community-group'] == 0) {
            filter['ComOfCom'] = 1;
            compute_com = true;
            graphStatus['community-group'] = 1;
            $('#community-group').removeClass('btn-default').addClass('btn-warning');
            $('#community-group i').removeClass('fa-times').addClass('fa-refresh');
            ajaxSetup();
            $.ajax({
                type: "POST",
                url: "http://localhost/seniorproject/public/processData",
                data : filter,
                success: function(e){
                    console.log(e);
                    $('#community-group').removeClass('btn-warning').addClass('btn-success');
                    $('#community-group i').removeClass('fa-refresh').addClass('fa-check');
                    // TODO : trigger button
                    graphStatus['community-group'] = 2;
                },
                error: function(rs, e){
                    console.log(rs.responseText);
                }
            });
        } else if(graphStatus['community-group'] == 1) {
            alert('Graph is processing ...'); 
        } else if(graphStatus['community-group'] == 2) {
            runGraph();
            graphStatus['community-group'] = 3;
        } else if(graphStatus['community-group'] == 3) {
            alert('The graph is already been shown.');
        }
        
     }

     function runGraph() {
        clearGraph();
        if(compute_com){
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

    function setDate(){
        var year = document.getElementById('e1').value.substring(0,4);
        if(year == "1970"){
            document.getElementById('date').innerHTML = "All Data";
        }
        else{
            var month = document.getElementById('e1').value.substring(4,6);
            var date = document.getElementById('e1').value.substring(6,8);
            if(month == "01") month = "Jan";
            else if(month == "02") month = "Feb";
            else if(month == "03") month = "Mar";
            else if(month == "04") month = "Apr";
            else if(month == "05") month = "May";
            else if(month == "06") month = "Jun";
            else if(month == "07") month = "Jul";
            else if(month == "08") month = "Aug";
            else if(month == "09") month = "Sep";
            else if(month == "10") month = "Oct";
            else if(month == "11") month = "Nov";
            else if(month == "12") month = "Dec";
            if(date == "") date = " - All Month";
            else if(date == "01") date = " - Week 1";
            else if(date == "08") date = " - Week 2";
            else if(date == "15") date = " - Week 3";
            else if(date == "22") date = " - Week 4";
            document.getElementById('date').innerHTML = month + date;
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
        initFilter();


        document.getElementById('community-group').addEventListener('click', processCommunityData);
        document.getElementById('full-graph').addEventListener('click', processData);
    }();

}();

