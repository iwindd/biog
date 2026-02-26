var host = "/admin"
if (location.hostname === "localhost" || location.hostname === "127.0.0.1"){
    host = '';
}
$("#search-region_id").on('change', function () {
    showProvince("#search");
});

$("#profile-region_id").on('change', function () {
    showProvince("#profile");
});

$("#content-region_id").on('change', function () {
    showProvince("#content");
});

$("#search-province_id").on('change', function () {
    showDistrict("#search");
});

$("#profile-province_id").on('change', function () {
    showDistrict("#profile");
});

$("#content-province_id").on('change', function () {
    showDistrict("#content");
});

$("#profile-district_id").on('change', function () {
    showSubDistrict("#profile");
});

$("#search-district_id").on('change', function () {
    showSubDistrict("#search");
});

$("#content-district_id").on('change', function () {
    showSubDistrict("#content");
});

$("#profile-subdistrict_id").on('change', function () {
    showZipcode("#profile");
});

$("#search-subdistrict_id").on('change', function () {
    showZipcode("#search");
});

$("#content-subdistrict_id").on('change', function () {
    showZipcode("#content");
});


$("#school-region_id").on('change', function () {
    showProvince("#school");
});

$("#school-province_id").on('change', function () {
    showDistrict("#school");
});

$("#school-district_id").on('change', function () {
    showSubDistrict("#school");
});

$("#school-subdistrict_id").on('change', function () {
    showZipcode("#school");
});


let  region_init = $('#region_init');
if(region_init){
    let region_id = region_init.val();
    $("#search-region_id").val(region_id);
    showProvince("#search");
}

function showProvince(idTag) {
    //$(idTag+"-district_id").prop( "disabled", true );
    //$("#province").prop( "disabled", false );
    if($(idTag+'-zipcode_id')){
        $(idTag+'-zipcode_id').val("");
    }

    if ($(idTag+"-region").val() == 0) {
        //$("#province").prop( "disabled", true );
        $(idTag+"-province_id").val(0)
    }
    var region_id = $(idTag+"-region_id").val();
    //console.log(region_id);
    $.ajax({
        method: "GET",
        url: host+"/api/province?region_id=" + region_id,
        cache: false,
        dataType: "json",
        contentType: 'application/json; charset=utf-8',
        error: function (err) { },
        success: function (res) {

            $(idTag+"-province_id").empty();
            $(idTag+"-district_id").empty();
            $(idTag+"-subdistrict_id").empty();
            if(idTag != '#search'){
                $(idTag+"-district_id").addClass('is-valid');
                $(idTag+"-district_id").attr("aria-invalid", "true");

                $(idTag+"-subdistrict_id").addClass('is-valid');
                $(idTag+"-subdistrict_id").attr("aria-invalid", "true");
            }


            $(idTag+"-province_id").append(
                $('<option></option>')
                .attr("value", '')
                .html("กรุณาเลือกจังหวัด")
            );
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

                $(idTag+"-province_id").append(
                $('<option></option>')
                    .attr("value", + res.data[index].id)
                    .html(res.data[index].name)
                    .attr('selected', false)
                );
                //}

            }
            
            //for search page
            let  province_init = $('#province_init');
            if(province_init){
                let province_id = province_init.val();
                $("#search-province_id").val(province_id);
                showDistrict("#search");
            }
        //showDistrict(district_id);
        },
    });
}

function showDistrict(idTag) {
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
            // $(idTag+"-subdistrict_id").addClass('is-valid');
            // $(idTag+"-subdistrict_id").attr("aria-invalid", "true");
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

            //for search page
            let  district_init = $('#district_init');
            if(district_init){
                let district_id = district_init.val();
                $("#search-district_id").val(district_id);
                showSubDistrict("#search");
            }
        },
    });
}

function showSubDistrict(idTag) {
    //$("#sub_district").prop( "disabled", false );

    if($(idTag+'-zipcode_id')){
        $(idTag+'-zipcode_id').val("");
    }

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

            //for search page
            let  subdistrict_init = $('#subdistrict_init');
            if(subdistrict_init){
                let subdistrict_id = subdistrict_init.val();
                $("#search-subdistrict_id").val(subdistrict_id);
                showZipcode("#search");
            }

            //showZipcode()
        },
    });
}

function showZipcode(idTag) {
    var subdistrict_id = $(idTag+"-subdistrict_id").val()

    $.ajax({
        method: "GET",
        url: host+"/api/zipcode?subdistrict_id=" + subdistrict_id,
        cache: false,
        dataType: "json",
        contentType: 'application/json; charset=utf-8',
        error: function (err) { },
        success: function (res) {
            if(idTag == '#content' || idTag == '#school' || idTag == '#search'){
                $(idTag+'-zipcode_id').val(res.data[0].zipcode);
            }else{
                $('#profile-zipcode_id').val(res.data[0].zipcode);
            }
        },
    });
}