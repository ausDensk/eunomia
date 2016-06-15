console.log("Map wird initialisiert.");
console.log(locationCoordinates);
var markerArray = [];
var windowArray = [];
var map = drawMap();

createArrayOfInfoWindows(locationCoordinates);
locationCoordinates.forEach(function (element, j, arr) {
    if (element[4] == "publish") {
        var marker = addMarker(Number(element[0]), Number(element[1]))
        markerArray.push(marker);
        marker.addListener("click", function () {
            console.log(windowArray + j);
            console.log(windowArray)
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
        zoom: 10,
        center: new google.maps.LatLng(51.4344079, 6.7623293),
    });
    return map;
};

function createArrayOfInfoWindows(coordinates) {
    for (var i = 0; i < locationCoordinates.length; i++) {
        var newLink = createNewLink(coordinates[i]);
        var infowindow = new google.maps.InfoWindow({
            content: newLink
        });
        windowArray.push(infowindow);
    };
}

function createNewLink(coordDataSet) {
    var newLink = document.createElement("a");
    newLink.href = coordDataSet[2];
    var postname = document.createTextNode(coordDataSet[3]);
    newLink.appendChild(postname);
    return newLink
}