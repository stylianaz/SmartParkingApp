@extends('layouts.app')

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
        --discard-color: #ff6347; 
        --success-color: #28a745; /* Green (Success) */

    }

    body {
        background-color: var(--background-color);
        color: var(--secondary-color);
        font-family: 'Raleway', sans-serif;
    }

    .card {
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        background-color: #fff;
        margin: 0 auto;
    }

    .card-header {
        background-color: var(--primary-color);
        color: #fff;
        font-weight: bold;
        text-align: center;
        padding: 1rem;
        border-bottom: 2px solid var(--light-blue-color);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .card-header img {
    margin-right: 5px;
    width: 28px; /* Adjust the width as needed */
    height: 25px; /* Adjust the height as needed */
    border-radius: 4px; /* Use small border-radius for slightly rounded corners */
}

    .card-body {
        padding: 2.5rem;
        background-color: #fff;
    }

    .form-control {
        border-radius: var(--border-radius);
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        border: 1px solid #ccc;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-group label {
        font-weight: bold;
        color: var(--primary-color);
    }

    .form-group .invalid-feedback {
        color: #e74c3c;
        font-size: 0.875rem;
    }

    .btn-primary, .btn-success, .btn-danger {
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: background-color var(--transition-speed), border-color var(--transition-speed);
    }

    .btn-success {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-danger {
        background-color: var(--discard-color);
        border-color: var(--discard-color);
    }

    .btn-success:hover {
        background-color: var(--success-color);
        border-color: var(--success-color);
    }

    .btn-danger:hover {
        background-color: #e63946;
        border-color: #e63946;
    }

    .alert-success {
        background-color: var(--light-blue-color);
        color: var(--secondary-color);
        border-radius: var(--border-radius);
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .form-footer {
        display: flex;
        justify-content: space-between;
        margin-top: 1.5rem;
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
        .form-footer {
            flex-direction: column;
            align-items: center;
        }
        .form-footer .btn {
            margin-bottom: 1rem;
            width: 100%;
        }
    }
</style>
@endsection

@section('content')

@include('includes.errors')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <img src="{{ asset('images/edit-prof.png') }}" alt="Profile Icon"> 
                    {{ __('Edit Your Profile') }}
                </div>

                <div class="card-body">
                    <form action="{{ route('user.profile.update') }}" method="post" enctype="multipart/form-data">
                        @csrf

                        <div class="form-group">
                            <label for="name">Name / Surname</label>
                            <input type="text" class="form-control" id="name" placeholder="Name" name="name" value="{{$user->name}}">
                        </div>

                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" placeholder="Username" name="username" value="{{$user->username}}">
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" placeholder="Email" name="email" value="{{$user->email}}">
                        </div>

                        <div class="form-group">
                            <label for="dateofbirth">Date of Birth</label>
                            <input id="dateofbirth" class="flatpickr form-control" name="dateofbirth" value="{{$user->dateofbirth}}">
                        </div>

                        <input type="hidden" name="id" id="id" value="{{$user->id}}"/>

                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select name="genders_id" id="gender" class="form-control">
                                @foreach($gender as $gen)
                                    <option value="{{ $gen->id }}" @if($user->genders_id == $gen->id) selected @endif>{{ $gen->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="New Password (if empty we keep old)">
                        </div>

                        @if (Auth::user()->isAdmin())
                            <div class="form-group">
                                <label for="apikey">API Key</label>
                                <input type="text" class="form-control" id="apikey" placeholder="API Key" name="apikey" value="{{$user->apikey}}">
                            </div>

                            <div class="form-group">
                                <label for="amount">Amount of Money</label>
                                <input type="number" class="form-control" id="amount" placeholder="Amount" name="amount" value="{{$user->amount}}">
                            </div>

                            <div class="form-group">
                                <label for="activated">Activated</label>
                                <input class="form-control" id="activated" name="activated" type="checkbox" value="1" @if (!empty($user->activated)) checked @endif>
                            </div>

                            <div class="form-group">
                                <label for="role">Role</label>
                                <select name="roles_id" id="role" class="form-control">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @if($user->roles_id == $role->id) selected @endif>{{ $role->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-footer">
                            <button type="button" class="btn btn-danger" onclick="window.history.back();">
                                Discard Changes
                            </button>
                            <button type="submit" class="btn btn-success">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts2')
<script type="text/javascript">
    flatpickr('#dateofbirth', {
        enableTime: false,
        dateFormat: "Y-m-d",
    });
</script>
@endsection
