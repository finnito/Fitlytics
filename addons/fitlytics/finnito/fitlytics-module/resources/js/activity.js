/**
 * Global Variables
 **/
var Map;


/**
 * Initaliser for a hike post.
 * Kick off functions that occur
 * before loading the GPX data.
 **/
function initMap() {
    // Create Map
    Map = L.map('Map', {zoom: 12, center: L.latLng(-43.55947876166007, 172.63676687379547), fullscreenControl: true});

    // Manage map tiles
    var layer50 = L.tileLayer('https://tiles-a.data-cdn.linz.govt.nz/services;key=50b8923a67814d28b7a1067e28f03000/tiles/v4/layer=50767/EPSG:3857/{z}/{x}/{y}.png', {
        attribution: 'NZ Topo Map by <a href="https://data.linz.govt.nz/layer/50767-nz-topo50-maps/">LINZ</a>'
    });
    var layer250 = L.tileLayer('https://tiles-a.data-cdn.linz.govt.nz/services;key=50b8923a67814d28b7a1067e28f03000/tiles/v4/layer=50798/EPSG:3857/{z}/{x}/{y}.png', {
        attribution: 'NZ Topo Map by <a href="https://data.linz.govt.nz/layer/50767-nz-topo250-maps/">LINZ</a>'
    });
    if (Map.getZoom() > 12) {
        Map.removeLayer(layer250);
        layer50.addTo(Map);
    } else {
        Map.removeLayer(layer50);
        layer250.addTo(Map);
    }
    Map.on('fullscreenchange', function () {
        Map.invalidateSize();
    });
    Map.on("zoomend", function(e){
        if (Map.getZoom() > 12) {
            Map.removeLayer(layer250);
            layer50.addTo(Map);
            return;
        }
        Map.removeLayer(layer50);
        layer250.addTo(Map);
    });

    // console.log(activityPolyline);
    var orangeIcon = L.icon({
        iconUrl: '/img/marker-icon-2x-orange.png',
        iconSize:     [25, 41],
        iconAnchor:   [13, 41],
        popupAnchor:  [13, -41],
        tooltipAnchor:  [13, -41]
    });

    var route = L.Polyline.fromEncoded(activityPolyline,{color: "#ff3838"}).addTo(Map);
    Map.fitBounds(route.getBounds());
}


/**
 * Kick off the hike listener
 * when the document has loaded.
 **/
document.addEventListener("DOMContentLoaded", initMap());
