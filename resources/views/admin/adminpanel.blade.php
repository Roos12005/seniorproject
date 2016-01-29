@extends('layout.default')

@section('title', 'AIS - CU : Admin Page')

@section('stylesheet')

    {!! Html::style('js/bootstrap-datepicker/css/datepicker.css') !!}
    {!! Html::style('js/select2/select2.css') !!}
    {!! Html::style('js/iCheck/skins/flat/_all.css') !!}
    {!! Html::style('css/adminpanel.css') !!}
    

@section('content')


<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                Pre-processing Data Setting
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-up"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                 </span>
            </header>
            <div class="panel-body" style="display: none;">
                    <p>
                        Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat
                    </p>
                    
                    
                    </br>
                    <i>
                        Data specified in this section will be automatically starting processed at midnight. More data specified will take longer time to process.
                    </i>
                    <table class="table  table-hover general-table" id="preprocess-table">
                        <thead>
                        <tr>
                            <th> #</th>
                            <th>Description</th>

                            <th>Centrality</th>
                            <th>Community</th>
                            <th>Profiling</th>
                            <th>Filters</th>

                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>



                        @foreach($preprocess as $key => $p)
                            <tr>
                                <td><a href="#">{{$key + 1}}</a></td>
                                <td>
                                    {{ $p['description'] }}
                                </td>

                                <td>Yes</td>
                                <td>Yes</td>
                                <td>No</td>
                                
                                <td>
                                    <a href="#" data-toggle="modal" class="preprocess-filter" data-pid="{{$p['id']}}">
                                        Click to see filters
                                    </a>
                                    <span id="pf-{{$p['id']}}" data-date="{{$p['date']}}" data-noOfCall="{{$p['noOfCall']}}"
                                    data-duration="{{$p['duration']}}" data-period="{{$p['period']}}" data-carrier="{{$p['carrier']}}">
                                    </span>
                                </td>

                                <td>High</td>
                                <td>
                                    <span class="label label-default label-mini"><i class="fa fa-cog"></i></span>
                                    <span class="label label-danger label-mini"><i class="fa fa-times"></i></span>
                                </td>
                            </tr>
                        @endforeach
                           <!--  <tr>
                                <td><a href="#">2</a></td>
                                <td>
                                    User may put thier own description here
                                </td>

                                <td>Week 2</td>
                                <td>Weekday</td>
                                <td>Nighttime</td>
                                <td>10 - 150</td>

                                <td>Low</td>
                                <td>
                                    <span class="label label-default label-mini"><i class="fa fa-cog"></i></span>
                                    <span class="label label-danger label-mini"><i class="fa fa-times"></i></span>
                                </td>
                            </tr> -->
                        </tbody>
                    </table>     
                    <button id="editable-sample_new" class="btn btn-primary">
                        Add New <i class="fa fa-plus"></i>
                    </button> 
            </div>
        </section>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                Pre-processing Data
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                 </span>
            </header>
            <div class="panel-body">
                    
                    <table class="table  table-hover general-table" id="preprocess-progress-table">
                        <thead>
                        <tr>
                            <th> #</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Customers</th>
                            <th>Size</th>
                            <th>Actions</th>
                            <th>Status</th>
                            <th>Progress</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($table as $key => $row)
                                <tr>
                                    <td><a href="#">{{ $key + 1 }}</a></td>
                                    <td>{{ $row['date'] }}</td>
                                    <td>
                                        <a class="label label-default label-mini table-filter" href="#" data-toggle="modal" data-tid="{{$row['id']}}"><i class="fa fa-info"></i></a>
                                        {{ $row['description'] }}
                                        <span id="tf-{{$row['id']}}" data-date="{{$row['date']}}" data-noOfCall="{{$row['noOfCall']}}"
                                        data-duration="{{$row['duration']}}" data-period="{{$row['period']}}" data-carrier="{{$row['carrier']}}">
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $row['customers'] }}</td>
                                    <td class="text-center">{{ $row['size'] }}</td>
                                    <td>
                                    @if($row['progress'] < 100)
                                        <span class="label label-default label-mini"><i class="fa fa-eye"></i></span>
                                        <span class="label label-default label-mini"><i class="fa fa-download"></i></span>
                                    @else
                                        <span class="label label-primary label-mini"><i class="fa fa-eye"></i></span>
                                        <span class="label label-success label-mini"><i class="fa fa-download"></i></span>
                                    @endif
                                        <span class="label label-danger label-mini"><i class="fa fa-times"></i></span>
                                    </td>
                                    <td><span class="label label-success label-mini">{{ $row['status'] }}</span></td>
                                    <td>
                                        <div class="progress progress-striped progress-xs">
                                            <div style="width: {{ $row['progress'] }}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="100" role="progressbar" class="progress-bar progress-bar-success">
                                                <span class="sr-only">100% Complete (success)</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table> 
            </div>
        </section>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                Batch Job Data
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                 </span>
            </header>
            <div class="panel-body">
                    
                    <table class="table  table-hover general-table" id="progress-table">
                        <thead>
                        <tr>
                            <th> #</th>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Customers</th>
                            <th>Size</th>
                            <th>Actions</th>
                            <th>Status</th>
                            <th>Progress</th>
                        </tr>
                        </thead>
                        <tbody>
                            @foreach($table as $key => $row)
                                <tr>
                                    <td><a href="#">{{ $key + 1 }}</a></td>
                                    <td>{{ $row['date'] }}</td>
                                    <td>
                                        <a class="label label-default label-mini table-filter" href="#" data-toggle="modal" data-tid="{{$row['id']}}"><i class="fa fa-info"></i></a>
                                        {{ $row['description'] }}
                                        <span id="tf-{{$row['id']}}" data-date="{{$row['date']}}" data-noOfCall="{{$row['noOfCall']}}"
                                        data-duration="{{$row['duration']}}" data-period="{{$row['period']}}" data-carrier="{{$row['carrier']}}">
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $row['customers'] }}</td>
                                    <td class="text-center">{{ $row['size'] }}</td>
                                    <td>
                                    @if($row['progress'] < 100)
                                        <span class="label label-default label-mini"><i class="fa fa-eye"></i></span>
                                        <span class="label label-default label-mini"><i class="fa fa-download"></i></span>
                                    @else
                                        <span class="label label-primary label-mini"><i class="fa fa-eye"></i></span>
                                        <span class="label label-success label-mini"><i class="fa fa-download"></i></span>
                                    @endif
                                        <span class="label label-danger label-mini"><i class="fa fa-times"></i></span>
                                    </td>
                                    <td><span class="label label-success label-mini">{{ $row['status'] }}</span></td>
                                    <td>
                                        <div class="progress progress-striped progress-xs">
                                            <div style="width: {{ $row['progress'] }}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="100" role="progressbar" class="progress-bar progress-bar-success">
                                                <span class="sr-only">100% Complete (success)</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button id="editable-sample_new" class="btn btn-primary">
                        Add New <i class="fa fa-plus"></i>
                    </button>   
            </div>
        </section>
    </div>
</div>


<!-- Proprocess Information Modal-->
<div aria-hidden="true" aria-labelledby="preprocessModalLabel" role="dialog" tabindex="-1" id="preprocessModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">Filters</h4>
            </div>
            <div class="modal-body">
                <form action="#" class="form-horizontal ">
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Date </label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="pf-date">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Day</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="pf-day">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Period</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="pf-period">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Duration</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="pf-duration">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">No. of Calls</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="pf-noOfCall">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Carrier</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="pf-carrier">-</p>
                        </div>
                    </div>
                </form> 
            </div>
        </div>
    </div>
</div>

<!-- Table Information Modal-->
<div aria-hidden="true" aria-labelledby="tableModalLabel" role="dialog" tabindex="-1" id="tableModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">Filters</h4>
            </div>
            <div class="modal-body">
                <form action="#" class="form-horizontal ">
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Date </label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-date">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Day</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-day">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Period</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-period">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Duration</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-duration">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">No. of Calls</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-noOfCall">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Carrier</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-carrier">-</p>
                        </div>
                    </div>
                </form> 
            </div>
        </div>
    </div>
</div>

@section('bottom-script')
{!! Html::script('js/jquery.js'); !!}
{!! Html::script('js/jquery.maskedinput.js'); !!}
{!! Html::script('js/data-tables/jquery.dataTables.js'); !!}
{!! Html::script('js/data-tables/DT_bootstrap.js'); !!}
{!! Html::script('js/admin_panel.js'); !!}


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