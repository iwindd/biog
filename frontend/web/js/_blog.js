
let stackIdRemovePictureInput = $('#removeImage');

let stackIdRemovePicture = [];

let stackIdRemoveDocumentInput = $('#removeDocument');

let stackIdRemoveDocument = [];

function removeImages(id) {
    // let imageId = $('#removeImage').val()
    // imageId = imageId + '/' + id
    // $('#removeImage').val(imageId)
    // $('#image-item-' + id).hide()

    if (confirm('ต้องการลบไฟล์นี้หรือไม่ ?')) {
        $('#image-item-' + id).hide();
        stackIdRemovePicture.push(id)
        stackIdRemovePictureInput.val(stackIdRemovePicture)

    }
}

function removeDocuments(id) {
    // let imageId = $('#removeImage').val()
    // imageId = imageId + '/' + id
    // $('#removeImage').val(imageId)
    // $('#image-item-' + id).hide()

    if (confirm('ต้องการลบไฟล์นี้หรือไม่ ?')) {
        $('#document-item-' + id).hide();
        stackIdRemoveDocument.push(id)
        stackIdRemoveDocumentInput.val(stackIdRemoveDocument)

    }
}

$(document).ready(function () {
    

    $(document).on('click', '.field-blog-picture_path .kv-file-remove', (event) => {
        event.stopPropagation()
        event.preventDefault()

        if (confirm('ต้องการลบภาพหน้าปกนี้ใช่หรือไม่ ?')) {
            $('#deletePic').val(1);
            $(event.target).parents('.file-preview-frame').remove()
        }
    })

    $(document).on('click', '.field-blog-files .kv-file-remove', (event) => {
        event.stopPropagation()
        event.preventDefault()

        if (confirm('ต้องการลบไฟล์นี้หรือไม่ ?')) {
            let kartikFileRemoveButton = $(event.target).closest('.kv-file-remove')[0]
            let fileId = $(kartikFileRemoveButton).attr('data-key')

            if(!fileId){
                $('#deletePic').val(1);
            }

            stackIdRemovePicture.push(fileId)
            stackIdRemovePictureInput.val(stackIdRemovePicture)

            $(event.target).parents('.file-preview-frame').remove()
        }
    })

    $(document).on('click', '.field-blog-document .kv-file-remove', (event) => {
        event.stopPropagation()
        event.preventDefault()

        if (confirm('ต้องการลบไฟล์นี้หรือไม่ ?')) {
            let kartikFileRemoveButton = $(event.target).closest('.kv-file-remove')[0]
            let fileId = $(kartikFileRemoveButton).attr('data-key')

            if(!fileId){
                $('#deletePic').val(1);
            }

            stackIdRemoveDocument.push(fileId)
            stackIdRemoveDocumentInput.val(stackIdRemoveDocument)

            $(event.target).parents('.file-preview-frame').remove()
        }
    })
})