
$(document).ready(function() {
    /*
    $('.summernote').summernote({
        height: 300,    
        callbacks: {
            onImageUpload: function(files, editor, welEditable) {
                sendFile(files[0], editor, welEditable);
            }
        }
        
    });

    function sendFile(file, editor, welEditable) {
        data = new FormData();
        data.append("file", file);
        $.ajax({
            data: data,
            type: "POST",
            url: "/api/summerupload",
            cache: false,
            contentType: false,
            processData: false,
            success: function(url) {
                editor.insertImage(welEditable, url);
            }
        });
    } */


    $('.summernote').summernote({
        callbacks: {
            onImageUpload: function(files) {
                for(let i=0; i < files.length; i++) {
                    $.upload(files[i], '.summernote');
                }
            }
        },
        height: 300,
    });

    $('.summernote-features').summernote({
        callbacks: {
            onImageUpload: function(files) {
                for(let i=0; i < files.length; i++) {
                    $.upload(files[i], '.summernote-features');
                }
            }
        },
        height: 300,
    });

    $('.summernote-benefit').summernote({
        callbacks: {
            onImageUpload: function(files) {
                for(let i=0; i < files.length; i++) {
                    $.upload(files[i], '.summernote-benefit');
                }
            }
        },
        height: 300,
    });

    $('.summernote-other_information').summernote({
        callbacks: {
            onImageUpload: function(files) {
                for(let i=0; i < files.length; i++) {
                    $.upload(files[i], '.summernote-other_information');
                }
            }
        },
        height: 300,
    });

    $('.summernote-product_features').summernote({
        callbacks: {
            onImageUpload: function(files) {
                for(let i=0; i < files.length; i++) {
                    $.upload(files[i], '.summernote-product_features');
                }
            }
        },
        height: 300,
    });

    $('.summernote-description').summernote({
        callbacks: {
            onImageUpload: function(files) {
                for(let i=0; i < files.length; i++) {
                    $.upload(files[i], '.summernote-description');
                }
            }
        },
        height: 300,
    });

    $('.summernote-travel_information').summernote({
        callbacks: {
            onImageUpload: function(files) {
                for(let i=0; i < files.length; i++) {
                    $.upload(files[i], '.summernote-travel_information');
                }
            }
        },
        height: 300,
    });

    $('.summernote-data_protection').summernote({
        callbacks: {
            onImageUpload: function(files) {
                for(let i=0; i < files.length; i++) {
                    $.upload(files[i], '.summernote-data_protection');
                }
            }
        },
        height: 300,
    });

    
        
    $.upload = function (file, classes) {
        let out = new FormData();
        out.append('file', file, file.name);
    
        $.ajax({
            method: 'POST',
            url: "/api/summerupload",
            contentType: false,
            cache: false,
            processData: false,
            data: out,
            success: function (img) {
                $(classes).summernote('insertImage', img);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error(textStatus + " " + errorThrown);
            }
        });
    };

});