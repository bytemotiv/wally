var map;
var markerLayer;
var searchresultsLayer;
var userPosition;
var mapPing;
var tags;

window.addEventListener("DOMContentLoaded", function () {

    map = L.map("map", {
        zoomControl: false,
    });
    map.setView([0, 0], 13);

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

    map.on("zoom", e => {
        if (map.getZoom() < 12) {
            document.querySelector("#map").classList.add("zoomedout");
        } else {
            document.querySelector("#map").classList.remove("zoomedout");
        }
    });

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
    tags = [];
    markerLayer.clearLayers();
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "/markers/geojson");
    xhr.responseType = "json";
    xhr.onload = function() {
        if (xhr.status !== 200) {
            return;
        }

        L.geoJSON(xhr.response, {
            pointToLayer: function (feature, latlng) {
                var marker = L.marker(latlng, {
                    icon: L.divIcon({
                        iconSize: [30, 30],
                        iconAnchor: [15, 30],
                        className: "wally-marker",
                        html: `
                        <div style="--color: ${feature.properties.color}" data-tooltip="${feature.properties.title}">
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

                marker.on("mouseover", e => {
                    let marker = e.target;
                    let rect = marker._icon.getBoundingClientRect();
                    document.querySelector("#marker-tooltip").dataset.tooltip = marker._icon.dataset.tooltip;
                    document.querySelector("#marker-tooltip").style.left = rect.x + "px";
                    document.querySelector("#marker-tooltip").style.top = rect.y + "px";
                    document.querySelector("#marker-tooltip").classList.add("visible");
                });

                marker.on("mouseout", e => {
                    document.querySelector("#marker-tooltip").classList.remove("visible");
                });

                marker.addTo(markerLayer);
                marker._icon.style.display = "block";
                marker._icon.dataset.tooltip = feature.properties.title;

                feature.properties.tags.forEach(tag => {
                    if (tags.indexOf(tag) < 0) {
                        tags.push(tag);
                    }
                });
            }
        })

        tags.sort();

        var lastCenter = getCookie("lastCenter");
        var lastZoom = getCookie("lastZoom");
        if (lastCenter == null && lastZoom == null) {
            centerOnVisibleMarkers();
        } else {
            var ll = lastCenter.split("|");
            map.setView([ll[0], ll[1]]);
            map.setZoom(lastZoom);
        }
    };
    xhr.send();
}

function centerOnVisibleMarkers() {
    var bounds = new L.LatLngBounds();
    markerLayer.eachLayer(marker => {
        if (marker._icon.style.display == "block") {
            bounds.extend(marker.getLatLng());
        }
    });

    let padding  = [100, 100];
    if (window.matchMedia("(max-width: 767px)").matches) {
        padding  = [25, 25];
    }
    map.fitBounds(bounds, { "padding": padding });
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

function filterByCategory(categoryId) {
    currentCategory = categoryId;
    markerLayer.eachLayer(marker => {
        if (categoryId === "*" || marker.options.properties && marker.options.properties.category === categoryId) {
            marker._icon.style.display = "block";
        } else {
            marker._icon.style.display = "none";
        }
    });
    centerOnVisibleMarkers();

    var categoryName = "";
    document.querySelectorAll("[data-drawer='categories'] li").forEach(li => {
        li.classList.toggle("active", li.dataset.category == categoryId);
        if (li.dataset.category == categoryId) {
            categoryName = li.querySelector(".name").innerText;
        }
    });

    if (categoryId != "*") {
        document.querySelector("#actionbar .viewtitle").innerHTML = "Markers in the category <b>" + categoryName + "</b>";
        actionbar.show("view");
    }

    toggleDrawer("categories", false);
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
    centerOnVisibleMarkers();

    document.querySelectorAll("[data-drawer='ratings'] li").forEach(li => {
        li.classList.toggle("active", li.dataset.rating == rating);
    });

    toggleDrawer("ratings", false);
}


function locateUser() {
    /*
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            var userLocation = [position.coords.latitude, position.coords.longitude];
            map.flyTo(userLocation);
            userPosition.setLatLng(userLocation);
        }, function(error) {
            alert("Error getting user location:" + error.code + " / " + error.message);
        });
    } else {
        alert("Geolocation is not supported by your browser");
    }
    */
    if ( navigator.permissions && navigator.permissions.query) {
        //try permissions APIs first
        navigator.permissions.query({ name: 'geolocation' }).then(function(result) {
            // Will return ['granted', 'prompt', 'denied']
            const permission = result.state;
            if ( permission === 'granted' || permission === 'prompt' ) {
                _onGetCurrentLocation();
            }
        });
      } else if (navigator.geolocation) {
        //then Navigation APIs
        _onGetCurrentLocation();
      }

      function _onGetCurrentLocation () {
          const options = {
                  enableHighAccuracy: true,
                  timeout: 5000,
                  maximumAge: 0
          };
          navigator.geolocation.getCurrentPosition( function (position) {
            var userLocation = [position.coords.latitude, position.coords.longitude];
            map.flyTo(userLocation);
            userPosition.setLatLng(userLocation);
          }, function (error) {
            alert("Error getting user location:" + error.code + " / " + error.message);
          }, options)
      }
}

// --------------------------------------------------------------------------------------------------------------------


// --- General Helpers

function toggleDrawer(area, force=null) {
    hideQuicksearch();
    if (force !== null) {
        document.querySelector(`[data-drawer="${area}"]`).classList.toggle("active", force);
    } else {
        document.querySelector(`[data-drawer="${area}"]`).classList.toggle("active");
    }
}


// --- Map Ping

function hideMapPing() {
    mapPing._icon.style.display = "none";
    mapPing.setOpacity(0);
    actionbar.previous()
}

function showMapPing(lat, lng) {
    hideMarkerDetails();
    hideQuicksearch();

    mapPing.setOpacity(1);
    mapPing._icon.style.display = "block";
    mapPing.setLatLng([lat, lng]);
    lat = lat.toFixed(4);
    lng = lng.toFixed(4);

    document.querySelector("#actionbar .coordinates .lat").innerHTML = lat;
    document.querySelector("#actionbar .coordinates .lng").innerHTML = lng;
    actionbar.show("ping", false);
}


//TODO: Remove marker instead of just hiding them?
function showMarkersWithTag(tag) {
    markerLayer.eachLayer(marker => {
        if (marker.options.properties.tags.indexOf(tag) < 0) {
            marker._icon.style.display = "none";
        } else {
            marker._icon.style.display = "block";
        }
    });

    document.querySelector("#actionbar .viewtitle").innerHTML = "Markers tagged with <b>#" + tag + "</b>";
    actionbar.show("view");

    centerOnVisibleMarkers();
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

function updateTags() {
    htmx.ajax(
        "GET",
        "tags/datalist",
        {
            "target": "datalist#tags",
            "swap": "innerHTML",
        }
    );

}

// --- State Graph ----------------------------------------------------------------------------------------------------

var states = [
    "map",
    "search",
    "shareview",
    ""
]
