function readURL(idTag, input, previewClass, errorClass ) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
      
        reader.onload = function(e) {
            
            $(previewClass).attr('src', e.target.result);
            $(errorClass).text('');
        }

        var exts = ['png','jpg','jpeg','gif'];

        var get_ext = input.files[0].name.split('.');

        get_ext = get_ext.reverse();

        if ( $.inArray ( get_ext[0].toLowerCase(), exts ) > -1 ){

            if(input.files[0].size < 2000000 ){
                reader.readAsDataURL(input.files[0]); // convert to base64 string
            }else{
                $(errorClass).text('รูปภาพต้องมีขนาดไม่เกิน 2MB');
                $(idTag).val('');
                $(previewClass).attr('src', '/images/default-user.png');
            }
        } else {
            $(errorClass).text('กรุณาเลือกไฟล์รูปภาพ');
            $(idTag).val('');
            $(previewClass).attr('src', '/images/default-user.png');
        }
        
    }
}
  