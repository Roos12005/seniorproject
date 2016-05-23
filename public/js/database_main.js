/**
 *  @file   database_main.js
 *
 *  @brief  Database Manaegr Page Script
 *
 *
 *  @author Thanaphoom Pungchaichan (pperfectionist)
 *  @bug    not found!
 *
 */

 !function(){
    'use strict';
    var uploader_factory = null;
    var db_name = "";
    var feature_extraction = 0;
    function foo() {

    }

    function init() {
        bindDeleteButtonListenner();
        bindRenameButtonListenner();
    }

    function bindDeleteButtonListenner() {
        $('.delete-button').on('click', function() {
            var db_id = $(this).attr('data-id');
            var db_name = $(this).attr('data-name');
            $('#dbname').html(db_name);
            $('#begin-delete').attr('data-id', db_id);
            $('#confirmationModal').modal('show');
        });

        $('#begin-delete').on('click', function() {
            var db_id = $(this).attr('data-id');
            $('#confirmationModal').modal('hide');
            ajaxSetup();
            $.ajax({
                type: "POST",
                url: "http://localhost/seniorproject/public/database/delete",
                data : {db_id: db_id},
                success: function(e){
                    console.log(e);
                },
                error: function(rs, e){
                    console.log(rs.responseText);
                    alert('Problem occurs during deleting data.');
                }
            })
        });
    }

    function bindRenameButtonListenner() {
        $('.rename-button').on('click', function() {
            var db_id = $(this).attr('data-id');
            var db_name = $(this).attr('data-name');
            $('#db-current-name').html(db_name);
            $('#begin-rename').attr('data-id', db_id);
            $('#renameModal').modal('show');
        });

        $('#begin-rename').on('click', function() {
            var db_id = $(this).attr('data-id');
            var new_name = $("#db-new-name").val();
            if(new_name.length < 3) {
                alert("Database name must contain at least 3 characters.")
            }
            $('#renameModal').modal('hide');

            ajaxSetup();
            $.ajax({
                type: "POST",
                url: "http://localhost/seniorproject/public/database/rename",
                data : {db_id: db_id, new_name: new_name},
                success: function(e){
                    console.log(e);
                    $('#db-name-' + db_id).html(new_name);
                    $('#db-rename-' + db_id).attr("data-name", new_name);
                },
                error: function(rs, e){
                    console.log(rs.responseText);
                    alert('Problem occurs during deleting data.');
                }
            })
        });
    }

    /**
     *  @brief  Basic setup for AJAX call.
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

    function startProfileUploader(uploader) {
        uploader.start();
    }

    function startWriteToDatabase() {
        ajaxSetup();
        $.ajax({
            type: "POST",
            url: "http://localhost/seniorproject/public/database/writedb",
            data : {name: db_name, feature: feature_extraction},
            success: function(e){
                console.log(e);
            },
            error: function(rs, e){
                console.log(rs.responseText);
                alert('Problem occurs during fetch data.');
            }
        })
        
    }

    /**
     *  @brief Main function of this file
     *
     *  @param undefined
     *  @return void
     */
     !function(undefined){
        init();
        uploader_factory = new UploaderFactory('alert-warning', 'alert-success');

        var profile_uploader = uploader_factory.createUploader('browse-profile', '2mb', 'uploadprofile');
        profile_uploader = uploader_factory.onFileAdded(profile_uploader, 'profile', 1);
        profile_uploader = uploader_factory.onUploading(profile_uploader, 'profile');
        profile_uploader = uploader_factory.onUploadCompleted(profile_uploader, 'profile', true, startWriteToDatabase, null);
        
        var cdr_uploader = uploader_factory.createUploader('browse-cdr', '2mb', 'uploadcdr');
        cdr_uploader = uploader_factory.onFileAdded(cdr_uploader, 'cdr', 1);
        cdr_uploader = uploader_factory.onUploading(cdr_uploader, 'cdr');
        cdr_uploader = uploader_factory.onUploadCompleted(cdr_uploader, 'cdr', true, startProfileUploader, profile_uploader);


        document.getElementById('start-upload').onclick = function() {
            db_name = $('#database-name').val();
            if ($('#feature-extraction').is(":checked"))
            {
                feature_extraction = $('#feature-extraction').val();
            }
            if(db_name == undefined || db_name == '') {
                alert("Please assign database name");
                return;
            }
            
            cdr_uploader.settings.multipart_params = {
                'name' : db_name
            }
            profile_uploader.settings.multipart_params = {
                'name' : db_name
            }
            cdr_uploader.start();
        };

    }();
}();

