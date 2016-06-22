var postDiv = document.createElement("div");
postDiv.id = "postDiv";
var sideSortables = document.getElementById("side-sortables");
var poststuff = document.getElementById("poststuff");
if (poststuff) {
    appendForm();
    sideSortables.appendChild(postDiv);
    testMapsAPIRequest();
};

function appendForm () {
    var getForm = new XMLHttpRequest();
    getForm.open("GET", "../wp-content/plugins/starrplugin/formular.html");
    getForm.onload = function() {
        postDiv.innerHTML = getForm.responseText;
    };
    getForm.send()
}

function testMapsAPIRequest () {
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