console.log(locationCoordinates);

function putFormInRightPlace() {
    var postDiv = document.createElement("div");
    postDiv.id = "postDiv";
    var sideSortables = document.getElementById("side-sortables");
    var poststuff = document.getElementById("poststuff");
    appendForm();
    sideSortables.appendChild(postDiv);
    if (locationCoordinates.length != 0) {
        setTimeout(findElementsAndPutTextIn, 1000, postDiv);
    };
};

function appendForm() {
    var getForm = new XMLHttpRequest();
    getForm.open("GET", "../wp-content/plugins/starrplugin/formular.html");
    getForm.onload = function() {
        postDiv.innerHTML = getForm.responseText;
    };
    getForm.send()
};

function findElementsAndPutTextIn(div) {
    var elementsArray = findElementsInDOM(["street", "housenumber", "postalcode", "city", "description"]);
    appendTextToElements(elementsArray, locationCoordinates.slice(2, locationCoordinates.length));
};

function findElementsInDOM(IDArray) {
    var resarr = [];
    for (var i in IDArray) {
        var newElement = document.getElementById(IDArray[i] + "id");
        resarr.push(newElement)
    };
    return resarr;
};

function appendTextToElements(elementsArray, textArray) {
    for (var i = 0; i < elementsArray.length; i++) {
        elementsArray[i].value = textArray[i];
    };
};

document.addEventListener("DOMContentLoaded", putFormInRightPlace);