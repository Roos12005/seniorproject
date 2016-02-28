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

    function foo() {

    }

    function startProfileUploader(uploader) {
        uploader.start();
    }

    /**
     *  @brief Main function of this file
     *
     *  @param undefined
     *  @return void
     */
     !function(undefined){
        uploader_factory = new UploaderFactory('alert-warning', 'alert-success');


        var profile_uploader = uploader_factory.createUploader('browse-profile', '2mb', 'uploadprofile');
        profile_uploader = uploader_factory.onFileAdded(profile_uploader, 'profile', 1);
        profile_uploader = uploader_factory.onUploading(profile_uploader, 'profile');
        profile_uploader = uploader_factory.onUploadCompleted(profile_uploader, 'profile', false, null, null);
        
        var cdr_uploader = uploader_factory.createUploader('browse-cdr', '2mb', 'uploadcdr');
        cdr_uploader = uploader_factory.onFileAdded(cdr_uploader, 'cdr', 1);
        cdr_uploader = uploader_factory.onUploading(cdr_uploader, 'cdr');
        cdr_uploader = uploader_factory.onUploadCompleted(cdr_uploader, 'cdr', true, startProfileUploader, profile_uploader);


        document.getElementById('start-upload').onclick = function() {
            var db_name = $('#database-name').val();
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

