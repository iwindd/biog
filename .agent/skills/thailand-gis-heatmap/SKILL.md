---
name: thailand-gis-heatmap
description: Create, optimize, and integrate a Leaflet-based interactive geographic heatmap for Thailand using GeoJSON/TopoJSON files for provinces and districts (Amphoe). Use this skill whenever a user requests a Thailand data map, a choropleth map, an interactive map with drill-down from province to district level, or when they want to visualize GIS data on a web application without an underlying world tile layer.
---

# Thailand GIS Heatmap Skill

This skill allows Claude to confidently generate and optimize an interactive, drill-down geographic heatmap specifically focused on Thailand's provinces and districts (Amphoe).

## When to use this skill

- When the user asks to build an interactive map for Thailand.
- When creating a choropleth map visualizing data metrics across Thai provinces.
- When there's a requirement to click on a province to zoom in and dynamically load its constituent districts.
- When optimizing map layers to ensure performance without requiring external tile mapping services (like OpenStreetMap or Google Maps as basemaps).

## Key Concepts and Workflow

### 1. The Map Foundation (Leaflet)

Use Leaflet.js as the core mapping library. Avoid ECharts for maps with complex zooming features if performance and "phantom panning" jitter are concerns.

- Center map on Thailand: `center: [13.736717, 100.523186]`
- Useful zoom ranges: `zoom: 5.5, minZoom: 5, maxZoom: 12`
- Omit `L.tileLayer` entirely so the background blends into the parent web UI.

### 2. Data Sources (GeoJSON/TopoJSON)

You will generally need two files:

1. `./data_sources/province_simplify.json` (or similar name) - Contains the boundary data for the 77 provinces.
2. `./data_sources/thailand_province_amphoe_simplify.json` (or similar name) - Contains the boundary data for all districts.

_Note: Always use topojson-client if the files might be TopoJSON format instead of standard GeoJSON._

### 3. Drill-Down Interaction Logic

1. Load and display the **Province** layer first.
2. Bind a `click` event listener to each province feature.
3. On Click:
   - Call `map.fitBounds(clickedLayer.getBounds())` to zoom in smoothly.
   - Remove/Hide the province layer and province labels layer.
   - Filter the **Amphoe** data to only include districts belonging to the clicked province (usually by string matching the province name `ADM1_TH`, `PROV_NAMT`, or `pro_name`).
   - Draw the **Amphoe** layer and amphoe labels layer.
   - Show a "Back" button to allow the user to return to the national view.
4. On "Back" click:
   - Remove/Hide the Amphone layers.
   - Restore the Province layers.
   - Call `map.fitBounds(provinceLayer.getBounds())`.

### 4. Visuals and Best Practices

- **Layer groups:** Use separate `L.layerGroup()` for province labels and amphoe labels to ensure they don't break when switching views.
- **Labels:** Use `L.tooltip({ permanent: true, direction: "center", className: "area-label", interactive: false })`.
- **Zoom Constraints for Labels:** Connect a `zoomend` map event listener. Hide province labels when zoomed out (e.g. `zoom < 6`) to avoid clutter, and show them when zoomed in to the region level (`zoom >= 6`). Amphoe labels should always be shown when in Amphoe mode.
- **Hover effects:** Use `layer.setStyle({ weight: 2, color: "#ff9900", fillOpacity: 0.95 });` on `mouseover`.

## Example Implementation

A core template for map initialization:

```javascript
const map = L.map("map", { center: [13.736717, 100.523186], zoom: 5.5 });
// No tileLayer added for a clean vector-only look

// Separate label layers
let provinceLabelsLayer = L.layerGroup().addTo(map);
let amphoeLabelsLayer = L.layerGroup().addTo(map);

map.on("zoomend", function () {
  // Only show province labels if zoomed in reasonably close
  // Amphoe labels are handled separately during the mode switch.
  const zoom = map.getZoom();
  document.querySelectorAll(".area-label").forEach((l) => {
    if (currentMode === "province") {
      zoom >= 6 ? l.classList.remove("hidden") : l.classList.add("hidden");
    } else {
      l.classList.remove("hidden");
    }
  });
});
```

## Handling Missing Links

If the joining key between the province name from the Province file and the Amphoe file differs:

- Attempt to normalize strings by `.replace("จ.", "").replace("จังหวัด", "").trim()`.
- If a match is completely empty, fallback gracefully (e.g., render 20 random districts for demonstration) to prevent a blank screen.

## Final Note

The output HTML/JS must be optimized for production. Avoid inline styles where classes serve better, remove debug comments, and ensure variables and elements follow clean conventions.

## Full Working Example

A complete HTML file demonstrating the full workflow (Map setup, TopoJSON parsing, drill-down logic, and UI styling) is available at [`examples/map.html`](./examples/map.html).

<details>
<summary>Click to view the full <code>map.html</code> source code</summary>

```html
<!doctype html>
<html lang="th">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Thailand Province to Amphoe Heatmap</title>
    <link
      rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""
    />
    <script
      src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
      integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
      crossorigin=""
    ></script>
    <script src="https://unpkg.com/topojson-client@3"></script>

    <style>
      :root {
        --primary-bg: #f4f4f9;
        --card-bg: #ffffff;
        --text-main: #2c3e50;
        --text-sub: #7f8c8d;
        --btn-color: #3498db;
        --btn-hover: #2980b9;
      }

      body {
        margin: 0;
        padding: 0;
        font-family: "Inter", "Roboto", sans-serif; /* Setup clean typography */
        background-color: var(--primary-bg);
      }

      /* Layout Container */
      .layout-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        box-sizing: border-box;
      }

      /* Main Card */
      .map-paper {
        background: var(--card-bg);
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
        padding: 24px;
        width: 100%;
        max-width: 900px;
        display: flex;
        flex-direction: column;
      }

      /* Header */
      .map-header {
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
      }

      .header-titles h2 {
        margin: 0;
        color: var(--text-main);
        font-size: 24px;
        font-weight: 600;
        transition: opacity 0.3s;
      }

      .header-titles p {
        margin: 6px 0 0 0;
        color: var(--text-sub);
        font-size: 14px;
      }

      .btn-back {
        padding: 10px 18px;
        background-color: var(--btn-color);
        color: white;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        display: none;
        transition:
          background-color 0.2s ease,
          transform 0.1s;
        box-shadow: 0 2px 6px rgba(52, 152, 219, 0.3);
      }

      .btn-back:hover {
        background-color: var(--btn-hover);
        transform: translateY(-1px);
      }

      .btn-back:active {
        transform: translateY(1px);
      }

      /* Map Container */
      .map-wrapper {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #eaeaea;
      }

      #map {
        width: 100%;
        height: 600px;
        background-color: transparent !important;
      }

      /* Spinner */
      .loading {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        font-size: 16px;
        color: var(--text-main);
        z-index: 10000;
        background: rgba(255, 255, 255, 0.95);
        padding: 15px 25px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        display: none;
        font-weight: 500;
      }

      /* Transparent text labels */
      .area-label {
        background: transparent;
        border: transparent;
        box-shadow: none;
        font-size: 12px;
        color: rgba(0, 0, 0, 0.6);
        font-weight: 600;
        text-align: center;
        text-shadow:
          2px 2px 0px rgba(255, 255, 255, 0.9),
          -2px -2px 0px rgba(255, 255, 255, 0.9),
          2px -2px 0px rgba(255, 255, 255, 0.9),
          -2px 2px 0px rgba(255, 255, 255, 0.9);
        pointer-events: none;
        transition: opacity 0.3s ease;
      }

      .area-label-hidden {
        opacity: 0 !important; /* Smooth fade rather than jumpy display:none */
      }

      /* Legend */
      .info.legend {
        background: white;
        padding: 12px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        line-height: 24px;
        color: var(--text-main);
        font-size: 13px;
      }

      .info.legend h4 {
        margin: 0 0 8px 0;
        font-weight: 600;
        font-size: 14px;
      }

      .info.legend i {
        width: 18px;
        height: 18px;
        float: left;
        margin-right: 8px;
        opacity: 0.9;
        border-radius: 4px;
      }
    </style>
  </head>
  <body>
    <div class="layout-container">
      <div class="map-paper">
        <div class="map-header">
          <div class="header-titles">
            <h2 id="main-title">ความหนาแน่นระดับจังหวัด</h2>
            <p id="sub-title">คลิกที่จังหวัดเพื่อดูข้อมูลในระดับอำเภอ</p>
          </div>
          <button
            id="btn-back"
            class="btn-back"
            aria-label="Go back to national view"
          >
            ⬅ กลับไปหน้าประเทศ
          </button>
        </div>
        <div class="map-wrapper">
          <div id="loading" class="loading">Loading Map Data...</div>
          <div id="map"></div>
        </div>
      </div>
    </div>

    <script>
      const CONFIG = {
        center: [13.736717, 100.523186],
        minZoom: 5,
        maxZoom: 12,
        zoomSnap: 0.5,
        zoomDelta: 0.5,
        provinceZoomThreshold: 6,
        paths: {
          province: "../../province/province_simplify.json",
          amphoe: "../../amphoe/thailand_province_amphoe_simplify.json",
        },
      };

      const Elements = {
        loading: document.getElementById("loading"),
        btnBack: document.getElementById("btn-back"),
        mainTitle: document.getElementById("main-title"),
      };

      const State = {
        currentMode: "province",
        cachedAmphoeData: null,
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

      // UTILITIES
      function getColor(d) {
        return d > 800
          ? "#005a96"
          : d > 600
            ? "#0070bc"
            : d > 400
              ? "#4293d3"
              : d > 200
                ? "#85bbee"
                : "#d4f0ff";
      }

      function styleGeoJson(feature) {
        return {
          fillColor: getColor(feature.properties.mockValue),
          weight: 1,
          opacity: 1,
          color: "#ffffff",
          fillOpacity: 0.85,
        };
      }

      function setLoading(isLoading) {
        Elements.loading.style.display = isLoading ? "block" : "none";
      }

      function parseMapData(data) {
        if (data.type === "Topology") {
          const key = Object.keys(data.objects)[0];
          return topojson.feature(data, data.objects[key]);
        }
        return data;
      }

      // LEGEND
      const legend = L.control({ position: "bottomright" });
      legend.onAdd = function () {
        const div = L.DomUtil.create("div", "info legend");
        const grades = [0, 200, 400, 600, 800];

        div.innerHTML += "<h4>ความหนาแน่น</h4>";
        for (let i = 0; i < grades.length; i++) {
          div.innerHTML +=
            '<i style="background:' +
            getColor(grades[i] + 1) +
            '"></i> ' +
            grades[i] +
            (grades[i + 1] ? "&ndash;" + grades[i + 1] + "<br>" : "+");
        }
        return div;
      };
      legend.addTo(map);

      // PROVINCE MAP
      async function loadProvinceMap() {
        setLoading(true);
        try {
          const res = await fetch(CONFIG.paths.province);
          const data = await res.json();
          const geoJsonData = parseMapData(data);

          // Populate mocks
          geoJsonData.features.forEach((f) => {
            f.properties.mockValue = Math.floor(Math.random() * 1000);
            f.properties.provName =
              f.properties.ADM1_TH ||
              f.properties.ADM1_EN ||
              f.properties.name ||
              "N/A";
          });

          if (provinceLayer) map.removeLayer(provinceLayer);
          provinceLabelsLayer.clearLayers();

          provinceLayer = L.geoJson(geoJsonData, {
            style: styleGeoJson,
            onEachFeature: function (feature, layer) {
              const provName = feature.properties.provName;
              const value = feature.properties.mockValue;

              layer.bindTooltip(`<b>${provName}</b><br>ระดับ: ${value}`, {
                sticky: true,
              });
              layer.on({
                mouseover: (e) => highlightFeature(e),
                mouseout: (e) => provinceLayer.resetStyle(e.target),
                click: () => handleProvinceClick(feature, layer),
              });

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

          switchMode("province");
        } catch (err) {
          console.error("Error loading province map module:", err);
          Elements.mainTitle.innerText = "Error Loading Map Data";
        } finally {
          setLoading(false);
        }
      }

      function highlightFeature(e) {
        const layer = e.target;
        layer.setStyle({ weight: 2, color: "#f39c12", fillOpacity: 1 });
        if (!L.Browser.ie && !L.Browser.opera && !L.Browser.edge) {
          layer.bringToFront();
        }
      }

      // LOAD AMPHOE MAP
      async function handleProvinceClick(feature, layer) {
        // Reset old hover style so it doesn't get stuck later
        provinceLayer.resetStyle(layer);

        const provName = feature.properties.provName;
        Elements.mainTitle.innerText = `ความหนาแน่นระดับอำเภอ : ${provName}`;
        Elements.btnBack.style.display = "inline-block";

        // Smoothly zoom to target
        map.fitBounds(layer.getBounds(), {
          padding: [50, 50],
          animate: true,
          duration: 0.8,
        });

        setLoading(true);

        try {
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

        // Fallback fallback mechanism
        if (filteredFeatures.length === 0) {
          console.warn(
            `Could not perfectly match province name '${targetProvName}'. Using fallback sample slice.`,
          );
          filteredFeatures = geoJsonData.features.slice(0, 20);
        }

        filteredFeatures.forEach((f) => {
          f.properties.mockValue = Math.floor(Math.random() * 1000);
          f.properties.ampName =
            f.properties.AP_TH ||
            f.properties.AMP_NAMT ||
            f.properties.ADM2_TH ||
            f.properties.amp_name ||
            "Unknown";
        });

        if (amphoeLayer) map.removeLayer(amphoeLayer);
        amphoeLabelsLayer.clearLayers();

        amphoeLayer = L.geoJson(
          { type: "FeatureCollection", features: filteredFeatures },
          {
            style: styleGeoJson,
            onEachFeature: function (feature, layer) {
              const ampName = feature.properties.ampName;

              layer.bindTooltip(
                `<b>${ampName}</b><br>ระดับ: ${feature.properties.mockValue}`,
                { sticky: true },
              );
              layer.on({
                mouseover: (e) => highlightFeature(e),
                mouseout: (e) => amphoeLayer.resetStyle(e.target),
              });

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

        switchMode("amphoe");
      }

      // VIEW STATE MANAGEMENT
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

          Elements.btnBack.style.display = "none";
          Elements.mainTitle.innerText = "ความหนาแน่นระดับจังหวัด";
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
      Elements.btnBack.addEventListener("click", () => switchMode("province"));

      // KICKOFF
      document.addEventListener("DOMContentLoaded", loadProvinceMap);
    </script>
  </body>
</html>
```

</details>
