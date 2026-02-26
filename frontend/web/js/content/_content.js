
let stackIdRemoveFileInput = $('#stack_id_remove_file')

let stackIdRemoveFile = [];

function removeImages(id) {
    // let imageId = $('#removeImage').val()
    // imageId = imageId + '/' + id
    // $('#removeImage').val(imageId)
    // $('#image-item-' + id).hide()

    if (confirm('ต้องการลบไฟล์นี้หรือไม่ ?')) {
        $('#image-item-' + id).hide();
        stackIdRemoveFile.push(id)
        stackIdRemoveFileInput.val(stackIdRemoveFile)

    }
}

$(document).ready(function () {

    
    

    $(document).on('click', '.kv-file-remove', (event) => {
        event.stopPropagation()
        event.preventDefault()

        if (confirm('ต้องการลบไฟล์นี้หรือไม่ ?')) {
            let kartikFileRemoveButton = $(event.target).closest('.kv-file-remove')[0]
            let fileId = $(kartikFileRemoveButton).attr('data-key')

            stackIdRemoveFile.push(fileId)
            stackIdRemoveFileInput.val(stackIdRemoveFile)

            $(event.target).parents('.file-preview-frame').remove()
        }
    })

    
    

    //function for lat long
    function validateLat(valueNumber, idTag){
        let idTags = '#'+idTag;
        if(valueNumber != null && valueNumber != ''){
            valueNumber = parseFloat(valueNumber);
            if (typeof valueNumber === 'number' && valueNumber <= 90 && valueNumber >= -90){
                valueNumber = valueNumber.toString();
                valueNumberString = valueNumber.split('.');

                if(valueNumberString[1].length > 6){
                    $(idTags).addClass('is-valid');
                    $(idTags).removeClass('is-invalid');
                    $(idTags).attr("aria-invalid", "false");
                    $('.field-'+idTag+' .invalid-feedback').text('');

                }else{
                    $(idTags).addClass('is-invalid');
                    $(idTags).attr("aria-invalid", "true");
                    $('.field-'+idTag+' .invalid-feedback').text('ละติจูดต้องมีทศนิยมอย่างน้อย 6 ตำแหน่ง');
                }
            }else{
                $(idTags).addClass('is-invalid');
                $(idTags).attr("aria-invalid", "true");
                $('.field-'+idTag+' .invalid-feedback').text('กรุณากรอกละติจูดให้ถูกต้อง');
            }
        }else{
            $(idTags).addClass('is-valid');
            $(idTags).removeClass('is-invalid');
            $(idTags).attr("aria-invalid", "false");
            $('.field-'+idTag+' .invalid-feedback').text('');
            console.log('isEmpty');
        }
    }

    function validateLong(valueNumber, idTag){
        let idTags = '#'+idTag;
        if(valueNumber != null && valueNumber != ''){
            valueNumber = parseFloat(valueNumber);
            if (typeof valueNumber === 'number' && valueNumber <= 180 && valueNumber >= -180){
                valueNumber = valueNumber.toString();
                valueNumberString = valueNumber.split('.');

                if(valueNumberString[1].length > 6){
                    $(idTags).addClass('is-valid');
                    $(idTags).removeClass('is-invalid');
                    $(idTags).attr("aria-invalid", "false");
                    $('.field-'+idTag+' .invalid-feedback').text('');

                }else{
                    $(idTags).addClass('is-invalid');
                    $(idTags).attr("aria-invalid", "true");
                    $('.field-'+idTag+' .invalid-feedback').text('ลองจิจูดต้องมีทศนิยมอย่างน้อย 6 ตำแหน่ง');
                }
            }else{
                $(idTags).addClass('is-invalid');
                $(idTags).attr("aria-invalid", "true");
                $('.field-'+idTag+' .invalid-feedback').text('กรุณากรอกลองจิจูดให้ถูกต้อง');
            }
        }else{
            $(idTags).addClass('is-valid');
            $(idTags).removeClass('is-invalid');
            $(idTags).attr("aria-invalid", "false");
            $('.field-'+idTag+' .invalid-feedback').text('');
            console.log('isEmpty');
        }
    }


    $('#content-latitude').focusout(function() { 
        let lat = $('#content-latitude').val();
        let long = $('#content-longitude').val();
        validateLat(lat, 'content-latitude' );

        if(lat != null && long != null && lat != '' && long != ''){
            new renderGoogleMap({
                divGoogleMapId: 'content-google-map',
                latitudeInputId: 'content-latitude',
                longitudeInputId: 'content-longitude',
            })
        }
    });

    $('#content-longitude').focusout(function() { 
        let lat = $('#content-latitude').val();
        let long = $('#content-longitude').val();
        validateLong(long, 'content-longitude' );

        if(lat != null && long != null && lat != '' && long != ''){
            new renderGoogleMap({
                divGoogleMapId: 'content-google-map',
                latitudeInputId: 'content-latitude',
                longitudeInputId: 'content-longitude',
            })
        }
    });
    
})

window.addEventListener('load', function () {
    new renderGoogleMap({
        divGoogleMapId: 'content-google-map',
        latitudeInputId: 'content-latitude',
        longitudeInputId: 'content-longitude',
    })
})
