<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale())}}" xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token()}}">

    <title>{{ config('app.name', 'Smart Parking') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/jquery/jquery.js') }}"></script>
    <script src="{{ asset('js/toastr.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="https://unpkg.com/leaflet@1.0.0-rc.3/dist/leaflet.js"></script>
    <script src="{{ asset('js/jqueryui/jquery-ui.js') }}"></script>
    <script src="{{ asset('js/bootstrap/bootstrap.js') }}"></script>
    <script src="{{ asset('js/chosen/chosen.jquery.min.js') }}"></script>
    <script src="{{ asset('js/chosen/chosen.order.jquery.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/leaflet-easybutton@2/src/easy-button.js"></script>
    <script src="{{ asset('js/validate/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('js/validate/additional-methods.min.js') }}"></script>
    @stack('header_scripts')

    <!-- heatmap -->
    <script src="{{ asset('js/leaflet-heat.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Raleway:300,400,600" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/toastr.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.0-rc.3/dist/leaflet.css"/>
    <link href="{{ asset('css/jqueryui/jquery-ui.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/bootstrap/bootstrap.css') }}" rel="stylesheet"/>
    <link href="{{ asset('css/chosen/chosen.min.css') }}" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/leaflet-easybutton@2/src/easy-button.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css"
          integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    @yield('styles')
</head>
<body>
<div>
    <nav class="navbar navbar-expand-md  navbar-dark bg-dark navbar-laravel">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                {{ config('app.name', 'Smart Parking') }}
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav mr-auto">
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ml-auto">
                    <!-- Authentication Links -->
                    @guest
                        <li><a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a></li>
                        <li><a class="nav-link" href="{{ route('register') }}">{{ __('Register') }}</a></li>
                    @else
                        @if (Auth::user()->isAdmin())
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    ADMIN TOOLBOX <span class="caret"></span>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item"
                                       href="{{ route('users') }}">{{ __('Edit/View users') }}</a>
                                    <a class="dropdown-item"
                                       href="{{ route('user.create')}}">{{ __('Add User') }}</a>
                                </div>
                            </li>
                        @endif

                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                MAP TOOLBOX <span class="caret"></span>
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item"
                                   href="{{ route('map.searchmap') }}">{{ __('Find Best Parking') }}</a>
                                <a class="dropdown-item"
                                   href="{{ route('get.bookmarks',['user_id' => Auth::user()->id ])}}">{{ __('Bookmarks') }}</a>
                                @if (Auth::user()->isAdmin())
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item"
                                       href="{{ route('map.adminmap') }}">{{ __('PARKING MANAGEMENT') }}</a>
                                    <a class="dropdown-item"
                                       href="{{ route('map.providermap') }}">{{ __('PROVIDER PARKING MANAGEMENT (MY PARKINGS)') }}</a>
                                    <a class="dropdown-item"
                                       href="{{ route('import.exceladmin') }}">{{ __('IMPORT EXCEL') }}</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item"
                                       href="{{ route('get.reports',['reporttype' => 1])}}">{{ __('REPORT ON DESTINATION REQUESTS') }}</a>
                                    <a class="dropdown-item"
                                       href="{{ route('get.reports',['reporttype' => 2])}}">{{ __('REPORT ON REQUESTS LOCATION') }}</a>
                                    <a class="dropdown-item"
                                       href="{{ route('get.reports',['reporttype' => 3])}}">{{ __('REPORT ON PLACES') }}</a>
                                    <a class="dropdown-item"
                                       href="{{ route('get.reports',['reporttype' => 4])}}">{{ __('REPORT ON RANKS') }}</a>
                                    <a class="dropdown-item"
                                       href="{{ route('get.reports',['reporttype' => 5])}}">{{ __('REPORT ON SOURCES') }}</a>
                                @else
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item"
                                       href="{{ route('map.providermap') }}">{{ __('Provider Parking Management') }}</a>
                                @endif
                            </div>
                        </li>
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                {{ Auth::user()->name }} <span class="caret"></span>
                            </a>

                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('user.edit') }}">
                                    {{ __('Edit Profile') }}
                                </a>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault();
                                                         document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
    @yield('content')
</div>
<script>
    @if(Session::has('success'))
    toastr.success("{{ Session::get('success') }}");
    //session()->forget('success');
    //session()->flush();
    @endif
    @if(Session::has('info'))
    toastr.info("{{ Session::get('info') }}");
    //session()->forget('info');
    //session()->flush();
    @endif
</script>
<script>
    var focusedCarparkId = null;
    var focusedCarparkRecord = null;
    var desiredParkingControls = null;

    function createParkingControls(controls){
        desiredParkingControls = controls;
        var buttonHTML = "";
        for(control of desiredParkingControls){
            buttonHTML += "<button class=\"btn btn-primary\" type=\"button\" id="
                + control.name
                + ">"
                + control.text
                + "</button>";
        }
        $('#insertParkingControls').html(buttonHTML);
        for(control of desiredParkingControls){
            (function (staleControl){
                $('#' + staleControl.name).on('click', function(){
                    staleControl.callback(focusedCarparkRecord);
                });
            })(control);
        }
        disableParkingControls();
    }

    function reserveParking(value) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "{{ route('map.reserverParkingPlace') }}",
            data: {
                id: value.id,
                user_id: "{{empty(\Auth::user())?1003:\Auth::user()->id}}",
                _token: '{{csrf_token()}}'
            },
            success: function (data) {
                if (data.status == "success") {
                    if (data.content == 1) {
                        alert("Parking Reserved!");
                    }
                    else {
                        alert("Parking is full! We can not reserve!")
                    }
                } else {
                    console.log("Error:" + data);
                    alert("Error! So Parking is not Reserved!");
                }
            }
            ,
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
                console.log(errorThrown);
                alert("Error! So Parking is not Reserved!");
            }
        });
    }

    //update parking if not good info
    function updateParking(carparkRecord) {
        $("#myParkingSave")[0].reset();
        deleteAllrowsExceptfirst();
        $("#parkingName").val(carparkRecord.name);
        $("#parkingDisabledParkingsCount").val(carparkRecord.disabledcount);
        $("#parkingParkingsCount").val(carparkRecord.capacity);
        $("#parkingOccupiedParkingsCount").val(carparkRecord.occupied);
        $("#parkingOccupiedParkingsMaximumDuration").val(carparkRecord.maximumduration);
        $("#datetimeYouPark").val(carparkRecord.time);
        $("#ParkingType").val(carparkRecord.parkingtype_id);
        $("#comments").val(carparkRecord.comments);
        $("#YouParkvalidity").val(carparkRecord.validity);
        if (carparkRecord.avaliable) {
            $('#avaliable').attr('checked', true);
        } else {
            $('#avaliable').attr('checked', false);
        }

        if (carparkRecord.placesCosts.length == 0) {
            $("#cost[0]").val(carparkRecord.cost);
        } else {
            for (i = 0; i < carparkRecord.placesCosts.length; i++) {
                if (i != 0) {
                    addextraRowAtTableofCostsTimes();
                }
                var variableCost = '"#cost[' + i + ']"';
                $([variableCost]).val(carparkRecord.placesCosts[i].cost);
                document.getElementById("cost[" + i + "]").value = carparkRecord.placesCosts[i].cost;
                var variableTime = '"#time[' + i + ']"';
                $([variableTime]).val(carparkRecord.placesCosts[i].time);
                document.getElementById("time[" + i + "]").value = carparkRecord.placesCosts[i].time;

            }
        }
        newParkingPossition = {"lat": carparkRecord.lat, "long": carparkRecord.long};
        dialog_form.dialog("open");
    }

    function deleteAllrowsExceptfirst() {
        while (counter >= 2) {
            //var variableCost='"#cost[' + counter + ']"';
            //$([variableCost]).closest("tr").remove();
            //document.getElementById("cost[" + counter + "]").closest("tr").remove();
            //counter -= 1;
            document.getElementById("CostsTable").deleteRow(counter);
            counter -= 1;
        }
        //counter++;
    }

    function addextraRowAtTableofCostsTimes() {
        var newRow = $("<tr>");
        var cols = "";

        cols += '<td><input type="number" class="form-control" id="cost[' + counter + ']" name="cost[' + counter + ']" pattern="\\d+" required/></td>';
        cols += '<td><input type="number" class="form-control" id="time[' + counter + ']" name="time[' + counter + ']" pattern="\\d+"  min="1" required/></td>';

        cols += '<td><input type="button" class="ibtnDel btn btn-md btn-danger "  value="Delete"></td>';
        newRow.append(cols);
        $("table.order-list").append(newRow);
        counter++;
    }

    function bookMarkParking(value) {
        $("#BookMarkSave")[0].reset();
        $("#bookmarkName").val(value.name);
        $("#places_id").val(value.id);
        dialog_bookmark.dialog("open");
    }

    function getPrediction(carpark_entry) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "{{ route('api.getprediction') }}",
            data: {
                carpark_id: carpark_entry.id,
                prediction_time: getTimestampFromFormattedDate($('#selected_date').val()),
                user_id: "{{empty(\Auth::user())?1003:\Auth::user()->id}}",
                _token: '{{csrf_token()}}'
            },
            success: function (data) {
                setFocusedCarpark(carpark_entry.id);
            }
            ,
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
                console.log(errorThrown);
                alert("Error, not able to make prediction!");
            }
        });
    }

    function setFocusedCarparkFromClick(event){
        setFocusedCarpark(event.target.properties.carpark_id);
    }

    function setFocusedCarpark(carpark_id){
        focusedCarparkId = carpark_id;
        disableParkingControls();
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "{{ route('api.getcarpark') }}",
            data: {
                carpark_id: focusedCarparkId,
                user_id: "{{empty(\Auth::user())?1003:\Auth::user()->id}}",
                _token: '{{csrf_token()}}'
            },
            success: function (data) {
                focusedCarparkRecord = data;
                bindParkingDetails(focusedCarparkRecord);
                enableParkingControls();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
                console.log(errorThrown);
                alert("Error, not able to get carpark!");
            }
        });
    }

    function disableParkingControls(){
        for(control of desiredParkingControls){
            $('#' + control.name).prop("disabled", true);
        }
    }

    function enableParkingControls(){
        for(control of desiredParkingControls){
            $('#' + control.name).prop("disabled", false);
        }
    }

    function bindParkingDetails(carpark_record){
        text="";
        let printoutFormat = [
            {kind: 'simple', dbName: 'name', humanReadable: 'Name'},
            {kind: 'functional', callback: capacityDisplay},
            {kind: 'functional', callback: predictionDisplay},
            {kind: 'simple', dbName: 'cost', humanReadable: 'Cost'},
            {kind: 'simple', dbName: 'maximumduration', humanReadable: 'Max Time (m)'},
            {kind: 'simple', dbName: 'comments', humanReadable: 'Comments'},
        ]
        for(specification of printoutFormat){
            if(specification.kind === 'simple'){
                text += `${specification.humanReadable}: ${carpark_record[specification.dbName]}<br>`;
            }
            else{
                toAdd = specification.callback(carpark_record);
                if(toAdd !== ""){
                    text += `${toAdd}<br>`;
                }
            }
        }
        $('#carpark_details').html(text);
    }

    function capacityDisplay(carpark_record){
        if (carpark_record.hasOwnProperty('spaces_available')) {
            displayedForm = carpark_record.spaces_available + '/' + carpark_record.capacity
                + " spaces available currently"
        } else {
            displayedForm = '?/' + carpark_record.capacity + " spaces available currently (this carpark is untracked)"
        }
        return displayedForm;
    }

    function predictionDisplay(carpark_record){
        displayedForm = "";
        if(carpark_record.predicted_spaces != null){
            displayedForm += `Predicted ${carpark_record.predicted_spaces} spaces at ${getFormattedDate(carpark_record.predicted_for_time)}`;
        }
        return displayedForm;
    }

    function getFormattedDate(timestamp){
        date = new Date(timestamp * 1000);
        timeDisplay = `${padDateOrTime(date.getHours())}:${padDateOrTime(date.getMinutes())}`;
        if(!timestampIsToday(timestamp)){
            timeDisplay += ` on ${date.getFullYear()}-${padDateOrTime(date.getMonth()+1)}-${padDateOrTime(date.getDate())}`;
        }
        return timeDisplay;
    }

    function timestampIsToday(timestamp){
        date = new Date(timestamp * 1000);
        today = new Date();
        return date.getFullYear() === today.getFullYear()
            && date.getMonth() === today.getMonth()
            && date.getDate() === today.getDate();
    }

    function padDateOrTime(toPad){
        toPad = toPad.toString();
        if(toPad.length === 1){
            return '0' + toPad;
        }
        return toPad;
    }

    function getTimestampFromFormattedDate(formatted_date){
        var date = new Date();
        var offset = date.getTimezoneOffset();
        return Date.parse(formatted_date)/1000 - offset*60; //Return UTC timestamp
    }
</script>
@yield('scripts')
</body>
@yield('scripts2')
</html>
    