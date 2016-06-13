console.log("Map wird initialisiert.");
var map = new google.maps.Map(document.getElementById('map'), {
    zoom: 10,
    center: new google.maps.LatLng(51.4344079, 6.7623293),
});
console.log(locationCoordinates);
var markerArray = [];
var windowArray = [];
for (var i = 0; i < locationCoordinates.length; i++) {
    var link = document.createElement("a");
    link.href = locationCoordinates[i][2];
    var postname = document.createTextNode(locationCoordinates[i][3]);
    link.appendChild(postname);
    var infowindow = new google.maps.InfoWindow({
        content: link
    });
    windowArray.push(infowindow);
};
locationCoordinates.forEach(function (element, j, arr) {
    if (element[4] == "publish") {
        var marker = new google.maps.Marker({
            position: {
                lat: Number(element[0]),
                lng: Number(element[1])
            },
            map: map
        });
        markerArray.push(marker);
        marker.addListener("click", function () {
            console.log(windowArray + j);
            windowArray[j].open(map, marker);
        })
    }
});