console.log("Map wird initialisiert.");
console.log(locationCoordinates);
var markerArray = [];
var windowArray = [];
var map = drawMap();
var coordinateObjects = convertCoordinateArraysToObjects(locationCoordinates);
console.log(coordinateObjects)
createArrayOfInfoWindows(coordinateObjects);
coordinateObjects.forEach(function (element, j, arr) {
    if (element.post_status == "publish") {
        var marker = addMarker(element.latitude, element.longitude)
        markerArray.push(marker);
        marker.addListener("click", function () {
            windowArray[j].open(map, marker);
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
}

function createNewLink(coordDataSet) {
    var newLink = document.createElement("a");
    newLink.href = coordDataSet.permalink;
    var postname = document.createTextNode(coordDataSet.post_title);
    newLink.appendChild(postname);
    return newLink
};

function convertCoordinateArraysToObjects (arr) {
    var resarr = [];
    for (var i in arr) {
        var newEntry = {
            latitude: Number(arr[i][0]),
            longitude: Number(arr[i][1]),
            permalink: arr[i][2],
            post_title: arr[i][3],
            post_status: arr[i][4],
        };
        resarr.push(newEntry);
    };
    return resarr;
}