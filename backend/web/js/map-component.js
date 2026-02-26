/**
 * Map Component JS
 * - Bidirectional sync between lat/lng inputs and Google Map marker
 * - UTM ↔ LatLng conversion
 * - Geolocation with Thailand fallback
 */
(function () {
    'use strict';

    // Thailand center (fallback)
    var THAILAND_LAT = 13.736717;
    var THAILAND_LNG = 100.523186;
    var THAILAND_ZOOM = 6;
    var PROVINCE_ZOOM = 10;
    var PIN_ZOOM = 15;

    var map = null;
    var marker = null;
    var latInput = null;
    var longInput = null;
    var isReadonly = false;
    var coordMode = 'latlng'; // 'latlng' or 'utm'
    var suppressInputSync = false; // prevent infinite loops

    // ==========================
    // UTM ↔ LatLng helpers
    // ==========================
    function latLngToUtm(lat, lng) {
        var zoneNumber = Math.floor((lng + 180) / 6) + 1;
        // Special zones for Norway/Svalbard omitted (not relevant for Thailand)
        var isNorth = lat >= 0;
        var zoneLetter = isNorth ? 'N' : 'S';

        var a = 6378137.0; // WGS84 semi-major
        var f = 1 / 298.257223563;
        var e2 = 2 * f - f * f;
        var ep2 = e2 / (1 - e2);
        var k0 = 0.9996;

        var radLat = lat * Math.PI / 180;
        var radLng = lng * Math.PI / 180;
        var centralMeridian = ((zoneNumber - 1) * 6 - 180 + 3) * Math.PI / 180;

        var N = a / Math.sqrt(1 - e2 * Math.sin(radLat) * Math.sin(radLat));
        var T = Math.tan(radLat) * Math.tan(radLat);
        var C = ep2 * Math.cos(radLat) * Math.cos(radLat);
        var A = Math.cos(radLat) * (radLng - centralMeridian);
        var M = a * ((1 - e2 / 4 - 3 * e2 * e2 / 64 - 5 * e2 * e2 * e2 / 256) * radLat
            - (3 * e2 / 8 + 3 * e2 * e2 / 32 + 45 * e2 * e2 * e2 / 1024) * Math.sin(2 * radLat)
            + (15 * e2 * e2 / 256 + 45 * e2 * e2 * e2 / 1024) * Math.sin(4 * radLat)
            - (35 * e2 * e2 * e2 / 3072) * Math.sin(6 * radLat));

        var easting = k0 * N * (A + (1 - T + C) * A * A * A / 6
            + (5 - 18 * T + T * T + 72 * C - 58 * ep2) * A * A * A * A * A / 120) + 500000.0;
        var northing = k0 * (M + N * Math.tan(radLat) * (A * A / 2
            + (5 - T + 9 * C + 4 * C * C) * A * A * A * A / 24
            + (61 - 58 * T + T * T + 600 * C - 330 * ep2) * A * A * A * A * A * A / 720));

        if (!isNorth) northing += 10000000.0;

        return {
            zone: zoneNumber + zoneLetter,
            easting: easting,
            northing: northing
        };
    }

    function utmToLatLng(zone, easting, northing) {
        var zoneNumber = parseInt(zone, 10);
        var zoneLetter = zone.replace(/[0-9]/g, '').toUpperCase();
        var isNorth = zoneLetter === 'N';

        var a = 6378137.0;
        var f = 1 / 298.257223563;
        var e2 = 2 * f - f * f;
        var ep2 = e2 / (1 - e2);
        var k0 = 0.9996;
        var e1 = (1 - Math.sqrt(1 - e2)) / (1 + Math.sqrt(1 - e2));

        var x = easting - 500000.0;
        var y = isNorth ? northing : northing - 10000000.0;

        var centralMeridian = (zoneNumber - 1) * 6 - 180 + 3;

        var M = y / k0;
        var mu = M / (a * (1 - e2 / 4 - 3 * e2 * e2 / 64 - 5 * e2 * e2 * e2 / 256));

        var phi1 = mu + (3 * e1 / 2 - 27 * e1 * e1 * e1 / 32) * Math.sin(2 * mu)
            + (21 * e1 * e1 / 16 - 55 * e1 * e1 * e1 * e1 / 32) * Math.sin(4 * mu)
            + (151 * e1 * e1 * e1 / 96) * Math.sin(6 * mu)
            + (1097 * e1 * e1 * e1 * e1 / 512) * Math.sin(8 * mu);

        var N1 = a / Math.sqrt(1 - e2 * Math.sin(phi1) * Math.sin(phi1));
        var T1 = Math.tan(phi1) * Math.tan(phi1);
        var C1 = ep2 * Math.cos(phi1) * Math.cos(phi1);
        var R1 = a * (1 - e2) / Math.pow(1 - e2 * Math.sin(phi1) * Math.sin(phi1), 1.5);
        var D = x / (N1 * k0);

        var lat = phi1 - (N1 * Math.tan(phi1) / R1) * (D * D / 2
            - (5 + 3 * T1 + 10 * C1 - 4 * C1 * C1 - 9 * ep2) * D * D * D * D / 24
            + (61 + 90 * T1 + 298 * C1 + 45 * T1 * T1 - 252 * ep2 - 3 * C1 * C1) * D * D * D * D * D * D / 720);

        var lng = (D - (1 + 2 * T1 + C1) * D * D * D / 6
            + (5 - 2 * C1 + 28 * T1 - 3 * C1 * C1 + 8 * ep2 + 24 * T1 * T1) * D * D * D * D * D / 120) / Math.cos(phi1);

        lat = lat * 180 / Math.PI;
        lng = centralMeridian + lng * 180 / Math.PI;

        return { lat: lat, lng: lng };
    }

    // ==========================
    // Map initialization
    // ==========================
    function initMapComponent() {
        var container = document.getElementById('map-component-container');
        if (!container) return;

        isReadonly = container.getAttribute('data-readonly') === 'true';

        // Find lat/lng inputs (Yii2 generates IDs like content-latitude, content-longitude)
        latInput = document.getElementById('content-latitude');
        longInput = document.getElementById('content-longitude');

        var existingLat = latInput ? parseFloat(latInput.value) : NaN;
        var existingLng = longInput ? parseFloat(longInput.value) : NaN;
        var hasExistingCoords = !isNaN(existingLat) && !isNaN(existingLng) && existingLat !== 0 && existingLng !== 0;

        if (hasExistingCoords) {
            // Model already has coordinates — show them
            createMap(existingLat, existingLng, PIN_ZOOM, true);
            hideGeolocationStatus();
        } else if (!isReadonly) {
            // No existing coords, try geolocation
            tryGeolocation();
        } else {
            // Readonly with no coords — just show Thailand
            createMap(THAILAND_LAT, THAILAND_LNG, THAILAND_ZOOM, false);
            hideGeolocationStatus();
        }

        // Bind input events (edit mode only)
        if (!isReadonly) {
            bindInputEvents();
            bindCoordModeSwitch();
        }
    }

    // ==========================
    // Geolocation
    // ==========================
    function tryGeolocation() {
        var statusEl = document.getElementById('map-geolocation-status');

        if (navigator.geolocation) {
            if (statusEl) statusEl.style.display = 'block';

            navigator.geolocation.getCurrentPosition(
                function (position) {
                    // Success — zoom to current position at province level, NO pin
                    createMap(position.coords.latitude, position.coords.longitude, PROVINCE_ZOOM, false);
                    hideGeolocationStatus();
                },
                function () {
                    // Permission denied or error — fallback to Thailand
                    createMap(THAILAND_LAT, THAILAND_LNG, THAILAND_ZOOM, false);
                    hideGeolocationStatus();
                },
                { enableHighAccuracy: false, timeout: 8000, maximumAge: 300000 }
            );
        } else {
            // Geolocation not supported
            createMap(THAILAND_LAT, THAILAND_LNG, THAILAND_ZOOM, false);
            hideGeolocationStatus();
        }
    }

    function hideGeolocationStatus() {
        var statusEl = document.getElementById('map-geolocation-status');
        if (statusEl) statusEl.style.display = 'none';
    }

    // ==========================
    // Create / update map
    // ==========================
    function createMap(lat, lng, zoom, placeMarker) {
        var container = document.getElementById('map-component-container');

        map = new google.maps.Map(container, {
            zoom: zoom,
            center: { lat: lat, lng: lng },
            mapTypeControl: true,
            streetViewControl: false,
            fullscreenControl: true,
        });

        if (placeMarker) {
            addOrMoveMarker(lat, lng);
        }

        // Click on map to place/move marker (edit mode only)
        if (!isReadonly) {
            map.addListener('click', function (e) {
                var clickLat = e.latLng.lat();
                var clickLng = e.latLng.lng();
                addOrMoveMarker(clickLat, clickLng);
                updateInputsFromLatLng(clickLat, clickLng);
                // Auto-sync address
                syncAddressFromPin();
            });
        }
    }

    function addOrMoveMarker(lat, lng) {
        if (marker) {
            marker.setPosition({ lat: lat, lng: lng });
        } else {
            marker = new google.maps.Marker({
                map: map,
                position: { lat: lat, lng: lng },
                draggable: !isReadonly,
                animation: google.maps.Animation.DROP,
            });

            if (!isReadonly) {
                google.maps.event.addListener(marker, 'dragend', function (e) {
                    var dragLat = e.latLng.lat();
                    var dragLng = e.latLng.lng();
                    updateInputsFromLatLng(dragLat, dragLng);
                    // Auto-sync address
                    syncAddressFromPin();
                });
            }
        }

        // Re-center the map
        map.panTo({ lat: lat, lng: lng });
    }

    // ==========================
    // Input ↔ Map sync
    // ==========================
    function updateInputsFromLatLng(lat, lng) {
        suppressInputSync = true;

        if (latInput) latInput.value = lat.toFixed(7);
        if (longInput) longInput.value = lng.toFixed(7);

        // Also trigger change event so Yii validation picks it up
        if (latInput) $(latInput).trigger('change');
        if (longInput) $(longInput).trigger('change');

        // If UTM mode is active, also update UTM fields
        if (coordMode === 'utm') {
            var utm = latLngToUtm(lat, lng);
            var zoneEl = document.getElementById('map-utm-zone');
            var eastEl = document.getElementById('map-utm-easting');
            var northEl = document.getElementById('map-utm-northing');
            if (zoneEl) zoneEl.value = utm.zone;
            if (eastEl) eastEl.value = utm.easting.toFixed(2);
            if (northEl) northEl.value = utm.northing.toFixed(2);
        }

        suppressInputSync = false;
    }

    function onLatLngInputChange() {
        if (suppressInputSync) return;

        var lat = latInput ? parseFloat(latInput.value) : NaN;
        var lng = longInput ? parseFloat(longInput.value) : NaN;

        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            addOrMoveMarker(lat, lng);
            map.setZoom(Math.max(map.getZoom(), PIN_ZOOM));

            // Sync UTM fields if in UTM mode
            if (coordMode === 'utm') {
                suppressInputSync = true;
                var utm = latLngToUtm(lat, lng);
                var zoneEl = document.getElementById('map-utm-zone');
                var eastEl = document.getElementById('map-utm-easting');
                var northEl = document.getElementById('map-utm-northing');
                if (zoneEl) zoneEl.value = utm.zone;
                if (eastEl) eastEl.value = utm.easting.toFixed(2);
                if (northEl) northEl.value = utm.northing.toFixed(2);
                suppressInputSync = false;
            }
        }
    }

    function onUtmInputChange() {
        if (suppressInputSync) return;

        var zoneEl = document.getElementById('map-utm-zone');
        var eastEl = document.getElementById('map-utm-easting');
        var northEl = document.getElementById('map-utm-northing');

        var zone = zoneEl ? zoneEl.value.trim() : '';
        var easting = eastEl ? parseFloat(eastEl.value) : NaN;
        var northing = northEl ? parseFloat(northEl.value) : NaN;

        if (zone && !isNaN(easting) && !isNaN(northing)) {
            try {
                var result = utmToLatLng(zone, easting, northing);
                if (result.lat >= -90 && result.lat <= 90 && result.lng >= -180 && result.lng <= 180) {
                    suppressInputSync = true;
                    if (latInput) latInput.value = result.lat.toFixed(7);
                    if (longInput) longInput.value = result.lng.toFixed(7);
                    if (latInput) $(latInput).trigger('change');
                    if (longInput) $(longInput).trigger('change');
                    suppressInputSync = false;

                    addOrMoveMarker(result.lat, result.lng);
                    map.setZoom(Math.max(map.getZoom(), PIN_ZOOM));
                }
            } catch (e) {
                // Invalid UTM values — ignore
            }
        }
    }

    // ==========================
    // Event bindings
    // ==========================
    function debounce(fn, delay) {
        var timer;
        return function () {
            clearTimeout(timer);
            timer = setTimeout(fn, delay);
        };
    }

    function bindInputEvents() {
        if (latInput) {
            $(latInput).on('change keyup', debounce(onLatLngInputChange, 500));
        }
        if (longInput) {
            $(longInput).on('change keyup', debounce(onLatLngInputChange, 500));
        }

        // UTM inputs
        var utmInputs = ['map-utm-zone', 'map-utm-easting', 'map-utm-northing'];
        utmInputs.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) {
                $(el).on('change keyup', debounce(onUtmInputChange, 500));
            }
        });
    }

    function bindCoordModeSwitch() {
        var modeSelect = document.getElementById('map-coord-mode');
        if (!modeSelect) return;

        $(modeSelect).on('change', function () {
            coordMode = this.value;
            var latlngGroup = document.getElementById('map-latlng-group');
            var utmGroup = document.getElementById('map-utm-group');

            if (coordMode === 'utm') {
                if (latlngGroup) latlngGroup.style.display = 'none';
                if (utmGroup) utmGroup.style.display = 'block';

                // Convert current lat/lng to UTM and fill fields
                var lat = latInput ? parseFloat(latInput.value) : NaN;
                var lng = longInput ? parseFloat(longInput.value) : NaN;
                if (!isNaN(lat) && !isNaN(lng)) {
                    var utm = latLngToUtm(lat, lng);
                    var zoneEl = document.getElementById('map-utm-zone');
                    var eastEl = document.getElementById('map-utm-easting');
                    var northEl = document.getElementById('map-utm-northing');
                    if (zoneEl) zoneEl.value = utm.zone;
                    if (eastEl) eastEl.value = utm.easting.toFixed(2);
                    if (northEl) northEl.value = utm.northing.toFixed(2);
                }
            } else {
                if (latlngGroup) latlngGroup.style.display = 'block';
                if (utmGroup) utmGroup.style.display = 'none';
            }
        });
    }

    var syncInProgress = false;

    // ==========================
    // Sync address from map pin
    // ==========================
    function syncAddressFromPin() {
        if (syncInProgress) return;

        var statusEl = document.getElementById('sync-address-status');
        var btn = document.getElementById('btn-sync-address');

        if (!marker) {
            if (statusEl) {
                statusEl.style.display = 'inline';
                statusEl.innerHTML = '<span style="color:#d9534f"><i class="fa fa-exclamation-circle"></i> กรุณาปักหมุดบนแผนที่ก่อน</span>';
                setTimeout(function () { statusEl.style.display = 'none'; }, 3000);
            }
            return;
        }

        var lat = marker.getPosition().lat();
        var lng = marker.getPosition().lng();

        // Show loading state
        syncInProgress = true;
        if (btn) btn.disabled = true;
        if (statusEl) {
            statusEl.style.display = 'inline';
            statusEl.innerHTML = '<i class="fa fa-spinner fa-spin"></i> กำลังค้นหาที่อยู่...';
        }

        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: { lat: lat, lng: lng }, language: 'th' }, function (results, status) {
            if (status !== 'OK' || !results || !results.length) {
                if (statusEl) {
                    statusEl.innerHTML = '<span style="color:#d9534f"><i class="fa fa-exclamation-circle"></i> ไม่พบที่อยู่จากพิกัดนี้</span>';
                    setTimeout(function () { statusEl.style.display = 'none'; }, 3000);
                }
                if (btn) btn.disabled = false;
                syncInProgress = false;
                return;
            }

            // Extract address components from first result
            var components = results[0].address_components;
            var province = '';
            var district = '';
            var subdistrict = '';
            var zipcode = '';

            for (var i = 0; i < components.length; i++) {
                var types = components[i].types;
                var longName = components[i].long_name;

                if (types.indexOf('administrative_area_level_1') !== -1) {
                    province = longName;
                } else if (types.indexOf('administrative_area_level_2') !== -1) {
                    district = longName;
                } else if (types.indexOf('sublocality_level_1') !== -1 || types.indexOf('sublocality') !== -1) {
                    subdistrict = longName;
                } else if (types.indexOf('locality') !== -1 && !subdistrict) {
                    // Some areas return locality instead of sublocality
                    subdistrict = longName;
                } else if (types.indexOf('postal_code') !== -1) {
                    zipcode = longName;
                }
            }

            // If no subdistrict from sublocality, try from route or neighborhood
            if (!subdistrict) {
                for (var j = 0; j < components.length; j++) {
                    if (components[j].types.indexOf('neighborhood') !== -1) {
                        subdistrict = components[j].long_name;
                        break;
                    }
                }
            }

            console.log('[map-sync] Geocoded:', { province: province, district: district, subdistrict: subdistrict, zipcode: zipcode });

            // Check if we're in Thailand
            var country = '';
            for (var k = 0; k < components.length; k++) {
                if (components[k].types.indexOf('country') !== -1) {
                    country = components[k].short_name;
                }
            }
            if (country !== 'TH') {
                if (statusEl) {
                    statusEl.innerHTML = '<span style="color:#d9534f"><i class="fa fa-exclamation-circle"></i> ตำแหน่งนี้ไม่อยู่ในประเทศไทย</span>';
                    setTimeout(function () { statusEl.style.display = 'none'; }, 3000);
                }
                if (btn) btn.disabled = false;
                return;
            }

            // Call backend to match names to DB IDs
            var apiUrl = (typeof appBaseUrl !== 'undefined' ? appBaseUrl : '') + '/api/reverse-geocode';
            $.ajax({
                method: 'GET',
                url: apiUrl,
                data: {
                    province: province,
                    district: district,
                    subdistrict: subdistrict,
                    zipcode: zipcode
                },
                cache: false,
                dataType: 'json',
                success: function (response) {
                    if (response.data) {
                        var d = response.data;
                        console.log('[map-sync] Matched IDs:', d);

                        // Use setLocationChain from location.js if available
                        if (typeof setLocationChain === 'function') {
                            setLocationChain('#content', d);
                        }

                        if (statusEl) {
                            statusEl.innerHTML = '<span style="color:#5cb85c"><i class="fa fa-check-circle"></i> ดึงที่อยู่สำเร็จ</span>';
                            setTimeout(function () { statusEl.style.display = 'none'; }, 3000);
                        }
                    }
                    if (btn) btn.disabled = false;
                    syncInProgress = false;
                },
                error: function () {
                    if (statusEl) {
                        statusEl.innerHTML = '<span style="color:#d9534f"><i class="fa fa-exclamation-circle"></i> เกิดข้อผิดพลาด</span>';
                        setTimeout(function () { statusEl.style.display = 'none'; }, 3000);
                    }
                    if (btn) btn.disabled = false;
                    syncInProgress = false;
                }
            });
        });
    }

    function bindSyncButton() {
        var btn = document.getElementById('btn-sync-address');
        if (btn) {
            $(btn).on('click', function () {
                syncAddressFromPin();
            });
        }
    }

    // ==========================
    // Google Maps callback
    // ==========================
    // The Google Maps API script uses callback=initialMap
    window.initialMap = function () {
        initMapComponent();
        bindSyncButton();
    };

    // Also support pages that load maps script before this file
    if (typeof google !== 'undefined' && google.maps) {
        $(document).ready(function () {
            initMapComponent();
            bindSyncButton();
        });
    }

})();
