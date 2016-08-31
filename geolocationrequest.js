console.log("Ich bin gestartet.");
var addressArray = createAddressArray();
console.log(addressArray);
console.log(addressArray);
sendRequestToGMapsAPI();

function createAddressArray() {
    var resarr = [];
    var housenumber = document.getElementById("housenumber");
    resarr.push(housenumber);
    var street = document.getElementById("street");
    resarr.push(street);
    var postalcode = document.getElementById("postalcode").toString();
    resarr.push(postalcode);
    var city = document.getElementById("city");
    resarr.push(city);
    return resarr;
};

function sendRequestToGMapsAPI() {
    var mapsReq = new XMLHttpRequest();
    mapsReq.open(
        "GET",
        "https://maps.googleapis.com/maps/api/geocode/json?address=+" + addressArray[0] + ", " + addressArray[1] + "," + addressArray[3] + "," + addressArray[2] + "&components=country:DE&key=AIzaSyD6GBI5RvXZF5h2rzooMQQq5EazNI4-e5U",
        true
    );
    mapsReq.onload = function () {
        console.log("GMaps Request hat gefunzt.");
        var response = JSON.parse(mapsReq.responseText);
        var coordinates = response.results[0].geometry.location;
        sendCoordinatesToPHP(coordinates);
    };
    mapsReq.send();
};

function sendCoordinatesToPHP(coordinates) {
    var responseToPHP = new XMLHttpRequest();
    responseToPHP.open(
        "POST",
        "index.php",
        true
    );
    responseToPHP.onload = function() {
        console.log("Sutsches");
    };
    responseToPHP.setRequestHeader("Content-Type", 'application/x-www-form-urlencoded');
    responseToPHP.send("latvalue=" + coordinates.lat + "&lngvalue=" + coordinates.lng);
}
