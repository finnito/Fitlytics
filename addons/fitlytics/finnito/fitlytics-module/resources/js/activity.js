/**
 * Initaliser for a hike post.
 * Kick off functions that occur
 * before loading the GPX data.
 **/
function initActivityMap() {
    // Create ActivityMap
    var ActivityMap = new L.Map('Map');

    // Manage ActivityMap tiles
    var layer50 = new L.tileLayer('https://tiles-a.data-cdn.linz.govt.nz/services;key=50b8923a67814d28b7a1067e28f03000/tiles/v4/layer=50767/EPSG:3857/{z}/{x}/{y}.png', {
        attribution: 'NZ Topo Map by <a href="https://data.linz.govt.nz/layer/50767-nz-topo50-ActivityMaps/">LINZ</a>'
    });
    var layer250 = new L.tileLayer('https://tiles-a.data-cdn.linz.govt.nz/services;key=50b8923a67814d28b7a1067e28f03000/tiles/v4/layer=50798/EPSG:3857/{z}/{x}/{y}.png', {
        attribution: 'NZ Topo Map by <a href="https://data.linz.govt.nz/layer/50767-nz-topo250-ActivityMaps/">LINZ</a>'
    });

    ActivityMap.on('fullscreenchange', function () {
        ActivityMap.invalidateSize();
    });

    ActivityMap.on("zoomend", function(e){
        if (ActivityMap.getZoom(e) > 12) {
            ActivityMap.removeLayer(layer250);
            layer50.addTo(ActivityMap);
            return;
        }
        ActivityMap.removeLayer(layer50);
        layer250.addTo(ActivityMap);
    });

    var route = L.Polyline.fromEncoded(activityPolyline, {color: "#ff3838"}).addTo(ActivityMap);
    ActivityMap.fitBounds(route.getBounds());
}


/**
 * Kick off the hike listener
 * when the document has loaded.
 **/
document.addEventListener("DOMContentLoaded", initActivityMap);
