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
    var colors = [];
    var numIDMapper = {};
    var s;
    var currentHilight = 'default';
    var initForce = false;

    var filter = {
        startDate : [19700101, 21000101],
        callDay : ['.+'],
        startTime : [0.0, 24.00],
        duration : [0, 99999],
        rnCode : ['.+']
    };
    var graphStatus = {
        'full-graph' : 2,
        'community' : 0,
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
        ajaxSetup();
        $.ajax({
            type: "GET",
            url: "http://localhost/seniorproject/public/getCDR/" + did,
            data : {},
            success: function(e){
                console.log(e);
                preparedData = e;
            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            },
            async: false,
        })
        return preparedData;
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
        s.bind('clickNode', clickNodeListener);
        s.bind('doubleClickNode', doubleClickNodeListener);


        // Display Graph using sigma object
        initForce = true;
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
        // console.log(nodeData['attributes']['Modularity Class']);
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
        graphData.nodes.forEach(function(n) {
            if(n.id == nodeID) {
                nodeData = n;
            }
        });
        if(nodeData == undefined) {
            alert('Can\'t get node data');
            return;
        }
        // TODO : Update right column
        document.getElementById('cname').innerHTML = 'Unknown';
        document.getElementById('cage').innerHTML = 'Unknown';
        document.getElementById('cnumber').innerHTML = 'Unknown';
        document.getElementById('cpromotion').innerHTML = 'Unknown';
        document.getElementById('ccarrier').innerHTML = 'Unknown';
        document.getElementById('cgender').innerHTML = 'Unknown';

        document.getElementById('comrank').innerHTML = '';
        document.getElementById('comsize').innerHTML = '';
        document.getElementById('cc').innerHTML = '### (' + parseFloat(nodeData.attributes['Closeness Centrality']).toFixed(3) + ')';
        document.getElementById('bc').innerHTML = '### (' + parseFloat(nodeData.attributes['Betweenness Centrality']).toFixed(3) + ')';
        
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
            node.color = node['attributes']['RnCode'] == 'TRUE' ? "#e74c3c" : (node['attributes']['RnCode'] == 'AIS' ? "#40d47e" : (node['attributes']['RnCode'] == 'DTC' ? "#3498db" : '#000000'));
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
        alert('Coming Soon ...');
    }

    function colorByPromotion() {
        alert('Coming Soon ...');
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
            rnCode : carrier.length == 0? ['.+'] : carrier
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
        graphStatus['full-community'] = 0;

        $('#full-graph').removeClass('btn-warning').removeClass('btn-success').addClass('btn-default');
        $('#full-graph i').removeClass('fa-refresh').removeClass('fa-check').addClass('fa-times');


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

     function runGraph() {
        clearGraph();
        graphData = fetchData();
        plotFullGraph();
        addZoomListener();
        addSearchBoxListener();
        addBackButtonListener();
        addHilightListener();
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



        document.getElementById('full-graph').addEventListener('click', processData);
    }();

}();

