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
        var i = 0 ;
        var infowindow = new google.maps.InfoWindow();
        let titleText = $('#contentteacher-name').val();
        google.maps.event.addListener(googleMapMarker, 'click', (function(googleMapMarker) {
            return function() {
              infowindow.setContent(titleText);
              infowindow.open(googleMap, googleMapMarker);
            }
        })(googleMapMarker, i)); 
        google.maps.event.addListener(googleMapMarker, 'dragend', function (event) {
            setLatitudeToTextInput(event.latLng.lat().toFixed(7))
            setLongitudeToTextInput(event.latLng.lng().toFixed(7))
        })
    }

    const createMarker = () => {
        googleMapMarker = new google.maps.Marker({
            map: googleMap,
            draggable: false,
            animation: google.maps.Animation.DROP,
            position: { lat: getLatitude(), lng: getLongitude() },
            center: { lat: getLatitude(), lng: getLongitude() },
        })

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
        latitudeInputId: 'contentteacher-latitude',
        longitudeInputId: 'contentteacher-longitude',
    })
})
