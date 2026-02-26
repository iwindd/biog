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

    let defaultLatitude = '18.8111222',
        defaultLongitude = '98.9665804',
        googleMap = null,
        googleMapMarker = null

    let getLatitude = () => {
        let latitude = parseFloat(latitudeInput.value)
        return isNaN(latitude) ? parseFloat(defaultLatitude) : latitude
    }
    let setLatitudeToTextInput = (latitude) => {
        latitudeInput.value = latitude
    }

    let getLongitude = () => {
        let longitude = parseFloat(longitudeInput.value)
        return isNaN(longitude) ? parseFloat(defaultLongitude) : longitude
    }
    let setLongitudeToTextInput = (longitude) => {
        longitudeInput.value = longitude
    }

    const setValueWhenPinDragend = () => {
        //googleMapMarker.addListener('click', toggleBounceAnimation)
        // google.maps.event.addListener(googleMapMarker, 'dragend', function (event) {
        //     setLatitudeToTextInput(event.latLng.lat().toFixed(7))
        //     setLongitudeToTextInput(event.latLng.lng().toFixed(7))
        // })
        
    }

    const createMarker = () => {

   
        googleMapMarker = new google.maps.Marker({
            map: googleMap,
            draggable: false,
            animation: google.maps.Animation.DROP,
            position: { lat: getLatitude(), lng: getLongitude() },
            center: { lat: getLatitude(), lng: getLongitude() },
            scaledSize: new google.maps.Size(40, 45),
        })

        var i = 0 ;
        var infowindow = new google.maps.InfoWindow();
        google.maps.event.addListener(googleMapMarker, 'click', (function(googleMapMarker) {
            return function() {
              infowindow.setContent('สำนักงานพัฒนาเศรษฐกิจจากฐานชีวภาพ (องค์การมหาชน)');
              infowindow.open(googleMap, googleMapMarker);
            }
        })(googleMapMarker, i)); 

        // var infowindow = new google.maps.InfoWindow();
        // var position_data = new google.maps.LatLng(getLatitude(),getLongitude());
        // var marker = new google.maps.Marker({
        //     position: position_data,
        //     scaledSize: new google.maps.Size(40, 45),
        //     map: map
        // });
        // google.maps.event.addListener(marker, 'click', (function(marker, i) {
        //     return function() {
        //       infowindow.setContent('โรงพยาบาลสัตว์ มหาวิทยาลัยเกษตรศาสตร์ บางเขน');
        //       infowindow.open(map, marker);
        //     }
        // })(marker, i));

        //setValueWhenPinDragend()
    }

    const toggleBounceAnimation = () => {
        if (googleMapMarker.getAnimation() !== null) {
            googleMapMarker.setAnimation(null)
        } else {
            googleMapMarker.setAnimation(google.maps.Animation.BOUNCE)
        }
    }

    const initialMap = () => {
        googleMap = new google.maps.Map(divGoogleMap, {
            zoom: 15,
            position: { lat: getLatitude(), lng: getLongitude() },
            center: { lat: getLatitude(), lng: getLongitude() },
        })

        createMarker()
    }

    initialMap()
}

window.addEventListener('load', function () {
    renderGoogleMap({
        divGoogleMapId: 'content-google-map',
        latitudeInputId: 'lat',
        longitudeInputId: 'lng',
    })
})
