var map;
var domainMap = "";
var mapContent;
var n = 0;
var markers = [];
var numResult=0;
var idMarker = 0;
var languageCurrent = "th";
var pathname = window.location.pathname; 
if(pathname.indexOf("/en/") != -1){
  languageCurrent = "en";
}
var urlParams = new URLSearchParams(window.location.search);
var host = ""
if (location.hostname === "localhost" || location.hostname === "127.0.0.1"){
    host = '';
}
var iconBase = domainMap + '/images/marker/';
var icons = {
    plant: iconBase + '01_Plan.png',
    animals: iconBase + '02_animal.png',
    fungi: iconBase + '03_fungi.png',
    expert: iconBase + '04_tk.png',
    ecotourism: iconBase + '05_ecotourism.png',
    product: iconBase + '06_product.png',
};
var nameDataBase={ 
    plant: 'พืช',
    animals: 'สัตว์',
    fungi: 'จุลินทรีย์',
    expert: 'ภูมิปัญญา / ปราชญ์ Expert',
    ecotourism: 'การท่องเที่ยวเชิงนิเวศ',
    product: 'ผลิตภัณฑ์ชุมชน',
};
var map_list_result = $('#map-list-result');

var myLat = 13.7471899;
var myLng = 100.5371793;
var map_div = 'map';
var mapConfig = "";
var zoom = 9;
//get param 
var searchData =  {
    'region_id':'',
    'province_id': '',
    'district_id': '',
    'subdistrict_id': '',
    'keyword': '',
    'type': '1,2,3,4,5,6',
    'page':1,
}

$(document)
// .ajaxStart(function () {
//   var siteMap=document.getElementById('map');
//   if(siteMap!=null){
//     $.preloader.start({
//       modal: true,
//       src : domainMap+'/images/preloader/sprites.png',
//         width : 64,
//         frames : 12
//     });
//   }
// })
// .ajaxStop(function () {
//   setTimeout(function() {
//   $.preloader.stop();   
//   },1000);
// });

$( document ).ready(function() { 
  
    var script = document.createElement('script');
        script.type = 'text/javascript';
    script.src = 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBDzKtsv5PEXijgyFZtaHx3mz42vcEoqDQ&callback=initMap&libraries=places';
        document.body.appendChild(script);

    $(".list-item-map .info").click(function(e){
        e.preventDefault();
        var key = $(this).attr("data-id");

    }); 
     
    window.onresize = function(event) {
        var w = window.innerWidth;
        var e = window.clientWidth;
        let width = w || e;
        if(width>=768 && $('#panel-map').hasClass('out')==true){
            $('#panel-map').removeClass('out');
            document.getElementById("respon-btn-panel").innerHTML="<i class='fa fa-angle-double-left' aria-hidden='true'></i>";
        }
        var siteMap=document.getElementById('map');
        if(siteMap!=null){
            // var heightBrowser = $(window).height();
            // heightBrowser = heightBrowser - 136;//header
            // heightBrowser = heightBrowser - 248;//filter block
            // $('.list-result').attr('style','height:'+ heightBrowser +'px;');
        }
    };

    

});

const initialMap = (myLat, myLng) => {
    // let divGoogleMap = document.getElementById('map')
    // googleMap = new google.maps.Map(divGoogleMap, {
    //     zoom: 15,
    //     position: { lat: myLat, lng: myLng },
    //     center: { lat: myLat, lng: myLng },
    // })
    

    googleMapMarker = new google.maps.Marker({
        map: mapConfig,
        draggable: false,
        animation: google.maps.Animation.DROP,
        position: { lat: myLat, lng: myLng },
        //center: { lat: myLat, lng: myLng },
        //scaledSize: new google.maps.Size(40, 45),
    })

    //mapConfig.setZoom(15);
    //googleMapMarker.setZoom(15);

    var i = 0 ;
    var infowindow = new google.maps.InfoWindow();
    var myContent = '<div id="content" class="block-detail-location" >'
                        +'<div class="head-picture">'
                        +'</div>'
                        +'<div class="body-info">'
                        +'<h4 id="firstHeading" class="firstHeading" title="ตำแหน่งของฉัน">ตำแหน่งของฉัน</h4>'
                        +'</div>'+'</div>';
    google.maps.event.addListener(googleMapMarker, 'click', (function(googleMapMarker) {
        
        return function() {
          infowindow.setContent(myContent);
          infowindow.open(mapConfig, googleMapMarker);

        }
    })(googleMapMarker, i)); 
}

function initMap() {

    mapConfig = new google.maps.Map(document.getElementById(map_div), {
        zoom: zoom,
        center: { lat: myLat, lng: myLng }
    });
    checkSearchParams();
    // marker = new google.maps.Marker({
    //     map: mapConfig,
    //     draggable: true,
    //     animation: google.maps.Animation.DROP,
    //     position: { lat: myLat, lng: myLng }
    // });
    // marker.addListener('click', toggleBounce);

    //get lat, lng from map
    // google.maps.event.addListener(marker, 'dragend', function (evt) {
    //     // $("#community-latitude").val(evt.latLng.lat().toFixed(7))
    //     // $("#community-longitude").val(evt.latLng.lng().toFixed(7))
    // })

    
}


function findLoaction(text = "") {
      
    if (text == "") {

        let myLatlng = new google.maps.LatLng(myLat,myLng);
        // // console.log(zoom);
        // // mapConfig = new google.maps.Map(document.getElementById(map_div), {
        // //     zoom: zoom,
        // //     center: { lat: myLat, lng: myLng }
        // // });
        mapConfig.setZoom(zoom);
        mapConfig.panTo(myLatlng);


        // google.maps.event.addListenerOnce(mapConfig, "idle", function() { 
        //     panTo(myLat, myLng);
        // });

        // marker = new google.maps.Marker({
        //     map: mapConfig,
        //     draggable: true,
        //     animation: google.maps.Animation.DROP,
        //     position: { lat: myLat, lng: myLng }
        // });
        // marker.addListener('click', toggleBounce);

        //get lat, lng from map
        // google.maps.event.addListener(marker, 'dragend', function (evt) {
        //     // $("#community-latitude").val(evt.latLng.lat().toFixed(7))
        //     // $("#community-longitude").val(evt.latLng.lng().toFixed(7))
        // })

    }else{

        //geocoder = new google.maps.Geocoder();
        // var mapProp = {
        //     center: new google.maps.LatLng(myLat, myLng),
        //     zoom: zoom
        // }
        // map = new google.maps.Map(
        //     document.getElementById(map_div),
        //     mapProp
        // )

        // console.log(mapConfig);

        // mapConfig.panTo(latLng);

        // mapConfig = new google.maps.Map(document.getElementById(map_div), {
        //     zoom: zoom,
        // });

        //mapConfig.setZoom(zoom);

        let service = new google.maps.places.PlacesService(mapConfig);

        var province = ['เชียงใหม่, แม่วาง, บ้านกาด', 'ลำพูน'];

        for (let index = 0; index < province.length; index++) {
            
            var request = {
                query: province[index],
                fields: ['name', 'geometry'],
            };
            service.findPlaceFromQuery(request, function (results, status) {
                //console.log(results);
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    // $('#community-latitude').val(results[0].geometry.location.lat().toFixed(7));
                    // $('#community-longitude').val(results[0].geometry.location.lng().toFixed(7));
                    createMarker(results[0]);
          
                    mapConfig.panTo(results[0].geometry.location);

                    //mapConfig.setCenter(results[0].geometry.location);
                    // console.log(zoom);
                    //mapConfig.setZoom(zoom);
                }
            });

          
            
        }
    }
}

function createMarker(place) {
    marker.setMap(null);
    marker = new google.maps.Marker({
      map: mapConfig,
      draggable: true,
      animation: google.maps.Animation.DROP,
      position: place.geometry.location
    });
    marker.addListener('click', toggleBounce);
    google.maps.event.addListener(marker, 'dragend', function (evt) {
        //   $("#community-latitude").val(evt.latLng.lat().toFixed(7))
        //   $("#community-longitude").val(evt.latLng.lng().toFixed(7))
    })
}

function toggleBounce() {
    if (marker.getAnimation() !== null) {
      marker.setAnimation(null);
    } else {
      marker.setAnimation(google.maps.Animation.BOUNCE);
    }
}

//show my location
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    } else { 
        x.innerHTML = "Geolocation is not supported by this browser.";
    }
}
  
function showPosition(position) {
    myLat = position.coords.latitude;
    myLng = position.coords.longitude;
    zoom = 20;
    //console.log('my lat: '+myLat+", My long: "+myLng)
    let myLatlng = new google.maps.LatLng(myLat, myLng);
    mapConfig.setZoom(zoom);
    mapConfig.panTo(myLatlng);
    initialMap(myLat, myLng);

}

function setLocationRegion(lat, lng, zoomNumber){
    myLat = lat;
    myLng = lng;
    zoom = zoomNumber;
    let myLatlng = new google.maps.LatLng(myLat, myLng);
    mapConfig.setZoom(zoom);
    mapConfig.panTo(myLatlng);
}

function infoPlaceShow(id){
    google.maps.event.trigger(markers[id], 'click');
    map.panTo(markers[id].position);
}


function updateSearchParamInSearchData() {
    let url = '';
    url = updateURLParameter(window.location.href, 'region_id', searchData.region_id);
    url = updateURLParameter(url, 'province_id', searchData.province_id);
    url = updateURLParameter(url, 'district_id', searchData.district_id);
    url = updateURLParameter(url, 'subdistrict_id', searchData.subdistrict_id);
    url = updateURLParameter(url, 'keyword', searchData.keyword);
    url = updateURLParameter(url, 'type', searchData.type);
   // url = updateURLParameter(url, 'page', searchData.page);
    return url;
}
function checkSearchParams(){

    if (urlParams.has('region_id')==true){
        searchData.region_id = urlParams.get('region_id');
        $('#map-region_id').val(searchData.region_id);
        showProvinceMap('#map','get');
    }
    if (urlParams.has('province_id') == true) {
        searchData.province_id = urlParams.get('province_id');

        showDistrictMap('#map', 'get');
    }
    if (urlParams.has('district_id') == true) {
        searchData.district_id = urlParams.get('district_id');

        showSubDistrictMap('#map', 'get');
    }
    if (urlParams.has('subdistrict_id') == true) {
        searchData.subdistrict_id = urlParams.get('subdistrict_id');
        //$('#map-subdistrict_id').val(searchData.subdistrict_id);
    }
    if (urlParams.has('keyword') == true) {
        searchData.keyword = urlParams.get('keyword');
        $('#keyword-text').val(searchData.keyword);
    }
    if (urlParams.has('type') == true) {
        searchData.type = urlParams.get('type');
    }
    // if (urlParams.has('page') == true) {
    //     searchData.page = urlParams.get('page');
    // }
    search();
}
$('#map-region_id').on('change',function(){
    searchData.region_id = $(this).val();
    searchData.province_id = "";
    searchData.district_id = "";
    searchData.subdistrict_id = "";
    search();
    showProvinceMap('#map')
    
});

$('#map-province_id').on('change',function(){
    searchData.province_id = $(this).val();
    searchData.district_id = "";
    searchData.subdistrict_id = "";
    search();
    showDistrictMap('#map')
});

$('#map-district_id').on('change',function(){
    searchData.district_id = $(this).val();
    searchData.subdistrict_id = "";
    search();
    showSubDistrictMap('#map')
});
$('#map-subdistrict_id').on('change', function () {
    searchData.subdistrict_id = $(this).val();
    search();
});
$('#keyword-text').on('change', function () {
    searchData.keyword  =$(this).val();
    search();
});
$('.btn-search').on('click',function(){
    searchData.keyword = $('#keyword-text').val();
    search();
})
function checkboxType(){
   let text = "";
    let div_checkbox = $('.check-content-type').find('input');
    for (let index = 0; index < div_checkbox.length; index++) {
        // console.log(div_checkbox[index].checked);
        if (div_checkbox[index].checked==true){
            text = text+div_checkbox[index].value+",";
        }
    }
    if(text.length>0){
        text = text.substr(0,text.length-1);
    }
    searchData.type=text;
    search();
}
function readMore(element){
    $(element).attr('disabled','disabled');

    searchData.page = searchData.page +1;
    $.ajax({
        method: "GET",
        url: host + "/api/searchcontent",
        cache: false,
        data: searchData,
        dataType: "json",
        contentType: 'application/json; charset=utf-8',
        error: function (err) {
             $(element).removeAttr('disabled');
        },
        success: function (res) {
            //console.log(res);
            if(res.data.page_current >= res.data.all_page){
                $('button.btn-read-more').hide();
            }else{
                $('button.btn-read-more').show();
            }

            if (res.data.content.length>0) {

               
                setContentMapMarker(res.data);
                setContentList(res.data);
            }else{
                 searchData.page = searchData.page - 1;
            }
              $(element).removeAttr('disabled');
        },
    });
    
}
function search(){
   // console.log(searchData);

    DeleteMarkers();
    searchData.page=1;
    $('#show-list-item').html('');
    let UrlNew = '';
    UrlNew = updateSearchParamInSearchData();
    window.history.replaceState(window.location.href, "", UrlNew);
     $.ajax({
         method: "GET",
         url: host + "/api/searchcontent",
         cache: false,
         data: searchData,
         dataType: "json",
         contentType: 'application/json; charset=utf-8',
         error: function (err) {},
         success: function (res) {
             //console.log(res);
            if(res){

                if(res.data.page_current >= res.data.all_page){
                    $('button.btn-read-more').hide();
                }else{
                    $('button.btn-read-more').show();
                }

                showCountAllType(res.type_count);
                setContentMapMarker(res.data);
                setContentList(res.data);
            }
            
         },
     });
    
}
function setContentBreadcrumb(){
    $('.search-breadcrumb').html('');
    let div = 'ขอบเขต: ';
    let url = '';
    let path_name = window.location.pathname;
    if(searchData.region_id!=''){
        url = path_name + '?'
            +'region_id=' + searchData.region_id 
            + "&province_id=" 
            + "&district_id="
            + "&subdistrict_id=" 
            +"&keyword=" + searchData.keyword
            +"&type=" + searchData.type;
        let name = $("#map-region_id option:selected").text();
        div = div + '<a href="' + url + '">' + name + '</a>';
    }
    if (searchData.province_id != '') {
         url = path_name + '?'
            +'region_id=' + searchData.region_id 
            +"&province_id=" + searchData.province_id
            + "&district_id="
            + "&subdistrict_id=" 
            +"&keyword=" + searchData.keyword
            +"&type=" + searchData.type;
        let name = $("#map-province_id option:selected").text();
        div = div + ' <i class="fas fa-angle-right"></i> <a href="' + url + '">' + name + '</a>';
    }
    if (searchData.district_id != '') {
        url = path_name + '?'
            +'region_id=' + searchData.region_id 
            +"&province_id=" + searchData.province_id
            +"&district_id=" + searchData.district_id
            + "&subdistrict_id=" 
            +"&keyword=" + searchData.keyword
            +"&type=" + searchData.type;
        let name = $("#map-district_id option:selected").text();
        div = div + ' <i class="fas fa-angle-right"></i> <a href="' + url + '">' + name + '</a>';

    }
    if (searchData.subdistrict_id != '') {
        url = path_name + '?'
            +'region_id=' + searchData.region_id 
            +"&province_id=" + searchData.province_id
            +"&district_id=" + searchData.district_id
            +"&subdistrict_id=" + searchData.subdistrict_id
            +"&keyword=" + searchData.keyword
            +"&type=" + searchData.type;
        let name = $("#map-subdistrict_id option:selected").text();
        div = div + ' <i class="fas fa-angle-right"></i> <a href="' + url + '">' + name + '</a>';
    }
    $('.search-breadcrumb').html(div);
}
function getContentPath(type){
    let path = '/files/';
        if (type == 1) {
            path = path + 'content-plant/';
        } else if (type == 2) {
            path = path + 'content-animal/';
        } else if (type == 3) {
            path = path + 'content-fungi/';
        } else if (type == 4) {
            path = path + 'content-expert/';
        } else if (type == 5) {
            path = path + 'content-ecotourism/';
        } else if (type == 6) {
            path = path + 'content-product/';
        }
    return path;
           
}
function getContentTypeName(type) {
    let type_text = '';
    if (type == 1) {
        type_text = 'plant';
    } else if (type == 2) {
        type_text = 'animals';
    } else if (type == 3) {
        type_text = 'fungi';
    } else if (type == 4) {
        type_text = 'expert';
    } else if (type == 5) {
        type_text = 'ecotourism';
    } else if (type == 6) {
        type_text = 'product';
    }else{
        type_text = '-';
    }
    return type_text;

}
var contentString = [];
var index_marker = 0;
function setContentMapMarker(data){
    if (searchData.region_id == 1) {
        setLocationRegion(14.3330374, 100.3160684, 7);
    } else if (searchData.region_id == 2) {
        setLocationRegion(18.8104532, 98.2289457, 7);
    } else if (searchData.region_id == 3) {
        setLocationRegion(16.6267726, 102.3843128, 7);
    } else if (searchData.region_id == 4) {
        setLocationRegion(12.889964, 101.6111887, 7);
    } else if (searchData.region_id == 5) {
        setLocationRegion(14.3762721, 98.7934812, 7);
    } else if (searchData.region_id == 6) {
        setLocationRegion(8.3618706, 99.0700231, 7);
    }else{
        setLocationRegion(14.3330374, 100.3160684, 6);
    }   
    //setLocationRegion(myLat, myLng, 9);
    let infowindow = new google.maps.InfoWindow();
    let data_content = data.content;

    for (let index = 0; index < data_content.length; index++) {
        let path = '/files/';
        let type_text = getContentTypeName(data_content[index].type_id);
        path = getContentPath(data_content[index].type_id) + data_content[index].picture_path;
        var position_data = new google.maps.LatLng(data_content[index].latitude, data_content[index].longitude);
        contentString[index_marker] = '<div id="content" class="block-detail-location" onClick="goToViewContent(' + data_content[index].id + ',' + data_content[index].type_id + ')">'
            +'<div class="head-picture">'
            + '<img src="' + path+'">'
            +'</div>'
            +'<div class="body-info">'
            +'<h4 id="firstHeading" class="firstHeading" title="' + data_content[index].name +" "+data_content[index].province_name + '">' + data_content[index].name + '</h4>'
            +"<p class='address mb-0'>"+data_content[index].province_name + " " + data_content[index].district_name + " " + data_content[index].subdistrict_name+"</p>"
            + '<div class="detail">'+(data_content[index].description != null ? data_content[index].description:"ไม่มีข้อมูล")+"</div>"
            +'</div>'+'</div>';


        var marker = new google.maps.Marker({
            position: position_data,
            icon: { url: icons[type_text], scaledSize: new google.maps.Size(55, 57) },
            title: data_content[index].name,
            map: mapConfig
        });


        google.maps.event.addListener(marker, 'click', (function (marker, index_marker) {
            return function () {
                infowindow.setContent(contentString[index_marker]);
                infowindow.open(mapConfig, marker);
                mapConfig.setZoom(14);
                mapConfig.setCenter(marker.getPosition());
            }
        })(marker, index_marker));
        markers[index_marker] = marker;
        // markers.push(marker);
        index_marker++;
    }

}
function setContentList(data){

    data = data.content;
    for (let index = 0; index < data.length; index++) {
        let address = "";

        if(data[index].region_name){
            address = data[index].region_name;
        }

        if(data[index].region_name && data[index].province_name){
            address = data[index].region_name + " " + data[index].province_name;
        }

        if(data[index].region_name && data[index].province_name && data[index].district_name){
            address = data[index].region_name + " " + data[index].province_name + " " + data[index].district_name ;
        }

        if(data[index].region_name && data[index].province_name && data[index].district_name && data[index].subdistrict_name){
            address = data[index].region_name + " " + data[index].province_name + " " + data[index].district_name + " " + data[index].subdistrict_name;
        }
        let path = getContentPath(data[index].type_id) + data[index].picture_path;
        let type_name = getContentTypeName(data[index].type_id);
        let div = '<a href="'+('/content-'+type_name+'/'+data[index].id)+'">'
                            +'<div class="row list-item">'
                                +' <div class="col-md-4 col-sm-12 image">'
                                     + data[index].path_image
                                +' </div>'
                                 +'<div class="col-md-8 col-sm-12 detail">'
                                    +' <p class="item-name">'+data[index].name+'</p>'
                                     +'<p class="item-address">' + address + '</p>'
                                     +'<div class="item-detail">'
                                        +(data[index].description != "" && data[index].description != null ? data[index].description : 'ไม่พบข้อมูล')
                                     +'</div> '
                                 +'</div>'
                            +' </div>'
                             +'<hr/>'
                             +'</a>';
     
        
        $('#show-list-item').append(div);
    }

   

}
    
function goToViewContent(id,type){
    let type_text = getContentTypeName(type);
    window.location.href = "/content-" + type_text+"/"+id;
}
var loading_time = 0;
function setMapTypeRegion(data){
    if (searchData.region_id == 1) {
        setLocationRegion(14.3330374, 100.3160684, 7);
    } else if (searchData.region_id == 2) {
        setLocationRegion(18.8104532, 98.2289457, 7);
    } else if (searchData.region_id == 3) {
        setLocationRegion(16.6267726, 102.3843128, 7);
    } else if (searchData.region_id == 4) {
        setLocationRegion(12.889964, 101.6111887, 7);
    } else if (searchData.region_id == 5) {
        setLocationRegion(14.3762721, 98.7934812, 7);
    } else if (searchData.region_id == 6) {
        setLocationRegion(8.3618706, 99.0700231, 7);
    }
    loading_time = 200 * data.length;
    var infowindow = new google.maps.InfoWindow();
    for (let index = 0; index < data.length; index++) {
         //console.log(data[index].province_name);
            let service = new google.maps.places.PlacesService(mapConfig);

            let request = {
                query: data[index].province_name,
                fields: ['name', 'geometry'],
            };
            //OVER_QUERY_LIMIT  =  10 quota
        // (function (index) {
        //     setTimeout(function () {
        //         service.findPlaceFromQuery(request, function (results, status) {
        //             // console.log(results);
        //             if (status === google.maps.places.PlacesServiceStatus.OK) {
        //                 // console.log(results[0].geometry.location.lat());
        //                 // console.log(results[0].geometry.location.lng());

        //                 var position_data = new google.maps.LatLng(results[0].geometry.location.lat(), results[0].geometry.location.lng());

        //                 var contentString = '<div id="content" class="block-detail-location" onClick="setProvinceById(' + data[index].province_id+')">';
        //                 contentString = contentString + setBodyInfo(data[index].province_name, data[index].type_count);
        //                 contentString = contentString +'</div>';


        //                 var marker = new google.maps.Marker({
        //                     position: position_data,
        //                     //icon: { url: icons[array[i].database_name].icon, scaledSize: new google.maps.Size(35, 45) },
        //                     title: data[index].province_name,
        //                     map: mapConfig
        //                 });
        //                 let i = markers.length;
        //                 google.maps.event.addListener(marker, 'click', (function (marker, i) {
        //                     return function () {
        //                         infowindow.setContent(contentString);
        //                         infowindow.open(mapConfig, marker);
        //                     }
        //                 })(marker, i));

        //                 markers.push(marker);
        //             } else {
        //                 console.log(status);
        //             }
        //         });
        //     }, 200 * index);
        // })(index);
            
    }
}
function setMapTypeProvince(data){
    setLocationRegion(myLat, myLng, 9);
    loading_time = 300 * data.length;
    var infowindow = new google.maps.InfoWindow();
    for (let index = 0; index < data.length; index++) {
        //console.log(data[index].district_name);
            let service = new google.maps.places.PlacesService(mapConfig);

            let request = {
                query: data[index].district_name,
                fields: ['name', 'geometry'],
            };
            //OVER_QUERY_LIMIT  =  10 quota
            
    }
}

function setMapTypeDistrict(data) {
    setLocationRegion(myLat, myLng, 10);
    loading_time = 200 * data.length;
    var infowindow = new google.maps.InfoWindow();
    for (let index = 0; index < data.length; index++) {
        //console.log(data[index].subdistrict_name);
        let service = new google.maps.places.PlacesService(mapConfig);

        let request = {
            query: data[index].subdistrict_name,
            fields: ['name', 'geometry'],
        };
        //OVER_QUERY_LIMIT  =  10 quota
    }
}

function setMapTypeContent(data) {
    //console.log(data);

}

function setBodyInfo(title,data_type){
    let div = "";
    div = '<div id="bodyContent" class="body-info">' +
        '<h4 id="firstHeading" class="firstHeading" title="' + title + '">' + title + '</h4>';

    if (searchData.type.indexOf(1) != -1) {
        div = div + '<div class="info-type-count">'
            + '<img src="/images/icon/S_Plant.svg">'
            + '<span>' + data_type.plant + ' แห่ง</span>'
            + '</div>';
    }
    if (searchData.type.indexOf(2) != -1) {
        div = div + '<div class="info-type-count">'
            + '<img src="/images/icon/S_Animals.svg">'
            + '<span>' + data_type.animal + ' แห่ง</span>'
            + '</div>';
    }
    if (searchData.type.indexOf(3) != -1) {
        div = div + '<div class="info-type-count">'
            + '<img src="/images/icon/S_Funji.svg">'
            + '<span>' + data_type.fungi + ' แห่ง</span>'
            + '</div>';
    }
    if (searchData.type.indexOf(4) != -1) {
        div = div + '<div class="info-type-count">'
            + '<img src="/images/icon/S_Expert.svg">'
            + '<span>' + data_type.expert + ' แห่ง</span>'
            + '</div>';
    }
    if (searchData.type.indexOf(5) != -1) {
        div = div + '<div class="info-type-count">'
            + '<img src="/images/icon/S_Ecotourism.svg">'
            + '<span>' + data_type.ecotourism + ' แห่ง</span>'
            + '</div>';
    }
    if (searchData.type.indexOf(6) != -1) {
        div = div + '<div class="info-type-count">'
            + '<img src="/images/icon/S_Product.svg">'
            + '<span>' + data_type.product + ' แห่ง</span>'
            + '</div>';
    }
    div = div + '</div>';
    return div;
}

function setProvinceById(id){
    searchData.province_id = id;
    $('#map-province_id').val(id);
    search();
}

function setDistrictById(id){
    searchData.district_id = id;
    $('#map-district_id').val(id);
    search();
}
function setSubdistrictById(id) {
    searchData.subdistrict_id = id;
    $('#map-subdistrict_id').val(id);
    search();
}

function showCountAllType(data){
    $('#span-type-1').html(data.plant);
    $('#span-type-2').html(data.animal);
    $('#span-type-3').html(data.fungi);
    $('#span-type-4').html(data.expert);
    $('#span-type-5').html(data.ecotourism);
    $('#span-type-6').html(data.product);
}

function showProvinceMap(idTag,type="") {

    let text_region = $(idTag+"-region_id option:selected").text();

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

            $(idTag+"-district_id").addClass('is-valid');
            $(idTag+"-district_id").attr("aria-invalid", "true");

            $(idTag+"-subdistrict_id").addClass('is-valid');
            $(idTag+"-subdistrict_id").attr("aria-invalid", "true");


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

            }


            if (type=="get"){
                $(idTag + "-province_id").val(searchData.province_id);
            }
             setContentBreadcrumb();
            
          

        },
    });
}

function showDistrictMap(idTag,type="") {

    if($(idTag+'-zipcode_id')){
        $(idTag+'-zipcode_id').val("");
    }

    var province_id = $(idTag+"-province_id").val();
    if (province_id==""){
        province_id = searchData.province_id;
    }
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
        

            if (urlParams.has('district_id') == true) {
             
                searchData.district_id = urlParams.get('district_id');
                   //console.log(searchData.district_id);
                $(idTag + "-district_id").val(searchData.district_id);
            }
             setContentBreadcrumb();

        },
    });
}

function showSubDistrictMap(idTag,type="") {
    //$("#sub_district").prop( "disabled", false );

    if($(idTag+'-zipcode_id')){
        $(idTag+'-zipcode_id').val("");
    }

    var district_id = $(idTag+"-district_id").val();
      if (district_id == "") {
          district_id = searchData.district_id;
      }
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
            if (urlParams.has('subdistrict_id') == true) {
                searchData.subdistrict_id = urlParams.get('subdistrict_id');
                $(idTag + "-subdistrict_id").val(searchData.subdistrict_id);
                //$('#map-subdistrict_id').val(searchData.subdistrict_id);
            }

             setContentBreadcrumb();

        },
    });
}
function DeleteMarkers() {
    //Loop through all the markers and remove
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(null);
    }
    markers = [];
    contentString = [];
    index_marker = 0;
}

function updateURLParameter(url, param, paramVal) {
    var newAdditionalURL = "";
    var tempArray = url.split("?");
    var baseURL = tempArray[0];
    var additionalURL = tempArray[1];
    var temp = "";
    if (additionalURL) {
        tempArray = additionalURL.split("&");
        for (var i = 0; i < tempArray.length; i++) {
            if (tempArray[i].split('=')[0] != param) {
                newAdditionalURL += temp + tempArray[i];
                temp = "&";
            }
        }
    }

    var rows_txt = temp + "" + param + "=" + paramVal;
    return baseURL + "?" + newAdditionalURL + rows_txt;
}
function getUrlParameter(sParam) {
    var sPageURL = window.location.search.substring(1),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
        }
    }
};
function removeURLParameter(url, parameter) {

    var urlparts = url.split('?');
    if (urlparts.length >= 2) {

        var prefix = encodeURIComponent(parameter) + '=';
        var pars = urlparts[1].split(/[&;]/g);

        for (var i = pars.length; i-- > 0;) {
            if (pars[i].lastIndexOf(prefix, 0) !== -1) {
                pars.splice(i, 1);
            }
        }

        return urlparts[0] + (pars.length > 0 ? '?' + pars.join('&') : '');
    }
    return url;
}
