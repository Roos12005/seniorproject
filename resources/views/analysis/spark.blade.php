@extends('layout.default')

@section('title', 'AIS - CU : About Us')

@section('stylesheet')

@section('content')

<div class="row">
    <div class="col-md-6">
        <section class="panel">
            <header class="panel-heading">
                Graph Processing Submit Form
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-up"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                </span>
            </header>
            <div class="panel-body">
                <form action="/job" method="get" id="addJobToProcess" role="form">
                    <div class="form-body">
                        <div class="form-group">
                            <label>Processing Name</label>
                            <div class="input-icon right">
                                <i class="fa fa-microphone fa-spin font-blue"></i>
                                <input type="text" name="tbProcessingName" id="tbProcessingName" class="form-control" placeholder="Ex. Find Influencer"> </div>
                        </div>
                         <div class="form-group">
                            <label>Algorithm</label>
                            <select name="dbAlgorithm" id="dbAlgorithm" class="form-control">
                                <option value="CC">Influentcer (Closeness Centrality))</option>
                                <option value="KBC">Linker (K-Betweeness Centrality)</option>
                                <option value="LC">Community Detection (Louvain Modularity)</option>
                                <option value="ALL">ALL IN ONE</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Data Location</label>
                            <div class="mt-radio-list">
                                <label class="mt-radio"> Local
                                    <input type="radio" name="rbInputMode" id="local" value="TEST">
                                    <span></span>
                                </label>
                                <label class="mt-radio"> Server (Hive MetaStore)
                                    <input type="radio" name="rbInputMode" id="hive" value="HIVE">
                                    <span></span>
                                </label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Input File</label>
                            <div class="input-icon right">
                                <i class="fa fa-microphone fa-spin font-blue"></i>
                                <input type="text" name="tbInputFile" id="tbInputFile" class="form-control" value="/Users/ponthaiklinsompus/Desktop/multiple-proj/NewData/test2"> </div>
                                <p class="help-block"> * if select data from "Server (Hive MetaStore)"</p>
                            <p class="help-block"> * if select data from "Local" directory</p>
                            <p class="help-block"> ** Support .txt, .csv file only</p>
                        </div>
                        
                        <div class="form-group">
                            <label>Selection table</label>
                            <div class="input-icon right">
                                <i class="fa fa-microphone fa-spin font-blue"></i>
                                <input type="text" name="tbTable" id="tbTable" class="form-control" placeholder="Ex. default.table"> </div>
                                <p class="help-block"> * if select data from "Server (Hive MetaStore)"</p>
                        </div>
                        <div class="form-group">
                            <label>Define Caller Data</label>
                            <div class="input-icon right">
                                <i class="fa fa-microphone fa-spin font-blue"></i>
                                <input type="text" name="tbCaller" id="tbCaller" class="form-control" placeholder=""> </div>
                                <p class="help-block"> * if select data from "Local" (.txt), Choose index => Ex. 0 etc.</p>
                                <p class="help-block"> * if select data from "Local" (.csv), Choose caller field name => Ex. caller etc.</p>
                                <p class="help-block"> * if select data from "Server (Hive MetaStore), Choose caller attr. in table => Ex. caller etc."</p>
                        </div>
                        <div class="form-group">
                            <label>Define Receiver Data</label>
                            <div class="input-icon right">
                                <i class="fa fa-microphone fa-spin font-blue"></i>
                                <input type="text" name="tbRev" id="tbRev" class="form-control" placeholder=""> </div>
                                <p class="help-block"> * if select data from "Local" (.txt), Choose index => Ex. 1 etc.</p>
                                <p class="help-block"> * if select data from "Local" (.csv), Choose receiver field name => Ex. receiver etc.</p>
                                <p class="help-block"> * if select data from "Server (Hive MetaStore), Choose receiver attr. in table => Ex. receiver etc."</p>
                        </div>
                        <div class="form-group">
                            <label>Result File</label>
                            <div class="input-icon right">
                                <i class="fa fa-microphone fa-spin font-blue"></i>
                                <input type="text"  name="tbResult" id="tbResult" class="form-control" placeholder="Ex. result.txt"> </div>
                                <p class="help-block"> * Default currentDirectory/result.txt"</p>
                        </div>
                        <div class="form-actions">
                            <button type="submit" name="btAddJob" class="btn blue">Add Job</button>
                            <button type="button" name="btCancelAddJob" class="btn default">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <div class="col-md-6">
        <section class="panel">
            <header class="panel-heading">
                Status Panel
                <span class="tools pull-right">
                    <a href="javascript:;" class="fa fa-chevron-up"></a>
                    <a href="javascript:;" class="fa fa-cog"></a>
                    <a href="javascript:;" class="fa fa-times"></a>
                </span>
            </header>
            <div class="panel-body">
                <div class="portlet-title tabbable-line">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#tab_actions_pending" data-toggle="tab" aria-expanded="true"> Pending </a>
                        </li>
                        <li class="">
                            <a href="#tab_actions_completed" data-toggle="tab" aria-expanded="false"> Completed </a>
                        </li>
                    </ul>
                </div>
                <form action="/job" method="get" id="statusJob" role="form"> <!-- START FORM -->
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_actions_pending">
                             <!-- BEGIN: Actions -->
                             <div class="mt-actions">
                                <c:choose>
                                    <c:when test="${not empty jobList}">
                                        <c:forEach var="job" items="${jobList}">  
                                            <div class="mt-action"> <!-- ACTION in PANEL -->
                                               <!--  <div class="mt-action-img">
                                                     <img src="../assets/pages/media/users/avatar9.jpg"> </div> -->
                                                     
                                                <div class="mt-action-body"> <!-- BODY element in PANEL -->
                                                 
                                                    <div class="mt-action-row"> <!-- ROW element in PANEL -->
                                                        <div class="mt-action-info ">
                                                            <div class="mt-action-icon ">
                                                                <i class="icon-magnet"></i>
                                                            </div>
                                                            <div class="mt-action-details ">
                                                                <input type="hidden" name="jobId" id="jobId" class="form-control" value=${job.id}></input>
                                                                <span class="mt-action-author">${job.processingName}</span>
                                                                <p class="mt-action-desc">AL: ${job.algorithm}, MODE: ${job.inputMode}, </p>
                                                                <p class="mt-action-desc">INPUT: ${job.inputFile}, [ ${job.callerCol}, ${job.revCol} ]
                                                                </p>
                                                                <p class="mt-action-desc">OUTPUT : ${job.resultFile}</p>
                                                            </div>
                                                        </div>
                                                        <div class="mt-action-datetime ">
                                                            <span class="mt-action-date">${job.date}</span>
                                                            <span class="mt-action-dot bg-green"></span>
                                                            <span class="mt=action-time">${job.time}</span>
                                                            <p class="mt-action-desc">STATUS : ${job.status}</p>
                                                        </div>
                                                        <div class="mt-action-buttons ">
                                                            <div class="btn-group btn-group-circle">
                                                                <button type="submit" name="btSubmit" class="btn btn-outline green btn-sm">Submit</button>
                                                                <button type="button" name="btCancelSubmit" class="btn btn-outline red btn-sm">Cancel</button>
                                                            </div>
                                                        </div>
                                                    </div> <!-- ROW element in PANEL -->
                                                     
                                                </div> <!-- BODY element in PANEL -->
                                                 
                                            </div> <!-- ACTION in PANEL -->
                                             
                                        </c:forEach>
                                    </c:when>
                                </c:choose>
                                 
                            </div>
                            <!-- END: Actions -->          
                             
                        </div>
                        <div class="tab-pane" id="tab_actions_completed">
                            <!-- BEGIN:Completed -->
                            <div class="mt-actions">
                             
                                <div class="mt-action">
                                    <div class="mt-action-img">
                                        <img src="../assets/pages/media/users/avatar2.jpg"> </div>
                                    <div class="mt-action-body">
                                        <div class="mt-action-row">
                                            <div class="mt-action-info ">
                                                <div class="mt-action-icon ">
                                                    <i class="icon-badge"></i>
                                                </div>
                                                <div class="mt-action-details ">
                                                    <span class="mt-action-author">Jan Kim</span>
                                                    <p class="mt-action-desc">Lorem Ipsum is simply dummy</p>
                                                </div>
                                            </div>
                                            <div class="mt-action-datetime ">
                                                <span class="mt-action-date">3 jun</span>
                                                <span class="mt-action-dot bg-green"></span>
                                                <span class="mt=action-time">9:30-13:00</span>
                                            </div>
                                            <div class="mt-action-buttons ">
                                                <div class="btn-group btn-group-circle">
                                                    <button type="button" class="btn btn-outline green btn-sm">View</button>
                                                    <button type="button" class="btn btn-outline red btn-sm">Remove</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                 
                                <!-- END: Completed -->
                            </div>
                        </div>
                    </div>
                </form><!-- END: FORM -->
            </div>
        </section>
    </div>
</div>
<!-- Other Statistic Section End -->
@section('bottom-script')
{!! Html::script('js/jquery/jquery.js'); !!}


@endsection
@stop
 
