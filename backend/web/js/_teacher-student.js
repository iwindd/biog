var thisUrl = document.URL;
var host = "/admin"
if (location.hostname === "localhost" || location.hostname === "127.0.0.1"){
    host = '';
}

var school_id = $('#school-id').val();
if(!school_id){
    school_id = 0;
}
var studentAPI = host+"/api/studentschoolall?school_id="+school_id;
var studentTags = "";
$.getJSON( studentAPI)
    .done(function( data ) {
        studentTags =  data;
    $( ".student-name-autocomplete" ).autocomplete({
        source: studentTags
    });

    $(".dynamicform_student").on('afterInsert', function(e, item) {
        $( ".student-name-autocomplete" ).autocomplete({
            source: studentTags
        });
    });
    
});