
function UploaderFactory(pending, success) {   
    
    this.wrapper_pending_class = pending;
    this.wrapper_success_class = success;
}

UploaderFactory.prototype.foo = function() {
    
}

UploaderFactory.prototype.createUploader = function(element, chunk_size, url) {
    var uploader = new plupload.Uploader({
        browse_button: element,
        url: url,
        chunk_size: chunk_size,
        max_retries: 3,
        prevent_duplicates: true,
        multi_selection: false,
        filters: {
          mime_types : [
                { title : "CDR files", extensions : "txt,csv" }
            ]
        },
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    uploader.init();
    return uploader;
}

UploaderFactory.prototype.clear = function() {
    $("[id*=-uploader-wrapper]").removeClass(this.wrapper_success_class);
    $("[id*=-uploader-wrapper]").addClass(this.wrapper_pending_class);
    
    var progress_bar = $("[id*=-progress-bar]");
    progress_bar.attr('current', 0);
    progress_bar.css('width', '0%');
}

UploaderFactory.prototype.onFileAdded = function(uploader, target, max_upload) {
    var self = this;
    uploader.bind('FilesAdded', function(up, files) {
        self.clear();

        var html = '';
        $()

        if(up.files.length > max_upload) {
            up.splice(0,1);
        }

        var file = files[0];
        html += '<li id="' + file.id + '">' + file.name + ' (' + plupload.formatSize(file.size) + ') <b></b></li>';

        document.getElementById(target + '-list').innerHTML = html;
    });
    return uploader;
}

UploaderFactory.prototype.onUploading = function(uploader, target) {
    uploader.bind('UploadProgress', function(up, file) {
        document.getElementById(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
        var progress_bar = $('#' + target + '-progress-bar');
        var new_val = parseInt(file.percent);
        progress_bar.attr('current', new_val);
        progress_bar.css('width', new_val + '%');
    });
    return uploader;
}

UploaderFactory.prototype.onError = function(uploader) {
    uploader.bind('Error', function(up, err) {
        document.getElementById('console').innerHTML += "\nError #" + err.code + ": " + err.message;
    });
    return uploader;
}

UploaderFactory.prototype.onUploadCompleted = function(uploader, target, doFunction, fx, param) {
    var self = this;
    uploader.bind('UploadComplete', function(up, files) {
        try {
            $('#' + target + '-uploader-wrapper').removeClass(self.wrapper_pending_class);
            $('#' + target + '-uploader-wrapper').addClass(self.wrapper_success_class);
        } catch (err) {

        } finally {
            if(doFunction) {
                fx(param);
            }
        }
        
        
    });

    return uploader;
}

