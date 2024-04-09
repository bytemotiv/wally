var map;
var markerLayer;
var searchresultsLayer;
var userPosition;
var mapPing;

window.addEventListener("DOMContentLoaded", function () {

    map = L.map("map", {
        zoomControl: false,
    });
    map.setView([51.505, -0.09], 13);

    L.tileLayer(
        "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
        //"https://server.arcgisonline.com/ArcGIS/rest/services/World_Topo_Map/MapServer/tile/{z}/{y}/{x}",
        //"https://tile.openstreetmap.org/{z}/{x}/{y}.png",
        {
            minZoom: 3,
            maxZoom: 19,

            attribution: "&copy; <a href='http://www.openstreetmap.org/copyright'>OpenStreetMap</a>",

            useCache: true,
            cacheMaxAge: 365 * 24 * 60 * 60 * 1000,
            crossOrigin: true
        }
    ).addTo(map);

    markerLayer = L.layerGroup().addTo(map);
    searchresultsLayer = L.layerGroup().addTo(map);

    userPosition = L.marker([0,0], {
        icon: L.divIcon({
            iconSize: [12, 12],
            className: "wally-user",
        })
    });
    userPosition.addTo(map);

    mapPing = L.marker([0,0], {
        icon: L.divIcon({
            iconSize: [8, 8],
            className: "wally-ping",
        })
    });
    mapPing.addTo(map);

    map.on("click", e => {
        showMapPing(e.latlng.lat, e.latlng.lng);
    });


    map.on("moveend", e => {
        var ll = map.getCenter();
        setCookie("lastCenter", ll.lat + "|" + ll.lng);
        setCookie("lastZoom", map.getZoom());
    });

    loadMarkers();
});

// ----------------

function loadMarkers() {
    markerLayer.clearLayers();
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/markers/geojson");
    xhr.responseType = "json";
    xhr.onload = function() {
        if (xhr.status !== 200) {
            return;
        }

        var bounds = new L.LatLngBounds();
        L.geoJSON(xhr.response, {
            pointToLayer: function (feature, latlng) {
                bounds.extend(latlng);

                var marker = L.marker(latlng, {
                    icon: L.divIcon({
                        iconSize: [30, 30],
                        iconAnchor: [15, 30],
                        className: "wally-marker",
                        html: `
                        <div hx-get="/markers/details/${feature.properties.id}" hx-target="#details" hx-swap="innerHTML" hx-on::after-request="showDetailsDrawer()" style="--color: ${feature.properties.color}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="${feature.properties.color}" viewBox="0 0 24 24"><path d="M12 0C6.4 0 1.9 4.3 1.9 9.4c0 3.4 1.6 7 4.8 10.3 1.4 1.6 3 3 4.8 4.1.3.3.7.3 1 0 1.8-1.2 3.4-2.5 4.8-4 3.2-3.4 4.8-7 4.8-10.4C22.1 4.3 17.6 0 12 0Z" style="fill-rule:nonzero"/></svg>
                            <i class="ph-bold ph-${feature.properties.icon}"></i>
                        </div>
                        `
                    }),
                    properties: feature.properties,
                });

                marker.on("click", e => {
                    var data = e.target.options.properties;
                    showMarkerDetails(data.id);
                });

                marker.addTo(markerLayer);
            }
        })

        var lastCenter = getCookie("lastCenter");
        var lastZoom = getCookie("lastZoom");
        if (lastCenter == null && lastZoom == null) {
            map.fitBounds(bounds);
        } else {
            var ll = lastCenter.split("|");
            map.setView([ll[0], ll[1]]);
            map.setZoom(lastZoom);
        }
    };
    xhr.send();
}

function getCookie(name) {
    var cookie = document.cookie.split("; ").find(cookie => cookie.startsWith(name + "="));
    return cookie ? decodeURIComponent(cookie.split("=")[1]) : null;
}

function setCookie(name, value, daysToExpire = 365) {
    var date = new Date();
    date.setTime(date.getTime() + (daysToExpire * 24 * 60 * 60 * 1000));
    var expires = "expires=" + date.toUTCString();
    document.cookie = name + "=" + encodeURIComponent(value) + ";" + expires + ";path=/";
};

function showMarkerPane() {
    document.querySelector("#details").classList.add("active");
}
/*
function showMarkerDetails(markerId) {
    hideBottomDrawer();

    document.querySelector("#details").innerHTML = "";
    htmx.ajax(
        "GET",
        `/markers/details/${markerId}`,
        {
            "target": "#details"
        }
    );
    document.querySelector("#details").classList.add("active");
}

function hideMarkerDetails() {
    document.querySelector("#details").classList.remove("active");
}
*/

function filterByCategory(categoryId) {
    currentCategory = categoryId;
    markerLayer.eachLayer(marker => {
        if (categoryId === "*" || marker.options.properties && marker.options.properties.category === categoryId) {
            marker._icon.style.display = 'block';
        } else {
            marker._icon.style.display = 'none';
        }
    });
    document.querySelectorAll("#bottomDrawer li").forEach(li => {
        li.classList.toggle("active", li.dataset.category == categoryId);
    });
    hideBottomDrawer();
}

function filterByRating(rating) {
    currentRating = rating;
    markerLayer.eachLayer(marker => {
        if (rating === "*" || marker.options.properties && marker.options.properties.rating === rating) {
            marker._icon.style.display = 'block';
        } else {
            marker._icon.style.display = 'none';
        }
    });
    document.querySelectorAll("#bottomDrawer li").forEach(li => {
        li.classList.toggle("active", li.dataset.rating == rating);
    });
    hideBottomDrawer();
}


function locateUser() {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var userLocation = [position.coords.latitude, position.coords.longitude];
            map.flyTo(userLocation);
            userPosition.setLatLng(userLocation);
        }, function(error) {
            console.error("Error getting user location:", error);
        });
    } else {
        console.error("Geolocation is not supported by your browser");
    }
}

function showDetailsDrawer() {
    document.querySelector("#bottomDrawer").classList.add("active");
}

function showBottomDrawer() {
    document.querySelector("#bottomDrawer").classList.add("active");
}

function hideBottomDrawer() {
    document.querySelector("#bottomDrawer").classList.remove("active");
}



// --------------------------------------------------------------------------------------------------------------------


// --- General Helpers

function toggleDrawer(area, force=null) {
    if (force !== null) {
        document.querySelector(`[data-drawer="${area}"]`).classList.toggle("active", force);
    } else {
        document.querySelector(`[data-drawer="${area}"]`).classList.toggle("active");
    }
}


// --- Map Ping

function hideMapPing() {
    mapPing.setOpacity(0);
    toggleDrawer("ping", false);
}

function showMapPing(lat, lng) {
    hideMarkerDetails();

    mapPing.setOpacity(1);
    mapPing.setLatLng([lat, lng]);
    lat = lat.toFixed(4);
    lng = lng.toFixed(4);
    document.querySelector("[data-drawer='ping'] .lat").innerHTML = lat;
    document.querySelector("[data-drawer='ping'] .lng").innerHTML = lng;
    toggleDrawer("ping", true);
}


// --- Marker Details
function extendMarker() {
    document.querySelector("[data-drawer='marker']").classList.toggle("extended", true);
}

function showMarkerDetails(markerId) {
    hideMapPing();
    document.querySelector("[data-drawer='marker']").classList.toggle("extended", false);
    toggleDrawer("marker", false);
    document.querySelector("[data-drawer='marker']").innerHTML = "";

    markerLayer.eachLayer(layer => {
        if (layer.options.properties.id == markerId) {
            map.panTo(layer.getLatLng());
            layer._icon.classList.add("selected");
        } else {
            layer._icon.classList.remove("selected");
        }
    });

    htmx.ajax(
        "GET",
        `/markers/details/${markerId}`,
        {
            "target": "[data-drawer='marker']"
        }
    );
    toggleDrawer("marker", true);
}

function hideMarkerDetails() {
    toggleDrawer("marker", false);
    document.querySelector("[data-drawer='marker']").classList.toggle("extended", false);

    markerLayer.eachLayer(layer => {
        layer._icon.classList.remove("selected");
    });
}


