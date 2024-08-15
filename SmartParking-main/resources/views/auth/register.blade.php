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
        border-bottom: 2px solid var(--highlight-color);
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

    .btn-primary {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        transition: background-color var(--transition-speed), border-color var(--transition-speed);
    }

    .btn-primary:hover {
        background-color: var( --light-blue-color);
        border-color: var(--light-blue-color);
    }

    .alert-success {
        background-color: var(--light-blue-color);
        color: var(--secondary-color);
        border-radius: var(--border-radius);
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .form-footer {
        text-align: center;
        margin-top: 1.5rem;
    }

    .form-footer p {
        margin: 0;
    }

    .form-footer a {
        color: var(--primary-color);
        font-weight: bold;
        text-decoration: none;
        transition: color var(--transition-speed);
    }

    .form-footer a:hover {
        color: var(--light-blue-color);
    }

    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
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
                <div class="card-header">{{ __('Register') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('register') }}" aria-label="{{ __('Register') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="username" class="col-md-4 col-form-label text-md-right">{{ __('Username') }}</label>

                            <div class="col-md-6">
                                <input id="username" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" required autofocus>

                                @if ($errors->has('username'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>          

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Name and Surname') }}</label>

                            <div class="col-md-6">
                                <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <input type="hidden" name="roles_id" value="2" />

                        <div class="form-group row">
                            <label for="gender" class="col-md-4 col-form-label text-md-right">{{ __('Gender') }}</label>
                            <div class="col-md-6">
                                <select name="genders_id" id="gender" class="form-control">
                                    @foreach($genders as $gender)
                                        <option value="{{ $gender->id }}">{{ $gender->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>  

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="col-md-6">
                                <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>

                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                            <div class="col-md-6">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                            <div class="col-md-6">
                                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="dateofbirth" class="col-md-4 col-form-label text-md-right">{{ __('Birthday') }}</label>
                            <div class="col-md-6">
                                <input id="date" class="flatpickr form-control" data-enabletime="true" name="dateofbirth" required>
                            </div>    
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Register') }}
                                </button>
                            </div>
                        </div>

                        <div class="form-footer">
                            <p>Already have an account? <a href="{{ route('login') }}">Login here</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts2')

<script>
    $(document).ready(function(){
      flatpickr('#date',{
            enableTime: true,
            dateFormat: "Y-m-d H:i",
      });
    })
</script>
@endsection
