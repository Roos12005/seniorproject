!function(){
    'use strict';

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
        'community-group' : 0,
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
            color: n.color,
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
            url: "http://localhost/seniorproject/public/getCommunityOfCommunity",
            data : {},
            success: function(e){
                console.log(e);
                preparedData = e;
                setDate();
                var communities = new Array();

                $.each(e.nodes, function(index, community_info) {
                     communities[community_info['id']] = community_info['attributes']['Member'];
                     color_data[community_info['id']] = community_info['color'];
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
        s.bind('clickNode', clickCommunityListener);
        s.bind('doubleClickNode', doubleClickCommunityListener);


        // Display Graph using sigma object
        if(initForce) {
            s.refresh();
        } else {
            initForce = true;
            s.startForceAtlas2({});
            setTimeout(function () {
                s.stopForceAtlas2();
            }, 500);
            
            
        }
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
     *  @brief  Listener on clicking node
     *
     *  Handling event when clicking on node ???
     *
     *  @param  node   clicked node
     *  @return void
     */
     function clickCommunityListener(node) {
        console.log("Community_graph");
        var nodeData = updateInformation(node);
     }

     function doubleClickCommunityListener(node) {
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

     function processData() {
        if(graphStatus['community-group'] == 0) {
            graphStatus['community-group'] = 1;
            $('#community-group').removeClass('btn-default').addClass('btn-warning');
            $('#community-group i').removeClass('fa-times').addClass('fa-refresh');
            ajaxSetup();
            $.ajax({
                type: "POST",
                url: "http://localhost/seniorproject/public/processData",
                data : {},
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
        graphData = fetchData();
        plotFullGraph();
        addZoomListener();
        //addSearchBoxListener();
        addBackButtonListener();
        //addHilightListener();
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

        document.getElementById('community-group').addEventListener('click', processData);
    }();

}();

