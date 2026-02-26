function removeImages(id) {
    let imageId = $("#removeImage").val();
    imageId = imageId + "/" + id;
    $("#removeImage").val(imageId);
    $("#image-item-" + id).hide();
}

function removeDocuments(id) {
    let documentId = $("#removeDocument").val();
    documentId = documentId + "/" + id;
    $("#removeDocument").val(documentId);
    $("#document-item-" + id).hide();
}

$(document).on("click", ".fileinput-remove", function() {
    $('#deletePic').val(1);
});

var typeKnowledge = $('#knowledge-type').val();
if(typeKnowledge){
    if(typeKnowledge == 'Infographic'){
        $('.field-knowledge-path_url').hide();
    }else if(typeKnowledge == 'Video'){
        $('.field-knowledge-path_picture').hide();
    }
}
// console.log(typeKnowledge);

$('#knowledge-type').on('click', function(){
    if(this.value == 'Video'){
        $('.field-knowledge-path_url').show();
        $('.field-knowledge-path_picture').hide();
    }else if(this.value == 'Infographic'){
        $('.field-knowledge-path_picture').show();
        $('.field-knowledge-path_url').hide();
    }

});


$(document).on("click", ".select2-selection__choice__remove", function() {
    let dataId = $(this).closest('.select2-selection__choice').attr('title');
    if(dataId){
        let taxonomy = $('#removeTaxonomy').val();
        if(taxonomy){
            dataId = taxonomy+"/"+dataId;
            $('#removeTaxonomy').val(dataId);
        }else{
            $('#removeTaxonomy').val(dataId);
        }

    }
});

$(document).on("click", ".field-content-taxonomy .select2-selection__clear", function() {
    $('#removeTaxonomy').val('all');
});


$('.field-content-taxonomy .select2-selection__clear').on('click', function(){
    $('#removeTaxonomy').val('all');
})