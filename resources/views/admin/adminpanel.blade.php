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
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                 </span>
            </header>
            <div class="panel-body">
                    <p>
                        Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat
                    </p>
                    
                    <button id="editable-sample_new" class="btn btn-primary">
                        Add New <i class="fa fa-plus"></i>
                    </button>
                    </br>
                    <i>
                        Data specified in this section will be automatically starting processed at midnight. More data specified will take longer time to process.
                    </i>
                    <table class="table  table-hover general-table">
                        <thead>
                        <tr>
                            <th> #</th>
                            <th>Description</th>

                            <th>Date</th>
                            <th>Day</th>
                            <th>Period</th>
                            <th>Duration</th>
                            <th>No. of Call</th>
                            <th>Carrier</th>

                            <th>Priority</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><a href="#">1</a></td>
                                <td>
                                    User may put thier own description here
                                </td>

                                <td>Week 1</td>
                                <td>All</td>
                                <td>Daytime</td>
                                <td>> 0</td>
                                <td>> 0</td>
                                <td>AIS,TRUE,DTAC</td>

                                <td>High</td>
                                <td>
                                    <span class="label label-default label-mini"><i class="fa fa-cog"></i></span>
                                    <span class="label label-danger label-mini"><i class="fa fa-times"></i></span>
                                </td>
                            </tr>
                            <tr>
                                <td><a href="#">2</a></td>
                                <td>
                                    User may put thier own description here
                                </td>

                                <td>Week 2</td>
                                <td>Weekday</td>
                                <td>Nighttime</td>
                                <td>10 - 150</td>
                                <td>60 - 100</td>
                                <td>AIS</td>

                                <td>Low</td>
                                <td>
                                    <span class="label label-default label-mini"><i class="fa fa-cog"></i></span>
                                    <span class="label label-danger label-mini"><i class="fa fa-times"></i></span>
                                </td>
                            </tr>
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
                Data Table
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-down"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                 </span>
            </header>
            <div class="panel-body">
                    <button id="editable-sample_new" class="btn btn-primary">
                        Add New <i class="fa fa-plus"></i>
                    </button>
                    <table class="table  table-hover general-table">
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
                            <tr>
                                <td><a href="#">1</a></td>
                                <td>2015/09/01 - 2015/09/15</td>
                                <td>
                                    <span class="label label-default label-mini"><i class="fa fa-info"></i></span>
                                    User may put thier own description here
                                </td>
                                <td class="text-center">1,203,791</td>
                                <td class="text-center">5.2 GB</td>
                                <td>
                                    <span class="label label-primary label-mini"><i class="fa fa-eye"></i></span>
                                    <span class="label label-success label-mini"><i class="fa fa-download"></i></span>
                                    <span class="label label-danger label-mini"><i class="fa fa-times"></i></span>
                                </td>
                                <td><span class="label label-success label-mini">Ready</span></td>
                                <td>
                                    <div class="progress progress-striped progress-xs">
                                        <div style="width: 100%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="100" role="progressbar" class="progress-bar progress-bar-success">
                                            <span class="sr-only">100% Complete (success)</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><a href="#">2</a></td>
                                <td>2015/09/01 - 2015/09/15</td>
                                <td>
                                    <span class="label label-default label-mini"><i class="fa fa-info"></i></span>
                                    User may put thier own description here
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                                <td>
                                    <span class="label label-default label-mini"><i class="fa fa-eye"></i></span>
                                    <span class="label label-default label-mini"><i class="fa fa-download"></i></span>
                                    <span class="label label-danger label-mini"><i class="fa fa-times"></i></span>
                                </td>
                                <td><span class="label label-warning label-mini">Processing</span></td>
                                <td>
                                    <div class="progress progress-striped progress-xs">
                                        <div style="width: 60%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="60" role="progressbar" class="progress-bar progress-bar-success">
                                            <span class="sr-only">100% Complete (success)</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><a href="#">3</a></td>
                                <td>2015/09/15 - 2015/09/30</td>
                                <td>
                                    <span class="label label-default label-mini"><i class="fa fa-info"></i></span>
                                    User may put thier own description here
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-center">-</td>
                                <td>
                                    <span class="label label-default label-mini"><i class="fa fa-eye"></i></span>
                                    <span class="label label-default label-mini"><i class="fa fa-download"></i></span>
                                    <span class="label label-danger label-mini"><i class="fa fa-times"></i></span>
                                </td>
                                <td><span class="label label-warning label-mini">Processing</span></td>
                                <td>
                                    <div class="progress progress-striped progress-xs">
                                        <div style="width: 20%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="20" role="progressbar" class="progress-bar progress-bar-success">
                                            <span class="sr-only">100% Complete (success)</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>   
            </div>
        </section>
    </div>
</div>

@section('bottom-script')
{!! Html::script('js/jquery.js'); !!}
{!! Html::script('js/jquery.maskedinput.js'); !!}



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