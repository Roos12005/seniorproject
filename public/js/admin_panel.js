/**
 *  @file   admin_panel.js
 *
 *  @brief  Admin Page Script
 *
 *
 *  @author Thanaphoom Pungchaichan (pperfectionist)
 *  @bug    not found!
 *
 */

!function(){
    'use strict';

    var dh = new DateHelper();
    var tmpStorage = {};

    function unaryModetoReadable(m) {
        var result = "";
        var mode = ['Centrality', 'Community', 'Customer Profiling', 'Community Profiling'];
        for (var i = 0, len = m.length; i < len; i++) {
            result = result + (m[i] == '1'? ', ' + mode[i] : '');
        }
        return result.substr(2);
    }

    function addPFilterListener() {
        $(".preprocess-filter").on('click', function() {
            var pid = $(this).attr('data-pid');
            var tag = $('#pf-' + pid);
            var props = {
                'date' : tag.attr('data-date'),
                'carrier' : tag.attr('data-carrier'),
                'period' : tag.attr('data-period'),
                'noOfCall' : tag.attr('data-noOfCall'),
                'duration' : tag.attr('data-duration'),
                'days' : tag.attr('data-days'),

            };
            
            $('#pf-date').html(props.date);
            $('#pf-carrier').html(props.carrier);
            $('#pf-period').html(props.period);
            $('#pf-noOfCall').html(props.noOfCall);
            $('#pf-duration').html(props.duration);
            $('#pf-days').html(props.days);

            $('#preprocessModal').modal('show');
        });
    }

    function addTFilterListener() {
        $(".table-filter").on('click', function() {
            var tid = $(this).attr('data-tid');
            var tag = $('#tf-' + tid);
            var props = {
                'date' : tag.attr('data-date'),
                'carrier' : tag.attr('data-carrier'),
                'period' : tag.attr('data-period'),
                'noOfCall' : tag.attr('data-noOfCall'),
                'duration' : tag.attr('data-duration'),
                'days' : tag.attr('data-days'),
                'mode' : tag.attr('data-calculation')
            };
            
            $('#tf-date').html(props.date);
            $('#tf-carrier').html(props.carrier);
            $('#tf-period').html(props.period);
            $('#tf-noOfCall').html(props.noOfCall);
            $('#tf-duration').html(props.duration);
            $('#tf-days').html(props.days);
            $('#tf-calculation').html(unaryModetoReadable(props.mode));

            console.log(props);
            $('#tableModal').modal('show');
        });
    }

    function rebindTFilterListener() {
        $(".table-filter").unbind();
        $(".table-filter").on('click', function() {
            var tid = $(this).attr('data-tid');
            var tag = $('#tf-' + tid);
            var props = {
                'date' : tag.attr('data-date'),
                'carrier' : tag.attr('data-carrier'),
                'period' : tag.attr('data-period'),
                'noOfCall' : tag.attr('data-noOfCall'),
                'duration' : tag.attr('data-duration'),
                'days' : tag.attr('data-days'),
                'mode' : tag.attr('data-calculation')
            };
            
            $('#tf-date').html(props.date);
            $('#tf-carrier').html(props.carrier);
            $('#tf-period').html(props.period);
            $('#tf-noOfCall').html(props.noOfCall);
            $('#tf-duration').html(props.duration);
            $('#tf-days').html(props.days);
            $('#tf-calculation').html(unaryModetoReadable(props.mode));

            console.log(props);
            $('#tableModal').modal('show');
        });
    }

    function rebindPFilterListener() {
        $(".preprocess-filter").unbind();
        $(".preprocess-filter").on('click', function() {
            var pid = $(this).attr('data-pid');
            var tag = $('#pf-' + pid);
            var props = {
                'date' : tag.attr('data-date'),
                'carrier' : tag.attr('data-carrier'),
                'period' : tag.attr('data-period'),
                'noOfCall' : tag.attr('data-noOfCall'),
                'duration' : tag.attr('data-duration'),
                'days' : tag.attr('data-days'),

            };
            
            $('#pf-date').html(props.date);
            $('#pf-carrier').html(props.carrier);
            $('#pf-period').html(props.period);
            $('#pf-noOfCall').html(props.noOfCall);
            $('#pf-duration').html(props.duration);
            $('#pf-days').html(props.days);

            $('#preprocessModal').modal('show');
        });
    }

    function initPagination() {
        $('#preprocess-table').dataTable({
            "aLengthMenu": [
                [5, 15, 20, -1],
                [5, 15, 20, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 5,
            "sDom": "<'row'<'col-lg-6'l><'col-lg-6'f>r>t<'row'<'col-lg-6'i><'col-lg-6'p>>",
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page",
                "oPaginate": {
                    "sPrevious": "Prev",
                    "sNext": "Next"
                }
            },
            "aoColumnDefs": [{
                    'bSortable': false,
                    'aTargets': [0]
                }
            ]
        });
        jQuery('#preprocess-table_wrapper .dataTables_filter input').addClass("form-control medium"); // modify table search input
        jQuery('#preprocess-table_wrapper .dataTables_length select').addClass("form-control xsmall"); // modify table per page dropdown

        $('#preprocess-progress-table').dataTable({
            "aLengthMenu": [
                [5, 15, 20, -1],
                [5, 15, 20, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 5,
            "sDom": "<'row'<'col-lg-6'l><'col-lg-6'f>r>t<'row'<'col-lg-6'i><'col-lg-6'p>>",
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page",
                "oPaginate": {
                    "sPrevious": "Prev",
                    "sNext": "Next"
                }
            },
            "aoColumnDefs": [{
                    'bSortable': false,
                    'aTargets': [0]
                }
            ]
        });
        jQuery('#preprocess-progress-table_wrapper .dataTables_filter input').addClass("form-control medium"); // modify table search input
        jQuery('#preprocess-progress-table_wrapper .dataTables_length select').addClass("form-control xsmall"); // modify table per page dropdown


        $('#progress-table').dataTable({
            "aLengthMenu": [
                [5, 15, 20, -1],
                [5, 15, 20, "All"] // change per page values here
            ],
            // set the initial value
            "iDisplayLength": 5,
            "sDom": "<'row'<'col-lg-6'l><'col-lg-6'f>r>t<'row'<'col-lg-6'i><'col-lg-6'p>>",
            "sPaginationType": "bootstrap",
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page",
                "oPaginate": {
                    "sPrevious": "Prev",
                    "sNext": "Next"
                }
            },
            "aoColumnDefs": [{
                    'bSortable': false,
                    'aTargets': [0]
                }
            ]
        });
        jQuery('#progress-table_wrapper .dataTables_filter input').addClass("form-control medium"); // modify table search input
        jQuery('#progress-table_wrapper .dataTables_length select').addClass("form-control xsmall"); // modify table per page dropdown

    }

    function intArrayToUnary(arr, size) {
        var result = '';
        var c = 0;
        for(var idx=0;idx<size;idx+=1) {
            if(idx == arr[c]) {
                result += 1;
                c += 1;
            } else {
                result += 0;
            }
        }
        return result;
    }

    function initPreprocessForm() {
        // hide preprocess form until user clicked on add button
        $('#preprocess-form-wrapper').hide();
        
        // force community mode checkbox to be checked when community profiling checkbox is checked
        $('#preprocess-community-profiling-mode').on('change', function(){
            if(this.checked) {
                $('#preprocess-community-mode').prop('checked',this.checked);
            }
        });
        // force community profiling mode checkbox to be unchecked when community checkbox is unchecked
        $('#preprocess-community-mode').on('change', function(){
            if(!this.checked) {
                $('#preprocess-community-profiling-mode').prop('checked',this.checked);
            }
        });

        // add listener to preprocess form
        // submit button
        $('#submit-preprocess').on('click', function() {
            // submit form
            var days = $('.preprocess-days:checked').map(function(_, el) {
                return $(el).val();
            }).get();
            days = intArrayToUnary(days,7);
            if(days.indexOf('1') < 0) {
                alert('Please select at least one day.');
                return;
            }

            var carriers = $('.preprocess-carriers:checked').map(function(_, el) {
                return $(el).val();
            }).get();
            carriers = intArrayToUnary(carriers,5);
            if(carriers.indexOf('1') < 0) {
                alert('Please select at least one carriers.');
                return;
            }

            var mode = $('.preprocess-mode:checked').map(function(_, el) {
                return $(el).val();
            }).get();
            mode = intArrayToUnary(mode,4);
            if(mode.indexOf('1') < 0) {
                alert('Please select at least one mode.');
                return;
            }
            

            var validator = new Validator();

            var durationMin = validator.validateMinRange($('#preprocess-durationFrom').val());
            var durationMax = validator.validateMaxRange($('#preprocess-durationTo').val());
            if(durationMin > durationMax && durationMax !== -1) {
                alert('Minimum duration is exceeded the Maximun duration.');
                return;
            }

            var callsMin = validator.validateMinRange($('#preprocess-callsFrom').val());
            var callsMax = validator.validateMaxRange($('#preprocess-callsTo').val());
            if(callsMin > callsMax && callsMax !== -1) {
                alert('Minimum calls is exceeded the Maximun calls.');
                return;
            }

            var periodMin = validator.validateMinTime($('#preprocess-periodFrom').val());
            var periodMax = validator.validateMaxTime($('#preprocess-periodTo').val());
            if(periodMin > periodMax && periodMax !== -1) {
                alert('Minimum period is exceeded the Maximun period.');
                return;
            }

            var submit = {
                'filters' : {
                    'startDate' : $('#preprocess-date').val(),
                    'callDay' : days,
                    'startTime' : [periodMin, periodMax],
                    'duration' : [durationMin, durationMax],
                    // 'callsMin' : callsMin,
                    // 'callsMax' : callsMax,
                    'rnCode' : carriers,
                    'priority' : $('#preprocess-priority input[name="preprocess-priority"]:checked').val()
                },
                'mode' : mode,
                'description' : $('#preprocess-description').val(),
                
            }
            console.log('Preprocess Form submission : ');

            // TODO : ajax
            // getEstimation(submit, 'preprocess');
            tmpStorage = {
                "d" : submit,
                "type" : 'preprocess',
            };
            console.log(submit);
            submitForm();
        });

        // cancel button
        $('#cancel-preprocess').on('click', function() {
            // clear all fields
            $('#preprocess-form')[0].reset();

            // hide preprocess form and show add button
            $('#new-preprocess').show(300);
            $('#preprocess-form-wrapper').hide();
        });

        // add button listener
        $('#new-preprocess').on('click', function() {
            // hide add button and show preprocess form
            $('#new-preprocess').hide();
            $('#preprocess-form-wrapper').show(300);
        });
    }

    function initBatchForm() {
        // hide batch form until user clicked on add button
        $('#batch-form-wrapper').hide();

        $('#begin-batch').on('click', function() {
            $('#estimationModal').modal('hide');
            submitForm();
        });

        // add listener to batch form
        // submit button
        $('#submit-batch').on('click', function() {
            // submit form
            var days = $('.batch-days:checked').map(function(_, el) {
                return $(el).val();
            }).get();
            days = intArrayToUnary(days,7);
            if(days.indexOf('1') < 0) {
                alert('Please select at least one day.');
                return;
            }

            var carriers = $('.batch-carriers:checked').map(function(_, el) {
                return $(el).val();
            }).get();
            carriers = intArrayToUnary(carriers,5);
            if(carriers.indexOf('1') < 0) {
                alert('Please select at least one carriers.');
                return;
            }

            var mode = $('.batch-mode:checked').map(function(_, el) {
                return $(el).val();
            }).get();
            mode = intArrayToUnary(mode,4);
            if(mode.indexOf('1') < 0) {
                alert('Please select at least one mode.');
                return;
            }

            var validator = new Validator();

            var durationMin = validator.validateMinRange($('#batch-durationFrom').val());
            var durationMax = validator.validateMaxRange($('#batch-durationTo').val());
            if(durationMin > durationMax && durationMax !== -1) {
                alert('Minimum duration is exceeded the Maximun duration.');
                return;
            }

            var callsMin = validator.validateMinRange($('#batch-callsFrom').val());
            var callsMax = validator.validateMaxRange($('#batch-callsTo').val());
            if(callsMin > callsMax && callsMax !== -1) {
                alert('Minimum calls is exceeded the Maximun calls.');
                return;
            }

            var periodMin = validator.validateMinTime($('#batch-periodFrom').val());
            var periodMax = validator.validateMaxTime($('#batch-periodTo').val());
            if(periodMin > periodMax && periodMax !== -1) {
                alert('Minimum period is exceeded the Maximun period.');
                return;
            }

            var submit = {
                'filters' : {
                    'startDate' : $('#batch-date').val(),
                    'callDay' : days,
                    'startTime' : [periodMin, periodMax],
                    'duration' : [durationMin, durationMax],
                    // 'callsMin' : callsMin,
                    // 'callsMax' : callsMax,
                    'rnCode' : carriers
                },
                'mode' : mode,
                'description' : $('#batch-description').val()
            }
            console.log('Batch Form submission : ');
            console.log(submit);

            // TODO : ajax
            getEstimation(submit, 'batch');
        });

        // cancel button
        $('#cancel-batch').on('click', function() {
            // clear all fields
            $('#batch-form')[0].reset();

            // hide batch form and show add button
            $('#new-batch').show(300);
            $('#batch-form-wrapper').hide();

            
        });

        // add button listener
        $('#new-batch').on('click', function() {
            // clear all fields
            $('#batch-form')[0].reset();

            // hide add button and show batch form
            $('#new-batch').hide();
            $('#batch-form-wrapper').show(300);
        });
    }

    function addInputFormMasking() {
        var notOverTwentyFour = function(val) {
            return parseFloat(val) > 24.00 ? '24.\0\0' : '00.00';
        }
        $('.time-mask').mask('00.00',  {
            onKeyPress: function(val, e, field, options) {
                field.mask(notOverTwentyFour.apply({}, arguments), options);
            },
            clearIfNotMatch: true

        });
        $('.integer-mask').mask("#", {reverse: true})
    }


    /**
     *  @brief  Basic setuo for AJAX call.
     *
     *  This function must be called everytime before using ajax call.
     *  This function contains CSRF generator which will generate
     *  a CSRF token from page cookie.
     *
     *  Note that CSRF must be placed in HTML file that includes this script.
     *  Otherwise, backend side will reject any AJAX call.
     *
     *  @return void
     */
    function ajaxSetup(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    }

    function getEstimation(d, type) {
        ajaxSetup();
        $.ajax({
            type: "POST",
            url: "http://localhost/seniorproject/public/getEstimation",
            data : {'filter' : d['filters'], 'type' : type, 'mode' : d['mode'], 'description' : d['description']},
            success: function(e){
                console.log(e);
                // TODO : modal ask for confirmation before start Executing
                $('#exectime').html(dh.toReadable(e.execTime));
                $('#estimationModal').modal('show');
                tmpStorage = {
                    "d" : d,
                    "type" : type,
                    "execTime" : e.execTime,
                    "customers" : e.customers
                };
            },
            error: function(rs, e){
                console.log(rs.responseText);
            }
        });
    }

    function submitForm() {
        var d = tmpStorage['d'];
        var type = tmpStorage['type'];
        var others = {
            'customers' : tmpStorage['customers'],
            'estimatedExecTime' : tmpStorage['execTime'],
        };
        ajaxSetup();
        $.ajax({
            type: "POST",
            url: "http://localhost/seniorproject/public/processSetup",
            data : {'filter' : d['filters'], 'type' : type, 'mode' : d['mode'], 'description' : d['description'], 'others' : others},
            success: function(e){
                console.log(e);
                if(type == 'batch') {
                    
                    var f = e['filters'];
                    var idCol = '<td><a href="./analysis/' + e.nid + '">New</a></td>';
                    var dateCol = '<td>' + f['startDate'] + '</td>';
                    var descCol = '<td>'+d['description']+'</td>';
                    var customerCol = '<td class="text-center">'+others['customers']+'</td>';
                    var sizeCol = '<td class="text-center">-</td>';
                    var actionCol = '<td><div class="label label-default label-mini table-filter margin-right-4" href="#" data-toggle="modal" data-tid="'+e.nid+'"><i class="fa fa-info"></i></div><span id="tf-'+e.nid+'" data-date="'+ e['filters']['startDate'] +'" data-noOfCall="'+'"data-duration="'+ e['filters']['duration'] +'" data-period="'+ e['filters']['startTime'] +'" data-carrier="'+ e['filters']['rnCode'] +'" data-days="' + e['filters']['callDay'] + '" data-calculation="'+ e['mode'] +'"></span><div class="label label-primary label-mini margin-right-4"><i class="fa fa-eye"></i></div><div class="label label-success label-mini margin-right-4"><i class="fa fa-download"></i></div><div class="label label-danger label-mini"><i class="fa fa-times"></i></div></td>';
                    var statusCol = '<td><span class="label label-warning label-mini">Processing</span></td>';
                    var progressCol = '<td><div class="progress progress-striped progress-xs"><div style="width: 5%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="100" role="progressbar" class="progress-bar progress-bar-success"></div></div></td>';
                    $('#progress-table-body').append('<tr>' + idCol + dateCol + descCol + customerCol + sizeCol + actionCol + statusCol + progressCol + '</tr>');

                    // hide batch form and show add button
                    $('#new-batch').show(300);
                    $('#batch-form-wrapper').hide();
                    rebindTFilterListener();
                    $.ajax({
                        type: "POST",
                        url: "http://localhost/seniorproject/public/startProcess",
                        data : {'filter' : d['filters'], 'type' : type, 'mode' : d['mode'], 'description' : d['description'], 'others' : others, 'nid' : e.nid},
                        success: function(e){},
                        error: function(rs, e){
                            console.log(rs.responseText);
                        }
                    });
                } else if(type == 'preprocess') {
                    console.log(e);
                    var f = e['filters'];
                    var idCol = '<td><a href="#">New</a></td>';
                    var descCol = '<td>' + d['description'] + '</td>';
                    var cenModeCol = '<td>' + (d['mode'].substr(0,1) == 1? 'Yes' : 'No') + '</td>';
                    var comModeCol = '<td>' + (d['mode'].substr(1,1) == 1? 'Yes' : 'No') + '</td>';
                    var custProModeCol = '<td>' + (d['mode'].substr(2,1) == 1? 'Yes' : 'No') + '</td>';
                    var comProModeCol = '<td>' + (d['mode'].substr(3,1) == 1? 'Yes' : 'No') + '</td>';
                    var filtersCol = '<td><a href="#" data-toggle="modal" class="preprocess-filter" data-pid="' + e.nid + '"> Click to see filters </a><span id="pf-' + e.nid + '" data-date="" data-noOfCall="" data-days="" data-duration="" data-period="" data-carrier=""></span></td>';
                    var priorityCol = '<td>' + (d['filters']['priority'] == 3? 'High' : d['filters']['priority'] == 2? 'Medium' : 'Low') + '</td>';
                    var actionCol = '<td><span class="label label-default label-mini margin-right-4"><i class="fa fa-cog"></i></span><span class="label label-danger label-mini margin-right-4"><i class="fa fa-times"></i></span></td>';
                    $('#preprocess-table-body').append('<tr>' + idCol + descCol + cenModeCol + comModeCol + custProModeCol + comProModeCol + filtersCol + priorityCol + actionCol + '</tr>');
                    $('#new-preprocess').show(300);
                    $('#preprocess-form-wrapper').hide();
                    rebindPFilterListener();
                }
            },
            error: function(rs, e){
                console.log(rs.responseText);
            }
        });
    }

    /**
     *  @brief Main function of this file
     *
     *  @param undefined
     *  @return void
     */
    !function(undefined){
        addPFilterListener();
        addTFilterListener();
        initPagination();
        initPreprocessForm();
        initBatchForm();
        addInputFormMasking();
    }();

}();

