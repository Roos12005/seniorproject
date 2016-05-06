/**
 *  @file   graph_listener.js
 *
 *  @brief  Graph Listener Script
 *
 *  This file contains methods for adding listener such as label listerner,
 *  click listener or back button listener.
 */

function addLabelListener() {
    // Show Node Label Button
    console.log("call");
    document.getElementById("node_label").addEventListener("click", function(){
        console.log("click");
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