/**
 *  @file   graph_main.js
 *
 *  @brief  Graph Script
 *
 *  This file contains a script for generating graph using 
 *  SigmaJS library (http://sigmajs.org) and method for 
 *  additional functions on the graph.
 *
 */

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
        edgeColor : 'default',
        defaultEdgeArrow: 'source',
        mouseWheelEnabled: false,
        edgeLabelThreshold: 10,
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

/**
 *  @brief  Remove node from graph
 *
 *  Remove node with specific ID from the sigma graph object
 *
 *  @param  n  Input node
 *  @return void
 */
function removeNode(n) {
    s.graph.dropNode(n.id);
}

/**
 *  @brief  Empty Graph
 *
 *  Remove all nodes and edges from the graph object
 *
 *  @return void
 */
function clearGraph() {
    s.graph.clear();
}

/**
 *  @brief  Comma Formatting for Number
 *
 *  Format the input number to comma separated format (e.g. x,xxx,xxx)
 *
 *  @param  x  Any Number
 *  @return int - Comma Separated Number
 */
function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
 *  @brief  Display Full Graph
 *
 *  First, display the loading screen and then set flags along with reset the buttons.
 *  display graph and change the color of the button.
 *
 *  Note that once the graph is already been shown, this method will not do as stated above but
 *  show alert box with warning message instead.
 *
 *  @return void
 */
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

/**
 *  @brief  Display Graph in Community View
 *
 *  First, display the loading screen and then set flags along with reset the buttons.
 *  display graph and change the color of the button.
 *
 *  Note that once the graph is already been shown, this method will not do as stated above but
 *  show alert box with warning message instead.
 *
 *  @return void
 */
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

/**
 *  @brief  Display Filtered Graph with Community Profiles
 *
 *  First, we get all filter values from the input form and format them.
 *  Then, we send an ajax request with these filters to get the data to display.
 *  After the ajax responsed, plot the returned data with partialPlot method.
 *
 *  Note that once the graph is already been shown, this method will not do as stated above but
 *  show alert box with warning message instead.
 *
 *  @return void
 */
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
        $("#averageArpuProfile :selected").each(function(){
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
                url: "http://localhost:8000/getNodeCommunityProfile/" + did,
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

/**
 *  @brief  Get data and Display Graph
 *
 *  This method will empty the graph object and call another function to get the data needed
 *  based on the compute_com flag
 *
 *  @return void
 */
function runGraph() {
    clearGraph();
    if(flag['compute_com']){
        console.log("Fetching community data")
        fetchCommunityData();
        console.log("Fetched")
    } else {
        console.log("Fetching node data")
        fetchData();
        console.log(graphData)
    }
}

/**  
 *  @brief  Fetch all data
 *
 *  Send Ajax request to server-side to fetch all graph data,
 *  including all nodes and all edges with thier properties.
 *
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
        url: "http://localhost:8000/getCDR/" + did,
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
                url: "http://localhost:8000/getCarrier/" + did,
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
            console.log(preparedData);
            graphData = preparedData;
            console.log(graphData);
            plotFullGraph();
            addZoomListener();
           // addLabelListener();
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

/**  
 *  @brief  Fetch all data
 *
 *  Send Ajax request to server-side to fetch community graph data,
 *  including all nodes and all edges with thier properties.
 *
 *  @return JSON object - contains graph data
 */
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
        url: "http://localhost:8000/getCommunityOfCommunity/" + did,
        data : {},
        success: function(e){


            console.log(e);
            preparedData = e;

            $.ajax({
                type: "GET",
                url: "http://localhost:8000/getCarrier/" + did,
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

/**  
 *  @brief  Create Carriers Pie Chart
 *
 *  Creating pie chart to display the proportion of each carrier displayed in the graph
 *  using Morris Chart
 *
 *  @return void
 */
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
        addLabelListener();
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
}

/**  
 *  @brief  Find Community ID of the specific number
 *
 *  Sending the ajax request to get the community ID of the input number
 *  and plot the community on the graph
 *
 *  @param  num   input number
 *  @return void
 */
function findCommunityID(num) {
    ajaxSetup();
    $.ajax({
        type: "GET",
        url: "http://localhost:8000/findCommunity/" + did,
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

// 

function resetButton(button) {
    graphStatus[button] = 0;

    $('#' + button).removeClass('btn-warning').removeClass('btn-success').addClass('btn-default');
    $('#' + button +' i').removeClass('fa-refresh').removeClass('fa-check').addClass('fa-times');
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

    document.getElementById('full-graph').addEventListener('click', processData);
    document.getElementById('community-group').addEventListener('click', processCommunityData);
    document.getElementById('communityProfile-filter').addEventListener('click', processCommunityProfile);
    initSlider();
    $(document).ready(function() {
        if(isAPIAvailable()) {
            $('#files').bind('change', handleFileSelect);
        }
    });
    document.getElementById('highlightByFile').addEventListener('click',colorByAttribute);
}();


