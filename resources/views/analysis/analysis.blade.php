@extends('layout.default')

@section('title', 'AIS - CU : Main Page')

@section('stylesheet')

    {!! Html::style('js/bootstrap-datepicker/css/datepicker.css') !!}
    {!! Html::style('js/select2/select2.css') !!}
    {!! Html::style('js/iCheck/skins/flat/_all.css') !!}
    

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}" />
<meta name="data-id" content="{{ $data_id }}" />

<!-- Header Start -->
<div class="row">
    <div class="col-md-12">

        <!-- Data Description section start -->
        <section class="panel">
            <header class="panel-heading">
                Data Description
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                 </span>
            </header>
            <div class="panel-body">
                <div class="modal-body">
                <form action="#" class="form-horizontal ">
                    <div class="form-group">
                        <label class=" col-sm-2 control-label">Database </label>
                        <div class="col-lg-3">
                            <p class="form-control-static">{{$database}}</p>
                        </div>
                        <label class=" col-sm-2 control-label">Date </label>
                        <div class="col-lg-3">
                            <p class="form-control-static">{{$startDate}}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-2 control-label">Duration</label>
                        <div class="col-lg-3">
                            <p class="form-control-static">{{$duration}}</p>
                        </div>
                        <label class=" col-sm-2 control-label">Period</label>
                        <div class="col-lg-3">
                            <p class="form-control-static">{{$period}}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-2 control-label">Day</label>
                        <div class="col-lg-3">
                            <p class="form-control-static">{{$callDay}}</p>
                        </div>
                        <label class=" col-sm-2 control-label">Carrier</label>
                        <div class="col-lg-3">
                            <p class="form-control-static">{{$carrier}}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-2 control-label">No. of Outgoing Calls</label>
                        <div class="col-lg-3">
                            <p class="form-control-static">{{$noOfOutgoing}}</p>
                        </div>
                        <label class=" col-sm-2 control-label">No. of Incoming Calls</label>
                        <div class="col-lg-3">
                            <p class="form-control-static">{{$noOfIncoming}}</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-2 control-label">Calculation</label>
                        <div class="col-lg-3">
                            <p class="form-control-static">{{$calculation}}</p>
                        </div>
                    </div>
                </form> 
            </div>
            </div>
        </section>
        <!-- Data Description section end -->

        <!-- Graph Controller section start -->
        <section class="panel">
            <header class="panel-heading"> Graph Controller
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                </span>
            </header>
            <div class="panel-body">
                <div class="col-md-5">
                    <div class="col-sm-12" style="padding-bottom: 5px;">
                    <span> Node Highlight </span>
                        <div class="clearfix separate-line"></div>
                    </div>
                    <div class="col-sm-12">  
                        <a class="btn btn-default three-col-button hilightNode h-on" id="h-defaultNode">
                            Default
                        </a>
                        <a class="btn btn-default three-col-button hilightNode" id="h-centrality">
                            Centrality
                        </a>
                        <a class="btn btn-default three-col-button hilightNode" id="h-community">
                            Community
                        </a>
                        <a class="btn btn-default three-col-button hilightNode" id="h-carrier">
                            Carrier
                        </a>
                        <a class="btn btn-default three-col-button hilightNode" id="h-ais">
                            AIS Only
                        </a>
                        <a class="btn btn-default three-col-button hilightNode" id="h-promotion">
                            Promotion
                        </a>
                        <a class="btn btn-default three-col-button hilightNode" id="h-degree">
                            Degree 
                        </a>
                        <a class="btn btn-default three-col-button hilightNode" id="h-degreeIn">
                            Degree In
                        </a>
                        <a class="btn btn-default three-col-button hilightNode" id="h-degreeOut">
                            Degree Out
                        </a>
                    </div>  
                    <div class="col-sm-12" style="padding-bottom: 5px;">
                    <span> Edge Highlight </span>
                        <div class="clearfix separate-line"></div>
                    </div>
                    <div class="col-sm-12">  
                        <a class="btn btn-default three-col-button hilightEdge h-on" id="h-defaultEdge">
                            Default
                        </a>
                        <a class="btn btn-default three-col-button hilightEdge" id="h-daynight">
                            Day / Night
                        </a>
                        <a class="btn btn-default three-col-button hilightEdge" id="h-duration">
                            Duration
                        </a>
                    </div>  
                </div>

                <div class="col-md-4">
                    <div class="col-sm-12" style="padding-bottom: 5px;">
                    <span> Highlight Description </span>
                        <div class="clearfix separate-line"></div>
                    </div>
                    <div class="col-sm-12">  
                        <h5>Node Highlight : <span id="highlightNode">Default</span></h5>
                        <li>Node size : <span id="highlightNodeSize"></span></li>
                        <li>Node color : <span id="highlightNodeColor"></span></li>
                        <br>
                        <h5>Edge Highlight : <span id="highlightEdge">Default</span></h5>
                        <li>Edge color : <span id="highlightEdgeColor"></span></li>
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
                        
                        <a class="btn btn-default single-col-button" id="community-group">
                            Community Group
                            <i class="fa fa-times status"></i>
                        </a>
                        
                        <a href="#exportCSVModal" data-toggle ="modal" class="btn btn-default single-col-button" id="export-data">
                            Export Data
                            <i class="fa fa-times status"></i>
                        </a>
                        
                    </div>  
                </div>
            </div>
        </section>
        <!-- Graph Controller section end -->

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
                    <span class="mini-stat-icon pink"><i class="fa fa-mail-forward"></i></span>
                    <div class="mini-stat-info">
                        <span id="transactions">0</span>
                        Transactions
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
                                <label class=" col-sm-5 control-label">Number</label>
                                <div class=" col-sm-7">
                                    <p class="form-control-static" id="cnumber">-</p>
                                </div>
                            </div>
                            <div class="clearfix"></div>

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

                            <div class="col-sm-12">
                                <label class=" col-sm-5 control-label">Call</label>
                                <div class=" col-sm-7">
                                    <p class="form-control-static" id="cnoOfCall">-</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>

                            <div class="col-sm-12">
                                <label class=" col-sm-5 control-label">Receive</label>
                                <div class=" col-sm-7">
                                    <p class="form-control-static" id="cnoOfReceive">-</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>

                            <div class="col-sm-12" style="margin-top:10px;">
                                <!-- <a href="#" class="btn btn-default more-button">
                                    View full profile
                                </a> -->
                            </div>
                        </div>
                        <!-- Customer Information Body End -->
                        
                        <!-- Statistic Section Start -->
                        <!-- Statistic Header Start -->
                        <div class="tiny-stat clearfix">
                            <span class="tiny-stat-icon pink"><i class="fa fa-signal"></i></span>
                            <div class="tiny-stat-info">
                                <span>Statistics</span>
                                Statistics Details
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
                                <!-- <a href="#" class="btn btn-default more-button">
                                    View this Community
                                </a> -->

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
                                <label class=" col-sm-8 control-label">Community ID</label>
                                <div class=" col-sm-3">
                                    <p class="form-control-static" id="comid">0</p>
                                </div>
                            </div>
                            <div class="clearfix "></div>
                            <div class="col-sm-12">
                                <label class=" col-sm-8 control-label">Total Number</label>
                                <div class=" col-sm-3">
                                    <p class="form-control-static" id="comnum">0</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>
                            <div class="col-sm-12">
                                <label class=" col-sm-8 control-label">Profile</label>
                                <div class=" col-sm-3">
                                    <p class="form-control-static" id="profile">-</p>
                                </div>
                            </div>
                            <div class="clearfix separate-line"></div>
                            <!-- <div class="col-sm-12">
                                <a href="#" class="btn btn-default more-button">
                                    View Community Details
                                </a>
                            </div> -->
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
    <div class="col-sm-6">
        <section class="panel">
            <header class="panel-heading">
                COMMUNITIES CHART
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                </span>
            </header>
            <div class="panel-body">
                <div id="graph-donut"></div>
            </div>
        </section>
    </div>
    <!-- First Widget End -->
    <div class="col-sm-6">
        <section class="panel">
            <header class="panel-heading">
                CARRIER CHART
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                </span>
            </header>
            <div class="panel-body">
                <div id="graph-donut2"></div>
            </div>
        </section>
    </div>
    <!-- Second Widget End -->
</div>

<div aria-hidden="true" aria-labelledby="exportCSVModalLabel" role="dialog" tabindex="-1" id="exportCSVModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">Export Data</h4>
            </div>
            <div class="modal-body">
            <ul>
                <form action="#" class="form-horizontal " method="GET">
                     <div class="form-group">
                        <label class="col-lg-2 col-sm-2 control-label">Community</label>
                        <div class="col-lg-6">
                            <select multiple name="e2" id="e2" style="width:300px" class="populate">
                            </select>
                            <input type="hidden" name="senddata" id="senddata" value="">  
                        </div>
                    </div>
                    <div style="float: right;">
                        <button class="btn btn-success" id="exportCSV-export" onclick="return false;">Export</button>
                        <button class="btn btn-danger" id="exportCSV-cancel">Cancel</button>
                    </div>
                    <div class="clearfix"></div>
                </form> 
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
{!! Html::script('js/exportCSV.js'); !!}

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

<!--Morris Chart-->
{!! Html::script('js/morris-chart/morris.js'); !!}
{!! Html::script('js/morris-chart/raphael-min.js'); !!}

@endsection
@stop