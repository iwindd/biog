const CONFIG = {
  center: [13.736717, 100.523186],
  minZoom: 5,
  maxZoom: 12,
  zoomSnap: 0.5,
  zoomDelta: 0.5,
  provinceZoomThreshold: 6,
  paths: {
    province: "/data/province_simplify.json",
    amphoe: "/data/thailand_province_amphoe_simplify.json",
  },
  heatmapColors: ["#d4d4d4", "#e0e7ff", "#818cf8", "#4f46e5", "#3730a3"],
  heatmapRanges: [0, 1, 10, 50, 100],
};

const Elements = {
  loading: document.getElementById("loading"),
  btnBack: document.getElementById("btn-back-map"),
  mainTitle: document.getElementById("main-title-map"),
};

const State = {
  currentMode: "province",
  cachedAmphoeData: null,
  provinceCounts: {},
  districtCounts: {},
  provinceNameToId: {},
  districtNameToId: {},
};

function normalizeThaiName(str) {
  if (!str) return "";
  return str.replace(/จังหวัด|จ\.|เขต|อ\.|อำเภอ/g, "").trim();
}

// Variable from old map.js
var domainMap = "", n = 0, markers = [], numResult = 0, idMarker = 0, languageCurrent = "th", pathname = window.location.pathname;
if (pathname.indexOf("/en/") != -1) {
  languageCurrent = "en";
}
var urlParams = new URLSearchParams(window.location.search), host = "";
if (location.hostname !== "localhost" && location.hostname !== "127.0.0.1") {
  // host = "";
}

var iconBase = domainMap + "/images/marker/";
var icons = {
  plant: iconBase + "01_Plan.png",
  animals: iconBase + "02_animal.png",
  fungi: iconBase + "03_fungi.png",
  expert: iconBase + "04_tk.png",
  ecotourism: iconBase + "05_ecotourism.png",
  product: iconBase + "06_product.png"
};
var nameDataBase = {
  plant: "พืช",
  animals: "สัตว์",
  fungi: "จุลินทรีย์",
  expert: "ภูมิปัญญา / ปราชญ์ Expert",
  ecotourism: "การท่องเที่ยวเชิงนิเวศ",
  product: "ผลิตภัณฑ์ชุมชน"
};

var searchData = {
  region_id: "",
  province_id: "",
  district_id: "",
  subdistrict_id: "",
  keyword: "",
  type: "1,2,3,4,5,6",
  page: 1
};


// INIT LEAFLET
const map = L.map("map", {
  center: CONFIG.center,
  zoom: 5.5,
  minZoom: CONFIG.minZoom,
  maxZoom: CONFIG.maxZoom,
  zoomSnap: CONFIG.zoomSnap,
  zoomDelta: CONFIG.zoomDelta,
});

// LAYERS
let provinceLayer = null;
let amphoeLayer = null;
let provinceLabelsLayer = L.layerGroup().addTo(map);
let amphoeLabelsLayer = L.layerGroup().addTo(map);
let markersLayerGroup = L.layerGroup().addTo(map);

// UTILITIES
function getHeatmapColor(count) {
  const colors = CONFIG.heatmapColors;
  const ranges = CONFIG.heatmapRanges;
  for (let i = ranges.length - 1; i >= 0; i--) {
    if (count >= ranges[i]) return colors[i];
  }
  return colors[0];
}

function styleGeoJson(feature) {
  let count = 0;
  if (State.currentMode === "province") {
    const pId = feature.properties.ID || feature.properties.id;
    count = State.provinceCounts[pId];

    if (count === undefined) {
      const thName = normalizeThaiName(feature.properties.ADM1_TH || feature.properties.name_th || feature.properties.name);
      const enName = (feature.properties.ADM1_EN || "").toLowerCase();
      const mappedId = State.provinceNameToId[thName] || State.provinceNameToId[enName];
      if (mappedId) count = State.provinceCounts[mappedId];
    }
    if (count === undefined) count = 0;
  } else {
    const dId = feature.properties.ID || feature.properties.id || feature.properties.ADM2_PCODE;
    count = State.districtCounts[dId];

    if (count === undefined) {
      const aThName = normalizeThaiName(feature.properties.ADM2_TH || feature.properties.AMP_NAMT || feature.properties.amp_name);
      const aEnName = (feature.properties.ADM2_EN || "").toLowerCase();
      const dMappedId = State.districtNameToId[aThName] || State.districtNameToId[aEnName];
      if (dMappedId) count = State.districtCounts[dMappedId];
    }
    if (count === undefined) count = 0;
  }

  return {
    fillColor: getHeatmapColor(count),
    weight: 1,
    opacity: 1,
    color: "#ffffff",
    fillOpacity: 0.8,
  };
}

function setLoading(isLoading) {
  if (Elements.loading) {
    Elements.loading.style.display = isLoading ? "block" : "none";
  }
}

function parseMapData(data) {
  if (data.type === "Topology") {
    const key = Object.keys(data.objects)[0];
    return topojson.feature(data, data.objects[key]);
  }
  return data;
}

// PROVINCE MAP
async function loadProvinceMap() {
  setLoading(true);
  try {
    const countRes = await fetch(host + "/api/heatmap-province");
    const countResult = await countRes.json();
    if (countResult.status === 200) {
      State.provinceCounts = {};
      State.provinceNameToId = {};
      countResult.data.forEach(item => {
        State.provinceCounts[item.id] = item.total;
        if (item.name_th) State.provinceNameToId[normalizeThaiName(item.name_th)] = item.id;
        if (item.name_en) State.provinceNameToId[item.name_en.toLowerCase()] = item.id;
      });
    }

    const res = await fetch(CONFIG.paths.province);
    const data = await res.json();
    const geoJsonData = parseMapData(data);

    // Populate properties
    geoJsonData.features.forEach((f) => {
      f.properties.provName =
        f.properties.ADM1_TH ||
        f.properties.ADM1_EN ||
        f.properties.name ||
        "N/A";
    });

    if (provinceLayer) map.removeLayer(provinceLayer);
    provinceLabelsLayer.clearLayers();

    State.currentMode = "province";
    provinceLayer = L.geoJson(geoJsonData, {
      style: styleGeoJson,
      onEachFeature: function (feature, layer) {
        const provName = feature.properties.provName;
        const pId = feature.properties.ID || feature.properties.id;
        let count = State.provinceCounts[pId];

        if (count === undefined) {
          const thName = normalizeThaiName(feature.properties.ADM1_TH || feature.properties.name_th || feature.properties.name);
          const enName = (feature.properties.ADM1_EN || "").toLowerCase();
          const mappedId = State.provinceNameToId[thName] || State.provinceNameToId[enName];
          if (mappedId) count = State.provinceCounts[mappedId];
        }
        count = count || 0;
        
        layer.on({
          mouseover: (e) => highlightFeature(e),
          mouseout: (e) => provinceLayer.resetStyle(e.target),
          click: () => handleProvinceClick(feature, layer),
        });

        layer.bindTooltip(`${provName} (เนื้อหา: ${count})`, { sticky: true });

        const center = layer.getBounds().getCenter();
        const label = L.tooltip({
          permanent: true,
          direction: "center",
          className: "area-label area-label-hidden",
          interactive: false,
        })
          .setContent(provName)
          .setLatLng(center);

        provinceLabelsLayer.addLayer(label);
      },
    });

    switchMode(State.currentMode);
    renderLegend();
  } catch (err) {
    console.error("Error loading province map module:", err);
    if (Elements.mainTitle) Elements.mainTitle.innerText = "Error Loading Map Data";
  } finally {
    setLoading(false);
  }
}

function highlightFeature(e) {
  const layer = e.target;
  layer.setStyle({ weight: 2, color: "#f39c12", fillOpacity: 0.8 });
  if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
    layer.bringToFront();
  }
}

// LOAD AMPHOE MAP
async function handleProvinceClick(feature, layer) {
  provinceLayer.resetStyle(layer);

  const provName = feature.properties.provName;
  if (Elements.mainTitle) Elements.mainTitle.innerText = `ขอบเขต: ${provName}`;
  if (Elements.btnBack) Elements.btnBack.style.display = "inline-block";

  // Smoothly zoom to target
  map.fitBounds(layer.getBounds(), {
    padding: [50, 50],
    animate: true,
    duration: 0.8,
  });

  setLoading(true);

  try {
    const countRes = await fetch(host + `/api/heatmap-district?province_name=${feature.properties.ADM1_TH}`);
    const countResult = await countRes.json();
    if (countResult.status === 200) {
      State.districtCounts = {};
      State.districtNameToId = {};
      countResult.data.forEach(item => {
        State.districtCounts[item.id] = item.total;
        if (item.name_th) State.districtNameToId[normalizeThaiName(item.name_th)] = item.id;
        if (item.name_en) State.districtNameToId[item.name_en.toLowerCase()] = item.id;
      });
    }

    if (!State.cachedAmphoeData) {
      const res = await fetch(CONFIG.paths.amphoe);
      const data = await res.json();
      State.cachedAmphoeData = parseMapData(data);
    }
    renderAmphoeMap(State.cachedAmphoeData, provName);
  } catch (err) {
    console.error("Error loading amphoe data:", err);
  } finally {
    setLoading(false);
  }
}

// RENDER AMPHOE
function renderAmphoeMap(geoJsonData, targetProvName) {
  const cleanTarget = targetProvName
    .replace("จ.", "")
    .replace("จังหวัด", "")
    .trim();

  let filteredFeatures = geoJsonData.features.filter((f) => {
    let pName =
      f.properties.PV_TH ||
      f.properties.PROV_NAMT ||
      f.properties.ADM1_TH ||
      f.properties.pro_name ||
      "";
    return (
      pName.replace("จ.", "").replace("จังหวัด", "").trim() ===
      cleanTarget
    );
  });

  if (filteredFeatures.length === 0) {
    console.warn(`Could not perfectly match province name '${targetProvName}'.`);
    filteredFeatures = geoJsonData.features.slice(0, 20); // fallback
  }

  filteredFeatures.forEach((f) => {
    f.properties.ampName =
      f.properties.AP_TH ||
      f.properties.AMP_NAMT ||
      f.properties.ADM2_TH ||
      f.properties.amp_name ||
      "Unknown";
  });

  if (amphoeLayer) map.removeLayer(amphoeLayer);
  amphoeLabelsLayer.clearLayers();

  State.currentMode = "amphoe";
  amphoeLayer = L.geoJson(
    { type: "FeatureCollection", features: filteredFeatures },
    {
      style: styleGeoJson,
      onEachFeature: function (feature, layer) {
        const ampName = feature.properties.ampName;
        const dId = feature.properties.ID || feature.properties.id || feature.properties.ADM2_PCODE;
        let dCount = State.districtCounts[dId];

        if (dCount === undefined) {
          const aThName = normalizeThaiName(feature.properties.ADM2_TH || feature.properties.AMP_NAMT || feature.properties.amp_name);
          const aEnName = (feature.properties.ADM2_EN || "").toLowerCase();
          const dMappedId = State.districtNameToId[aThName] || State.districtNameToId[aEnName];
          if (dMappedId) dCount = State.districtCounts[dMappedId];
        }
        const count = dCount || 0;

        layer.on({
          mouseover: (e) => highlightFeature(e),
          mouseout: (e) => amphoeLayer.resetStyle(e.target),
        });

        layer.bindTooltip(`${ampName} (เนื้อหา: ${count})`, { sticky: true });

        const center = layer.getBounds().getCenter();
        const label = L.tooltip({
          permanent: true,
          direction: "center",
          className: "area-label area-label-hidden",
          interactive: false,
        })
          .setContent(ampName)
          .setLatLng(center);

        amphoeLabelsLayer.addLayer(label);
      },
    },
  );

  switchMode(State.currentMode);
}

function switchMode(mode) {
  State.currentMode = mode;
  if (mode === "province") {
    if (amphoeLayer) map.removeLayer(amphoeLayer);
    if (amphoeLabelsLayer) map.removeLayer(amphoeLabelsLayer);

    if (provinceLayer && !map.hasLayer(provinceLayer))
      provinceLayer.addTo(map);
    if (provinceLabelsLayer && !map.hasLayer(provinceLabelsLayer))
      provinceLabelsLayer.addTo(map);

    if (provinceLayer)
      map.fitBounds(provinceLayer.getBounds(), {
        animate: true,
        duration: 0.6,
      });

    if (Elements.btnBack) Elements.btnBack.style.display = "none";
    if (Elements.mainTitle) Elements.mainTitle.innerText = "ขอบเขตระดับประเทศ";
  } else if (mode === "amphoe") {
    if (provinceLayer) map.removeLayer(provinceLayer);
    if (provinceLabelsLayer) map.removeLayer(provinceLabelsLayer);

    if (amphoeLayer && !map.hasLayer(amphoeLayer)) amphoeLayer.addTo(map);
    if (amphoeLabelsLayer && !map.hasLayer(amphoeLabelsLayer))
      amphoeLabelsLayer.addTo(map);
  }

  requestAnimationFrame(() => checkLabelsVisibility());
}

function checkLabelsVisibility() {
  const zoom = map.getZoom();
  const labels = document.querySelectorAll(".area-label");

  if (State.currentMode === "province") {
    if (zoom >= CONFIG.provinceZoomThreshold) {
      labels.forEach((t) => t.classList.remove("area-label-hidden"));
    } else {
      labels.forEach((t) => t.classList.add("area-label-hidden"));
    }
  } else {
    labels.forEach((t) => t.classList.remove("area-label-hidden"));
  }
}

// BIND EVENTS
map.on("zoomend", checkLabelsVisibility);
if (Elements.btnBack) Elements.btnBack.addEventListener("click", () => switchMode("province"));

// ------- INTEGRATING OLD MAP.JS API LOGIC --------

$(document).ready(function () {
  checkSearchParams();
});

function getLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(showPosition);
  } else {
    console.log("Geolocation is not supported by this browser.");
  }
}

function showPosition(position) {
  let lat = position.coords.latitude;
  let lng = position.coords.longitude;
  map.setView([lat, lng], 14);
}

function setLocationRegion(lat, lng, zoomLevel) {
  map.setView([lat, lng], zoomLevel);
}

function checkSearchParams() {
  if (urlParams.has("region_id")) {
    searchData.region_id = urlParams.get("region_id");
    $("#map-region_id").val(searchData.region_id);
    showProvinceMap("#map-form", "get");
  }
  if (urlParams.has("province_id")) {
    searchData.province_id = urlParams.get("province_id");
    showDistrictMap("#map-form", "get");
  }
  if (urlParams.has("district_id")) {
    searchData.district_id = urlParams.get("district_id");
    showSubDistrictMap("#map-form", "get");
  }
  if (urlParams.has("subdistrict_id")) {
    searchData.subdistrict_id = urlParams.get("subdistrict_id");
  }
  if (urlParams.has("keyword")) {
    searchData.keyword = urlParams.get("keyword");
    $("#keyword-text").val(searchData.keyword);
  }
  if (urlParams.has("type")) {
    searchData.type = urlParams.get("type");
    // Update checkboxes UI based on URL type param
    let arr = searchData.type.split(",");
    let chks = $(".check-content-type").find("input");
    chks.each(function() {
      $(this).prop("checked", arr.includes($(this).val()));
    });
  }
  search();
}

function checkboxType() {
  let typeStr = "";
  let typeEle = $(".check-content-type").find("input");
  for (let i = 0; i < typeEle.length; i++) {
    if (typeEle[i].checked == true) {
      typeStr = typeStr + typeEle[i].value + ",";
    }
  }
  if (typeStr.length > 0) {
    typeStr = typeStr.substr(0, typeStr.length - 1);
  }
  searchData.type = typeStr;
  search();
}

function readMore(e) {
  $(e).attr('disabled', 'disabled');
  searchData.page = searchData.page + 1;
  $.ajax({
    method: "GET",
    url: host + "/api/searchcontent",
    cache: false,
    data: searchData,
    dataType: "json",
    contentType: "application/json; charset=utf-8",
    error: function (error) {
      $(e).removeAttr('disabled');
    },
    success: function (result) {
      if (result.data.page_current >= result.data.all_page) {
        $("button.btn-read-more").hide();
      } else {
        $("button.btn-read-more").show();
      }
      if (result.data.content.length > 0) {
        setContentMapMarker(result.data);
        setContentList(result.data);
      } else {
        searchData.page = searchData.page - 1;
      }
      $(e).removeAttr('disabled');
    }
  });
}

function updateSearchParamInSearchData() {
  let urlStr = "";
  urlStr = updateURLParameter(window.location.href, 'region_id', searchData.region_id);
  urlStr = updateURLParameter(urlStr, 'province_id', searchData.province_id);
  urlStr = updateURLParameter(urlStr, 'district_id', searchData.district_id);
  urlStr = updateURLParameter(urlStr, 'subdistrict_id', searchData.subdistrict_id);
  urlStr = updateURLParameter(urlStr, 'keyword', searchData.keyword);
  urlStr = updateURLParameter(urlStr, 'type', searchData.type);
  return urlStr;
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

function search() {
  DeleteMarkers();
  searchData.page = 1;
  $("#show-list-item").html("");
  
  let urlStr = updateSearchParamInSearchData();
  window.history.replaceState(window.location.href, "", urlStr);

  $.ajax({
    method: "GET",
    url: host + "/api/searchcontent",
    cache: false,
    data: searchData,
    dataType: "json",
    contentType: "application/json; charset=utf-8",
    error: function (error) {
      console.log(error);
    },
    success: function (result) {
      if (result) {
        if (result.data.page_current >= result.data.all_page) {
          $("button.btn-read-more").hide();
        } else {
          $("button.btn-read-more").show();
        }
        showCountAllType(result.data.type_count); // Ensure it's passed correct property
        setContentMapMarker(result.data);
        setContentList(result.data);
      }
    }
  });
}

function DeleteMarkers() {
  markersLayerGroup.clearLayers();
  markers = [];
}

function getContentTypeName(type_id) {
  let text = "";
  if (type_id == 1) {
    text = "plant";
  } else if (type_id == 2) {
    text = "animals";
  } else if (type_id == 3) {
    text = "fungi";
  } else if (type_id == 4) {
    text = "expert";
  } else if (type_id == 5) {
    text = "ecotourism";
  } else if (type_id == 6) {
    text = "product";
  } else { text = "-" }
  return text;
}
function getContentPath(type_id) {
  let text = "/files/";
  if (type_id == 1) {
    text += "content-plant/";
  } else if (type_id == 2) {
    text += "content-animal/";
  } else if (type_id == 3) {
    text += "content-fungi/";
  } else if (type_id == 4) {
    text += "content-expert/";
  } else if (type_id == 5) {
    text += "content-ecotourism/";
  } else if (type_id == 6) {
    text += "content-product/";
  }
  return text;
}

$("#map-region_id").on("change", function () {
  searchData.region_id = $(this).val();
  searchData.province_id = "";
  searchData.district_id = "";
  searchData.subdistrict_id = "";
  search();
  showProvinceMap("#map-form");
  
  // Custom pan logic based on region
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
  } else {
      map.fitBounds(provinceLayer.getBounds());
  }
});

$("#map-province_id").on("change", function () {
  searchData.province_id = $(this).val();
  searchData.district_id = "";
  searchData.subdistrict_id = "";
  search();
  showDistrictMap("#map-form");
});

$("#map-district_id").on("change", function () {
  searchData.district_id = $(this).val();
  searchData.subdistrict_id = "";
  search();
  showSubDistrictMap("#map-form");
});

$("#map-subdistrict_id").on("change", function () {
  searchData.subdistrict_id = $(this).val();
  search();
});

$("#keyword-text").on("change", function () {
  searchData.keyword = $(this).val();
  search();
});

$(".btn-search").on("click", function () {
  searchData.keyword = $("#keyword-text").val();
  search();
});


function setContentMapMarker(data) {
  let contentData = data.content;

  for (let i = 0; i < contentData.length; i++) {
    let img_folder = "/files/";
    let type_name = getContentTypeName(contentData[i].type_id);

    img_folder = getContentPath(contentData[i].type_id) + contentData[i].picture_path;

    let contentString =
      '<div id="content" class="block-detail-location" onClick="goToViewContent(' + contentData[i].id + ',' + contentData[i].type_id + ')">' +
      '<div class="head-picture">' +
      '<img src="' + img_folder + '">' +
      '</div>' +
      '<div class="body-info">' +
      '<h4 id="firstHeading" class="firstHeading" title="' + contentData[i].name + ' ' + contentData[i].province_name + '">' + contentData[i].name + '</h4>' +
      "<p class='address mb-0'>" + contentData[i].province_name + " " + contentData[i].district_name + " " + contentData[i].subdistrict_name + "</p>" +
      '<div class="detail">' +
      (contentData[i].description != null ? contentData[i].description : "ไม่มีข้อมูล") +
      '</div>' +
      '</div>' +
      '</div>';


    let customIcon = L.icon({
      iconUrl: icons[type_name],
      iconSize: [45, 47], 
      iconAnchor: [22, 47], 
      popupAnchor: [0, -45]
    });

    let marker = L.marker([contentData[i].latitude, contentData[i].longitude], {icon: customIcon})
      .bindPopup(contentString)
      .addTo(markersLayerGroup);

    markers.push(marker);
  }
}

function setContentList(data) {
  let contentData = data.content;
  for (let i = 0; i < contentData.length; i++) {

    let address = "";
    if (contentData[i].region_name) { address = contentData[i].region_name; }
    if (contentData[i].region_name && contentData[i].province_name) { address = contentData[i].region_name + " " + contentData[i].province_name; }
    if (contentData[i].region_name && contentData[i].province_name && contentData[i].district_name) { address = contentData[i].region_name + " " + contentData[i].province_name + " " + contentData[i].district_name; }
    if (contentData[i].region_name && contentData[i].province_name && contentData[i].district_name && contentData[i].subdistrict_name) { address = contentData[i].region_name + " " + contentData[i].province_name + " " + contentData[i].district_name + " " + contentData[i].subdistrict_name; }


    let txt = '<a href="/content-' + getContentTypeName(contentData[i].type_id) + '/' + contentData[i].id + '">' +
      '<div class="row list-item"> ' +
      '<div class="col-md-4 col-sm-12 image">' +
      contentData[i].path_image +
      ' </div>' +
      '<div class="col-md-8 col-sm-12 detail"> ' +
      '<p class="item-name">' + contentData[i].name + '</p>' +
      '<p class="item-address">' + address + '</p>' +
      '<div class="item-detail">' +
      ((contentData[i].description != "" && contentData[i].description != null)  ? contentData[i].description : "ไม่พบข้อมูล") +
      '</div> ' +
      '</div> ' +
      '</div>' +
      '<hr/>' +
      '</a>';
    $("#show-list-item").append(txt);
  }
}

function showCountAllType(data) {
  if(!data) return;
  $("#span-type-1").html(data.plant);
  $("#span-type-2").html(data.animal);
  $("#span-type-3").html(data.fungi);
  $("#span-type-4").html(data.expert);
  $("#span-type-5").html(data.ecotourism);
  $("#span-type-6").html(data.product);
}

function goToViewContent(id, type_id) {
  let txt = getContentTypeName(type_id);
  window.location.href = "/content-" + txt + "/" + id;
}


function showProvinceMap(id_form, type = "") {
  let strSelectRegion = $(id_form + "-region_id option:selected").text();

  if ($(id_form + "-zipcode_id")) {
    $(id_form + "-zipcode_id").val("");
  }
  if ($(id_form + "-region").val() == 0) {
    $(id_form + "-province_id").val(0);
  }
  var region_id = $(id_form + "-region_id").val();

  $.ajax({
    method: "GET",
    url: host + "/api/province?region_id=" + region_id,
    cache: false,
    dataType: "json",
    contentType: "application/json; charset=utf-8",
    error: function (error) {

    },
    success: function (result) {

      $(id_form + "-province_id").empty();
      $(id_form + "-district_id").empty();
      $(id_form + "-subdistrict_id").empty();

      $(id_form + "-district_id").addClass("is-valid");
      $(id_form + "-district_id").attr('aria-invalid', "true");
      $(id_form + "-subdistrict_id").addClass("is-valid");
      $(id_form + "-subdistrict_id").attr('aria-invalid', "true");

      $(id_form + "-province_id").append($("<option></option>").attr("value", "").html("กรุณาเลือกจังหวัด"));
      $(id_form + "-district_id").append($("<option></option>").attr("value", "").html("กรุณาเลือกอำเภอ"));
      $(id_form + "-subdistrict_id").append($("<option></option>").attr("value", "").html("กรุณาเลือกตำบล"));
      for (let i = 0; i < result.data.length; i++) {
        $(id_form + "-province_id").append($("<option></option>").attr("value", +(result.data[i].id)).html(result.data[i].name).attr("selected", false));
      }

      if (type == "get") {
        $(id_form + "-province_id").val(searchData.province_id);
      }
      setContentBreadcrumb();
    }
  });

}
function showDistrictMap(id_form, type = "") {
  if ($(id_form + "-zipcode_id")) {
    $(id_form + "-zipcode_id").val("");
  }
  var province_id = $(id_form + "-province_id").val();
  if (province_id == "") { province_id = searchData.province_id }

  $.ajax({
    method: "GET",
    url: host + "/api/district?province_id=" + province_id,
    cache: false,
    dataType: "json",
    contentType: "application/json; charset=utf-8",
    error: function (error) {

    },
    success: function (result) {
      $(id_form + "-district_id").empty();
      $(id_form + "-subdistrict_id").empty();
      $(id_form + "-subdistrict_id").attr('aria-invalid', "true");
      $(id_form + "-district_id").append($("<option></option>").attr("value", "").html("กรุณาเลือกอำเภอ"));
      $(id_form + "-subdistrict_id").append($("<option></option>").attr("value", "").html("กรุณาเลือกตำบล"));

      for (let i = 0; i < result.data.length; i++) {

        $(id_form + "-district_id").append($("<option></option>").attr("value", +(result.data[i].id)).html(result.data[i].name).attr("selected", false));
      }

      if (urlParams.has('district_id') == true) {
        searchData.district_id = urlParams.get('district_id');
        $(id_form + "-district_id").val(searchData.district_id);
      }
      
      // Attempt to zoom to province
      if (province_id !== "" && result.data.length > 0 && provinceLayer) {
        let selectedProvinceText = $(id_form + "-province_id option:selected").text().trim();
        provinceLayer.eachLayer(function(layer) {
           let mapProvName = layer.feature.properties.provName.replace('จ.', '').replace('จังหวัด', '').trim();
           if(selectedProvinceText.indexOf(mapProvName) > -1 || mapProvName.indexOf(selectedProvinceText) > -1) {
             map.fitBounds(layer.getBounds());
           }
        });
      }

      setContentBreadcrumb();
    }
  });

}

function showSubDistrictMap(id_form, type = "") {
  if ($(id_form + "-zipcode_id")) {
    $(id_form + "-zipcode_id").val("");
  }
  var district_id = $(id_form + "-district_id").val();
  if (district_id == "") { district_id = searchData.district_id }

  $.ajax({
    method: "GET",
    url: host + "/api/subdistrict?district_id=" + district_id,
    cache: false,
    dataType: "json",
    contentType: "application/json; charset=utf-8",
    error: function (error) {

    },
    success: function (result) {
      $(id_form + "-subdistrict_id").empty();
      $(id_form + "-subdistrict_id").append($("<option></option>").attr("value", "").html("กรุณาเลือกตำบล"));
      for (let i = 0; i < result.data.length; i++) {
        $(id_form + "-subdistrict_id").append($("<option></option>").attr("value", +(result.data[i].id)).html(result.data[i].name).attr("selected", false));
      }

      if (urlParams.has('subdistrict_id') == true) {
        searchData.subdistrict_id = urlParams.get('subdistrict_id');
        $(id_form + "-subdistrict_id").val(searchData.subdistrict_id);
      }
      
      // Attempt to zoom to amphoe
      if(district_id !== "" && amphoeLayer) {
        let selectedAmpText = $(id_form + "-district_id option:selected").text().trim().replace('อ.', '').replace('อำเภอ', '').trim();
        amphoeLayer.eachLayer(function(layer) {
           let mapAmpName = layer.feature.properties.ampName.replace('อ.', '').replace('อำเภอ', '').trim();
           if(selectedAmpText.indexOf(mapAmpName) > -1 || mapAmpName.indexOf(selectedAmpText) > -1) {
             map.fitBounds(layer.getBounds());
           }
        });
      }

      setContentBreadcrumb();
    }
  });
}

function setContentBreadcrumb() {
  $(".search-breadcrumb").html("");

  let txt = "ขอบเขต: ";
  let url = "";
  let pash_name = window.location.pathname;
  if (searchData.region_id != "") {
    url = pash_name + "?region_id=" + searchData.region_id + "&province_id=&district_id=&subdistrict_id=&keyword=" + searchData.keyword + "&type=" + searchData.type;
    txt = txt + '<a href="' + url + '">' + $("#map-region_id option:selected").text() + "</a>";
  }
  if (searchData.province_id != "") {
    url = pash_name + "?region_id=" + searchData.region_id + "&province_id=" + searchData.province_id + "&district_id=&subdistrict_id=&keyword=" + searchData.keyword + "&type=" + searchData.type;
    txt = txt + ' <i class="fas fa-angle-right"></i> ' + '<a href="' + url + '">' + $("#map-province_id option:selected").text() + "</a>";
  }

  if (searchData.district_id != "") {
    url = pash_name + "?region_id=" + searchData.region_id + "&province_id=" + searchData.province_id + "&district_id=" + searchData.district_id + "&subdistrict_id=&keyword=" + searchData.keyword + "&type=" + searchData.type;
    txt = txt + ' <i class="fas fa-angle-right"></i> ' + '<a href="' + url + '">' + $("#map-district_id option:selected").text() + "</a>";
  }

  if (searchData.subdistrict_id != "") {
    url = pash_name + "?region_id=" + searchData.region_id + "&province_id=" + searchData.province_id + "&district_id=" + searchData.district_id + "&subdistrict_id=" + searchData.subdistrict_id + "&keyword=" + searchData.keyword + "&type=" + searchData.type;
    txt = txt + ' <i class="fas fa-angle-right"></i> ' + '<a href="' + url + '">' + $("#map-subdistrict_id option:selected").text() + "</a>";
  }


  $(".search-breadcrumb").html(txt);
}

let legendControl = null;
function renderLegend() {
  if (legendControl) map.removeControl(legendControl);

  legendControl = L.control({ position: "bottomright" });
  legendControl.onAdd = function() {
    const div = L.DomUtil.create("div", "info legend-map");
    const grades = CONFIG.heatmapRanges;
    const colors = CONFIG.heatmapColors;

    div.innerHTML = "<strong>จำนวนข้อมูล (หน่วย)</strong><br>";
    for (let i = 0; i < grades.length; i++) {
        div.innerHTML +=
            '<i style="background:' + colors[i] + '"></i> ' +
            grades[i] + (grades[i + 1] ? '&ndash;' + (grades[i + 1] - 1) + '<br>' : '+');
    }
    return div;
  };
  legendControl.addTo(map);
}

// KICKOFF
document.addEventListener("DOMContentLoaded", loadProvinceMap);
