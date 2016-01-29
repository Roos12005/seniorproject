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
    }();

}();

