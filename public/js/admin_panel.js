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

    function addPFilterListener() {
        $(".preprocess-filter").on('click', function() {
            var pid = $(this).attr('data-pid');
            var tag = $('#pf-' + pid);
            var props = {
                'date' : tag.attr('data-date'),
                'carrier' : tag.attr('data-carrier'),
                'period' : tag.attr('data-period'),
                'noOfCall' : tag.attr('data-noOfCall'),
                'duration' : tag.attr('data-duration')
            };
            
            $('#pf-date').html(props.date);
            $('#pf-carrier').html(props.carrier);
            $('#pf-period').html(props.period);
            $('#pf-noOfCall').html(props.noOfCall);
            $('#pf-duration').html(props.duration);


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
                'duration' : tag.attr('data-duration')
            };
            
            $('#tf-date').html(props.date);
            $('#tf-carrier').html(props.carrier);
            $('#tf-period').html(props.period);
            $('#tf-noOfCall').html(props.noOfCall);
            $('#tf-duration').html(props.duration);

            console.log(props);
            $('#tableModal').modal('show');
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
                'date' : $('#preprocess-date').val(),
                'days' : days,
                'periodMin' : periodMin,
                'periodMax' : periodMax,
                'durationMin' : durationMin,
                'durationMax' : durationMax,
                'callsMin' : callsMin,
                'callsMax' : callsMax,
                'carriers' : carriers,
                'mode' : mode
            };
            console.log('Preprocess Form submission : ');
            console.log(submit);

            // TODO : ajax

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
        $('#batch-form').hide();

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
                'date' : $('#batch-date').val(),
                'days' : days,
                'periodMin' : periodMin,
                'periodMax' : periodMax,
                'durationMin' : durationMin,
                'durationMax' : durationMax,
                'callsMin' : callsMin,
                'callsMax' : callsMax,
                'carriers' : carriers,
                'mode' : mode

            }
            console.log('Batch Form submission : ');
            console.log(submit);

            // TODO : ajax
        });

        // cancel button
        $('#cancel-batch').on('click', function() {
            // clear all fields
            $('#batch-form')[0].reset();

            // hide batch form and show add button
            $('#new-batch').show(300);
            $('#batch-form').hide();

            
        });

        // add button listener
        $('#new-batch').on('click', function() {
            // hide add button and show batch form
            $('#new-batch').hide();
            $('#batch-form').show(300);
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

