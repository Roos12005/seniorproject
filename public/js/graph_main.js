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

    let graphData = [];

    /**
     *  @brief  Instantiate Sigma object.
     *
     *  Initializing Sigma object and setup the object configurations
     *
     *  @return instantiated Sigma object
     */    
    function initSigma() {
        let s = new sigma({
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
            // autoResize: false
            // zoomingRatio : 1
        });

        return s;
    }

    /**
     *  @brief  Add node to Sigma object.
     *
     *  Adding the input node to the Sigma object with the given node setting,
     *  including id, label, x, y, size and color.
     *
     *  @param  s  Sigma object
     *  @param  n  Input node
     *  @return void
     */
    function addNode(s,n) {
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
            color: n.color
        })
    }

    /**
     *  @brief  Add edge to Sigma object.
     *
     *  Adding the input edge to the Sigma object with the given edge setting,
     *  including id, source, target, color.
     *
     *  @param  s  Sigma object
     *  @param  e  Input edge
     *  @return void
     */
    function addEdge(s, e) {
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
            color: e.color
        })
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
            beforeSend: function(xhr, settings) {
                function getCookie(name) {
                    var cookieValue = null;
                    if (document.cookie && document.cookie != '') {
                        var cookies = document.cookie.split(';');
                        for (var i = 0; i < cookies.length; i++) {
                            var cookie = jQuery.trim(cookies[i]);
                            if (cookie.substring(0, name.length + 1) == (name + '=')) {
                                cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                                break;
                            }
                        }
                    }
                    return cookieValue;
                }
                if (!(/^http:.*/.test(settings.url) || /^https:.*/.test(settings.url))) {
                    xhr.setRequestHeader("X-CSRFToken", getCookie('csrftoken'));
                }
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
            url: "data2.json",
            data : {},
            success: function(e){
                preparedData = JSON.parse(e);
            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            },
            async:false,
        })
        return preparedData;

        // return {
        //     nodes: [
        //         {
        //             id: 1,
        //             label: 'node#1',
        //             x: 0,
        //             y: 0,
        //             size: 1,
        //             color: '#000'
        //         },
        //         {
        //             id: 2,
        //             label: 'node#2',
        //             x: 1,
        //             y: 1,
        //             size: 1,
        //             color: '#f00'
        //         }
        //     ],
        //     edges: [
        //         {
        //             id: 1,
        //             source: 1,
        //             target: 2,
        //             color: '#a5adb0'
        //         }
        //     ]
        // }
    }

    /**  
     *  @brief  Plot Graph
     *
     *  First, fetch all graph data from server-side and
     *  then add all nodes and edges to the sigma object.
     *  All listener for nodes and edges are also setup here.
     *  Finally, ask sigma object to display graph.
     *
     *  @param  s   Sigma object
     *  @return void
     */
    function plotGraph(s){
        // Fetch data
        graphData = fetchData();
        console.log(graphData);
        // Add all returned nodes to sigma object
        graphData.nodes.forEach(function(node) {
            addNode(s, node);
        });

        // Add all return edges to sigma object
        graphData.edges.forEach(function(edge) {
            addEdge(s,edge);
        });

        // Add Click Listener to all Nodes
        s.bind('clickNode', clickNodeListener);

        // Display Graph using sigma object
        s.refresh();
        console.log(s.camera);
    }

    /**  
     *  @brief  Scripting Zoom Button
     *
     *  Add listeners to all three zoom buttons,
     *  zoom-in, zoom-out, refresh-zoom, by recalculating
     *  camera position of sigma object
     *
     *  @param  s   Sigma object
     *  @return void
     */
     function addZoomListener(s) {
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
     *  @param  s   Sigma object
     *  @return void
     */
     function addSearchBoxListener(s) {
        document.getElementById("searchbox").addEventListener("keypress", function(key){
            // Detect only "Enter" key - keyCode = 13
            if (key.keyCode === 13) {
                //  Move camera to entered node
                let input = document.getElementById("searchbox").value;
                let node = s.graph.nodes(input);
                
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
        updateInformation(node);

        // Show back button on the top right of the div
        document.getElementsByClassName('back-section')[0].style.display = 'block';

        // TODO : Display only selected community
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
        let nodeData = undefined;
        let nodeID = node.id == undefined ? node.data.node.id : node.id; 
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
        document.getElementById('cc').innerHTML = '### (' + parseFloat(nodeData.attributes['Closeness Centrality']).toFixed(4) + ')';
        document.getElementById('bc').innerHTML = '### (' + parseFloat(nodeData.attributes['Betweenness Centrality']).toFixed(4) + ')';
     }

     /**  
     *  @brief  Listener on clicking back button
     *
     *  Hide the back button and change the displayed graph
     *  to the full one
     *
     *  @param  s      sigma object   
     *  @return void
     */
     function addBackButtonListener(s) {
        document.getElementById('back').addEventListener('click', function() {
            document.getElementsByClassName('back-section')[0].style.display = 'none';
            // TODO : Change displayed graph back to the full one
        });
     }


    /**
     *  @brief Main function of this file
     *
     *  @param undefined
     *  @return void
     */
    !function(undefined){
        let s = initSigma();
        addZoomListener(s);
        plotGraph(s);
        addSearchBoxListener(s);
        addBackButtonListener(s);
    }();

}();

