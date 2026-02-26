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
