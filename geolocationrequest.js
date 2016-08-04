var addressArray = createAddressObject();
replaceBlankspacesWithPlusses();
sendRequestToGMapsAPI();

function createAddressArray() {
    var resarr = [];
    var housenumber = document.getElementById("housenumber");
    resarr.push(housenumber);
    var street = document.getElementById("street");
    resarr.push(street);
    var postalcode = document.getElementById("postalcode");
    resarr.push(postalcode);
    var city = document.getElementById("city");
    resarr.push(city);
    return resarr;
};

function replaceBlankspacesWithPlusses() {
    addressArray.forEach(element, index, value) {
        for (var i = 0; i < value.length; i++) {
            if (value[i] == " ") {
                value.splice(i, 1, "+")
            }
        };
        value.splice(0, 0, "+");
    }
};

function sendRequestToGMapsAPI() {
    var mapsReq = new XMLHttpRequest();
    mapsReq.open(
        "GET",
        "https://maps.googleapis.com/maps/api/geocode/json?address=+" + addressArray[0] + ", " + addressArray[1] + "," + addressArray[3] + "," + addressArray[2] + "&components=country:DE&key=AIzaSyD6GBI5RvXZF5h2rzooMQQq5EazNI4-e5U",
        true
    );
    mapsReq.onload = function () {
        var response = JSON.parse(mapsReq.responseText);
        var coordinates = response.results[0].geometry.location;
        sendCoordinatesToPHP(coordinates);
    };
    mapsReq.send();
};

function sendCoordinatesToPHP() {

}