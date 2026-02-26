var thisUrl = document.URL;
var host = "/admin"
if (location.hostname === "localhost" || location.hostname === "127.0.0.1"){
    host = '';
}

var flickerAPI = host+"/api/teacher";
var availableTags = "";
$.getJSON( flickerAPI)
    .done(function( data ) {
    availableTags =  data;
    $( ".thacher-name-autocomplete" ).autocomplete({
        source: availableTags
    });

    $(".dynamicform_inner").on('afterInsert', function(e, item) {
        
        $( ".thacher-name-autocomplete" ).autocomplete({
            source: availableTags
        });

        //$('.btn-info').css("display", "none");

        $(".dynamicform_inner .thacher-item .btn-info").last().css( "display", "none" );
        $(".dynamicform_inner .thacher-item .btn-warning").last().css( "display", "none" );
    });

});


var studentAPI = host+"/api/student";
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