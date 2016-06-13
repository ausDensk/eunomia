// Checken ob das überhaupt klappt.
console.log("Koordinatenformulare initialisiert.");
//Kreiere Rahmen-Div. Style muss angepasst werden.
var postDiv = document.createElement("div");
postDiv.id = "postDiv";
var poststuff = document.getElementById("side-sortables");
var poststuffi = document.getElementById("poststuff");
if (poststuffi) {
    var latitude = document.createElement("input");
    var longitude = document.createElement("input");
    latitude.id = "latitudeID";
    latitude.type = "number";
    latitude.name = "latitudevalue";
    longitude.id = "longitudeID";
    longitude.type = "number";
    longitude.name = "longitudevalue";
    poststuff.appendChild(postDiv);
    postDiv.appendChild(latitude);
    postDiv.appendChild(longitude);
    var mapsReq = new XMLHttpRequest();
    mapsReq.open(
        "GET",
        "https://maps.googleapis.com/maps/api/geocode/json?address=+9a, +Swakopmunder+Straße,+Duisburg,+47249&components=country:DE&key=AIzaSyD6GBI5RvXZF5h2rzooMQQq5EazNI4-e5U",
        true
    );
    mapsReq.onload = function () {
        var response = JSON.parse(mapsReq.responseText);
        console.log(response.results[0].geometry.location)
    };
    mapsReq.send();
};

function createCoordinateInput (name) {
    var newInput = document.createElement("input");
    newInputput.id = name + "ID";
    newInput.name = name + "value";
    newInput.type = "number";
    postDiv.appendChild(newInput);
}

