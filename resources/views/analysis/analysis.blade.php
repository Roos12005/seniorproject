@extends('layout.default')

@section('title', 'AIS - CU : Main Page')


@section('content')

<!-- Header Start -->
<div class="row">
    <div class="col-md-12">
        
        <!-- Graph Filter section start -->
        <section class="panel">
            <header class="panel-heading"> Graph Filters 
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                </span>
            </header>
            <div class="panel-body">
                <form action="#" class="form-horizontal ">
                    <div class="form-group">
                        <div class="col-md-6">
                            <label class="control-label col-md-3">Date Range</label>
                            <div class="col-md-9">
                                <div class="input-group input-large" data-date="13/07/2013" data-date-format="mm/dd/yyyy">
                                    <input type="text" class="form-control dpd1" name="from">
                                    <span class="input-group-addon">To</span>
                                    <input type="text" class="form-control dpd2" name="to">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3" style="border-right: 1px solid #f1f2f7; width: 21%;">
                            <label class="checkbox-inline">
                                <input type="checkbox" id="inlineCheckbox1" value="option1" checked> Weekdays
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" id="inlineCheckbox2" value="option2"> Weekends
                            </label>
                        </div>
                        <div class="col-md-3">
                            <label class="checkbox-inline">
                                <input type="checkbox" id="inlineCheckbox1" value="option1" checked> Day time
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" id="inlineCheckbox2" value="option2" checked> Night time
                            </label>
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
                                <a href="#" class="btn btn-default" style="margin-right: 10px">
                                    Self-Coloring
                                </a>
                                <a href="#" class="btn btn-primary" style="margin-right: 10px">
                                    Full Graph
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
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
                        <span>2,564,982</span>
                        Unique Numbers
                    </div>
                </div>
            </div>

            <!-- Second widget -->
            <div class="col-md-4">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon tar"><i class="fa fa-users"></i></span>
                    <div class="mini-stat-info">
                        <span>2,450</span>
                        Communities
                    </div>
                </div>
            </div>

            <!-- Third widget -->
            <div class="col-md-4">
                <div class="mini-stat clearfix">
                    <span class="mini-stat-icon pink"><i class="fa fa-calendar"></i></span>
                    <div class="mini-stat-info">
                        <span>30 Days</span>
                        Sep 1st - Sep 30th
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
                    </div>
                    <ul class="gauge-meta clearfix">
                        <li id="gauge-textfield" class="pull-left gauge-value"></li>
                        <li class="pull-right gauge-title">Max</li>
                    </ul>
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
                        <div id="daily-visit-chart" style="width:100%; height: 100px; display: block">

                        </div>
                        <ul class="chart-meta clearfix">
                            <li class="pull-left visit-chart-value">3233</li>
                            <li class="pull-right visit-chart-title"><i class="fa fa-arrow-up"></i> 15%</li>
                        </ul>
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
                    <div class="bar-stats">
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
                    </div>
                </div>
            </div>
        </section>
    </div>
    <!-- Third Widget End -->
</div>
<!-- Other Statistic Section End -->
@section('bottom-script')
{!! Html::script('js/jquery-1.8.3.min.js'); !!}
{!! Html::script('js/sigmajs/sigma.min.js'); !!}
{!! Html::script('js/graph_main.js'); !!}
{!! Html::script('js/jquery.js"'); !!}

<!-- Date Range -->
{!! Html::script('bs3/js/bootstrap.min.js'); !!}
{!! Html::script('js/advanced-form.js'); !!}
{!! Html::script('js/bootstrap-datepicker/js/bootstrap-datepicker.js'); !!}
{!! Html::script('js/bootstrap-switch.js'); !!}

<!-- Side Bar -->
{!! Html::script('js/jquery.nicescroll.js'); !!}
{!! Html::script('js/scripts.js'); !!}

@endsection
@stop