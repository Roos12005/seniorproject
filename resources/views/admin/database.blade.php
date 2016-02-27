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
                    Anyways, uploading a huge database (~ 5 GB) may take very long uploading time.
                </p>
                <em>
                    Note that closing or leaving this page while uploading is in progress will cause failure on uploading process.
                </em>
                
            
            </div>
        </section>
    </div>
</div>

@section('bottom-script')
{!! Html::script('js/jquery.js'); !!}
{!! Html::script('js/jquery.maskedinput.js'); !!}
{!! Html::script('js/data-tables/jquery.dataTables.js'); !!}
{!! Html::script('js/data-tables/DT_bootstrap.js'); !!}
{!! Html::script('js/validator.js'); !!}
{!! Html::script('js/date_helpers.js'); !!}
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