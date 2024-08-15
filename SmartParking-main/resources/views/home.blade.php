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
        --light-blue-color: #4596e9; /* Light Blue */
    }

    body {
        background-color: var(--background-color);
        color: var(--secondary-color);
        font-family: 'Raleway', sans-serif;
    }

    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
    }

    /* Centering container for the link */
    .center-container {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        margin-top: 0px;
        animation: fadeIn 1.5s ease-in-out;
        padding: 20px;
    }

    /* Styling the link */
    .center-link {
        display: inline-block;
        font-size: 1.5rem;
        font-weight: bold;
        text-transform: uppercase;
        padding: 1rem 2rem;
        color: #fff;
        background-color: var(--primary-color);
        border-radius: var(--border-radius);
        text-decoration: none;
        box-shadow: var(--box-shadow);
        transition: background-color var(--transition-speed), transform var(--transition-speed);
        margin-top: 1rem;
    }

    .center-link:hover {
        background-color: var(--link-hover-color);
        transform: scale(1.05);
    }

    /* Enhanced Welcome message styling */
    .welcome-message {
        animation: fadeIn 2s ease-in-out;
        margin-top: 2rem;
        margin-bottom: 2.5rem;
        text-align: center;
        color: var(--secondary-color);
        font-weight: bold;
        padding: 0 20px;
    }

    .welcome-message h4 {
        color: var(--light-blue-color);
        font-weight: 600;
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .welcome-message p {
        font-size: 1.2rem;
        color: var(--secondary-color);
    }

    /* Icon styling */
    .parking-icon {
        width: 25%;
        height: auto;
        animation: bounce 2s infinite;
        transition: transform var(--transition-speed);
    }

    .parking-icon:hover {
        transform: scale(1.05);
    }

    /* Information section */
    .info-section {
        text-align: center;
        padding: 2rem 0;
    }

    .info-section .info-card {
        background-color: #fff;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 1.5rem;
        margin: 0.5rem;
        transition: transform var(--transition-speed);
    }

    .info-section .info-card:hover {
        transform: translateY(-5px);
    }

    .info-section .info-card h5 {
        color: var(--primary-color);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .info-section .info-card img {
        width: 24px;
        height: 24px;
        margin-right: 8px;
    }

    .info-section .info-card p {
        color: var(--secondary-color);
    }

    /* Responsive design for mobile devices */
    @media (max-width: 768px) {
        .center-link {
            font-size: 1.5rem;
            padding: 0.8rem 1.6rem;
        }

        .welcome-message h4 {
            font-size: 1.8rem;
        }

        .welcome-message p {
            font-size: 1rem;
        }

        .parking-icon {
            width: 50%;
        }

        .info-section {
            padding: 1rem 0;
        }

        .info-section .info-card {
            margin: 0.5rem auto;
            max-width: 90%;
        }
    }

    @media (max-width: 480px) {
        .center-link {
            font-size: 1.2rem;
            padding: 0.6rem 1.2rem;
        }

        .welcome-message h4 {
            font-size: 1.6rem;
        }

        .welcome-message p {
            font-size: 1rem;
        }

        .parking-icon {
            width: 40%;
        }

        .info-section {
            padding: 0.5rem 0;
        }

        .info-section .info-card {
            margin: 0.3rem auto;
            max-width: 95%;
        }

        .info-section .info-card h5 {
            font-size: 1.2rem;
        }

        .info-section .info-card img {
            width: 20px;
            height: 20px;
            margin-right: 6px;
        }
    }
</style>
@endsection

@section('content')
@include('includes.errors')

<!-- Welcome message with no container -->
<div class="welcome-message">
    <h4>Welcome Back, {{ Auth::user()->name }}!</h4>
    <p>You are successfully logged in. Explore our features and manage your parking with ease.</p>
</div>

<div class="center-container">
    <!-- Wrap the parking icon in an anchor tag -->
    <a href="{{ route('map.searchmap') }}">
        <img class="parking-icon" src="{{ asset('images/parking-icon.png') }}" alt="Parking Icon">
    </a>
    <a class="center-link" href="{{ route('map.searchmap') }}">
        Browse Parking
    </a>
</div>

<div class="info-section container">
    <div class="row">
        <div class="col-md-4">
            <div class="info-card">
                <h5>
                    <img src="{{ asset('images/quick-access.png') }}" alt="Quick Access Icon">
                    Quick Access
                </h5>
                <p>Instantly find the best parking spots near you with our advanced search tools.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <h5>
                    <img src="{{ asset('images/favorite.png') }}" alt="Favorites Icon">
                    Save Favorites
                </h5>
                <p>Bookmark your favorite parking locations for quick access on your next visit.</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="info-card">
                <h5>
                    <img src="{{ asset('images/manage-spots.png') }}" alt="Manage Spots Icon">
                    Manage Your Spots
                </h5>
                <p>Easily manage your reserved spots and see your parking history at a glance.</p>
            </div>
        </div>
    </div>
</div>
@endsection
