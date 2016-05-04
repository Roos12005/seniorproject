/**
 *  @file   graph_variable.js
 *
 *  @brief  Graph Variable Script
 *
 *  This file contains all global variable for generating graph using 
 *  SigmaJS library (http://sigmajs.org).
 *
 */

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