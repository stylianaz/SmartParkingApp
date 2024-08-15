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
    }

    .card-header {
        background-color: var(--primary-color);
        color: #fff;
        font-weight: bold;
        text-align: center;
        padding: 1rem;
        border-bottom: 2px solid var(--light-blue-color);
    }

    .card-body {
        background-color: #fff;
        padding: 2.5rem;
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

    .form-group .help-block {
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
        background-color: var(--light-blue-color);
        border-color: var(--light-blue-color);
    }

    .btn-link {
        color: var(--primary-color);
        font-weight: bold;
        text-decoration: none;
        transition: color var(--transition-speed);
    }

    .btn-link:hover {
        color: var(--light-blue-color);
    }

    .checkbox label {
        font-size: 0.875rem;
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
                <div class="card-header">{{ __('Login') }}</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}">
                        {{ csrf_field() }}
                        @if(session()->has('login_error'))
                            <div class="alert alert-success">
                                {{ session()->get('login_error') }}
                            </div>
                        @endif

                        <div class="form-group{{ $errors->has('identity') ? ' has-error' : '' }}">
                            <label for="identity">Email or Username</label>
                            <input id="identity" type="text" class="form-control" name="identity" value="{{ old('identity') }}" autofocus>
                            @if ($errors->has('identity'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('identity') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                            <label for="password">Password</label>
                            <input id="password" type="password" class="form-control" name="password">
                            @if ($errors->has('password'))
                                <span class="help-block">
                                    <strong>{{ $errors->first('password') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                Login
                            </button>
                            <a class="btn btn-link" href="{{ route('password.request') }}">
                                Forgot Your Password?
                            </a>
                        </div>

                        <div class="form-footer">
                            <p>Don't have an account? <a href="{{ route('register') }}">Register here</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts2')
@endsection
