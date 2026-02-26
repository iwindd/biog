
$(document).ready(function() {

    var host = "/admin"
    if (location.hostname === "localhost" || location.hostname === "127.0.0.1"){
        host = '';
    }
    console.log(1);
    $('.roleClick').on('click', function(){
        let dataRoleAttribute = $(this).data("role");
        let dataPermissionAttribute = $(this).data("permission");

        if($(this).prop("checked") == true){
            //add permission

            $.ajax({
                url: host+'/permission/add',
                type: 'POST',
                data: {
                    'role': dataRoleAttribute,
                    'permission': dataPermissionAttribute,
                },
                dataType: "json",
                success: function(data) {
                    console.log(data);
                    if(data == 'success'){
                        alert('บันทึกข้อมูลสำเร็จ');
                    }else{
                        alert(data);
                    }
                }
            });

        }
        else if($(this).prop("checked") == false){
            //delete permission
            $.ajax({
                url: host+'/permission/delete',
                type: 'POST',
                data: {
                    'role': dataRoleAttribute,
                    'permission': dataPermissionAttribute,
                },
                dataType: "json",
                success: function(data) {
                    console.log(data);
                    if(data == 'success'){
                        alert('บันทึกข้อมูลสำเร็จ');
                    }else{
                        alert(data);
                    }
                }
            });
        }

        //console.log(dataRoleAttribute+" === "+dataPermissionAttribute);
    })

});