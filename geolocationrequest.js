var addressArray = [];
var housenumber = document.getElementById("housenumber");
addressArray.push(housenumber);
var street = document.getElementById("street");
addressArray.push(street);
var postalcode = document.getElementById("postalcode");
addressArray.push(postalcode);
var city = document.getElementById("city");
addressArray.push(city);
addressArray.forEach(element, index, value) {
    for (var i = 0; i < value.length; i++) {
        if (value[i] == " ") {
            value.splice(i, 1, "+")
        }
    };
    value.splice(0, 0, "+");
}
var mapsReq = new XMLHttpRequest();
mapsReq.open(
    "GET",
    "https://maps.googleapis.com/maps/api/geocode/json?address=+" + addressArray[0] + ", " + addressArray[1] + "," + addressArray[3] + "," + addressArray[2] + "&components=country:DE&key=AIzaSyD6GBI5RvXZF5h2rzooMQQq5EazNI4-e5U",
    true
);
mapsReq.onload = function () {
    var response = JSON.parse(mapsReq.responseText);
    var latitude = document.createElement("div");
    latitude.visbility = "hidden";
    var longitude = document.createElement("div");
    latitude.visbility = "hidden";
    console.log(response.results[0].geometry.location)
    console.log(mapsReq.responseText);
};
mapsReq.send();