extends('layout.default')

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
                Scheduler Setting
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
                Data specified in this section will be automatically starting processed at specified time. More data specified will take longer time to process.
            </i>
            <table class="table  table-hover general-table" id="preprocess-table">
                <thead>
                    <tr>
                        <th> #</th>
                        <th>Description</th>

                        <th>Centrality</th>
                        <th>Community</th>
                        <th>Customer <br>Profiling</th>
                        <th>Community <br>Profiling</th>
                        <th>Filters</th>

                        <th>Priority</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="preprocess-table-body">



                    @foreach($preprocess_settings as $key => $p)
                    <tr id="row-p-{{$p['id']}}">
                        <td>{{$key + 1}}</td>
                        <td>
                            {{ $p['description'] }}
                        </td>

                        <td>Yes</td>
                        <td>Yes</td>
                        <td>Yes</td>
                        <td>No</td>

                        <td>
                            <a href="#" data-toggle="modal" class="preprocess-filter" data-pid="{{$p['id']}}">
                                Click to see filters
                            </a>
                            <span id="pf-{{$p['id']}}" data-date="{{$p['date']}}" data-noOfCall="{{$p['noOfCall']}}" data-days="{{$p['days']}}"
                            data-duration="{{$p['duration']}}" data-period="{{$p['period']}}" data-carrier="{{$p['carrier']}}">
                        </span>
                    </td>

                    <td>{{ $p['priority'] }}</td>
                    <td>
                        <!-- <span class="label label-default label-mini"><i class="fa fa-cog"></i></span> -->
                        <a href="#" class="label label-danger label-mini delete-button" data-pid="{{$p['id']}}" data-type="preprocess"><i class="fa fa-times"></i></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <button id="new-preprocess" class="btn btn-default round-add-button">
            <i class="fa fa-plus"></i>
        </button>
        <div class="col-lg-12" id="preprocess-form-wrapper">
            <header class="panel-heading m-bot15">
              ADD NEW SCHEDULER SETTING
          </header>   
          <div class="col-md-12">

            <form action="#" class="form-horizontal bucket-form" id="preprocess-form" onsubmit="return false;">

                <div class="form-group">
                    <label class="col-sm-3 control-label">Database</label>
                    <div class="col-sm-6">
                        <select class="form-control" id="preprocess-database">
                            <optgroup label="Database Name">
                                @foreach($database as $db) 
                                    <option value="{{ $db['dbid'] }}"> {{ $db['dbid'] }} : {{ $db['n']['name'] }} </option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Description</label>
                    <div class="col-lg-6">
                            <input type="text" class="form-control" id="preprocess-description">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label">Date</label>
                    <div class="col-sm-6">
                        <select class="form-control" id="preprocess-date">
                            <optgroup label="Monthly">
                                <option value="0000010">Whole Month</option>
                            </optgroup>
                            <optgroup label="Weekly">
                                <option value="0000011">Week 1</option>
                                <option value="0000012">Week 2</option>
                                <option value="0000013">Week 3</option>
                                <option value="0000014">Week 4</option>
                                <option value="0000015">Week 5</option>
                            </optgroup>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Days</label>
                    <div class="col-lg-6">
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-days" value="0" checked> Sun
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-days" value="1" checked> Mon
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-days" value="2" checked> Tue
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-days" value="3" checked> Wed
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-days" value="4" checked> Thu
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-days" value="5" checked> Fri
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-days" value="6" checked> Sat
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Calling Time</label>
                    <div class="col-lg-6">
                        <div class="input-group input-medium" data-time="00:00" data-time-format="hh:mm">
                            <input type="text" class="form-control time-filter time-mask" id="preprocess-periodFrom" name="from">
                            <span class="input-group-addon">To</span>
                            <input type="text" class="form-control time-filter time-mask" id="preprocess-periodTo" name="to">
                        </div>
                        <p class="help-block">Example: 12.00 To 23.59 <i> &nbsp;*Leave blank for not specify</i></p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Call Duration</label>
                    <div class="col-lg-6">
                        <div class="input-group input-medium" data-time="05:00" data-time-format="hh:mm">
                            <input type="text" class="form-control integer-mask" id="preprocess-durationFrom" name="from">
                            <span class="input-group-addon">To</span>
                            <input type="text" class="form-control integer-mask" id="preprocess-durationTo" name="to">
                        </div>
                        <p class="help-block">Example: 0 To 100 <i> &nbsp;*Leave blank for not specify</i></p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">No. of Incoming Calls</label>
                    <div class="col-lg-6">
                        <div class="input-group input-medium" data-time="05:00" data-time-format="hh:mm">
                            <input type="text" class="form-control integer-mask" id="preprocess-incallsFrom" name="from" disabled>
                            <span class="input-group-addon">To</span>
                            <input type="text" class="form-control integer-mask" id="preprocess-incallsTo" name="to" disabled>
                        </div>
                        <p class="help-block">Example: 0 To 10 <i> &nbsp;*Leave blank for not specify</i></p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">No. of Outgoing Calls</label>
                    <div class="col-lg-6">
                        <div class="input-group input-medium" data-time="05:00" data-time-format="hh:mm">
                            <input type="text" class="form-control integer-mask" id="preprocess-outcallsFrom" name="from" disabled>
                            <span class="input-group-addon">To</span>
                            <input type="text" class="form-control integer-mask" id="preprocess-outcallsTo" name="to" disabled>
                        </div>
                        <p class="help-block">Example: 0 To 10 <i> &nbsp;*Leave blank for not specify</i></p>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Carrier</label>
                    <div class="col-lg-6">
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-carriers" value="0" checked> AIS
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-carriers" value="1" checked> TRUE
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-carriers" value="2" checked> DTAC
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-carriers" value="4" checked> Others
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Social Network Statistics</label>
                    <div class="col-lg-9">
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-mode" value="0"> Centrality
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" class="preprocess-mode" value="2" checked> Customer Profiling
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" id="preprocess-community-mode" class="preprocess-mode" value="1" checked> Community
                        </label>
                        <label class="checkbox-inline">
                            <input type="checkbox" id="preprocess-community-profiling-mode" class="preprocess-mode" value="3" checked> Community Profiling
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Set this task priority</label>
                    <div class="col-lg-9" id="preprocess-priority">
                        <label class="checkbox-inline">
                            <input type="radio" class="preprocess-priority" name="preprocess-priority" value="3"> High
                        </label>
                        <label class="checkbox-inline">
                            <input type="radio" class="preprocess-priority" name="preprocess-priority" value="2" checked> Medium
                        </label>
                        <label class="checkbox-inline">
                            <input type="radio" class="preprocess-priority" name="preprocess-priority" value="1"> Low
                        </label>
                    </div>
                </div>

                <div class="text-center">
                    <button class="btn btn-success" id="submit-preprocess">Submit</button>
                    <button class="btn btn-danger" id="cancel-preprocess">Cancel</button>
                </div>  

            </form>
        </div>
    </div>
</div>
</section>
</div>
</div>

<div class="row">
    <div class="col-sm-12">
        <section class="panel">
            <header class="panel-heading">
                SCHEDULER JOBS
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
                        @foreach($preprocess_jobs as $key => $row)
                        <tr id="row-pr-{{$p['id']}}">
                            <td><a href="./analysis/{{$row['id']}}">{{ $key + 1 }}</a></td>
                            <td>{{ $row['date'] }}</td>
                            <td>
                                <a class="label label-default label-mini table-filter" href="#" data-toggle="modal" data-tid="{{$row['id']}}"><i class="fa fa-info"></i></a>
                                {{ $row['description'] }}
                                <span id="tf-{{$row['id']}}" data-date="{{$row['date']}}" data-noOfCall="{{$row['noOfCall']}}" data-days="{{$row['days']}}"
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
                            <a href="#" class="label label-danger label-mini delete-button" data-pid="{{$row['id']}}" data-type="preprocess-result"><i class="fa fa-times"></i></a>
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
        <section class="panel" style="white-space: nowrap;">
            <header class="panel-heading">
                BATCH JOBS
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
                            <th hidden></th>
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
                    <tbody id="progress-table-body">
                        @foreach($batch_jobs as $key => $row)
                        <tr id="row-b-{{$row['id']}}">
                            <td hidden><i>{{ 1 }}</i></td>
                            <td><i>{{ $key + 1 }}</i></td>
                            <td width="180">{{ $row['date'] }}</td>
                            <td width="250">
                                
                                {{ $row['description'] }}
                                
                            
                            </td>
                        <td class="text-center" width="100">{{ $row['customers'] }}</td>
                        <td class="text-center" width="50">{{ $row['size'] }}</td>
                        <td style="white-space: nowrap;" width="150">
                            <div class="label label-default label-mini table-filter" data-toggle="modal" data-tid="{{$row['id']}}"><i class="fa fa-info"></i></div>
                            <span id="tf-{{$row['id']}}" data-date="{{$row['date']}}" data-noOfCall="{{$row['noOfCall']}}" data-days="{{$row['days']}}"
                                data-duration="{{$row['duration']}}" data-period="{{$row['period']}}" data-carrier="{{$row['carrier']}}" data-calculation="{{$row['mode']}}"></span>
                            @if($row['progress'] < 100)
                            <div class="label label-default label-mini" id="tf-view-{{ $row['id'] }}"><i class="fa fa-eye"></i></div>
                            <div class="label label-default label-mini" id="tf-download-{{ $row['id'] }}"><i class="fa fa-download"></i></div>
                            @else
                            <div class="label label-primary label-mini"><i class="fa fa-eye tf-view" data-id="{{$row['id']}}"></i></div>
                            <div class="label label-success label-mini"><i class="fa fa-download tf-download" data-id="{{$row['id']}}"></i></div>
                            @endif
                            <div class="label label-danger label-mini delete-button" data-tid="{{$row['id']}}" data-type="batch"><i class="fa fa-times"></i></div>
                        </td>
                        @if($row['progress'] < 100)
                        <td width="30"><div class="label label-warning label-mini" id="tf-status-{{ $row['id'] }}">
                            {{ $row['status'] }}</div></td>
                        @else
                        <td width="30"><div class="label label-success label-mini" id="tf-status-{{ $row['id'] }}">
                            {{ $row['status'] }}</div></td>
                        @endif
                        <td width="70">
                            <div class="progress progress-striped progress-xs">
                                <div style="width: {{ $row['progress'] }}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="100" role="progressbar" class="progress-bar progress-bar-success" aria-speed="{{ $row['speed'] }}" aria-current="{{ $row['progress'] }}" data-id="{{ $row['id'] }}">
                                    <span class="sr-only">100% Complete (success)</span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <button id="new-batch" class="btn btn-default round-add-button">
                <i class="fa fa-plus"></i>
            </button>
            <div class="col-lg-12" id="batch-form-wrapper">
                <header class="panel-heading m-bot15">
                  ADD NEW BATCH JOB
              </header>   
              <div class="col-md-12">

                <form action="#" class="form-horizontal bucket-form" id="batch-form" onsubmit="return false;">

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Database</label>
                        <div class="col-sm-6">
                            <select class="form-control" id="batch-database">
                                <optgroup label="Database Name">
                                    @foreach($database as $db) 
                                        <option value="{{ $db['n']['name'] }}"> {{ $db['dbid'] }} : {{ $db['n']['name'] }} </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Description</label>
                        <div class="col-lg-6">
                                <input type="text" class="form-control" id="batch-description">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label">Date</label>
                        <div class="col-sm-6">
                            <select class="form-control" id="batch-date">
                                <optgroup label="September 2015">
                                    <option value="2015090">Sep - Month</option>
                                    <option value="2015091">Sep - Week 1</option>
                                    <option value="2015092">Sep - Week 2</option>
                                </optgroup>
                                <optgroup label="October 2015">
                                    <option value="2015100">Oct - Month</option>
                                    <option value="2015101">Oct - Week 1</option>
                                    <option value="2015102">Oct - Week 2</option>
                                    <option value="2015103">Oct - Week 3</option>
                                    <option value="2015104">Oct - Week 4</option>
                                    <option value="2015105">Oct - Week 5</option>
                                </optgroup>
                                <optgroup label="November 2015">
                                    <option value="2015110">Nov - Month</option>
                                    <option value="2015111">Nov - Week 1</option>
                                    <option value="2015112">Nov - Week 2</option>
                                    <option value="2015113">Nov - Week 3</option>
                                    <option value="2015114">Nov - Week 4</option>
                                    <option value="2015115">Nov - Week 5</option>
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Days</label>
                        <div class="col-lg-6">
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-days" value="0" checked> Sun
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-days" value="1" checked> Mon
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-days" value="2" checked> Tue
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-days" value="3" checked> Wed
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-days" value="4" checked> Thu
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-days" value="5" checked> Fri
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-days" value="6" checked> Sat
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Calling Time</label>
                        <div class="col-lg-6">
                            <div class="input-group input-medium" data-time="00:00" data-time-format="hh:mm">
                                <input type="text" class="form-control time-filter time-mask" id="batch-periodFrom" name="from">
                                <span class="input-group-addon">To</span>
                                <input type="text" class="form-control time-filter time-mask" id="batch-periodTo" name="to">
                            </div>
                            <p class="help-block">Example: 12.00 To 23.59 <i> &nbsp;*Leave blank for not specify</i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Call Duration</label>
                        <div class="col-lg-6">
                            <div class="input-group input-medium" data-time="05:00" data-time-format="hh:mm">
                                <input type="text" class="form-control integer-mask" id="batch-durationFrom" name="from">
                                <span class="input-group-addon">To</span>
                                <input type="text" class="form-control integer-mask" id="batch-durationTo" name="to">
                            </div>
                            <p class="help-block">Example: 0 To 100 <i> &nbsp;*Leave blank for not specify</i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">No. of Incoming Calls</label>
                        <div class="col-lg-6">
                            <div class="input-group input-medium" data-time="05:00" data-time-format="hh:mm">
                                <input type="text" class="form-control" id="batch-incallsFrom" name="from" disabled>
                                <span class="input-group-addon">To</span>
                                <input type="text" class="form-control" id="batch-incallsTo" name="to" disabled>
                            </div>
                            <p class="help-block">Example: 0 To 100 <i> &nbsp;*Leave blank for not specify</i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">No. of Outgoing Calls</label>
                        <div class="col-lg-6">
                            <div class="input-group input-medium" data-time="05:00" data-time-format="hh:mm">
                                <input type="text" class="form-control" id="batch-outcallsFrom" name="from" disabled>
                                <span class="input-group-addon">To</span>
                                <input type="text" class="form-control" id="batch-outcallsTo" name="to" disabled>
                            </div>
                            <p class="help-block">Example: 0 To 100 <i> &nbsp;*Leave blank for not specify</i></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Carrier</label>
                        <div class="col-lg-6">
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-carriers" value="0" checked> AIS
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-carriers" value="1" checked> TRUE
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-carriers" value="2" checked> DTAC
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-carriers" value="4" checked> Others
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 control-label col-lg-3" for="inputSuccess">Social Network Statistics</label>
                        <div class="col-lg-9">
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-mode" value="0"> Centrality
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" class="batch-mode" value="2" checked> Customer Profiling
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" id="batch-community-mode" class="batch-mode" value="1" checked> Community
                            </label>
                            <label class="checkbox-inline">
                                <input type="checkbox" id="batch-community-profiling-mode" class="batch-mode" value="3" checked> Community Profiling
                            </label>
                        </div>
                    </div>

                    <div class="text-center">
                        <button class="btn btn-success" id="submit-batch">Submit</button>
                        <button class="btn btn-danger" id="cancel-batch">Cancel</button>
                    </div>  

                </form>
            </div>
        </div>

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
                        <label class=" col-sm-3 control-label">Database </label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-database">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Date </label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="pf-date">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Day</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="pf-days">-</p>
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
                        <label class=" col-sm-3 control-label">Database </label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-database">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Date </label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-date">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Day</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-days">-</p>
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
                        <label class=" col-sm-3 control-label">No. of Outgoing Calls</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-noOfCall">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">No. of Incoming Calls</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-noOfReceive">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Carrier</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-carrier">-</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class=" col-sm-3 control-label">Calculation</label>
                        <div class="col-lg-6">
                            <p class="form-control-static" id="tf-calculation">-</p>
                        </div>
                    </div>
                </form> 
            </div>
        </div>
    </div>
</div>

<!-- Table Information Modal-->
<div aria-hidden="true" aria-labelledby="estimationModalLabel" role="dialog" tabindex="-1" id="estimationModal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                <h4 class="modal-title">Execution time estimation</h4>
            </div>
            <div class="modal-body text-center">
                <p>This process will take approximately <span id="exectime"></span>. Are you sure to start this proess ?</p>
            </div>
            <div class="modal-footer">
                <div class="text-right">
                    <button class="btn btn-success" id="begin-batch">Submit</button>
                    <button class="btn btn-danger" data-dismiss="modal" aria-hidden="true">Close</button>
                </div> 
            </div>  
        </div>
    </div>
</div>
@section('bottom-script')
{!! Html::script('js/jquery.js'); !!}
{!! Html::script('js/jquery.maskedinput.js'); !!}
{!! Html::script('js/data-tables/jquery.dataTables.js'); !!}
{!! Html::script('js/data-tables/DT_bootstrap.js'); !!}
{!! Html::script('js/validator.js'); !!}
{!! Html::script('js/date_helpers.js'); !!}
{!! Html::script('js/converter.js'); !!}
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

@endsection
@stop