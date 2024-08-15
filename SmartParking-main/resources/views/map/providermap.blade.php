@extends('layouts.map')

@section('styles')
<style>
    /* Global Styles */
body {
    font-family: 'Roboto', sans-serif;
    background-color: #f4f4f9;
    color: #333;
}

.container {
    max-width: 960px;
    margin: 0 auto;
    padding: 20px;
}

.card {
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.card-header,
.card-footer {
    background-color: #2d3748;
    color: white;
    text-align: center;
    font-weight: bold;
    padding: 10px 0;
}

.card-body {
    padding: 20px;
}

.form-group {
    margin-bottom: 15px;
}

.form-control {
    border-radius: 4px;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    border-color: #0056b3;
    box-shadow: 0 0 5px rgba(0, 0, 255, 0.2);
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.btn-primary:hover {
    background-color: #0056b3;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
}

/* Tooltip Styles */
.tooltip {
    background-color: #333;
    color: #fff;
    border-radius: 4px;
    padding: 5px;
    position: absolute;
    z-index: 10;
    font-size: 0.85em;
}

/* Table Styles */
#CostsTable th,
#CostsTable td {
    text-align: center;
    vertical-align: middle;
}

/* Modal Styles */
.ui-dialog {
    z-index: 1000 !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }

    .card-header,
    .card-footer {
        font-size: 14px;
        padding: 8px 0;
    }

    .card-title {
        font-size: 18px;
    }

    .card-body {
        padding: 15px;
    }

    .form-control {
        font-size: 14px;
    }

    .btn-primary {
        font-size: 14px;
        padding: 8px 15px;
    }

    .instructions {
        font-size: 0.9em;
        padding: 10px;
    }

    .instructions h4 {
        font-size: 1em;
    }

    .instructions ol {
        padding-left: 15px;
    }

    .instructions li {
        margin-bottom: 5px;
    }
}

/* Instructions */
.instructions {
    font-size: 1em;
    color: #333;
    margin-bottom: 20px;
    padding: 15px;
    background-color: #e2e8f0;
    border-radius: 5px;
    border-left: 5px solid #007bff;
    line-height: 1.6;
}

.instructions h4 {
    margin-bottom: 10px;
    color: #2d3748;
    font-size: 1.2em;
}

.instructions ol {
    padding-left: 20px;
}

.instructions li {
    margin-bottom: 8px;
}

/* Feedback Message */
.feedback {
    display: none;
    margin-top: 10px;
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    padding: 10px;
    border-radius: 5px;
}

</style>
@stop

@section('content')

@include('includes.errors')

<div class="container">
    <!-- Instructions -->
    <div class="instructions">
        <h4>Instructions:</h4>
        <ol>
            <li><strong>Type the address:</strong> Enter the address of the parking location you want to manage or add.</li>
            <li><strong>Place the parking pin:</strong> Click on the icon on the map to drop a parking pin.</li>
            <li><strong>Adjust/Move the pin:</strong> Drag the pin to the precise location if needed.</li>
            <li><strong>Click OK:</strong> Confirm the placement and proceed to add the parking details.</li>
        </ol>
    </div>

    <!-- OWNER PARKING MANAGE CARD -->
    <div class="card">
        <div class="card-header">
            OWNER PARKING MANAGE
        </div>
        <div class="card-body">
            <h4 class="card-title text-center">Parking Data</h4>
            <p class="card-text">
            <form>
                <div class="form-group">
                    <label for="addr" class="col-form-label">{{ __('Address to Edit Parkings') }}</label>
                    <input type="text" name="addr" value="" id="addr" class="form-control" placeholder="Enter address to find parking locations"/>
                    <div class="tooltip" id="addr-tooltip">Please enter the address where you want to manage parking.</div>
                </div>
                <div class="form-group">
                    <div id="map" style="height: 400px;"></div>
                </div>
            </form>
            </p>
        </div>
    </div>

    <!-- DIALOG MESSAGE RESULT -->
    <div id="dialog-message" title="RESULTS">
        <p>
            <span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>
            THE RESULTS OF PARKINGS.
        </p>
        <div id="tableModal">
        </div>
    </div>

    <!-- ADD PARKING CARD -->
    <div id="dialog-message-add-parking" title="ADD PARKING (CROWD SOURCING)">
        <p>
            <span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>
            ADD PARKING.
        </p>
        <div class="card text-center">
           
            <div class="card-body">
                <h4 class="card-title">New Parking Data</h4>
                <form name="myParkingSave" id="myParkingSave">
                    <div class="form-group">
                        <label for="parkingName">Parking Name <span class="tooltip" id="name-tooltip">Enter a name for the parking area, e.g., "Downtown Parking Lot".</span></label>
                        <input type="text" class="form-control" id="parkingName" placeholder="UNKNOWN" name="name" value="UNKNOWN" onfocus="showTooltip('name-tooltip')" onblur="hideTooltip('name-tooltip')">
                    </div>
                    <div class="form-group">
                        <label for="parkingDisabledParkingsCount">Number of Disabled parkings <span class="tooltip" id="disabled-tooltip">Specify the number of parking spaces reserved for disabled individuals.</span></label>
                        <input type="number" class="form-control" id="parkingDisabledParkingsCount" placeholder="0" name="disabledcount" value="0" pattern="\d+" onfocus="showTooltip('disabled-tooltip')" onblur="hideTooltip('disabled-tooltip')">
                    </div>
                    <div class="form-group">
                        <label for="parkingParkingsCount">Number of parkings <span class="tooltip" id="total-tooltip">Total number of parking spaces available at this location.</span></label>
                        <input type="number" class="form-control" id="parkingParkingsCount" placeholder="0" name="reportedcount" value="0" pattern="\d+" min="1" onfocus="showTooltip('total-tooltip')" onblur="hideTooltip('total-tooltip')">
                    </div>
                    <div class="form-group">
                        <label for="parkingOccupiedParkingsCount">Number of occupied parkings <span class="tooltip" id="occupied-tooltip">Number of currently occupied parking spaces.</span></label>
                        <input type="number" class="form-control" id="parkingOccupiedParkingsCount" placeholder="0" name="occupied" value="0" pattern="\d+" onfocus="showTooltip('occupied-tooltip')" onblur="hideTooltip('occupied-tooltip')">
                    </div>
                    <div class="form-group">
                        <label for="parkingOccupiedParkingsMaximumDuration">Max parking duration (minutes) <span class="tooltip" id="duration-tooltip">Maximum allowed parking time in minutes, e.g., 3600 for one day.</span></label>
                        <input type="number" class="form-control" id="parkingOccupiedParkingsMaximumDuration" placeholder="3600 (minutes - 1 day)" name="maximumduration" value="3600" pattern="\d+" min="1" onfocus="showTooltip('duration-tooltip')" onblur="hideTooltip('duration-tooltip')">
                    </div>
                    <div class="form-group">
                        <label for="datetimeYouPark">Date and Time that you park: <span class="tooltip" id="datetime-tooltip">Select the date and time when you parked your vehicle.</span></label>
                        <input id="datetimeYouPark" class="flatpickr form-control" data-enabletime=true name="time" onfocus="showTooltip('datetime-tooltip')" onblur="hideTooltip('datetime-tooltip')">
                    </div>
                    <div class="form-group">
                        <label for="YouParkvalidity">Validity: <span class="tooltip" id="validity-tooltip">Validity period of the parking in hours.</span></label>
                        <input id="YouParkvalidity" type="number" name="validity" value="10" min="0" max="10" class="form-control" required onfocus="showTooltip('validity-tooltip')" onblur="hideTooltip('validity-tooltip')">
                    </div>
                    <div class="form-group">
                        <label for="ParkingType">Parking Type</label>
                        <select class="form-control" id="ParkingType" name="parkingtype_id">
                            @foreach($parkingtypes as $parkingtype)
                                <option value="{{ $parkingtype->id }}">{{ $parkingtype->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="avaliable">Available</label>
                        <input class="form-control" id="avaliable" name="avaliable" type="checkbox" value="1">
                    </div>
                    <div class="form-group">
                        <table id="CostsTable" class="table order-list">
                            <thead>
                                <tr>
                                    <td>Cost</td>
                                    <td>Time (in minutes)</td>
                                    <td>Action</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="number" name="cost[0]" id="cost[0]" class="form-control" value="0" pattern="\d+"/>
                                    </td>
                                    <td>
                                        <input type="number" name="time[0]" id="time[0]" class="form-control" value="60" pattern="\d+" min="1"/>
                                    </td>
                                    <td>
                                        <a class="deleteRow btn btn-danger btn-sm">Delete</a>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align: left;">
                                        <input type="button" class="btn btn-lg btn-block btn-secondary" id="addrow" value="Add Row"/>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="form-group">
                        <label for="comments">Comments <span class="tooltip" id="comments-tooltip">Provide additional information about the parking spot.</span></label>
                        <textarea class="form-control" id="comments" rows="3" name="comments" placeholder="Additional information about the parking spot" onfocus="showTooltip('comments-tooltip')" onblur="hideTooltip('comments-tooltip')"></textarea>
                    </div>
                </form>
                <button type="button" id="SAVEparkingInfo" class="btn btn-primary" style="width: 50%">SAVE</button>
                <div class="feedback" id="feedback">Your parking information has been saved successfully!</div>
            </div>
         
        </div>
    </div>
</div>



@endsection


@section('scripts')
    <script>

             // Tooltip functions
    function showTooltip(id) {
        document.getElementById(id).style.display = 'inline';
    }

    function hideTooltip(id) {
        document.getElementById(id).style.display = 'none';
    }

    // Show feedback after form submission
    document.getElementById('SAVEparkingInfo').addEventListener('click', function() {
        document.getElementById('feedback').style.display = 'block';
        setTimeout(function(){
            document.getElementById('feedback').style.display = 'none';
        }, 3000);
    });
        //   var possition;
        //   var possitionData;
        var map;
        var feature;
        var features;
        var UserPossition;
        // setup a marker group
        var markers;
        var addParkingbutton;
        var checkWayToAsk = 0;
        var dialog;
        var dialog_form;
        var sortedParkings;
        var controlLayerParkings;
        var parkingsControl;
        var HomeParkings;
        var WorkParkings;
        var ShopParkings;
        var FoodParkings;
        var DringParkings;
        var OtherParkings;
        var LotParkings;
        var PlaceParkings;
        var counter = 1;
        var newParkingPossition;
        var lowConfitence;
        var highConfitence;
        var disabledIncludeParkings;
        var enabledIncludeParkings;
        $(document).ready(function () {

            //AJAX SETUP FOR AUTOCOMPLETE
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            //GET ADDRESS OF USER
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                alert("Geolocation is not supported by this browser.");
            }

            function showPosition(position) {
                lat = position.coords.latitude;
                long = position.coords.longitude;
                loc1 = new L.LatLng(lat, long);
                UserPossition = {"lat": lat, "long": long};
                map.setView(loc1, 14);
                getAddressNamefromCoordinates(UserPossition, '#addr');
                chooseLocationAdd(UserPossition);
                loadParkings(UserPossition);
            }

            //END OF SHOWING INFO FOR PLACE OF USER

            //WINDOWS ONLOAD LOAD MAP ALSO
            window.onload = load_map;

            //LOAD MAP
            function load_map() {
                /*
                map = new L.Map('map', {zoomControl: true});

                var osmUrl = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                    osmAttribution = 'Map data &copy; 2012 <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
                    osm = new L.TileLayer(osmUrl, {maxZoom: 18, attribution: osmAttribution});

                map.setView(new L.LatLng(51.538594, -0.198075), 12).addLayer(osm);
                */
                //map = new L.Map('map'); // global
                map = new L.Map('map', {zoomControl: true});
                $("#map").css({"height": "600px"});

                url = 'http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                opt = {minZoom: 1, maxZoom: 18, attribution: "Leafletjs.com - OpenStreetMap.org - RISE.org.cy"};
                var layer = new L.TileLayer(url, opt);
                map.addLayer(layer);
                map.setView(new L.LatLng(34.8717199, 33.6049646), 9);
                var scale = L.control.scale(); // Creating scale control
                scale.addTo(map); // Adding scale control to the map
            }

            //SET ALL VALUES OF TEXTBOXEX TO EMPTY
            //IF RELOAD PAGE!
            $('#addr').val("");

            /*
            AUTOCOMPLETE FOR ADDRESSES
             */
            //end
            $("#addr").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ route('api.getAddressAutocomplete') }}",
                        data: {
                            address: request.term,
                            _token: '{{csrf_token()}}',
                            waytoask: checkWayToAsk
                        },
                        success: function (data) {
                            if (data.status == "success") {
                                console.log(data);
                            } else {
                                console.log("Error:" + data);
                            }
                            response($.map(data.content, function (item) {
                                return {
                                    label: item.label,
                                    value: item.label,
                                    id: item.id,
                                    lat: item.lat,
                                    long: item.long
                                };
                            }));
                            //possitionData = data.alladata;
                        }
                        ,
                        error: function (jqXHR, textStatus, errorThrown) {
                            //alert();
                            console.log(textStatus);
                            console.log(errorThrown);
                        }
                    });
                },
                minLength: 2,
                delay: 1000, // mum of miliseconds to wait before making request.
                // optional (if other layers overlap autocomplete list)
                open: function (event, ui) {
                    $(this).removeClass("ui-corner-all").addClass("ui-corner-top");
                    $(".ui-autocomplete").css("z-index", 1000);
                },
                select: function (event, ui) {
                    console.log("Selected End: " + ui.item.value + " aka " + ui.item.id);
                    //possition = possitionData[ui.item.id];
                    //console.log("possition information " + possition);
                    chooseLocationAdd(ui.item);
                    UserPossition = ui.item;
                    loadParkings(ui.item);
                },
                close: function () {
                    $(this).removeClass("ui-corner-top").addClass("ui-corner-all");
                },
                search: function (e, u) {
                    $(this).addClass('loader');
                },
                response: function (e, u) {
                    $(this).removeClass('loader');
                },
                change: function (event, ui) {
                    // console.log(this.value);
                    //gamw=addr_searchStart();
                    if (ui.item == null) {
                        checkWayToAsk = 1;
                        $(this).autocomplete("search", $('#addr').val());
                    } else {
                        checkWayToAsk = 0;
                    }
                    //this.response(gamw);
                    //$(this).autocomplete("search", $('#addrStart').val());
                }
            }).autocomplete("instance")._renderItem = function (ul, item) {
                return $("<li>")
                    .append("<div>" + item.label + "</div>")
                    .appendTo(ul);
            };

            function chooseLocationAdd(possition) {
                var myIcon;
                loc1 = new L.LatLng(possition.lat, possition.long);
                if (features) {
                    map.removeLayer(features);
                }
                myIcon = L.icon({
                    iconUrl: '{{ asset('images/marker-icon.png') }}',
                    iconSize: [30, 30],
                    iconAnchor: [15, 30],
                    popupAnchor: [0, -25],
                    //iconSize: [38, 95],
                    //iconAnchor: [37, 94],
                    //popupAnchor: [-3, -76],
                    //shadowUrl: 'my-icon-shadow.png',
                    //shadowSize: [68, 95],
                    //shadowAnchor: [22, 94]
                });
                features = L.marker(loc1, {icon: myIcon}, {opacity: 0.5}, {draggable: true}).addTo(map);
                features.dragging.enable();
                features.on('dragend', function (event) {
                    var markerEnd = event.target;
                    var positionEnd = markerEnd.getLatLng();
                    markerEnd.setLatLng(new L.LatLng(positionEnd.lat, positionEnd.lng), {draggable: true});
                    markerEnd.dragging.enable();
                    map.panTo(new L.LatLng(positionEnd.lat, positionEnd.lng));
                    UserPossition = {"lat": positionEnd.lat, "long": positionEnd.lng};
                    getAddressNamefromCoordinates(UserPossition, '#addr');
                    loadParkings(UserPossition);
                });

                //user can add its own parking place that went and it is ok! CROWD SOURCING
                if (!addParkingbutton) {
                    addParkingbutton = L.easyButton('<i class="fas fa-plus-square">P</i>', function () {
                        addMarkerParking();
                    });
                    addParkingbutton.addTo(map);
                }

                //L.circle(loc1, 25, {color: 'green', fill: false}).addTo(map);
                //map.fitBounds(bounds);
                //map.setZoom(18);
                map.setView(loc1, 14);
            }

            function loadParkings(possition) {
                //alert(possition.lat + " " + possition.long);

                loc = new L.LatLng(possition.lat, possition.long);
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "{{ route('api.getParkingsOwner') }}",
                    data: {
                        lat: possition.lat,
                        long: possition.long,
                        user_id: "{{empty(Auth::user())?1003:Auth::user()->id}}",
                        url: possition.url,
                        _token: '{{csrf_token()}}'
                    },
                    success: function (data) {
                        if (data.status == "success") {
                            console.log(data);
                        } else {
                            console.log("Error:" + data);
                        }
                        addPakrings(data.content);

                    }
                    ,
                    error: function (jqXHR, textStatus, errorThrown) {
                        //alert();
                        console.log(textStatus);
                        console.log(errorThrown);
                    }
                });
            }

            function addPakrings(parkings) {
                if (parkings.length > 0) {
                    // markers.clearLayers();
                    if (markers) {
                        map.removeLayer(markers);
                    }
                    markers = new L.FeatureGroup();

                    if (HomeParkings) {
                        map.removeLayer(HomeParkings);
                    }
                    HomeParkings = new L.FeatureGroup();

                    if (WorkParkings) {
                        map.removeLayer(WorkParkings);
                    }
                    WorkParkings = new L.FeatureGroup();

                    if (ShopParkings) {
                        map.removeLayer(ShopParkings);
                    }
                    ShopParkings = new L.FeatureGroup();

                    if (FoodParkings) {
                        map.removeLayer(FoodParkings);
                    }
                    FoodParkings = new L.FeatureGroup();

                    if (DringParkings) {
                        map.removeLayer(DringParkings);
                    }
                    DringParkings = new L.FeatureGroup();

                    if (OtherParkings) {
                        map.removeLayer(OtherParkings);
                    }
                    OtherParkings = new L.FeatureGroup();

                    if (LotParkings) {
                        map.removeLayer(LotParkings);
                    }
                    LotParkings = new L.FeatureGroup();

                    if (PlaceParkings) {
                        map.removeLayer(PlaceParkings);
                    }
                    PlaceParkings = new L.FeatureGroup();

                    if (lowConfitence) {
                        map.removeLayer(lowConfitence);
                    }
                    lowConfitence = new L.FeatureGroup();
                    if (highConfitence) {
                        map.removeLayer(highConfitence);
                    }
                    highConfitence = new L.FeatureGroup();
                    if (disabledIncludeParkings) {
                        map.removeLayer(disabledIncludeParkings);
                    }
                    disabledIncludeParkings = new L.FeatureGroup();
                    if (enabledIncludeParkings) {
                        map.removeLayer(enabledIncludeParkings);
                    }
                    enabledIncludeParkings = new L.FeatureGroup();


                    if (controlLayerParkings) {
                        map.removeControl(controlLayerParkings);
                    }


                    var countAllParkings = 0;
                    var HomeParkingsCount = 0;
                    var WorkParkingsCount = 0;
                    var ShopParkingsCount = 0;
                    var FoodParkingsCount = 0;
                    var DringParkingsCount = 0;
                    var OtherParkingsCount = 0;
                    var LotParkingsCount = 0;
                    var PlaceParkingsCount = 0;

                    var countlowConfitence = 0;
                    var counthighConfitence = 0;
                    var countdisabledIncludeParkings = 0;
                    var countenabledIncludeParkings = 0;

                    var HomeParkingsImgURL;
                    var WorkParkingsImgURL;
                    var ShopParkingsImgURL;
                    var FoodParkingsImgURL;
                    var DringParkingsImgURL;
                    var OtherParkingsImgURL;
                    var LotParkingsImgURL;
                    var PlaceParkingsImgURL;

                    var lowConfitenceImgURL;
                    var highConfitenceImgURL;
                    var disabledIncludeParkingsImgURL;
                    var enabledIncludeParkingsImgURL;

                    $.each(parkings, function (index, value) {
                        var myIcon;
                        var container = $('<div />');
                        loc1 = new L.LatLng(value.lat, value.long);
                        myIcon = L.icon({
                            iconUrl: value.url,// parkingUrlBaseonType(value),
                            iconSize: [20, 20],
                            iconAnchor: [10, 20],
                            popupAnchor: [0, -15],
                            //shadowUrl: 'my-icon-shadow.png',
                            //shadowSize: [68, 95],
                            //shadowAnchor: [22, 94]
                        });

                        popup = getCarparkPopupStart(value, container);
                        popup = addUpdateButton(popup, value, container, updateParking);
                        container.html(popup);
                        feature = L.marker(loc1, {icon: myIcon}, {opacity: 0.5}).bindPopup(container[0]);
                        markers.addLayer(feature);

                        countAllParkings++;
                        switch (value.parkingtype_id) {
                            case 1:
                                HomeParkings.addLayer(feature);
                                HomeParkingsCount++;
                                HomeParkingsImgURL = "{{ asset('images/HomeIcon.png') }}";
                                break;
                            case 2:
                                WorkParkings.addLayer(feature);
                                WorkParkingsCount++;
                                WorkParkingsImgURL = "{{ asset('images/WorkIcon.png') }}";
                                break;
                            case 3:
                                ShopParkings.addLayer(feature);
                                ShopParkingsCount++;
                                ShopParkingsImgURL = "{{ asset('images/ShopIcon.png') }}";
                                break;
                            case 4:
                                FoodParkings.addLayer(feature);
                                FoodParkingsCount++;
                                FoodParkingsImgURL = "{{ asset('images/FoodIcon.png') }}";
                                break;
                            case 5:
                                DringParkings.addLayer(feature);
                                DringParkingsCount++;
                                DringParkingsImgURL = "{{ asset('images/DringIcon.png') }}";
                                break;
                            case 6:
                                OtherParkings.addLayer(feature);
                                OtherParkingsCount++;
                                OtherParkingsImgURL = "{{ asset('images/OtherIcon.png') }}";
                                break;
                            case 7:
                                LotParkings.addLayer(feature);
                                LotParkingsCount++;
                                LotParkingsImgURL = "{{ asset('images/parkingLot.png') }}";
                                break;
                            case 8:
                                PlaceParkings.addLayer(feature);
                                PlaceParkingsCount++;
                                PlaceParkingsImgURL = "{{ asset('images/parkingplace.png') }}";
                                break;
                        }

                        if (value.validity < 5) {
                            lowConfitence.addLayer(feature);
                            countlowConfitence++;
                            lowConfitenceImgURL = "{{ asset('images/parkingplace.png') }}";
                        } else {
                            highConfitence.addLayer(feature);
                            counthighConfitence++;
                            highConfitenceImgURL = "{{ asset('images/parkingplace.png') }}";
                        }

                        if (value.avaliable == 0) {
                            disabledIncludeParkings.addLayer(feature);
                            countdisabledIncludeParkings++;
                            disabledIncludeParkingsImgURL = "{{ asset('images/parkingplace.png') }}";
                        } else {
                            enabledIncludeParkings.addLayer(feature);
                            countenabledIncludeParkings++;
                            enabledIncludeParkingsImgURL = "{{ asset('images/parkingplace.png') }}";
                        }

                    });
                    map.addLayer(markers);

                    //for all parkings
                    allparkings = '<img src="{{ asset('images/parkingplace.png') }}" style="width:15px;height:15px;"/><i class="text-left">ALL PARKINGS (' + countAllParkings + ')</i>';
                    parkingsControl = {
                        [allparkings]: markers
                    };


                    if (HomeParkingsCount > 0) {
                        homeparkings = '<img src="' + HomeParkingsImgURL + '" style="width:15px;height:15px;"/><i class="text-left">HOME(' + HomeParkingsCount + ')</i>';
                        parkingsControl[homeparkings] = HomeParkings;
                    }

                    if (WorkParkingsCount > 0) {
                        workparkings = '<img src="' + WorkParkingsImgURL + '" style="width:15px;height:15px;"/><i class="text-left">WORK(' + WorkParkingsCount + ')</i>';
                        parkingsControl[workparkings] = WorkParkings;
                    }

                    if (ShopParkingsCount > 0) {
                        shopparkings = '<img src="' + ShopParkingsImgURL + '" style="width:15px;height:15px;"/><i class="text-left">SHOP(' + ShopParkingsCount + ')</i>';
                        parkingsControl[shopparkings] = ShopParkings;
                    }

                    if (FoodParkingsCount > 0) {
                        foodparkings = '<img src="' + FoodParkingsImgURL + '" style="width:15px;height:15px;"/><i class="text-left">FOOD(' + FoodParkingsCount + ')</i>';
                        parkingsControl[foodparkings] = FoodParkings;
                    }

                    if (DringParkingsCount > 0) {
                        dringparkings = '<img src="' + DringParkingsImgURL + '" style="width:15px;height:15px;"/><i class="text-left">DRING(' + DringParkingsCount + ')</i>';
                        parkingsControl[dringparkings] = DringParkings;
                    }

                    if (OtherParkingsCount > 0) {
                        otherparkings = '<img src="' + OtherParkingsImgURL + '" style="width:15px;height:15px;"/><i class="text-left">OTHER(' + OtherParkingsCount + ')</i>';
                        parkingsControl[otherparkings] = OtherParkings;
                    }

                    if (LotParkingsCount > 0) {
                        lotparkings = '<img src="' + LotParkingsImgURL + '" style="width:15px;height:15px;"/><i class="text-left">LOT(' + LotParkingsCount + ')</i>';
                        parkingsControl[lotparkings] = LotParkings;
                    }
                    if (PlaceParkingsCount > 0) {
                        placeparkings = '<img src="' + PlaceParkingsImgURL + '" style="width:15px;height:15px;"/><i class="text-left">PLACE(' + PlaceParkingsCount + ')</i>';
                        parkingsControl[placeparkings] = PlaceParkings;
                    }

                    if (countlowConfitence > 0) {
                        PlacelowConfitence = '<img src="' + lowConfitenceImgURL + '" style="width:15px;height:15px;"/><i class="text-left">LOW CONFITENSE(' + countlowConfitence + ')</i>';
                        parkingsControl[PlacelowConfitence] = lowConfitence;
                    }

                    if (counthighConfitence > 0) {

                        PlacehighConfitence = '<img src="' + highConfitenceImgURL + '" style="width:15px;height:15px;"/><i class="text-left">HIGH CONFITENSE(' + counthighConfitence + ')</i>';
                        parkingsControl[PlacehighConfitence] = highConfitence;
                    }

                    if (countdisabledIncludeParkings > 0) {
                        PlacedisabledIncludeParkings = '<img src="' + disabledIncludeParkingsImgURL + '" style="width:15px;height:15px;"/><i class="text-left">DISABLED PARKING(' + countdisabledIncludeParkings + ')</i>';
                        parkingsControl[PlacedisabledIncludeParkings] = disabledIncludeParkings;
                    }

                    if (countenabledIncludeParkings > 0) {
                        PlaceenabledIncludeParkings = '<img src="' + enabledIncludeParkingsImgURL + '" style="width:15px;height:15px;"/><i class="text-left">ENABLED PARKING(' + countenabledIncludeParkings + ')</i>';
                        parkingsControl[PlaceenabledIncludeParkings] = enabledIncludeParkings;
                    }

                    controlLayerParkings = L.control.layers(null, parkingsControl, {
                        position: 'topright',
                        collapsed: false
                    }).addTo(map);

                    if (!addParkingbutton) {
                        addParkingbutton = L.easyButton('<i class="fas fa-plus-square">P</i>', function () {
                            addMarkerParking();
                        });
                        addParkingbutton.addTo(map);
                    }
                }
            }

            function addMarkerParking() {
                //myparkings = new L.FeatureGroup();
                var myIcon;
                loc1 = map.getCenter();
                myIcon = L.icon({
                    iconUrl: "{{ asset('images/parkingplace.png') }}",// parkingUrlBaseonType(value),
                    iconSize: [40, 40],
                    iconAnchor: [20, 40],
                    popupAnchor: [0, -15],
                });
                feature = L.marker(loc1, {icon: myIcon}, {opacity: 0.5}, {draggable: true}).addTo(map);
                feature.dragging.enable();
                feature.on('dragend', function (event) {
                    var marker = event.target;
                    var position = marker.getLatLng();
                    marker.setLatLng(new L.LatLng(position.lat, position.lng), {draggable: true});
                    marker.dragging.enable();
                    map.panTo(new L.LatLng(position.lat, position.lng));
                    newParkingPossition = {"lat": position.lat, "long": position.lng};
                    var CheckIfFinalDestination = confirm("Save parking changes?");
                    if (CheckIfFinalDestination == true) {
                        marker.dragging.disable();
                        counter = 1;
                        $("#myParkingSave")[0].reset();
                        dialog_form.dialog("open");
                    }
                });

            }

            function getAddressNamefromCoordinates(location, whereToShowAddress) {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "{{ route('api.getAddressfromCoordinates') }}",
                    data: {
                        lat: location.lat,
                        long: location.long,
                        user_id: "{{empty(Auth::user())?1003:Auth::user()->id}}",
                        _token: '{{csrf_token()}}'
                    },
                    success: function (data) {
                        if (data.status == "success") {
                            console.log(data);
                        } else {
                            console.log("Error:" + data);
                        }
                        //addPakringsSorted();
                        $(whereToShowAddress).val(data.content.Name);

                    }
                    ,
                    error: function (jqXHR, textStatus, errorThrown) {
                        //alert();
                        console.log(textStatus);
                        console.log(errorThrown);
                    }
                });
            }


            dialog = $("#dialog-message").dialog({
                autoOpen: false,
                modal: true,
                show: {
                    effect: "blind",
                    duration: 1000
                },
                hide: {
                    effect: "drop",
                    duration: 1000
                },
                // height: 600,
                // width: 800,
                buttons: {
                    Ok: function () {
                        $(this).dialog("close");
                    }
                }
            });

            dialog_form = $("#dialog-message-add-parking").dialog({
                autoOpen: false,
                modal: true,
                show: {
                    effect: "blind",
                    duration: 1000
                },
                hide: {
                    effect: "drop",
                    duration: 1000
                },
                //height: 600,
                //width: 800,
                buttons: {
                    Ok: function () {
                        $(this).dialog("close");
                    }
                }
            });


            $("#parkingInfo").button().on("click", function () {
                if (sortedParkings) {
                    dialog.dialog("open");
                } else {
                    alert("You did not choose calculate to find optimum parkings location!");
                }
            });

            function generateTableCode(data) {
                var html = '<table class="table table-striped">';
                html += '<tr>';
                var flag = 0;
                $.each(data[0], function (index, value) {
                    html += '<th>' + index + '</th>';
                });
                html += '</tr>';
                $.each(data, function (index, value) {
                    html += '<tr>';
                    $.each(value, function (index2, value2) {
                        html += '<td>' + value2 + '</td>';
                    });
                    html += '<tr>';
                });
                html += '</table>';
                return html;
            }

            $("#addrow").on("click", function () {
                addextraRowAtTableofCostsTimes();
            });

            $("table.order-list").on("click", ".ibtnDel", function (event) {
                $(this).closest("tr").remove();
                counter -= 1;
            });

            $("#SAVEparkingInfo").button().on("click", function () {
                if ($("#myParkingSave").valid()) {
                    var formData = JSON.stringify($("#myParkingSave").serializeArray());
                    //alert(formData);
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ route('map.storeParking') }}",
                        data: {
                            data: formData,
                            count: counter,
                            lat: newParkingPossition.lat,
                            long: newParkingPossition.long,
                            user_id: "{{empty(Auth::user())?1003:Auth::user()->id}}",
                            _token: '{{csrf_token()}}',
                        },
                        success: function (data) {
                            if (data.status == "success") {
                                console.log(data);
                            } else {
                                console.log("Error:" + data);
                            }
                            //possitionData = data.alladata;
                            dialog_form.dialog("close");
                        }
                        ,
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log(textStatus);
                            console.log(errorThrown);
                        }
                    });
                } else {
                    $("#myParkingSave").validate();
                }
            });

            // for selecting time date
            flatpickr('#datetimeYouPark', {
                enableTime: true,
                dateFormat: "Y-m-d H:i",
            });

        });
    </script>
@endsection