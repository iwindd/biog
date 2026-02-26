/**
 * Cascading Location Dropdowns
 * Region → Province → District → Subdistrict → Zipcode
 *
 * Uses appBaseUrl variable injected from PHP (via Yii2 Url::base()).
 * This ensures AJAX calls use the correct base path matching Yii2's baseUrl config.
 */
var host = (typeof appBaseUrl !== 'undefined') ? appBaseUrl : '';

/**
 * Load provinces by region_id
 * @param {string} t - prefix selector e.g. "#content", "#profile", "#school"
 */
function showProvince(t) {
    var regionId = $(t + '-region_id').val();

    // Clear downstream dropdowns
    $(t + '-province_id').empty().append('<option value="">กรุณาเลือกจังหวัด</option>');
    $(t + '-district_id').empty().append('<option value="">กรุณาเลือกอำเภอ</option>');
    $(t + '-subdistrict_id').empty().append('<option value="">กรุณาเลือกตำบล</option>');
    $(t + '-zipcode_id').empty().append('<option value="">กรุณาเลือกรหัสไปรษณีย์</option>');

    if (!regionId) return;

    $.ajax({
        method: 'GET',
        url: host + '/api/province',
        data: { region_id: regionId },
        cache: false,
        dataType: 'json',
        success: function (response) {
            $(t + '-province_id').empty().append('<option value="">กรุณาเลือกจังหวัด</option>');
            if (response.data && response.data.length) {
                for (var i = 0; i < response.data.length; i++) {
                    $(t + '-province_id').append(
                        $('<option></option>').attr('value', response.data[i].id).text(response.data[i].name)
                    );
                }
            }
        },
        error: function (xhr, status, error) {
            console.error('showProvince error:', status, error);
        }
    });
}

/**
 * Load districts by province_id
 */
function showDistrict(t) {
    var provinceId = $(t + '-province_id').val();

    // Clear downstream dropdowns
    $(t + '-district_id').empty().append('<option value="">กรุณาเลือกอำเภอ</option>');
    $(t + '-subdistrict_id').empty().append('<option value="">กรุณาเลือกตำบล</option>');
    $(t + '-zipcode_id').empty().append('<option value="">กรุณาเลือกรหัสไปรษณีย์</option>');

    if (!provinceId) return;

    $.ajax({
        method: 'GET',
        url: host + '/api/district',
        data: { province_id: provinceId },
        cache: false,
        dataType: 'json',
        success: function (response) {
            $(t + '-district_id').empty().append('<option value="">กรุณาเลือกอำเภอ</option>');
            if (response.data && response.data.length) {
                for (var i = 0; i < response.data.length; i++) {
                    $(t + '-district_id').append(
                        $('<option></option>').attr('value', response.data[i].id).text(response.data[i].name)
                    );
                }
            }
        },
        error: function (xhr, status, error) {
            console.error('showDistrict error:', status, error);
        }
    });
}

/**
 * Load subdistricts by district_id
 */
function showSubDistrict(t) {
    var districtId = $(t + '-district_id').val();

    // Clear downstream dropdowns
    $(t + '-subdistrict_id').empty().append('<option value="">กรุณาเลือกตำบล</option>');
    $(t + '-zipcode_id').empty().append('<option value="">กรุณาเลือกรหัสไปรษณีย์</option>');

    if (!districtId) return;

    $.ajax({
        method: 'GET',
        url: host + '/api/subdistrict',
        data: { district_id: districtId },
        cache: false,
        dataType: 'json',
        success: function (response) {
            $(t + '-subdistrict_id').empty().append('<option value="">กรุณาเลือกตำบล</option>');
            if (response.data && response.data.length) {
                for (var i = 0; i < response.data.length; i++) {
                    $(t + '-subdistrict_id').append(
                        $('<option></option>').attr('value', response.data[i].id).text(response.data[i].name)
                    );
                }
            }
        },
        error: function (xhr, status, error) {
            console.error('showSubDistrict error:', status, error);
        }
    });
}

/**
 * Load zipcodes by subdistrict_id
 */
function showZipcode(t) {
    var subdistrictId = $(t + '-subdistrict_id').val();

    $(t + '-zipcode_id').empty().append('<option value="">กรุณาเลือกรหัสไปรษณีย์</option>');

    if (!subdistrictId) return;

    $.ajax({
        method: 'GET',
        url: host + '/api/zipcode',
        data: { subdistrict_id: subdistrictId },
        cache: false,
        dataType: 'json',
        success: function (response) {
            $(t + '-zipcode_id').empty().append('<option value="">กรุณาเลือกรหัสไปรษณีย์</option>');
            if (response.data && response.data.length) {
                for (var i = 0; i < response.data.length; i++) {
                    var label = response.data[i].zipcode || response.data[i].name;
                    $(t + '-zipcode_id').append(
                        $('<option></option>').attr('value', response.data[i].id).text(label)
                    );
                }
            }
        },
        error: function (xhr, status, error) {
            console.error('showZipcode error:', status, error);
        }
    });
}

/**
 * Set location dropdowns from reverse-geocoded data
 * Called by map-component.js syncAddressFromPin()
 *
 * @param {string} prefix - selector prefix e.g. "#content", "#profile", "#school"
 * @param {object} data - { region_id, province_id, district_id, subdistrict_id, zipcode_id }
 */
function setLocationChain(prefix, data) {
    // Step 1: Set region
    if (data.region_id) {
        $(prefix + '-region_id').val(data.region_id);
    }

    // Step 2: Load provinces for this region, then set province
    if (data.region_id && data.province_id) {
        $.ajax({
            method: 'GET',
            url: host + '/api/province',
            data: { region_id: data.region_id },
            cache: false,
            dataType: 'json',
            success: function (res) {
                var $province = $(prefix + '-province_id');
                $province.empty().append('<option value="">กรุณาเลือกจังหวัด</option>');
                if (res.data) {
                    for (var i = 0; i < res.data.length; i++) {
                        $province.append('<option value="' + res.data[i].id + '">' + res.data[i].name + '</option>');
                    }
                }
                $province.val(data.province_id);

                // Step 3: Load districts
                if (data.district_id) {
                    loadDistrictChain(prefix, data);
                }
            }
        });
    }

    function loadDistrictChain(prefix, data) {
        $.ajax({
            method: 'GET',
            url: host + '/api/district',
            data: { province_id: data.province_id },
            cache: false,
            dataType: 'json',
            success: function (res) {
                var $district = $(prefix + '-district_id');
                $district.empty().append('<option value="">กรุณาเลือกอำเภอ</option>');
                if (res.data) {
                    for (var i = 0; i < res.data.length; i++) {
                        $district.append('<option value="' + res.data[i].id + '">' + res.data[i].name + '</option>');
                    }
                }
                $district.val(data.district_id);

                // Step 4: Load subdistricts
                if (data.subdistrict_id) {
                    loadSubdistrictChain(prefix, data);
                }
            }
        });
    }

    function loadSubdistrictChain(prefix, data) {
        $.ajax({
            method: 'GET',
            url: host + '/api/subdistrict',
            data: { district_id: data.district_id },
            cache: false,
            dataType: 'json',
            success: function (res) {
                var $subdistrict = $(prefix + '-subdistrict_id');
                $subdistrict.empty().append('<option value="">กรุณาเลือกตำบล</option>');
                if (res.data) {
                    for (var i = 0; i < res.data.length; i++) {
                        $subdistrict.append('<option value="' + res.data[i].id + '">' + res.data[i].name + '</option>');
                    }
                }
                $subdistrict.val(data.subdistrict_id);

                // Step 5: Load zipcodes
                if (data.zipcode_id) {
                    loadZipcodeChain(prefix, data);
                }
            }
        });
    }

    function loadZipcodeChain(prefix, data) {
        $.ajax({
            method: 'GET',
            url: host + '/api/zipcode',
            data: { subdistrict_id: data.subdistrict_id },
            cache: false,
            dataType: 'json',
            success: function (res) {
                var $zipcode = $(prefix + '-zipcode_id');
                $zipcode.empty().append('<option value="">กรุณาเลือกรหัสไปรษณีย์</option>');
                if (res.data) {
                    for (var i = 0; i < res.data.length; i++) {
                        $zipcode.append('<option value="' + res.data[i].id + '">' + res.data[i].zipcode + '</option>');
                    }
                }
                $zipcode.val(data.zipcode_id);
            }
        });
    }
}

// Bind change events when DOM is ready
$(document).ready(function () {
    // Content forms (content-plant, content-animal, content-fungi, etc.)
    $('#content-region_id').on('change', function () { showProvince('#content'); });
    $('#content-province_id').on('change', function () { showDistrict('#content'); });
    $('#content-district_id').on('change', function () { showSubDistrict('#content'); });
    $('#content-subdistrict_id').on('change', function () { showZipcode('#content'); });

    // Profile (users) form
    $('#profile-region_id').on('change', function () { showProvince('#profile'); });
    $('#profile-province_id').on('change', function () { showDistrict('#profile'); });
    $('#profile-district_id').on('change', function () { showSubDistrict('#profile'); });
    $('#profile-subdistrict_id').on('change', function () { showZipcode('#profile'); });

    // School form
    $('#school-region_id').on('change', function () { showProvince('#school'); });
    $('#school-province_id').on('change', function () { showDistrict('#school'); });
    $('#school-district_id').on('change', function () { showSubDistrict('#school'); });
    $('#school-subdistrict_id').on('change', function () { showZipcode('#school'); });
});
