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
    var category = {};
    var numIDMapper = {};
    var s;
    var currentHighlightNode = 'null';
    var currentHighlightEdge = 'null';
    var selectedCom = 'null';
    var flag = {
        compute_com : false,
        activateClick : false,
        clickListenerComOfCom : false,
        canImport : false
    }
    var currentMaxNodeSize = 5;
    var currentMaxEdgeSize = 0.1;
    var zoomScale = {
        prev: 1,
        current: 1
    };

    var graphStatus = {
        'full-graph' : 0,
        'community-group' : 0,
        'community-profile' : 0,
    }

    function numberWithCommas(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
                type: 'canvas'
            }]

            /* 
             *  TODO : May add some other setting here... 
             *  Visit https://github.com/jacomyal/sigma.js/wiki/Settings for more setting available
             *
             */
         });
        s.settings({
            defaultEdgeType: "curvedArrow",
            minEdgeSize : 0.003,
            maxEdgeSize : currentMaxEdgeSize,
            minNodeSize : 0.5,
            maxNodeSize : currentMaxNodeSize,
            // zoomMin : 0.25,
            // zoomMax : 40,
            edgeColor : 'default',
            defaultEdgeArrow: 'source',
            mouseWheelEnabled: false,
            edgeLabelThreshold: 10,
            //enableEdgeHovering: true,
            //edgeHoverPrecision: 5,
            //edgeHoverExtremities: true,
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
            size: 0.5,
            color: '#a5adb0',
            communityColor: n.color,
            defaultSize: 0.5,
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
            label: e.label,
            attributes : e.attributes,
            color: '#a5adb0',
            type: "curvedArrow",
            size: 0.005
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
        $('#loading-overlay').show();
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
                $('#loading-overlay').hide();
                console.log(e);

                preparedData = e;
                user_num = e['nodes'].length;
                $.each(e.nodes, function(index, user_info) {
                    if (!communities[user_info['attributes']['Modularity Class']]) {
                        communities[user_info['attributes']['Modularity Class']] = 1;
                    }
                });

                $.ajax({
                    type: "GET",
                    url: "http://localhost/seniorproject/public/getCarrier/" + did,
                    data : {},
                    success: function(e){
                        console.log(e);

                        carrier[0] = e['ais'];
                        carrier[1] = e['true'];
                        carrier[2] = e['dtac'];
                        carrier[3] = e['tot'];
                        carrier[4] = e['all']-e['ais']-e['true']-e['dtac']-e['tot'];
                        createPieChart(carrier,user_num);
                    },
                    error: function(rs, e){
                        console.log(rs.responseText);
                        alert('Problem occurs during fetch data.');
                    }
                });
                document.getElementById('unique_numbers').innerHTML = numberWithCommas(user_num);
                document.getElementById('communities').innerHTML = numberWithCommas(communities.length);
                document.getElementById('transactions').innerHTML = numberWithCommas(e['edges'].length);

                graphData = preparedData;
                plotFullGraph();
                addZoomListener();
                addLabelListener();
                addSearchBoxListener();
                addBackButtonListener();
                addHilightListener();
                flag['canImport'] = true;
                graphStatus['full-graph'] = 1;
                $('#full-graph').removeClass('btn-default').addClass('btn-success');
                $('#full-graph i').removeClass('fa-times').addClass('fa-check');

                $('#loading-overlay').hide();
            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            },
            async: false,
        })
    }

    function fetchCommunityData(){
        $('#loading-overlay').show();
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

                $.ajax({
                    type: "GET",
                    url: "http://localhost/seniorproject/public/getCarrier/" + did,
                    data : {},
                    success: function(e){
                        console.log(e);
                        node_num = e['all'];
                        carrier[0] = e['ais'];
                        carrier[1] = e['true'];
                        carrier[2] = e['dtac'];
                        carrier[3] = e['tot'];
                        carrier[4] = e['all']-e['ais']-e['true']-e['dtac']-e['tot'];

                        document.getElementById('unique_numbers').innerHTML = numberWithCommas(e['all']);
                        document.getElementById('transactions').innerHTML = numberWithCommas(e['calls']);
                        createPieChart(carrier,node_num);
                    },
                    error: function(rs, e){
                        console.log(rs.responseText);
                        alert('Problem occurs during fetch data.');
                    }
                });
                communities = e.nodes;
                document.getElementById('communities').innerHTML = numberWithCommas(communities.length);
                graphData = preparedData;
                plotFullGraph();
                addZoomListener();
                addLabelListener();
                addSearchBoxListener();
                addBackButtonListener();
                addHilightListener();
                graphStatus['community-group'] = 1;
                $('#community-group').removeClass('btn-default').addClass('btn-success');
                $('#community-group i').removeClass('fa-times').addClass('fa-check');



            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            }
        })
    }

    function createPieChart(carrier, node_num){        
        Morris.Donut({
            element: 'graph-donut2',
            data: [
                {value: carrier[0], label: 'AIS', formatted: numberWithCommas(carrier[0]) + ' users : ' + (carrier[0]/node_num * 100).toFixed(0) + "%" },
                {value: carrier[1], label: 'TRUE', formatted: numberWithCommas(carrier[1]) + ' users : ' + (carrier[1]/node_num * 100).toFixed(0) + "%" },
                {value: carrier[2], label: 'DTAC', formatted: numberWithCommas(carrier[2]) + ' users : ' + (carrier[2]/node_num * 100).toFixed(0) + "%" },
                {value: carrier[3], label: 'TOT', formatted: numberWithCommas(carrier[3]) + ' users : ' + (carrier[3]/node_num * 100).toFixed(0) + "%" },
                {value: carrier[4], label: 'OTHER', formatted: numberWithCommas(carrier[4]) + ' users : ' + (carrier[4]/node_num * 100).toFixed(0) + "%" }
            ],
            backgroundColor: '#fff',
            labelColor: '#1fb5ac',
            colors: [
            '#66CC66','#FF0000','#00CCFF','#ABDEEA','#DDDDDD'
            ],
            formatter: function (x, data) { return data.formatted; }
        });
    }

    function replotGraph(gdata) {
        numIDMapper = {};
        // Add all returned nodes to sigma object
        gdata.nodes.forEach(function(n) {
            addNode(n);
            numIDMapper[n.label] = n.id;
        });
        // Add all return edges to sigma object
        gdata.edges.forEach(function(edge) {
            addEdge(edge);
        });
        
        colorByCentrality();
        s.startForceAtlas2({});
        setTimeout(function () {
            s.killForceAtlas2();

            $('#loading-overlay').hide();
        }, 10000 + Math.pow(1.00025,gdata.nodes.length)*gdata.nodes.length);
        s.camera.goTo({x:0, y:0, ratio: 1});
        s.refresh();
        flag['clickListenerComOfCom'] = true;
        flag['compute_com'] = false;
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
            if(edge['attributes']['noDayTime'] + edge['attributes']['noNightTime'] > parseInt($("#weight_filter").val())){
                addEdge(edge); 
            }
        });

        // Add Click Listener to all Nodes
        if(!flag['activateClick']){
            s.bind('clickNode', clickNodeListener);
            s.bind('doubleClickNode', doubleClickNodeListener);
            flag['activateClick'] = true;
        }

        // Display Graph using sigma object
        s.startForceAtlas2({adjustSizes: true, linLogMode:  true});
        currentHighlightNode = 'null';
        currentHighlightEdge = 'null';

        setTimeout(function () {
            s.killForceAtlas2();
            console.log('done');
            $('#loading-overlay').hide();
        }, 10000 + graphData.nodes.length);
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

        s.startForceAtlas2({adjustSizes: true, linLogMode:  true});
        setTimeout(function () {
            s.killForceAtlas2();
        }, 500);
        s.refresh();

        // colorByDefaultNode();
        // colorByDefaultEdge();
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
            s.camera.goTo({x:s.camera.x, y:s.camera.y, ratio: 0.75 * s.camera.ratio});
        });

        // Zoom out Button
        document.getElementById("zoomout").addEventListener("click", function(){
            s.camera.goTo({x:s.camera.x, y:s.camera.y, ratio: 1.25 * s.camera.ratio});
        });

        // Refresh Zoom Button
        document.getElementById("nozoom").addEventListener("click", function(){
            s.camera.goTo({x:0, y:0, ratio: 1});
        });
    }


    function findCommunityID(num) {
        ajaxSetup();
        $.ajax({
            type: "GET",
            url: "http://localhost/seniorproject/public/findCommunity/" + did,
            data : {number: num},
            success: function(e){
                
                var node = s.graph.nodes(numIDMapper['Community' + e['communityID']]);
                if(node == undefined) {
                    alert("Number " + input + " is not found. Please check your input number again.");
                    return;
                }
                doubleClickNodeAndZoom(node, num);

            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            }
        })
    }

    function addLabelListener() {
        // Show Node Label Button
        document.getElementById("node_label").addEventListener("click", function(){
            if($('#node_label').attr("class") == "btn btn-danger"){
                $('#node_label').removeClass('btn-danger').addClass('btn-success');
                s.settings({
                    labelThreshold: 0.1,
                });
                s.refresh();
            } else {
                $('#node_label').removeClass('btn-success').addClass('btn-danger');
                s.settings({
                    labelThreshold: 8,
                });
                s.refresh();
            }  
        });

        // Show Edge Label Button
       document.getElementById("edge_label").addEventListener("click", function(){
            if($('#edge_label').attr("class") == "btn btn-danger"){
                $('#edge_label').removeClass('btn-danger').addClass('btn-success');
                s.settings({
                    edgeLabelThreshold: 0.01,
                });
                s.refresh();
            } else {
                $('#edge_label').removeClass('btn-success').addClass('btn-danger');
                s.settings({
                    edgeLabelThreshold: 10,
                });
                s.refresh();
            }  
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
                try {
                    var node = s.graph.nodes(numIDMapper[input]);
                    doubleClickNodeListener(node);    
                } catch (err) {
                    if(flag['compute_com']){
                        findCommunityID(input);
                    }
                }
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
        $('#loading-overlay').show();
        setTimeout(function() {
            var nodeData = updateInformation(node);
            currentHighlightNode = 'null';
            currentHighlightEdge = 'null';
            // Show back button on the top right of the div
            document.getElementsByClassName('back-section')[0].style.display = 'block';
            if(flag['compute_com']){
                var selectedCommunity = nodeData['attributes']['Modularity Class'];
                clearGraph();

                ajaxSetup();
                $.ajax({
                    type: "GET",
                    url: "http://localhost/seniorproject/public/getNodeInSelectedCommunity/" + did,
                    data : {"senddata":selectedCommunity},
                    success: function(e){
                       console.log(e);
                       communityData = e;
                       selectedCom = selectedCommunity;
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
                        
                        colorByCentrality();
                        s.startForceAtlas2({});
                        setTimeout(function () {
                            s.killForceAtlas2();
                            s.camera.goTo({x:0, y:0, ratio: 1});
                            s.refresh();
                            $('#loading-overlay').hide();
                        }, 10000 + Math.pow(1.00025,e.nodes.length)*e.nodes.length);
                        addLabelListener();
                        flag['clickListenerComOfCom'] = true;
                        flag['canImport'] = true;
                        flag['compute_com'] = false;
                    },
                    error: function(rs, e){
                        console.log(rs.responseText);
                        alert('Problem occurs during fetch data.');
                    }
                });        
            } else { 
                flag['drilldown'] = true;
                clearGraph();
                console.log(node);
                ajaxSetup();
                $.ajax({
                    type: "GET",
                    url: "http://localhost/seniorproject/public/getNeighbors/" + did,
                    data : {"node" : node.data.node.label},
                    success: function(e){
                       console.log(e);
                       var neighborsData = e;

                       numIDMapper = {};
                        // Add all returned nodes to sigma object
                        neighborsData.nodes.forEach(function(n) {
                            addNode(n);
                            numIDMapper[n.label] = n.id;
                        });
                        // Add all return edges to sigma object
                        neighborsData.edges.forEach(function(edge) {
                            addEdge(edge);
                        });
                        
                        colorByCommunity();
                        s.startForceAtlas2({});
                        setTimeout(function () {
                            s.killForceAtlas2();
                            $('#loading-overlay').hide();
                        }, 5000 + Math.pow(1.00025,e.nodes.length)*e.nodes.length);
                        s.camera.goTo({x:0, y:0, ratio: 1});
                        addLabelListener();
                        s.refresh();
                    },
                    error: function(rs, e){
                        console.log(rs.responseText);
                        alert('Problem occurs during fetch data.');
                    }
                });    
            }
        }, 500);
        
    }

    function doubleClickNodeAndZoom(node, zoomNode) {
        // TODO : Display only selected community
        $('#loading-overlay').show();
        setTimeout(function() {
            var nodeData = updateInformation(node);
            currentHighlightNode = 'null';
            currentHighlightEdge = 'null';
            // Show back button on the top right of the div
            document.getElementsByClassName('back-section')[0].style.display = 'block';
            if(flag['compute_com']){
                var selectedCommunity = nodeData['attributes']['Modularity Class'];
                clearGraph();

                ajaxSetup();
                $.ajax({
                    type: "GET",
                    url: "http://localhost/seniorproject/public/getNodeInSelectedCommunity/" + did,
                    data : {"senddata":selectedCommunity},
                    success: function(e){
                       console.log(e);
                       communityData = e;
                       selectedCom = selectedCommunity;
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
                        var node = s.graph.nodes(numIDMapper[zoomNode]);
                        colorByCentrality();
                        s.startForceAtlas2({});
                        setTimeout(function () {
                            s.killForceAtlas2();
                            s.camera.goTo({
                                x: node['read_cam0:x'], 
                                y: node['read_cam0:y'], 
                                ratio: 0.1
                            });
                            s.refresh();
                            $('#loading-overlay').hide();
                        }, 10000 + Math.pow(1.00025,e.nodes.length)*e.nodes.length);
                        addLabelListener();
                        flag['clickListenerComOfCom'] = true;
                        flag['canImport'] = true;
                        flag['compute_com'] = false;
                    },
                    error: function(rs, e){
                        console.log(rs.responseText);
                        alert('Problem occurs during fetch data.');
                    }
                });        
            } else { 
                flag['drilldown'] = true;
                clearGraph();
                console.log(node);
                ajaxSetup();
                $.ajax({
                    type: "GET",
                    url: "http://localhost/seniorproject/public/getNeighbors/" + did,
                    data : {"node" : node.data.node.label},
                    success: function(e){
                       console.log(e);
                       var neighborsData = e;

                       numIDMapper = {};
                        // Add all returned nodes to sigma object
                        neighborsData.nodes.forEach(function(n) {
                            addNode(n);
                            numIDMapper[n.label] = n.id;
                        });
                        // Add all return edges to sigma object
                        neighborsData.edges.forEach(function(edge) {
                            addEdge(edge);
                        });
                        
                        colorByCommunity();
                        s.startForceAtlas2({});
                        setTimeout(function () {
                            s.killForceAtlas2();
                            $('#loading-overlay').hide();
                        }, 5000 + Math.pow(1.00025,e.nodes.length)*e.nodes.length);
                        s.camera.goTo({x:0, y:0, ratio: 1});
                        addLabelListener();
                        s.refresh();
                    },
                    error: function(rs, e){
                        console.log(rs.responseText);
                        alert('Problem occurs during fetch data.');
                    }
                });    
            }
        }, 500);
        
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
        var cc_rank = parseInt(cc.indexOf(parseFloat(nodeData.attributes['Closeness Centrality']))) + 1;
        var bc_rank = parseInt(bc.indexOf(parseFloat(nodeData.attributes['Betweenness Centrality']))) + 1;
        document.getElementById('cc').innerHTML = cc_rank + ' (' + numberWithCommas(parseFloat(nodeData.attributes['Closeness Centrality']).toFixed(1)) + ')';
        document.getElementById('bc').innerHTML = bc_rank + ' (' + numberWithCommas(parseFloat(nodeData.attributes['Betweenness Centrality']).toFixed(1)) + ')';
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
            $('#loading-overlay').show();

            if(graphStatus['community-profile'] == 2){
                resetButton('community-profile');
                graphStatus['community-group'] = 1;
                $('#community-group').removeClass('btn-default').addClass('btn-success');
                $('#community-group i').removeClass('fa-times').addClass('fa-check');
            }
            if(flag['drilldown']) {
                flag['drilldown'] = false;
                clearGraph();
                replotGraph(communityData);
            } else {
                document.getElementsByClassName('back-section')[0].style.display = 'none';
                flag['compute_com'] = true;
                flag['canImport'] = false;
                graphStatus['community-group'] = 1;
                flag['clickListenerComOfCom'] = false;
                clearGraph();
                s.stopForceAtlas2();
                plotFullGraph();
                s.camera.goTo({x:0, y:0, ratio: 1});    
            }
        });
    }

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
                    // console.log(node.size);
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
        // colorByDefaultNode();
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
        // if(currentHighlightEdge == 'default') return;
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
        $('#loading-overlay').show();
        if(graphStatus['full-graph'] == 0) {
            flag['compute_com'] = false;
            // currentHighlightNode = 'null';
            // currentHighlightEdge = 'null';
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
        $('#loading-overlay').show();
        setTimeout(function () {
            if(graphStatus['community-group'] == 0) {
                flag['compute_com'] = true;
                resetButton('full-graph');
                resetButton('community-profile');
                runGraph();
                
            } else if(graphStatus['community-group'] == 1) {
                alert('The graph is already been shown.');
            }
        }, 500);
    }

    function runGraph() {
        clearGraph();
        if(flag['compute_com']){
            graphData = fetchCommunityData();
        } else {
            graphData = fetchData();
        }
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
                data['memberProfile'] = myJsonString;
            }
            if(aisRatioSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(aisRatioSelected);
                data['aisRatioProfile'] = myJsonString;
            }
            if(daytimeNighttimeSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(daytimeNighttimeSelected);
                data['daytimeNighttimeProfile'] = myJsonString;
            }
            if(weekdayWeekendSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(weekdayWeekendSelected);
                data['weekdayWeekendProfile'] = myJsonString;
            }
            if(callOtherCarrierSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(callOtherCarrierSelected);
                data['callOtherCarrierProfile'] = myJsonString;
            }
            if(averageNoOfCallSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(averageNoOfCallSelected);
                data['averageNoOfCallProfile'] = myJsonString;
            }
            if(averageArpuSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(averageArpuSelected);
                data['averageArpuProfile'] = myJsonString;
            }
            if(averageDurationSelected.length > 0){
                existValue = true;
                var myJsonString = JSON.stringify(averageDurationSelected);
                data['averageDurationProfile'] = myJsonString;
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
                        $('#community-profile').removeClass('btn-warning').addClass('btn-success');
                        $('#community-profile i').removeClass('fa-refresh').addClass('fa-check');
                    
                        var filteredNodes = [];    
                        var match = false;

                        graphData.nodes.forEach(function(n) {
                            for(var i = 0; i < e.length; i++){
                                if(n['attributes']['Modularity Class'] == e[i]){
                                    match = true;
                                    break;
                                }
                                match = false;
                            }
                            if(!match && filteredNodes.indexOf(n) < 0){
                                filteredNodes.push(n); 
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
        //var category = {};
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
    
    function setMaxNodeSize(n) {
        s.settings({
            maxNodeSize: n
        });
        currentMaxNodeSize = n;
    }

    function setMaxEdgeSize(n) {
        s.settings({
            maxEdgeSize: n
        });
        currentMaxEdgeSize = n;
    }

    function enlargeGraph() {
        $('#loading-overlay').show();
        setTimeout(function(){
            setMaxNodeSize(zoomScale['current']*(currentMaxNodeSize/zoomScale['prev']));
            setMaxEdgeSize(zoomScale['current']*(currentMaxEdgeSize/zoomScale['prev']));
            s.graph.nodes().forEach(function(n) {
                n.size = (n.size/zoomScale['prev']) * zoomScale['current'];
            });
            s.graph.edges().forEach(function(e) {
                e.size = (e.size/zoomScale['prev']) * zoomScale['current'];
            });
            s.refresh();
            $('#loading-overlay').hide();
        },100);
    }

    function initSlider() {
        $("#size-slider").ionRangeSlider({
            min: 0.1,
            max: 4,
            from: 1,
            type: 'single',
            postfix: " x",
            step: 0.1,
            hasGrid: true,
            grid_num: 1,
            onFinish: function(data) {
                zoomScale['prev'] = zoomScale['current'];
                zoomScale['current'] = data.fromNumber;
                enlargeGraph();
            }
        });
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
        initSlider();
        $(document).ready(function() {
            if(isAPIAvailable()) {
                $('#files').bind('change', handleFileSelect);
            }
        });
        document.getElementById('highlightByFile').addEventListener('click',colorByAttribute);
    }();
}();

