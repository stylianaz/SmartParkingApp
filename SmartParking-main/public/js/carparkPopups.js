function getCarparkPopupStart(carpark_entry) {
    cost = (carpark_entry.cost == -1) ? "Unknown" : carpark_entry.cost;
    let popup;

    if (carpark_entry.hasOwnProperty('spaces_available')) {
        popup = carpark_entry.spaces_available + '/' + carpark_entry.capacity + " spaces available"
    } else {
        popup = '?/' + carpark_entry.capacity + " spaces available (this carpark is not in the database)"
    }

    popup = popup + "<br/>Disabled parking spaces:" + carpark_entry.disabledcount +
                    "<br/>Details:" + carpark_entry.comments;

    if (carpark_entry.hasOwnProperty('FuzzyValueTotal')) {
        popup += "<br/>Fuzzy Score:" + Math.round(carpark_entry.FuzzyValueTotal * 100.00)/100.00;
    }

    return popup + "<br/>Cost:" + cost +
                   "<br/>Validity:" + carpark_entry.validity;
}

function addReservationButton(popup, carpark_entry, container, reserveParking) {
    popup += "<br/><br/><button class=\"btn btn-primary\" type=\"button\" id=\"reserveParking" + carpark_entry.id + "\">Reserve</button>";
    container.on('click', '#reserveParking' + carpark_entry.id, function () {
        reserveParking(carpark_entry);
    });
    return popup;
}

function addUpdateButton(popup, carpark_entry, container, updateParking) {
    if (carpark_entry.validity <= 5) {
        popup = popup + "<br/><br/><button class=\"btn btn-primary\" type=\"button\" id=\"updateparking" + carpark_entry.id + "\">Update</button>";
        // Delegate all event handling for the container itself and its contents to the container
        container.on('click', '#updateparking' + carpark_entry.id, function () {
            updateParking(carpark_entry);
        });
    }
    return popup;
}

function addBookmarkButton(popup, carpark_entry, container, bookmarkParking){
    popup = popup + "<br/><br/><button class=\"btn btn-primary\" type=\"button\" id=\"favoriteparking" + carpark_entry.id + "\">Bookmark</button>";
    // Delegate all event handling for the container itself and its contents to the container
    container.on('click', '#favoriteparking' + carpark_entry.id, function () {
        bookmarkParking(carpark_entry);
    });
    return popup;
}

function addPredictionButton(popup, carpark_entry, container, getPrediction){
    popup = popup + "<br/><br/><button class=\"btn btn-primary\" type=\"button\" id=\"makeprediction" + carpark_entry.id + "\">Get Prediction</button>";
    container.on('click', '#makeprediction' + carpark_entry.id, function () {
        getPrediction(carpark_entry);
    });
    return popup;
}