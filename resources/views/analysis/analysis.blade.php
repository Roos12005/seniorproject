@extends('layout.default')

@section('title', 'AIS - CU : Main Page')

@section('stylesheet')

    {!! Html::style('js/bootstrap-datepicker/css/datepicker.css') !!}
    {!! Html::style('js/select2/select2.css') !!}
    {!! Html::style('js/iCheck/skins/flat/_all.css') !!}
    

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}" />
<!-- Header Start -->
<div class="row">
    <div class="col-md-12">
        
        <!-- Graph Filter section start -->
        <section class="panel">
            <header class="panel-heading"> Graph Controller
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                </span>
            </header>
            <div class="panel-body">
                <!-- <form action="#" class="form-horizontal ">
                    <div class="form-group">
                        <div class="col-md-6">
                            <label class="control-label col-md-3">Date Range</label>

                            <div class="form-group">
                                <div class="col-lg-6">
                                    <select id="e1" class="populate " style="width: 250px">
                                        <optgroup label="September 2015">
                                            <option>Sep 16 - Sep 30</option>
                                        </optgroup>
                                        <optgroup label="October 2015">
                                            <option>Oct 1 - Oct 15</option>
                                            <option>Oct 16 - Oct 31</option>
                                        </optgroup>
                                        <optgroup label="November 2015">
                                            <option>Nov 1 - Nov 15</option>
                                            <option>Nov 16 - Nov 30</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 icheck " style="border-right: 1px solid #f1f2f7; width: 21%;">
                            <div class="square">
                                <div class="checkbox ">
                                    <input type="checkbox" id="weekdays" value="weekdays_on" checked>
                                    <label>Weekdays </label>
                                </div>
                            </div>
                            <div class="square-red">
                                <div class="checkbox ">
                                    <input type="checkbox"  class="day-checkbox"  value="weekends_on">
                                    <label>Weekends </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 icheck ">
                            <div class="square-green">
                                <div class="checkbox ">
                                    <input type="checkbox" id="dayTime" value="dayTime_on" checked>
                                    <label>Day time </label>
                                </div>
                            </div>
                            <div class="square-blue">
                                <div class="checkbox ">
                                    <input type="checkbox" id="nightTime" value="nightTime_on" checked>
                                    <label>Night time </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" style="margin-top: 12px;">
                            <div class="text-center">
                                <a href="#" class="btn btn-success" style="margin-right: 10px">
                                    Grouping Communities
                                </a>
                                <a href="#" class="btn btn-warning" style="margin-right: 10px">
                                    AIS Only
                                </a>
                                <a href="#" class="btn btn-danger" style="margin-right: 10px">
                                    CC Interaction
                                </a>
                                <a class="btn btn-default" style="margin-right: 10px">
                                    Self-Coloring
                                </a>
                                <a class="btn btn-primary" style="margin-right: 10px" onclick="return fullGraphClick()">
                                    Full Graph
                                </a>
                            </div>
                        </div>
                    </div>
                </form> -->

                <div class="col-md-4">
                    <div class="col-sm-12" style="padding-bottom: 5px;">
                        <span> Graph Filters </span>
                        <div class="clearfix separate-line"></div>
                    </div>
                    <div class="col-sm-12">  
                        <a href="#filterModal" data-toggle="modal" class="btn btn-default two-col-button" id="startDate-button">
                            Date
                        </a>
                        <a href="#filterModal" data-toggle="modal" class="btn btn-default two-col-button" id="callDay-button">
                            Day
                        </a>
                        <a href="#filterModal" data-toggle="modal" class="btn btn-default two-col-button" id="startTime-button">
                            Call Period
                        </a>
                        <a href="#filterModal" data-toggle="modal" class="btn btn-default two-col-button" id="duration-button">
                            Call Duration
                        </a>
                        <a href="#filterModal" data-toggle="modal" class="btn btn-default two-col-button" id="noOfCall-button">
                            No. of Call
                        </a>
                        <a href="#filterModal" data-toggle="modal" class="btn btn-default two-col-button" id="rnCode-button">
                            Carrier
                        </a>
                    </div>  
                </div>
                <div class="col-md-5">
                    <div class="col-sm-12" style="padding-bottom: 5px;">
                    <span> Graph Hi-light </span>
                        <div class="clearfix separate-line"></div>
                    </div>
                    <div class="col-sm-12">  
                        <a class="btn btn-default three-col-button hilight h-on" id="h-default">
                            Default
                        </a>
                        <a class="btn btn-default three-col-button hilight" id="h-centrality">
                            Centrality
                        </a>
                        <a class="btn btn-default three-col-button hilight" id="h-community">
                            Community
                        </a>
                        <a class="btn btn-default three-col-button hilight" id="h-carrier">
                            Carrier
                        </a>
                        <a class="btn btn-default three-col-button hilight" id="h-daynight">
                            Day / Night
                        </a>
                        <a class="btn btn-default three-col-button hilight" id="h-promotion">
                            Promotion
                        </a>
                        <a class="btn btn-default three-col-button hilight" id="h-degree">
                            Degree
                        </a>
                        <a class="btn btn-default three-col-button hilight" id="h-ais">
                            AIS Only
                        </a>
                    </div>  
                </div>
                <div class="col-md-3">
                    <div class="col-sm-12" style="padding-bottom: 5px;">
                    <span> Display Mode </span>
                        <div class="clearfix separate-line"></div>
                    </div>
                    <div class="col-sm-12 display-mode">  
                        <a class="btn btn-default single-col-button" id="full-graph">
                            Full Graph
                            <i class="fa fa-times status"></i>
                        </a>
                        
                        <a class="btn btn-default single-col-button">
                            Community Group
                            <i class="fa fa-times status"></i>
                        </a>
                        
                        <a class="btn btn-danger single-col-button">
                            Display Mode 3
                            <i class="fa fa-times status"></i>
                        </a>
                        
                    </div>  
                </div>
            </div>
        </section>
        <!-- Graph Filter section end -->

        <!-- Three Header Statistic Widget start -->
        <div class="col-md-12">

            <!-- First widget -->
            <div class="col-md-4">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon orange"><i class="fa fa-phone"></i></span>
                    <div class="mini-stat-info">
                        <span id="unique_numbers" name="unique_numbers">0</span>
                        Unique Numbers
                    </div>
                </div>
            </div>

            <!-- Second widget -->
            <div class="col-md-4">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon tar"><i class="fa fa-users"></i></span>
                    <div class="mini-stat-info">
                        <span id ="communities" name="communities">0</span>
                        Communities
                    </div>
                </div>
            </div>

            <!-- Third widget -->
            <div class="col-md-4">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon pink"><i class="fa fa-calendar"></i></span>
                    <div class="mini-stat-info">
                        <span id="date"> - </span>
                        <div id="filter">Date</div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Three header Statistic Widget end -->

    </div>
</div>
<!-- Header End -->

<!-- Main Graph Section Start -->
<div class="row">
    <div class="col-md-12">
        <!-- Graph Display Panel Start -->
        <div class="col-md-9">
            <section class="panel">
                <!-- Graph Display Header Start -->
                <header class="panel-heading"> Customer Interaction Graph 
                    <span class="tools pull-right">
                        <a href="javascript:;" class="fa fa-chevron-down"></a>
                        <a href="javascript:;" class="fa fa-cog"></a>
                        <a href="javascript:;" class="fa fa-times"></a>
                    </span>
                </header>
                <!-- Graph Display Header End -->

                <!-- Graph Display Body Start -->
                <div class="panel-body">
                    <div id="graph-area" class="main-chart">
                        <!-- This div is referenced by JS file for displaying graph -->
                        <div id="container">
                            <div class="back-section" hidden>
                                <button class="btn btn-default" id="back">
                                    <i class="fa fa-times"></i>
                                </button>
                            </div>
                            <div class="zoom-section">
                                <button class="btn btn-success" id="zoomin">
                                    <i class="fa fa-plus"></i>
                                </button>
                                <button class="btn btn-danger" id="zoomout">
                                    <i class="fa fa-minus"></i>
                                </button>
                                <button class="btn btn-primary" id="nozoom">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Graph Display Body End -->
            </section>
        </div>
        <!-- Graph Display Panel End -->

        <!-- Graph Information Panel Start -->
        <div class="col-md-3">
            <section class="panel">
                <!-- Graph Information Header Start -->
                <header class="panel-heading"> 
                    Information 
                </header>
                <!-- Graph Information Header End -->

                <!-- Graph Information Body Start -->
                <div class="panel-body desc-panel">
                    <div class="col-md-12">
                        <!-- Search Panel Start -->
                        <div class="input-group input-group-sm m-bot15">
                            <span class="input-group-addon"><i class="fa fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search" id="searchbox">
                        </div>
                        <!-- Search Panel End -->

                        <!-- Customer Information Section Start -->
                        <!-- Customer Information Header Start -->
                        <div class="tiny-stat clearfix">
                            <span class="tiny-stat-icon green"><i class="fa fa-user"></i></span>
                            <div class="tiny-stat-info">
                                <span>Information</span>
                                Customer Details
                            </div>
                        </div>
                        <!-- Customer Information Header End -->

                        <!-- Customer Information Body Start -->
                        <div class=" col-sm-12 no-padding tiny-stat-wrapper">
                            <div class="col-sm-12">
                                <label class=" col-sm-5 control-label">Name</label>
                                <div class=" col-sm-7">
                                    <p class="form-control-static" id="cname">-</p>
                                </div>
                            </div>
                            <div class="clearfix"></div>

                            <div class="col-sm-12">
                                <label class=" col-sm-5 control-label">Number</label>
                                <div class=" col-sm-7">
                                    <p class="form-control-static" id="cnumber">-</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>

                            <div class="col-sm-12">
                                <label class=" col-sm-5 control-label">Carrier</label>
                                <div class=" col-sm-7">
                                    <p class="form-control-static" id="ccarrier">-</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>

                            <div class="col-sm-12">
                                <label class=" col-sm-5 control-label">Age</label>
                                <div class=" col-sm-7">
                                    <p class="form-control-static" id="cage">-</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>

                            <div class="col-sm-12">
                                <label class=" col-sm-5 control-label">Gender</label>
                                <div class=" col-sm-7">
                                    <p class="form-control-static" id="cgender">-</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>

                            <div class="col-sm-12">
                                <label class=" col-sm-5 control-label">Promotion</label>
                                <div class=" col-sm-7">
                                    <p class="form-control-static" id="cpromotion">-</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>

                            <div class="col-sm-12" style="margin-top:10px;">
                                <a href="#" class="btn btn-default more-button">
                                    View full profile
                                </a>
                            </div>
                        </div>
                        <!-- Customer Information Body End -->
                        
                        <!-- Statistic Section Start -->
                        <!-- Statistic Header Start -->
                        <div class="tiny-stat clearfix">
                            <span class="tiny-stat-icon pink"><i class="fa fa-signal"></i></span>
                            <div class="tiny-stat-info">
                                <span>Statistics</span>
                                Lorem Ipsum
                            </div>
                        </div>
                        <!-- Statistic Header End -->

                        <!-- Statistic Body Start -->
                        <div class=" col-sm-12 no-padding tiny-stat-wrapper">
                            <div class="col-sm-12">
                                <label class=" col-sm-8 control-label">Community Rank</label>
                                <div class=" col-sm-3">
                                    <p class="form-control-static" id="comrank">-</p>
                                </div>
                            </div>
                            <div class="clearfix"></div>

                            <div class="col-sm-12">
                                <label class=" col-sm-8 control-label">Community Size</label>
                                <div class=" col-sm-3">
                                    <p class="form-control-static" id="comsize">-</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>

                            <div class="col-sm-12">
                                <label class=" col-sm-8 control-label">BC Rank</label>
                                <div class=" col-sm-4">
                                    <p class="form-control-static" id="bc">-</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>

                            <div class="col-sm-12">
                                <label class=" col-sm-8 control-label">CC Rank</label>
                                <div class=" col-sm-4">
                                    <p class="form-control-static" id="cc">-</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>

                            <div class="col-sm-12">
                                <a href="#" class="btn btn-default more-button">
                                    View this Community
                                </a>
                            </div>
                        </div>
                        <!-- Statistic Body End -->

                        <!-- Community Header Start -->
                        <div class="tiny-stat clearfix">
                            <span class="tiny-stat-icon orange"><i class="fa fa-users"></i></span>
                            <div class="tiny-stat-info">
                                <span>Community</span>
                                Community Details
                            </div>
                        </div>
                        <!-- Community Header End -->

                        <!-- Community Body Start -->
                        <div class=" col-sm-12  no-padding tiny-stat-wrapper">
                            <div class="col-sm-12">
                                <label class=" col-sm-8 control-label">Total Number</label>
                                <div class=" col-sm-3">
                                    <p class="form-control-static">27,394</p>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <div class="col-sm-12">
                                <label class=" col-sm-5 control-label">Top 5 Numbers</label>
                                <div class=" col-sm-7">
                                    <div class="btn-group">
                                        <button data-toggle="dropdown" class="btn btn-default dropdown-toggle btn-xs number-dropdown" type="button">Sort by Degree <span class="caret"></span></button>
                                        <ul role="menu" class="dropdown-menu" style="font-size: 12px; width:100px !important;">
                                            <li><a href="#">Sort by ABC</a></li>
                                            <li><a href="#">Sort by XYZ</a></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class=" col-sm-12">
                                    <ul>
                                        <li>081-xxxxxxx</li>
                                        <li>081-xxxxxxx</li>
                                        <li>081-xxxxxxx</li>
                                        <li>081-xxxxxxx</li>
                                        <li>081-xxxxxxx</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>
                            <div class="col-sm-12">
                                <a href="#" class="btn btn-default more-button">
                                    View Community Details
                                </a>
                            </div>
                        </div>
                        <!-- Community Body End -->
                    </div>
                </div>
                <!-- Graph Information Body End -->
            </section>
        </div>
        <!-- Graph Information Panel End -->
    </div>
</div>
<!-- Main Graph Section End -->

<!-- Other Statistic Section Start -->
<div class="row">
    <!-- First Widget Start -->
    <div class="col-md-4">
        <section class="panel">
            <div class="panel-body">
                <div class="top-stats-panel">
                    <div class="gauge-canvas">
                        <h4 class="widget-h">Statistics 1</h4>
                        <canvas width=160 height=100 id="gauge"></canvas>
                    <!-- </div>
                    <ul class="gauge-meta clearfix">
                        <li id="gauge-textfield" class="pull-left gauge-value"></li>
                        <li class="pull-right gauge-title">Max</li>
                    </ul> -->
                </div>
            </div>
        </section>
    </div>
    <!-- First Widget End -->

    <!-- Second Widget Start -->
    <div class="col-md-4">
        <section class="panel">
            <div class="panel-body">
                <div class="top-stats-panel">
                    <div class="daily-visit">
                        <h4 class="widget-h">Statistics 2</h4>
                        <!-- <div id="daily-visit-chart" style="width:100%; height: 100px; display: block">

                        </div>
                        <ul class="chart-meta clearfix">
                            <li class="pull-left visit-chart-value">3233</li>
                            <li class="pull-right visit-chart-title"><i class="fa fa-arrow-up"></i> 15%</li>
                        </ul> -->
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!-- Second Widget End -->

    <!-- Third Widget Start -->
    <div class="col-md-4">
        <section class="panel">
            <div class="panel-body">
                <div class="top-stats-panel">
                    <h4 class="widget-h">Statistics 3</h4>
                    <!-- <div class="bar-stats">
                        <ul class="progress-stat-bar clearfix">
                            <li data-percent="50%"><span class="progress-stat-percent pink"></span></li>
                            <li data-percent="90%"><span class="progress-stat-percent"></span></li>
                            <li data-percent="70%"><span class="progress-stat-percent yellow-b"></span></li>
                        </ul>
                        <ul class="bar-legend">
                            <li><span class="bar-legend-pointer pink"></span> New York</li>
                            <li><span class="bar-legend-pointer green"></span> Los Angels</li>
                            <li><span class="bar-legend-pointer yellow-b"></span> Dallas</li>
                        </ul>
                        <div class="daily-sales-info">
                            <span class="sales-count">1200 </span> <span class="sales-label">Products Sold</span>
                        </div>
                    </div> -->
                </div>
            </div>
        </section>
    </div>
    <!-- Third Widget End -->
</div>


<div aria-hidden="true" aria-labelledby="filterModalLabel" role="dialog" tabindex="-1" id="filterModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">Graph Filters</h4>
            </div>
            <div class="modal-body">
            <ul>
                <form action="#" class="form-horizontal ">
                    <div class="form-group">
                        <div class="col-md-6">
                            <label class="control-label col-md-6" >Date Picker</label>
                            <div class="form-group">
                                <div class="col-md-6">
                                    <select id="e1" class="populate " style="width: 250px">
                                        <optgroup label="September 2015">
                                            <option value="20150901">Sep 01 - Sep 15</option>
                                            <option value="20150916">Sep 16 - Sep 30</option>
                                        </optgroup>
                                        <optgroup label="October 2015">
                                            <option value="20151001">Oct 1 - Oct 15</option>
                                            <option value="20151016">Oct 16 - Oct 31</option>
                                        </optgroup>
                                        <optgroup label="November 2015">
                                            <option value="20151101">Nov 1 - Nov 15</option>
                                            <option value="20151116"> Nov 16 - Nov 30</option>
                                        </optgroup>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" >Day Selector</label>
                            
                            <div class="col-lg-12 icheck">
                                <div class="flat-red col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox" class="day-checkbox" value="Sunday">
                                        <label>Sunday </label>
                                    </div>
                                </div>
                                <div class="flat-orange col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox" class="day-checkbox"  value="Thursday">
                                        <label>Thursday </label>
                                    </div>
                                </div>
                                <div class="flat-yellow col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox"  class="day-checkbox"  value="Monday">
                                        <label>Monday </label>
                                    </div>
                                </div>
                                <div class="flat-blue col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox"  class="day-checkbox"  value="Friday">
                                        <label>Friday </label>
                                    </div>
                                </div>
                                <div class="flat-pink col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox"  class="day-checkbox"  value="Tuesday">
                                        <label>Tuesday </label>
                                    </div>
                                </div>
                                <div class="flat-purple col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox"  class="day-checkbox"  value="Saturday">
                                        <label>Saturday </label>
                                    </div>
                                </div>
                                <div class="flat-green col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox"  class="day-checkbox"  value="Wednesday">
                                        <label>Wednesday </label>
                                    </div>
                                </div>
                            </div>
                        </div> 

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <label class="control-label col-md-3" >Call Period</label>
                            <div class="col-md-6">
                                <div class="input-group input-large" data-time="05:00" data-time-format="hh:mm">
                                    <input type="text" class="form-control time-filter" id="callPeriodFrom" name="from" value="00.00">
                                    <span class="input-group-addon">To</span>
                                    <input type="text" class="form-control time-filter" id="callPeriodTo" name="to" value="24.00">
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <label class="control-label col-md-3" >Call Duration</label>
                            <div class="col-md-6">
                                <div class="input-group input-large" data-time="05:00" data-time-format="hh:mm">
                                    <input type="text" class="form-control" id="callDurationFrom" name="from" value="0">
                                    <span class="input-group-addon">To</span>
                                    <input type="text" class="form-control" id="callDurationTo" name="to" value="99999">
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <label class="control-label col-md-3" >Number of Call</label>
                            <div class="col-md-6">
                                <div class="input-group input-large" data-time="05:00" data-time-format="hh:mm">
                                    <input type="text" class="form-control" id="noOfCallFrom" name="from" disabled>
                                    <span class="input-group-addon">To</span>
                                    <input type="text" class="form-control" id="noOfCallTo" name="to" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <div class="col-md-12">
                            <label class="col-md-4 control-label" >Day Selector</label>
                            
                            <div class="col-lg-12 icheck">
                                <div class="flat-green col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox" class="carrier-checkbox" value="AIS">
                                        <label>AIS </label>
                                    </div>
                                </div>
                                <div class="flat-purple col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox" class="carrier-checkbox" value="JAS">
                                        <label>JAS </label>
                                    </div>
                                </div>
                                <div class="flat-blue col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox" class="carrier-checkbox" value="DTAC">
                                        <label>DTAC </label>
                                    </div>
                                </div>
                                <div class="flat-grey col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox" class="carrier-checkbox" value="OTHERS">
                                        <label>Other </label>
                                    </div>
                                </div>
                                <div class="flat-red col-sm-4">
                                    <div class="checkbox ">
                                        <input type="checkbox" class="carrier-checkbox" value="TRUE">
                                        <label>TRUE </label>
                                    </div>
                                </div>
                                
                                
                            </div>
                        </div> 
                    </div>
                    <div style="float: right;">
                        <button class="btn btn-success" id="filter-save" onclick="return false;">Comfirm</button>
                        <button class="btn btn-danger" id="filter-cancel">Cancel</button>
                    </div>
                    <div class="clearfix"></div>

                </form> 
                <!-- <li> Which day should we display for you ? </li>
                <li> Please specify Calling Period you would like to explore </li>
                <li> How long does customer talking duration you would like to explore ? </li>
                <li> Number of Calls </li>
                <li> Carrier Picker </li> -->
            </div>
        </div>
    </div>
</div>



<!-- Other Statistic Section End -->
@section('bottom-script')
{!! Html::script('js/jquery.js'); !!}
{!! Html::script('js/jquery.maskedinput.js'); !!}
{!! Html::script('js/sigmajs/sigma.min.js'); !!}
{!! Html::script('js/sigmajs/plugins/sigma.layout.forceAtlas2.min.js'); !!}
{!! Html::script('js/graph_main.js'); !!}
{!! Html::script('js/analysis.js'); !!}


<!-- Date Range (Date Picker) -->
{!! Html::script('bs3/js/bootstrap.min.js'); !!}
{!! Html::script('js/bootstrap-datepicker/js/bootstrap-datepicker.js'); !!}
{!! Html::script('js/bootstrap-switch.js'); !!}
{!! Html::script('js/main.js'); !!}

<!-- Date Range (Dropdown) -->
{!! Html::script('js/select2/select2.js'); !!}
{!! Html::script('js/select-init.js'); !!}

<!--Check Box -->
{!! Html::script('js/iCheck/jquery.icheck.js'); !!}
{!! Html::script('js/icheck-init.js'); !!}

<!-- Side Bar -->
{!! Html::script('js/jquery.nicescroll.js'); !!}
{!! Html::script('js/scripts.js'); !!}

@endsection
@stop