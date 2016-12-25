console.log("Map wird initialisiert.");
console.log(locationCoordinates);
var markerArray = [];
var windowArray = [];
var noDoubledCoordinates = [];
var map = drawMap();
var coordinateObjects = convertCoordinateArraysToObjects(locationCoordinates);
console.log(coordinateObjects)
createArrayOfInfoWindows(coordinateObjects);
deleteDoubles(coordinateObjects);
noDoubledCoordinates.forEach(function (element, j, arr) {
    if (element.markerData.post_status == "publish") {
        var marker = addMarker(element.markerData.latitude, element.markerData.longitude)
        markerArray.push(marker);
        marker.addListener("click", function () {
            for (var i in element.infowindows) {
                windowArray[element.infowindows[i]].open(map, marker);
            };
            //windowArray[j].open(map, marker);
        })
    }
});

function addMarker(mLat, mLng) {
    return new google.maps.Marker({
        position: {
            lat: mLat,
            lng: mLng
        },
        map: map
    });
}

function drawMap() {
    var map = new google.maps.Map(document.getElementById('map'), {
        zoom: 3,
        center: new google.maps.LatLng(51.4344079, 6.7623293),
    });
    return map;
};

function createArrayOfInfoWindows(coordinates) {
    for (var i = 0; i < coordinates.length; i++) {
        var newLink = createNewLink(coordinates[i]);
        var infowindow = new google.maps.InfoWindow({
            content: newLink
        });
        windowArray.push(infowindow);
    };
    console.log(windowArray)
};

function checkForDoubledCoordinates(coordinates, currentIndex) {
    for (var i = 0; i < currentIndex; i++) {
        if (coordinates[i].latitude == coordinates[currentIndex].latitude && coordinates[i].longitude == coordinates[currentIndex].longitude) {
            return i
        };
    }
    return
};

function createNewLink(coordDataSet) {
    var newLink = document.createElement("a");
    newLink.href = coordDataSet.permalink;
    var postname = document.createTextNode(coordDataSet.description);
    newLink.appendChild(postname);
    return newLink
};

function convertCoordinateArraysToObjects(coordinateArray) {
    var resarr = [];
    for (var i in coordinateArray) {
        var newEntry = {
            latitude: Number(coordinateArray[i][0]),
            longitude: Number(coordinateArray[i][1]),
            permalink: coordinateArray[i][2],
            description: coordinateArray[i][3],
            post_status: coordinateArray[i][4],
        };
        resarr.push(newEntry);
    };
    return resarr;
};

function deleteDoubles(coordinates) {
    for (var i in coordinates) {
        var replaced = false;
        if (noDoubledCoordinates != []) {
            for (var j in noDoubledCoordinates) {
                if (sameLatLng(coordinates[i], noDoubledCoordinates[j].markerData)) {
                    noDoubledCoordinates[j].infowindows.push(i)
                    replaced = true;
                }
            };
        };
        if (!replaced) {
            noDoubledCoordinates.push({
                markerData: coordinates[i],
                infowindows: [i]
            })
        }
    };
    console.log(noDoubledCoordinates)
};

function sameLatLng(coord1, coord2) {
    if (coord1.latitude == coord2.latitude && coord1.longitude == coord2.longitude) {
        return true
    }
    return false
}