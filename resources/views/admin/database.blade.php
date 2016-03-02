@extends('layout.default')

@section('title', 'AIS - CU : Admin Page')

@section('stylesheet')

{!! Html::style('js/bootstrap-datepicker/css/datepicker.css') !!}
{!! Html::style('js/select2/select2.css') !!}
{!! Html::style('js/iCheck/skins/flat/_all.css') !!}
{!! Html::style('css/adminpanel.css') !!}


@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}" />

<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
            Import new Database
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-up"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                </span>
            </header>
            <div class="panel-body">
                <p>
                    In case of you need to add new database for analysis, you may add your preferred database in this section. 
                    Anyways, uploading a huge file may take very long uploading time.
                </p>
                <em>
                    Note that closing or leaving this page while uploading is in progress will cause failure on uploading process.
                </em>
                </br>
                </br>
                
                <div class="col-lg-12" id="preprocess-form-wrapper">
                    <header class="panel-heading m-bot15">
                        Upload new Database
                    </header>
                    <div class="col-md-6">
                        <div class="alert alert-warning fade in" id="cdr-uploader-wrapper">
                            <h4>Choose CDR to Upload</h4>
                            <p id="cdr-list">
                                Empty
                            </p>

                            </br>

                            <div>
                                <div class="col-sm-6">
                                    <div class="progress progress-striped active progress-sm" style="margin-top: 12px;">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" current="0" style="width: 0%" id="cdr-progress-bar">
                                        </div>
                                    </div>
                                </div>
                                <div class="select-button-wrapper text-right col-sm-6">
                                    <button class="btn btn-default" id="browse-cdr">
                                        <i class="fa fa-phone"></i> Select CDR
                                    </button>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="alert alert-warning fade in" id="profile-uploader-wrapper">
                            <h4>Choose Profiles to Upload</h4>
                            <p id="profile-list">
                                Empty
                            </p>

                            </br>
                            <div>
                                <div class="col-sm-6">
                                    <div class="progress progress-striped active progress-sm" style="margin-top: 12px;">
                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" current="0" style="width: 0%" id="profile-progress-bar">
                                        </div>
                                    </div>
                                </div>
                                <div class="select-button-wrapper text-right col-sm-6">
                                    <button class="btn btn-default" id="browse-profile">
                                        <i class="fa fa-users"></i> Select Profiles
                                    </button>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>


                    <form action="#" class="form-horizontal bucket-form" id="preprocess-form" onsubmit="return false;">
                        <div class="form-group" style="border: none;">
                            <label class="col-sm-4 control-label" for="inputSuccess">Database Name</label>
                            <div class="col-lg-6">
                                <input type="text" class="form-control" id="database-name">
                            </div>
                        </div>
                        <div class="text-center">
                            <p>
                                <em>
                                    <strong> Warning! </strong> Do not leave this page until both boxes above <u>turn into Green</u>.
                                </em>
                            </p>
                            <button class="btn btn-default" id="start-upload"><i class="fa fa-cloud-upload"></i> Upload</button>
                        </div>  
                    </form>
                </div>

            </div>
        </section>
    </div>
</div>

@section('bottom-script')
{!! Html::script('js/jquery.js'); !!}
{!! Html::script('js/jquery.maskedinput.js'); !!}
{!! Html::script('js/data-tables/jquery.dataTables.js'); !!}
{!! Html::script('js/data-tables/DT_bootstrap.js'); !!}
{!! Html::script('js/uploader/plupload.full.min.js'); !!}
{!! Html::script('js/validator.js'); !!}
{!! Html::script('js/date_helpers.js'); !!}
{!! Html::script('js/uploader_factory.js'); !!}
{!! Html::script('js/database_main.js'); !!}

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