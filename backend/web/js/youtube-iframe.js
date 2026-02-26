$('#addLink').on('click', function(e){
    let link = $("#link").val();
    if(link != "" && link.indexOf('youtube.com') != -1 ){
        let videoId = getId(link);
        $("#youtube").append('<div class="col-md-4 youtube-item card-iframe-item img-item'+$.now()+'" data-id="'+$.now()+'"><button type="button" class="close delete-link-iframe" aria-label="ลบไฟล์นี้"><span aria-hidden="true">&times;</span></button><iframe width="100%" height="200" src="https://www.youtube.com/embed/'+videoId+'" frameborder="0" allowfullscreen=""></iframe> <input type="hidden" name="link[]" value="https://www.youtube.com/embed/'+videoId+'"></div>');
        $("#link").val('');
    }else{
        alert("ลิงค์ : "+link+" ไม่ถูกต้อง!");
    }
});

//remove link for new youtube
$(document).on('click', "button.delete-link-iframe", function() {
    let divId = $(this).parent("div").attr("data-id");
    $('.img-item'+divId).remove();     
});

function getId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);

    return (match && match[2].length === 11)
      ? match[2]
      : null;
}

function removeVideos(id){
    let videoId = $('#removeVideo').val();
    videoId = videoId+"/"+id;
    $('#removeVideo').val(videoId);
    $('#video-item-'+id).hide();
 }