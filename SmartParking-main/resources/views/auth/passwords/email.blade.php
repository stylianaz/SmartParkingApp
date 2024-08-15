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

    /* General Card Styles */
    .card {
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        background-color: #fff;
        margin: 0 auto; /* Center the card horizontally */
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
        padding: 2.5rem;
        background-color: #fff;
    }

    /* Form Control Styles */
    .form-control {
        border-radius: var(--border-radius);
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
        border: 1px solid #ccc;
    }

    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Form Group Styles */
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

    /* Button Styles */
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

    /* Alert Styles */
    .alert-success {
        background-color: var(--light-blue-color);
        color: var(--secondary-color);
        border-radius: var(--border-radius);
        padding: 1rem;
        margin-bottom: 1rem;
    }

    /* Responsive Padding */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }
    }

    /* Additional Text Styling */
    .info-text {
        margin-bottom: 1.5rem;
        font-size: 1rem;
        color: var(--secondary-color);
    }
</style>
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Reset Password') }}</div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <!-- Additional Information Text -->
                    <p class="info-text">
                         Provide the email connected to your account.
                    </p>

                    <form method="POST" action="{{ route('password.email') }}" aria-label="{{ __('Reset Password') }}">
                        @csrf

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

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Send Password Reset Link') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
