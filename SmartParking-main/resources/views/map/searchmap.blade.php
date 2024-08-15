@extends('layouts.map')

@section('styles')
<style>
    :root {
        --primary-color: #636b6f;
        --secondary-color: #2d3748;
        --background-color: #e2e2e2;
        --link-hover-color: #708090; /* Slate Grey */
        --border-radius: 8px;
        --transition-speed: 0.3s;
        --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --highlight-color: #ffdd57;
        --light-blue-color: #add8e6; /* Light Blue */
    }

    /* Font and typography */
    body {
        font-family: 'Raleway', sans-serif;
        color: #333;
    }

    /* Container */
    .container-fluid {
        margin: 0 20px 20px;
        background-color: #f4f4f9;
    }

    /* Card Styles */
    .card {
        border: 1px solid #ddd;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        background-color: #fff;
    }

    .card-header {
        background-color: var(--secondary-color);
        color: #fff;
        font-weight: bold;
        padding: 15px;
        border-radius: 8px 8px 0 0;
    }

    .card-body {
        padding: 20px;
    }

    .card-footer {
        background-color: #f8f9fa;
        border-radius: 0 0 8px 8px;
        padding: 10px;
    }

    /* Form Elements */
    input[type=text],
    input[type=email],
    input[type=number],
    textarea,
    select.form-control,
    .flatpickr {
        width: 100%;
        border: 1px solid #ddd;
        margin: 0;
        padding: 10px;
        font-family: inherit;
        font-size: 16px;
        box-sizing: border-box;
        border-radius: 4px;
        transition: border-color 0.3s;
    }

    input:focus, .form-control:focus, .flatpickr:focus {
        border-color: #007bff;
        outline: none;
    }

    input:invalid {
        box-shadow: 0 0 5px 1px red;
    }

    /* Button Styles */
    .btn-primary {
        background-color: var(--light-blue-color);
        border-color: var(--light-blue-color);
        border-radius: 4px;
        color: var(--secondary-color);
        font-size: 16px;
        padding: 10px 20px;
        transition: background-color 0.3s, border-color 0.3s;
    }

    .btn-primary:hover {
        background-color: #0056b3;
        border-color: #0056b3;
    }

    /* Label Styles */
    .form-label {
        margin-bottom: 5px;
        font-weight: 600;
    }

    /* Dialog Styles */
    .ui-dialog {
        z-index: 1000 !important;
    }

    /* Autocomplete Styles */
    .ui-autocomplete span.hl_results {
        background-color: #ffff66;
    }

    .ui-autocomplete-loading {
        background: white url('{{ asset('images/ui-anim_basic_16x16.gif') }}') right center no-repeat;
    }

    .ui-autocomplete {
        max-height: 250px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 5px;
    }

    .ui-autocomplete li {
        font-size: 16px;
    }

    /* Keyframe animation for intro text */
    @keyframes fadeInUp {
        0% {
            opacity: 0;
            transform: translateY(20px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .intro-text {
        text-align: center;
        margin-bottom: 0px;
        padding: 20px;
        background-color: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        animation: fadeInUp 1s ease-out;
    }

    .intro-text h2 {
        margin-bottom: 10px;
        color: #333;
    }

    .intro-text p {
        color: #666;
        font-size: 16px;
    }

    /* Responsive Styles */
    @media (max-width: 991px) {
        /* Adjust the flexbox layout for medium-sized devices */
        .row.flex-column-reverse.flex-lg-row {
            flex-direction: column;
        }

        /* Map and card sizes */
        #map, #parking_details, .col-lg-6 {
            height: auto;
            width: 100%;
        }

        /* Reduce padding for cards */
        .card-body {
            padding: 14px;
        }

        /* Font sizes */
        .intro-text h2 {
            font-size: 1.5rem;
        }

        .intro-text p {
            font-size: 14px;
        }
    }

    @media (max-width: 767px) {
        /* Mobile-specific adjustments */

        .container-fluid {
        margin: 0 0px 20px;
    }
        .intro-text, .card {
            margin: 0 auto 10px;
            width: 100%;
            padding: 15px;
        }

        /* Button styles */
        .btn-primary {
            font-size: 14px;
            padding: 8px 15px;
        }



        /* Reduce padding in card body */
        .card-body {
            padding: 10px;
        }
    }

    @media (max-width: 575px) {
        /* Extra small devices adjustments */

        .container-fluid {
        margin: 0 0px 20px;
    }
        .intro-text h2 {
            font-size: 1.5rem;
        }

        .intro-text p {
            font-size: 15px;
        }

        /* Adjust button width */
        .btn-primary {
            width: 100%;
            margin-top: 10px;
            padding: 5px;
        }

        /* Flexbox column layout */
        .row {
            flex-direction: column;
        }

        /* Form input and labels */
        .form-label {
            font-size: 14px;
        }

     
    }
</style>

@stop
@section('content')
    @include('includes.errors')
    
    <!-- Introductory Text -->
    <div class="intro-text">
        <h2>Welcome to the Parking Finder</h2>
        <p>Use this tool to find optimal parking suggestions based on your starting point, destination, and various preferences. Customize your search settings and view detailed parking options to find the best parking spots for your needs.</p>
    </div>

    <div id="parking_grid" class="container-fluid">
        <p class="card-text">
        <div class="card-body">
            <div class="row flex-column-reverse flex-lg-row">
                <div class="col-lg-6">
                    <div id="map" style="height: 400px;"></div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            Search Settings
                        </div>
                        <div class="card-body">
                            <form>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="addrStart" class="form-label">Starting Point</label>
                                        <input type="text" name="addrStart" id="addrStart" class="form-control" placeholder="Source Address"/>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="addrEnd" class="form-label">Destination</label>
                                        <input type="text" name="addrEnd" id="addrEnd" class="form-control" placeholder="Destination Address"/>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="travelDate" class="form-label">When are you setting off?</label>
                                        <input id="selected_date" class="flatpickr form-control" data-enabletime=true name="travelDate">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <span class="form-label">Select 3 factors to optimize (decreasing importance)</span>
                                        <select class="form-control chosen" multiple id="chosenSelectOrder">
                                            @foreach($parameters as $parameter)
                                                <option value="{{$parameter->id}}">{{$parameter->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <button type="button" id="s1-get-order" class="btn btn-primary w-100">
                                            Get suggestions
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="button" id="parkingInfo" class="btn btn-primary w-100">
                                            Suggestion details
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row flex-lg-row">
                <div id="parking_details" class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            Parking Details
                        </div>
                        <div class="card-body" id="carpark_details">
                            No carpark selected.
                        </div>
                        <div class="card-footer text-muted">
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            Parking Controls
                        </div>
                        <div id="insertParkingControls" class="card-body"></div>
                        <div class="card-footer text-muted">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="dialog-message" title="Results">
        <p>
            <span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>
            Parking suggestion results
        </p>
        <p>
        <div id="tableModal">
        </div>
    </div>

    <div id="dialog-message-add-parking" title="ADD PARKING (CROWD SOURCING)">
        <p>
            <span class="ui-icon ui-icon-circle-check" style="float:left; margin:0 7px 50px 0;"></span>
            ADD PARKING.
        </p>
        <p>
            <div class="card text-center">
                <div class="card-header">
                    PUT PARKING DATA
                </div>
                <div class="card-body">
                    <h4 class="card-title"> New Parking Data</h4>
                    <form name="myParkingSave" id="myParkingSave">
                        <div class="form-group mb-3">
                            <label for="parkingName">Parking Name</label>
                            <input type="text" class="form-control" id="parkingName" placeholder="UNKNOWN" name="name" value="UNKNOWN">
                        </div>
                        <div class="form-group mb-3">
                            <label for="parkingDisabledParkingsCount">How many Parkings for Disabled exist?</label>
                            <input type="number" class="form-control" id="parkingDisabledParkingsCount" placeholder="0" name="disabledcount" VALUE="0" pattern="\d+">
                        </div>
                        <div class="form-group mb-3">
                            <label for="parkingPricePerHour">Cost per hour (E.g. 1.50)</label>
                            <input type="number" class="form-control" id="parkingPricePerHour" placeholder="Cost per hour (E.g. 1.50)" step="0.01" name="cost" pattern="^\d*(\.\d{0,2})?$">
                        </div>
                        <div class="form-group mb-3">
                            <label for="parkingLatitude">Latitude</label>
                            <input type="text" class="form-control" id="parkingLatitude" name="latitude" pattern="-?\d+(\.\d{1,9})?" step="any" placeholder="latitude">
                        </div>
                        <div class="form-group mb-3">
                            <label for="parkingLongitude">Longitude</label>
                            <input type="text" class="form-control" id="parkingLongitude" name="longitude" pattern="-?\d+(\.\d{1,9})?" step="any" placeholder="longitude">
                        </div>
                        <div class="form-group mb-3">
                            <label for="parkingHeight">Maximum Parking Height (m) (e.g. 2.0)</label>
                            <input type="number" class="form-control" id="parkingHeight" placeholder="e.g. 2.0" name="height" pattern="^\d*(\.\d{0,1})?$">
                        </div>
                        <div class="form-group mb-3">
                            <label for="parkingHeight">Total Number of Spaces</label>
                            <input type="number" class="form-control" id="parkingSpaces" placeholder="e.g. 200" name="total" pattern="\d+">
                        </div>
                        <div class="form-group mb-3">
                            <label for="parkingSecure">Secure Parking?</label>
                            <select class="form-control" id="parkingSecure" name="secure">
                                <option value="NO">NO</option>
                                <option value="YES">YES</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-muted">
                    <button type="button" class="btn btn-primary" id="addParkingData">
                        <span class="glyphicon glyphicon-save" aria-hidden="true"></span> Save data
                    </button>
                </div>
            </div>
        </p>
    </div>
@endsection

@push('header_scripts')
@endpush

@section('scripts')
    <script>
        //   var possition;
        //   var possitionData;
        var map;
        var feature;
        var featureStart;
        var featureSEnd;
        var UserPossition;
        var DestinationPossition;
        // setup a marker group
        var markers;
        var addMyParkingbutton;
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
        var disabledIncludeParkings;
        var dialog_bookmark;
        $(document).ready(function () {
            const selectedDateFlatPickr = initialiseFlatPickrForTravelTime();

            createParkingControls([
                {'text': "Reserve", 'name': "reserveParking", 'callback': reserveParking}
               ,{'text': "Bookmark", 'name': "bookmarkParking", 'callback': bookMarkParking}
               ,{'text': "Update", 'name': "updateParking", 'callback': updateParking}
               ,{'text': "Make Prediction", 'name': "makeParkingPrediction", 'callback': getPrediction}
            ]);

            var wWidth = $(window).width();
            var dWidth = wWidth * 0.8;
            var wHeight = $(window).height();
            var dHeight = wHeight * 0.8;

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
                getAddressNamefromCoordinates(UserPossition, '#addrStart');
                chooseLocationAdd(UserPossition, "START");
            }

            //END OF SHOWING INFO FOR PLACE OF USER

            window.onload = load_map;

            //LOAD MAP
            function load_map() {
                console.log("loading");
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

                url = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
                opt = {minZoom: 1, maxZoom: 18, attribution: "Leafletjs.com - OpenStreetMap.org - cyens.org.cy"};
                var layer = new L.TileLayer(url, opt);
                map.addLayer(layer);
                map.setView(new L.LatLng(34.8717199, 33.6049646), 9);
                var scale = L.control.scale(); // Creating scale control
                scale.addTo(map); // Adding scale control to the map
            }

            // Chosenify every multiple select DOM elements with class 'chosen'
            // $('select[multiple].chosen').chosen();
            $("#chosenSelectOrder").chosen();

            $('#s1-get-order').click(function () {
                // Functional
                // var selection = ChosenOrder.getSelectionOrder(MY_SELECT);
                // Object-oriented
                var selection = ChosenOrder.getSelectionOrder($("#chosenSelectOrder"));
                if (selection && selection.length == 3 && UserPossition && DestinationPossition) {
                    loadParkingsSorted(UserPossition, DestinationPossition, selection);
                }
                else {
                    alert("Please fill in all the search settings.");
                }
            });
            //END OF  SETTINGS FOR CHOSENIFY

            //SET ALL VALUES OF TEXTBOXEX TO EMPTY
            //IF RELOAD PAGE!
            $('#addrStart').val("");
            $('#addrEnd').val("");

            /*
            AUTOCOMPLETE FOR ADDRESSES
             */
            //end
            $("#addrEnd").autocomplete({
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
                    chooseLocationAdd(ui.item, "END");
                    DestinationPossition = ui.item;
                    loadParkingsUnsorted(ui.item);
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
                        $(this).autocomplete("search", $('#addrEnd').val());
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

            //start
            $("#addrStart").autocomplete({
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
                    console.log("Selected Start: " + ui.item.value + " aka " + ui.item.id);
                    //possition = possitionData[ui.item.id];
                    //console.log("possition information " + possition);
                    chooseLocationAdd(ui.item, "START");
                    UserPossition = ui.item;
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
                        $(this).autocomplete("search", $('#addrStart').val());
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

            function chooseLocationAdd(possition, type) {
                var myIcon;
                if (feature) {
                    map.removeLayer(feature);
                }
                loc1 = new L.LatLng(possition.lat, possition.long);
                if (type == "START") {
                    if (featureStart) {
                        map.removeLayer(featureStart);
                    }
                    myIcon = L.icon({
                        iconUrl: '{{ asset('images/startIcon.png') }}',
                        iconSize: [30, 30],
                        iconAnchor: [15, 30],
                        //shadowUrl: 'my-icon-shadow.png',
                        //shadowSize: [68, 95],
                        //shadowAnchor: [22, 94]
                    });
                    featureStart = L.marker(loc1, {icon: myIcon}, {opacity: 0.5}, {draggable: true}).addTo(map);
                    featureStart.dragging.enable();
                    featureStart.on('dragend', function (event) {
                        var markerStart = event.target;
                        var positionStart = markerStart.getLatLng();
                        markerStart.setLatLng(new L.LatLng(positionStart.lat, positionStart.lng), {draggable: true});
                        markerStart.dragging.enable();
                        map.panTo(new L.LatLng(positionStart.lat, positionStart.lng));
                        UserPossition = {"lat": positionStart.lat, "long": positionStart.lng};
                        getAddressNamefromCoordinates(UserPossition, '#addrStart');
                    });
                }
                else if (type == "END") {
                    if (featureSEnd) {
                        map.removeLayer(featureSEnd);
                    }
                    myIcon = L.icon({
                        iconUrl: '{{ asset('images/marker-icon.png') }}',
                        iconSize: [30, 30],
                        iconAnchor: [15, 30],
                        //iconSize: [38, 95],
                        //iconAnchor: [37, 94],
                        //shadowUrl: 'my-icon-shadow.png',
                        //shadowSize: [68, 95],
                        //shadowAnchor: [22, 94]
                    });
                    featureSEnd = L.marker(loc1, {icon: myIcon}, {opacity: 0.5}, {draggable: true}).addTo(map);
                    featureSEnd.dragging.enable();
                    featureSEnd.on('dragend', function (event) {
                        var markerEnd = event.target;
                        var positionEnd = markerEnd.getLatLng();
                        markerEnd.setLatLng(new L.LatLng(positionEnd.lat, positionEnd.lng), {draggable: true});
                        markerEnd.dragging.enable();
                        map.panTo(new L.LatLng(positionEnd.lat, positionEnd.lng));
                        DestinationPossition = {"lat": positionEnd.lat, "long": positionEnd.lng};
                        getAddressNamefromCoordinates(DestinationPossition, '#addrEnd');
                        loadParkingsUnsorted(DestinationPossition);
                    });

                    //user can add its own parking place that went and it is ok! CROWD SOURCING
                    if (!addMyParkingbutton) {
                        addMyParkingbutton = L.easyButton('<i class="fas fa-plus-square">P</i>', function () {
                            addMarkerMyParking();
                        });
                        addMyParkingbutton.addTo(map);
                    }
                }

                //L.circle(loc1, 25, {color: 'green', fill: false}).addTo(map);
                //map.fitBounds(bounds);
                //map.setZoom(18);
                map.setView(loc1, 14);
            }

            function loadParkingsUnsorted(position) {
                //alert(position.lat + " " + position.long);

                loc = new L.LatLng(position.lat, position.long);
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "{{ route('api.getParkings') }}",
                    data: {
                        lat: position.lat,
                        long: position.long,
                        user_id: "{{empty(\Auth::user())?1003:\Auth::user()->id}}",
                        url: position.url,
                        _token: '{{csrf_token()}}'
                    },
                    success: function (data) {
                        if (data.status == "success") {
                            console.log(data);
                        } else {
                            console.log("Error:" + data);
                        }
                        addUnsortedCarparks(data.content);
                    }
                    ,
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus);
                        console.log(errorThrown);
                    }
                });
            }

            function addUnsortedCarparks(carparks) {
                if (markers) {
                    map.removeLayer(markers);
                }
                markers = new L.FeatureGroup();
                $.each(carparks, function (index, carpark_entry) {
                    var myIcon;
                    var container = $('<div />');
                    loc1 = new L.LatLng(carpark_entry.lat, carpark_entry.long);
                    myIcon = L.icon({
                        iconUrl: carpark_entry.url,// parkingUrlBaseonType(value),
                        iconSize: [20, 20],
                        iconAnchor: [10, 20],
                        //shadowUrl: 'my-icon-shadow.png',
                        //shadowSize: [68, 95],
                        //shadowAnchor: [22, 94]
                    });

                    // container.append($('<span class="bold">').text(" :)"))
                    feature = L.marker(loc1, {icon: myIcon}, {opacity: 0.5}).on('click', setFocusedCarparkFromClick);
                    feature.properties = {};
                    feature.properties.carpark_id = carpark_entry.id;
                    markers.addLayer(feature);
                    //feature = L.marker(loc1, {icon: myIcon}, {opacity: 0.5});//.addTo(map);
                    //markers.addLayer(feature);
                });
                map.addLayer(markers);
            }

            function loadParkingsSorted(userPosition, destinationPosition, selection) {
                // alert(Userpossition.lat + " " + Userpossition.long + "|" + DestinationPossition.lat + " " + DestinationPossition.long);
                //loc = new L.LatLng(possition.lat, possition.long);

                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "{{ route('api.getWeightedParkings') }}",
                    data: {
                        Userlat: userPosition.lat,
                        Userlong: userPosition.long,
                        Destinationlat: destinationPosition.lat,
                        Destinationlog: destinationPosition.long,
                        predictionForTime: getTimestampFromFormattedDate($('#selected_date').val()),
                        selection: selection,
                        user_id: "{{empty(\Auth::user())?1003:\Auth::user()->id}}",
                        _token: '{{csrf_token()}}'
                    },
                    success: function (data) {
                        if (data.status == "success") {
                            console.log(data);
                        } else {
                            console.log("Error:" + data);
                        }
                        sortedParkings = data.content;
                        htmlDiv = generateTableCode(sortedParkings);
                        $('#tableModal').html(htmlDiv).show;
                        addSortedCarparks(data.content);

                    }
                    ,
                    error: function (jqXHR, textStatus, errorThrown) {
                        //alert();
                        console.log(textStatus);
                        console.log(errorThrown);
                    }
                });
            }

            function addSortedCarparks(parkings) {
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
                    var HomeParkingsImgURL;
                    var WorkParkingsImgURL;
                    var ShopParkingsImgURL;
                    var FoodParkingsImgURL;
                    var DringParkingsImgURL;
                    var OtherParkingsImgURL;
                    var LotParkingsImgURL;
                    var PlaceParkingsImgURL;
                    $.each(parkings, function (index, carparkEntry) {
                        var myIcon;
                        var container = $('<div />');
                        loc1 = new L.LatLng(carparkEntry.lat, carparkEntry.long);
                        myIcon = L.icon({
                            iconUrl: carparkEntry.url,// parkingUrlBaseonType(value),
                            iconSize: [20, 20],
                            iconAnchor: [10, 20],
                            //shadowUrl: 'my-icon-shadow.png',
                            //shadowSize: [68, 95],
                            //shadowAnchor: [22, 94]
                        });

                        feature = L.marker(loc1, {icon: myIcon}, {opacity: 0.5}).on('click', setFocusedCarparkFromClick);
                        feature.properties = {};
                        feature.properties.carpark_id = carparkEntry.id;
                        markers.addLayer(feature);

                        countAllParkings++;
                        switch (carparkEntry.parkingtype_id) {
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
                    });
                    map.addLayer(markers);

                    //for all parkings
                    allparkings = '<img src="{{ asset('images/parkingplace.png') }}" style="width:15px;height:15px;"/><i>ALL PARKINGS (' + countAllParkings + ')</i>';
                    parkingsControl = {
                        [allparkings]: markers
                    };


                    if (HomeParkingsCount > 0) {
                        homeparkings = '<img src="' + HomeParkingsImgURL + '" style="width:15px;height:15px;"/><i>HOME PARKINGS (' + HomeParkingsCount + ')</i>';
                        parkingsControl[homeparkings] = HomeParkings;
                    }

                    if (WorkParkingsCount > 0) {
                        workparkings = '<img src="' + WorkParkingsImgURL + '" style="width:15px;height:15px;"/><i>WORK PARKINGS (' + WorkParkingsCount + ')</i>';
                        parkingsControl[workparkings] = WorkParkings;
                    }

                    if (ShopParkingsCount > 0) {
                        shopparkings = '<img src="' + ShopParkingsImgURL + '" style="width:15px;height:15px;"/><i>SHOP PARKINGS (' + ShopParkingsCount + ')</i>';
                        parkingsControl[shopparkings] = ShopParkings;
                    }

                    if (FoodParkingsCount > 0) {
                        foodparkings = '<img src="' + FoodParkingsImgURL + '" style="width:15px;height:15px;"/><i>FOOD PARKINGS (' + FoodParkingsCount + ')</i>';
                        parkingsControl[foodparkings] = FoodParkings;
                    }

                    if (DringParkingsCount > 0) {
                        dringparkings = '<img src="' + DringParkingsImgURL + '" style="width:15px;height:15px;"/><i>DRING PARKINGS (' + DringParkingsCount + ')</i>';
                        parkingsControl[dringparkings] = DringParkings;
                    }

                    if (OtherParkingsCount > 0) {
                        otherparkings = '<img src="' + OtherParkingsImgURL + '" style="width:15px;height:15px;"/><i>OTHER PARKINGS (' + OtherParkingsCount + ')</i>';
                        parkingsControl[otherparkings] = OtherParkings;
                    }

                    if (LotParkingsCount > 0) {
                        lotparkings = '<img src="' + LotParkingsImgURL + '" style="width:15px;height:15px;"/><i>LOT PARKINGS (' + LotParkingsCount + ')</i>';
                        parkingsControl[lotparkings] = LotParkings;
                    }
                    if (PlaceParkingsCount > 0) {
                        placeparkings = '<img src="' + PlaceParkingsImgURL + '" style="width:15px;height:15px;"/><i>PLACE PARKINGS (' + PlaceParkingsCount + ')</i>';
                        parkingsControl[placeparkings] = PlaceParkings;
                    }

                    controlLayerParkings = L.control.layers(null, parkingsControl, {
                        position: 'topright',
                        collapsed: false
                    }).addTo(map);

                    if (!addMyParkingbutton) {
                        addMyParkingbutton = L.easyButton('<i class="fas fa-plus-square">P</i>', function () {
                            addMarkerMyParking();
                        });
                        addMyParkingbutton.addTo(map);
                    }

                }
            }

            function addMarkerMyParking() {
                //myparkings = new L.FeatureGroup();
                var myIcon;
                loc1 = map.getCenter();
                myIcon = L.icon({
                    iconUrl: "{{ asset('images/parkingplace.png') }}",// parkingUrlBaseonType(value),
                    iconSize: [40, 40],
                    iconAnchor: [20, 40],
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
                    var CheckIfFinalDestination = confirm("You have finish defining the destination of Parking?");
                    if (CheckIfFinalDestination == true) {
                        marker.dragging.disable();
                        counter = 1;
                        $("#myParkingSave")[0].reset();
                        dialog_form.dialog("open");
                    }
                });
                //.addTo(map);
                /*
                if (!myparkings) {
                    //map.removeLayer(myparkings);
                    myparkings = new L.FeatureGroup();
                    myparkings.addLayer(feature);
                    map.addLayer(myparkings);
                }
                else {
                    myparkings.addLayer(feature);
                }
                */

            }

            function getAddressNamefromCoordinates(location, whereToShowAddress) {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: "{{ route('api.getAddressfromCoordinates') }}",
                    data: {
                        lat: location.lat,
                        long: location.long,
                        user_id: "{{empty(\Auth::user())?1003:\Auth::user()->id}}",
                        _token: '{{csrf_token()}}'
                    },
                    success: function (data) {
                        if (data.status == "success") {
                            console.log(data);
                        } else {
                            console.log("Error:" + data);
                        }
                        //addSortedCarparks();
                        $(whereToShowAddress).val(data.content.Name);

                    }
                    ,
                    error: function (jqXHR, textStatus, errorThrown) {
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
                height: dHeight,//300,
                width: dWidth,//400,
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
                height: dHeight,//300,
                width: dWidth,//400,
                buttons: {
                    Ok: function () {
                        $(this).dialog("close");
                    }
                }
            });

            dialog_bookmark = $("#dialog-message-add-bookmark-parking").dialog({
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
                height: dHeight,//300,
                width: dWidth,//400,
                //  height: "80%" ,//300,
                //  width: "60%" ,//400,
                buttons: {
                    Ok: function () {
                        $(this).dialog("close");
                    }
                }
            });

            $("#parkingInfo").button().on("click", function () {
                if (sortedParkings) {
                    dialog.dialog("open");
                }
                else {
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
                            user_id: "{{empty(\Auth::user())?1003:\Auth::user()->id}}",
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
                            //alert();
                            console.log(textStatus);
                            console.log(errorThrown);
                        }
                    });
                }
                else {
                    $("#myParkingSave").validate();
                }
            });

            $("#SAVEbookmarknfo").button().on("click", function () {
                if ($("#BookMarkSave").valid()) {
                    var formData = JSON.stringify($("#BookMarkSave").serializeArray());
                    //alert(formData);
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        url: "{{ route('map.storeBookMark') }}",
                        data: {
                            data: formData,
                            user_id: "{{empty(\Auth::user())?1003:\Auth::user()->id}}",
                            _token: '{{csrf_token()}}',
                        },
                        success: function (data) {
                            if (data.status == "success") {
                                console.log(data);
                            } else {
                                console.log("Error:" + data);
                            }
                            //possitionData = data.alladata;
                            dialog_bookmark.dialog("close");
                        }
                        ,
                        error: function (jqXHR, textStatus, errorThrown) {
                            console.log(textStatus);
                            console.log(errorThrown);
                        }
                    });
                }
                else {
                    $("#myParkingSave").validate();
                }
            });

            function initialiseFlatPickrForTravelTime(){
                const selectedDateFlatPickr = flatpickr('#selected_date',{
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: "Y-m-d H:i",
                    minDate: "today",
                    minTime: Date.now(),
                    defaultDate: Date.now(),
                });
                return selectedDateFlatPickr;
            }
        });
    </script>
@endsection