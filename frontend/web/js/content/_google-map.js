
var domainMap = "";
var iconBase = domainMap + '/images/marker/';
var icons = {
    plant: iconBase + '01_Plan.png',
    animal: iconBase + '02_animal.png',
    fungi: iconBase + '03_fungi.png',
    expert: iconBase + '04_tk.png',
    ecotourism: iconBase + '05_ecotourism.png',
    product: iconBase + '06_product.png',
};

var pageType = 'plant';
var nameType = $('#page-type');
if(nameType){
    pageType = nameType.val();
}

var mapConfig = '';
var mapMarker = '';

function initialMap() {}

function renderGoogleMap(setConfigs) {
    let configs = (setConfigs) => {
        let { divGoogleMapId, latitudeInputId, longitudeInputId } = setConfigs
        return {
            divGoogleMap: document.getElementById(divGoogleMapId),
            latitudeInput: document.getElementById(latitudeInputId),
            longitudeInput: document.getElementById(longitudeInputId),
        }
    }

    let { divGoogleMap, latitudeInput, longitudeInput } = configs(setConfigs)

    let defaultLatitude = '13.7244416',
        defaultLongitude = '100.3529157',
        googleMap = null,
        googleMapMarker = null

    let getLatitude = () => {
        let latitude = latitudeInput ? parseFloat(latitudeInput.value) : defaultLatitude
        return isNaN(latitude) ? parseFloat(defaultLatitude) : latitude
    }
    let setLatitudeToTextInput = (latitude) => {
        latitudeInput.value = latitude
    }

    let getLongitude = () => {
        let longitude = longitudeInput ? parseFloat(longitudeInput.value) : defaultLongitude
        return isNaN(longitude) ? parseFloat(defaultLongitude) : longitude
    }
    let setLongitudeToTextInput = (longitude) => {
        longitudeInput.value = longitude
    }

    const setValueWhenPinDragend = () => {
        googleMapMarker.addListener('click', toggleBounceAnimation)
        google.maps.event.addListener(googleMapMarker, 'dragend', function (event) {
            setLatitudeToTextInput(event.latLng.lat().toFixed(7))
            setLongitudeToTextInput(event.latLng.lng().toFixed(7))
        })
    }

    const createMarker = () => {
        mapConfig = googleMap;
        googleMapMarker = new google.maps.Marker({
            map: googleMap,
            draggable: true,
            animation: google.maps.Animation.DROP,
            position: { lat: getLatitude(), lng: getLongitude() },
            center: { lat: getLatitude(), lng: getLongitude() },
            icon: { url: icons[pageType], scaledSize: new google.maps.Size(55, 57) },
        })

        mapMarker = googleMapMarker;

        setValueWhenPinDragend()
    }

    const toggleBounceAnimation = () => {
        if (googleMapMarker.getAnimation() !== null) {
            googleMapMarker.setAnimation(null)
        } else {
            googleMapMarker.setAnimation(google.maps.Animation.BOUNCE)
        }
    }

    const initialMap = () => {
        if (divGoogleMap) {
            googleMap = new google.maps.Map(divGoogleMap, {
                zoom: 9,
                position: { lat: getLatitude(), lng: getLongitude() },
                center: { lat: getLatitude(), lng: getLongitude() },
            })

            createMarker()
        }
    }


    // for place

    function createMarkerPlace(place) {
        googleMapMarker.setMap(null);
        googleMapMarker = new google.maps.Marker({
            map: googleMap,
            draggable: true,
            animation: google.maps.Animation.DROP,
            position: place.geometry.location,
            icon: { url: icons[pageType], scaledSize: new google.maps.Size(55, 57) },
        });
        setValueWhenPinDragend();

    }

    createMarkerPlaceRegion = (latPlace, lngPlace) => {

        googleMapMarker.setMap(null);
        googleMapMarker = new google.maps.Marker({
            map: googleMap,
            draggable: true,
            animation: google.maps.Animation.DROP,
            position: { lat: latPlace, lng: lngPlace },
            center: { lat: latPlace, lng: lngPlace },
            icon: { url: icons[pageType], scaledSize: new google.maps.Size(55, 57) },
        });

        let latLng = new google.maps.LatLng(latPlace, lngPlace);
        googleMap.panTo(latLng);

        setValueWhenPinDragend();

    }

    findService = (text, zoom = null) => {

        if(text == 'ภาคเหนือ'){
            createMarkerPlaceRegion(18.796143, 98.979263);
        }else if(text == 'ภาคกลาง'){
            createMarkerPlaceRegion(13.736717, 100.523186)
        }else if(text == 'ภาคตะวันออกเฉียงเหนือ'){
            createMarkerPlaceRegion(17.4443097, 102.2812844)
        }else if(text == 'ภาคตะวันออก'){
            createMarkerPlaceRegion(12.9494054, 101.633078)
        }else if(text == 'ภาคตะวันตก'){
            createMarkerPlaceRegion(14.0023269, 99.5393187)
        }else if(text == 'ภาคใต้'){
            createMarkerPlaceRegion(8.582298, 99.2248988)
        }
        else{
            let service = new google.maps.places.PlacesService(googleMap);
            var request = {
                query: text,
                fields: ['name', 'geometry'],
            };
            service.findPlaceFromQuery(request, function (results, status) {
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    // $('#community-latitude').val(results[0].geometry.location.lat().toFixed(7));
                    let latPlace = results[0].geometry.location.lat().toFixed(7);
                    let lngPlace = results[0].geometry.location.lng().toFixed(7);
                    // $('#community-longitude').val(results[0].geometry.location.lng().toFixed(7));
                    createMarkerPlace(results[0]);
                    googleMap.setCenter(results[0].geometry.location);
                    if(zoom != null && $.isNumeric(zoom) ){
                        googleMap.setZoom(zoom);
                    }
                    
                }
            });
        }
    }

    //function for location


    $('#content_region_id').on('change', function(){
        let regionSelect = $('#content_region_id').find("option:selected").text();

        let lat = $('#content-latitude').val();
        let long = $('#content-longitude').val();

        if(lat == '' || long == ''){
            findService(regionSelect);
        }
    });

    $('#content_province_id').on('change', function(){
        
        let regionSelect = $('#content_region_id').find("option:selected").text();
        let provinceSelect = $('#content_province_id').find("option:selected").text();

        let lat = $('#content-latitude').val();
        let long = $('#content-longitude').val();

        if( provinceSelect != 'เลือกจังหวัด...' && provinceSelect != ''){
            if(lat == '' || long == ''){
                findService(regionSelect+','+provinceSelect);
            }
        }
    });

    $('#content_district_id').on('change', function(){
        
        let regionSelect = $('#content_region_id').find("option:selected").text();
        let provinceSelect = $('#content_province_id').find("option:selected").text();
        let districtSelect = $('#content_district_id').find("option:selected").text();

        let lat = $('#content-latitude').val();
        let long = $('#content-longitude').val();

        if( provinceSelect != 'เลือกจังหวัด...' && provinceSelect != '' && districtSelect != '' && districtSelect != 'เลือกอำเภอ...'){
            //console.log('pro+'+districtSelect);
            if(lat == '' || long == ''){
                findService(regionSelect+','+provinceSelect+','+districtSelect, 15);
            }
        }
    });

    $('#content_subdistrict_id').on('change', function(){
        
        let regionSelect = $('#content_region_id').find("option:selected").text();
        let provinceSelect = $('#content_province_id').find("option:selected").text();
        let districtSelect = $('#content_district_id').find("option:selected").text();
        let subdistrictSelect = $('#content_subdistrict_id').find("option:selected").text();

        let lat = $('#content-latitude').val();
        let long = $('#content-longitude').val();
        
        if( provinceSelect != 'เลือกจังหวัด...' && provinceSelect != '' && districtSelect != '' && districtSelect != 'เลือกอำเภอ...'  && subdistrictSelect != '' && subdistrictSelect != 'เลือกตำบล/เขต...'){
            //console.log('pro+'+districtSelect);
            if(lat == '' || long == ''){
                findService(regionSelect+','+provinceSelect+','+districtSelect+','+subdistrictSelect, 15);
            }
        }
    });

    initialMap()
}

// createMarkerPlace = (place) => {
//     mapMarker.setMap(null);
//     mapMarker = new google.maps.Marker({
//         map: mapConfig,
//         draggable: true,
//         animation: google.maps.Animation.DROP,
//         position: place.geometry.location,
//         icon: { url: icons[pageType], scaledSize: new google.maps.Size(55, 57) },
//     });
//     mapMarker.addListener('click', toggleBounceAnimationPlace);
//     google.maps.event.addListener(mapMarker, 'dragend', function (evt) {
       
//     })

// }

// toggleBounceAnimationPlace = () => {
//     if (mapMarker.getAnimation() !== null) {
//         mapMarker.setAnimation(null)
//     } else {
//         mapMarker.setAnimation(google.maps.Animation.BOUNCE)
//     }
// }


