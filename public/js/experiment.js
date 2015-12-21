/**
 *  @file   experiment.js
 *
 *  @brief  Graph Script
 *
 *  This file contains a script for generating graph using 
 *  SigmaJS library (http://sigmajs.org) and method for 
 *  additional functions on the graph.
 *
 *  @author Thanaphoom Pungchaichan (pperfectionist)
 *  @bug    No known bug
 *
 */

!function(){
    'use strict';

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
                container: document.getElementById('container')
            }]

            /* 
             *  TODO : May add some other setting here... 
             *  Visit https://github.com/jacomyal/sigma.js/wiki/Settings for more setting available
             *
            */
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
        // ajaxSetup();
        // $.ajax({
        //     type: "GET",
        //     url: "",
        //     data : {},
        //     success: function(e){
        //         console.log(e);
        //         e.forEach(function(pd){
                
        //         })
        //     },
        //     error: function(rs, e){
        //         console.log(rs.responseText);
        //         alert('Problem occurs during fetch data.');
        //     },
        //     async:false,
        // })
        // return preparedData;

        return {
            nodes: [
                {
                    id: 1,
                    label: 'node#1',
                    x: 0,
                    y: 0,
                    size: 1,
                    color: '#000'
                },
                {
                    id: 2,
                    label: 'node#2',
                    x: 1,
                    y: 1,
                    size: 1,
                    color: '#f00'
                }
            ],
            edges: [
                {
                    id: 1,
                    source: 1,
                    target: 2,
                    color: '#a5adb0'
                }
            ]
        }
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
        let graphData = fetchData();
        
        // Add all returned nodes to sigma object
        graphData.nodes.forEach(function(node) {
            addNode(s, node);
        });

        // Add all return edges to sigma object
        graphData.edges.forEach(function(edge) {
            addEdge(s,edge);
        });

        // Add Click Listener to all Nodes
        s.bind('clickNode', function(node){
            // TODO : Handling click event
            console.log(node.data.node.id);
        });

        // Display Graph using sigma object
        s.refresh();
    }

    /**
     *  @brief Main function of this file
     *
     *  @param undefined
     *  @return void
     */
    !function(undefined){
        let s = initSigma();
        plotGraph(s);
    }();

}();

