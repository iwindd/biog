var host = "";
if (location.hostname === "localhost" || location.hostname === "127.0.0.1"){
    host = '';
}

$('.field-school-add_new').hide();
$('.field-school-name').hide();
$('.field-profile-major').hide();
$('.field-profile-class').hide();
$('#school-info').hide();

var st = $('#studentteacher-teacher');
var addNew = $('input[name="School[add_new]"]:checked');
if(addNew){
    if(addNew.length > 0){
        $('.field-school-add_new').show();
        $('.field-school-name').show();  
    }
}
$('.field-studentteacher-teacher').hide();
var roleDefault = $('input[name="Users[role]"]:checked').val();
if(roleDefault){
    if(roleDefault == 'teacher'){
        $('.field-studentteacher-teacher').hide();
        $('.field-school-add_new').show();
        $('.field-profile-major').show();
       
    }else if(roleDefault == 'student'){
        $('.field-studentteacher-teacher').show();
        $('.field-profile-class').show();
    }
}
//console.log(roleDefault);

//copy invite link
$('.copyInvite').on('click', function(){
    let element = $('#profile-invite_friend');
    copyToClipboard(element)
});

$(".btn-upload-profile").click(function() {
    $("input[id='profile-picture']").click();
});

$("#profile-picture").change(function() {
    readURL('#profile-picture', this, '.profile-image', '.image-error');
});

$('input[name="Users[role]"]').click(function(){
    let idTag = "#school";
    if(this.value == 'teacher'){
        $('.field-school-add_new').show();
        $('.field-studentteacher-teacher').hide();
        $('.field-profile-class').hide();
        $('.field-profile-major').show();
    }

    if(this.value == 'student'){
        $('.field-school-add_new').hide();
        $('.field-school-name').show();
        $('.field-profile-major').hide();
        $('.field-profile-class').show();
        $('.field-studentteacher-teacher').show();

        $('input[name="School[add_new]"]').prop('checked', false);

        $('#add_new_school').prop('checked', false);

        $('#school-info').hide();

        $('#profile-major').val("");
        $(idTag+'-name').val("");
        $(idTag+'-phone').val("");
        $(idTag+'-address').val("");
        $(idTag+'-zipcode_id').val("");
        $(idTag+'-subdistrict_id').val("");
        $(idTag+'-district_id').val("");
        $(idTag+'-province_id').val("");

        $(idTag+'-name').prop('readonly', true);
        $(idTag+'-phone').prop('readonly', true);
        $(idTag+'-address').prop('readonly', true);
        $(idTag+'-zipcode_id').prop('readonly', true);
        $(idTag+'-subdistrict_id').prop('disabled', true);
        $(idTag+'-district_id').prop('disabled', true);
        $(idTag+'-province_id').prop('disabled', true);
    }
});

$('#add_new_school').click(function(){
    let idTag = "#school";

    console.log($('#add_new_school').is(':checked'));
    let checked = $('#add_new_school').is(':checked');

    if(checked){

        //$(idTag+'-school_id').val("").trigger("change");
        $('#school-info').show();;
        $('.field-school-name').show();
        $(idTag+'-name').val("");
        $(idTag+'-phone').val("");
        $(idTag+'-address').val("");
        $(idTag+'-zipcode_id').val("");
        $(idTag+'-subdistrict_id').val("");
        $(idTag+'-district_id').val("");
        $(idTag+'-province_id').val("");

        $(idTag+'-name').prop('readonly', false);
        $(idTag+'-phone').prop('readonly', false);
        $(idTag+'-address').prop('readonly', false);
        $(idTag+'-zipcode_id').prop('readonly', false);
        $(idTag+'-subdistrict_id').prop('disabled', false);
        $(idTag+'-district_id').prop('disabled', false);
        $(idTag+'-province_id').prop('disabled', false);

        $(idTag+'-zipcode_id').empty();
        $(idTag+'-subdistrict_id').empty();
        $(idTag+'-district_id').empty();

        $(idTag+"-district_id").append(
            $('<option></option>')
            .attr("value", '')
            .html("กรุณาเลือกอำเภอ")
        );
        $(idTag+"-subdistrict_id").append(
            $('<option></option>')
            .attr("value", '')
            .html("กรุณาเลือกตำบล")
        );

        $(idTag+'-school_id').val("").trigger("change");

        //$('input[name="School[add_new]"]').prop('checked', true);

    }else{
        $('#school-info').hide();
    }

});

$('#school-school_id').on('change', function(){
    let idTag = "#school";
    //$('#add_new_school').prop('checked', false);
    if(this.value){
        //console.log(this.value);

        $(idTag+'-name').val("");
        $(idTag+'-phone').val("");
        $(idTag+'-address').val("");

        $(idTag+'-name').prop('readonly', true);
        $(idTag+'-phone').prop('readonly', true);
        $(idTag+'-address').prop('readonly', true);
        $(idTag+'-zipcode_id').prop('readonly', true);
        $(idTag+'-subdistrict_id').prop('disabled', true);
        $(idTag+'-district_id').prop('disabled', true);
        $(idTag+'-province_id').prop('disabled', true);

        if(st){
            $('#studentteacher-teacher').empty();
            $("#studentteacher-teacher").append(
                $('<option></option>')
                .attr("value", '')
                .html("เลือกคุณครูที่ปรึกษา")
            );
        }

        $.ajax({
            method: "GET",
            url: host+"/api/findschool?id=" + this.value,
            cache: false,
            dataType: "json",
            contentType: 'application/json; charset=utf-8',
            error: function (err) { },
            success: function (res) {
                if(res.school.name){
                    $('#school-name').val(res.school.name);
                }
                if(res.school.phone){
                    $(idTag+'-phone').val(res.school.phone);
                }
                if(res.school.address){
                    $(idTag+'-address').val(res.school.address);
                }
                if(res.school.province_id){
                    $(idTag+"-province_id").val(res.school.province_id);
                }

                for (let index = 0; index < res.teacher.length; index++) {
                    $('#studentteacher-teacher').append(
                    $('<option></option>')
                        .attr("value", + res.teacher[index].id)
                        .html(res.teacher[index].name)
                        .attr('selected', false)
                    );
                }

                $('#add_new_school').prop('checked', false);
    
                $('#school-info').hide();

                if(res.school.district_id){
                    if(res.school.subdistrict_id){
                        showSubDistrictData(idTag, res.school.district_id, res.school.subdistrict_id);
                    }else{
                        showDistrictData(idTag, res.school.district_id);
                    }
                }
            },
        });
    }else{
        $(idTag+'-name').val("");
        $(idTag+'-phone').val("");
        $(idTag+'-address').val("");
        $(idTag+'-zipcode_id').val("");
        $(idTag+'-subdistrict_id').val("");
        $(idTag+'-district_id').val("");
        $(idTag+'-province_id').val("");

        $(idTag+'-zipcode_id').empty();
        $(idTag+'-subdistrict_id').empty();
        $(idTag+'-district_id').empty();

        $(idTag+"-district_id").append(
            $('<option></option>')
            .attr("value", '')
            .html("กรุณาเลือกอำเภอ")
        );
        $(idTag+"-subdistrict_id").append(
            $('<option></option>')
            .attr("value", '')
            .html("กรุณาเลือกตำบล")
        );
        
        if(st){
            $('#studentteacher-teacher').empty();
            $("#studentteacher-teacher").append(
                $('<option></option>')
                .attr("value", '')
                .html("เลือกคุณครูที่ปรึกษา")
            );
        }
    }
});

//register page
$('#biogand-acept').on('click', function(){
    $('#users-accept_biog').prop('checked', true);
    $('#users-accept_biog').removeClass('is-invalid').addClass('is-valid');
});


$('#biogand-condition').on('click', function(){
    $('#users-accept_condition').prop('checked', true);
    $('#users-accept_condition').removeClass('is-invalid').addClass('is-valid');
});

function showDistrictData(idTag, districtId) {
    //$("#district").prop( "disabled", false );

    if($(idTag+'-zipcode_id')){
        $(idTag+'-zipcode_id').val("");
    }

    var province_id = $(idTag+"-province_id").val()
    $.ajax({
        method: "GET",
        url: host+"/api/district?province_id=" + province_id,
        cache: false,
        dataType: "json",
        contentType: 'application/json; charset=utf-8',
        error: function (err) { },
        success: function (res) {
            $(idTag+"-district_id").empty();
            $(idTag+"-subdistrict_id").empty();
            $(idTag+"-subdistrict_id").addClass('is-invalid');
            $(idTag+"-subdistrict_id").attr("aria-invalid", "true");
            $(idTag+"-district_id").append(
                $('<option></option>')
                .attr("value", '')
                .html("กรุณาเลือกอำเภอ")
            );
            $(idTag+"-subdistrict_id").append(
                $('<option></option>')
                .attr("value", '')
                .html("กรุณาเลือกตำบล")
            );
            for (let index = 0; index < res.data.length; index++) {
                $(idTag+"-district_id").append(
                $('<option></option>')
                    .attr("value", + res.data[index].id)
                    .html(res.data[index].name)
                    .attr('selected', false)
                );
            }

            $(idTag+"-district_id").val(districtId);

        },
    });
}

function showSubDistrictData(idTag, districtId, subdistrictId ) {
    //$("#sub_district").prop( "disabled", false );

    if($(idTag+'-zipcode_id')){
        $(idTag+'-zipcode_id').val("");
    }

    var province_id = $(idTag+"-province_id").val()
    $.ajax({
        method: "GET",
        url: host+"/api/district?province_id=" + province_id,
        cache: false,
        dataType: "json",
        contentType: 'application/json; charset=utf-8',
        error: function (err) { },
        success: function (res) {
            $(idTag+"-district_id").empty();
            $(idTag+"-subdistrict_id").empty();
            $(idTag+"-subdistrict_id").addClass('is-invalid');
            $(idTag+"-subdistrict_id").attr("aria-invalid", "true");
            $(idTag+"-district_id").append(
                $('<option></option>')
                .attr("value", '')
                .html("กรุณาเลือกอำเภอ")
            );
            $(idTag+"-subdistrict_id").append(
                $('<option></option>')
                .attr("value", '')
                .html("กรุณาเลือกตำบล")
            );
            for (let index = 0; index < res.data.length; index++) {
                $(idTag+"-district_id").append(
                $('<option></option>')
                    .attr("value", + res.data[index].id)
                    .html(res.data[index].name)
                    .attr('selected', false)
                );
            }

            $(idTag+"-district_id").val(districtId);

       

            var district_id = $(idTag+"-district_id").val()
            $.ajax({
                method: "GET",
                url: host+"/api/subdistrict?district_id=" + district_id,
                cache: false,
                dataType: "json",
                contentType: 'application/json; charset=utf-8',
                error: function (err) { },
                success: function (res) {
                    $(idTag+"-subdistrict_id").empty();
                    $(idTag+"-subdistrict_id").append(
                        $('<option></option>')
                        .attr("value", '')
                        .html("กรุณาเลือกตำบล")
                    );
                    for (let index = 0; index < res.data.length; index++) {
                        $(idTag+"-subdistrict_id").append(
                        $('<option></option>')
                            .attr("value", + res.data[index].id)
                            .html(res.data[index].name)
                            .attr('selected', false)
                        );
                    }

                    $(idTag+"-subdistrict_id").val(subdistrictId);

                    showZipcode(idTag);
                },
            });

        },
    });
}
function copyToClipboard(element) {
    var $temp = $("<input>");
    $("body").append($temp);
    $temp.val($(element).val()).select();
    document.execCommand("copy");
    $temp.remove();
}

