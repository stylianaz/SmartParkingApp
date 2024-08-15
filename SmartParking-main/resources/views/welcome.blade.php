<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SmartParking</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <link rel="stylesheet" href="{{ asset('css/welcome.css') }}">
</head>
<body>
    <div class="sidebar">
        <a href="#home" id="link-home">Smart Parking</a>
        <a href="#learn-more" id="link-learn-more">How It Works</a>
        <a href="#contact" id="link-contact">Contact Us</a>
    </div>

    <div class="flex-center position-ref full-height">
        @if (Route::has('login'))
        <div class="top-right links">
            @auth
            <b><a href="{{ url('/home') }}" id="home-link">Home</a></b>
            @else
            <b><a href="{{ route('login') }}" id="home-link">Login</a></b>
            @endauth
        </div>
        @endif

        <div class="content">
            <div class="title m-b-md" id="home">
                Smart Parking
            </div>

            <div class="intro">
                <h2>Your Smart Solution to Hassle-Free Parking</h2>
                <p>Discover the most efficient way to park in your city. Real-time updates, secure reservations, and more.</p>
            </div>

            <img src="{{ asset('images/SParking.png') }}" alt="Images of SParking">

            <div class="cta">
                <a href="{{ route('register') }}" class="btn">Get Started</a>
            </div>

            <div class="features">
                <div class="feature-item">
                    <img src="{{ asset('images/booking-icon.png') }}" alt="Easy Booking">
                    <h3>Easy Booking</h3>
                    <p>Reserve your parking spot in just a few clicks.</p>
                </div>
                <div class="feature-item">
                    <img src="{{ asset('images/payment-icon.png') }}" alt="Secure Payment">
                    <h3>Secure Payment</h3>
                    <p>Fast and secure payment methods.</p>
                </div>
                <div class="feature-item">
                    <img src="{{ asset('images/time-icon.png') }}" alt="Real-time Updates">
                    <h3>Real-time Updates</h3>
                    <p>Stay informed with live parking availability.</p>
                </div>
            </div>

            <div class="how-it-works" id="learn-more">
                <h3>How It Works</h3>
                <ol>
                    <li>Select your location and find available spots.</li>
                    <li>Reserve your spot and pay securely.</li>
                    <li>Park your car and enjoy your day!</li>
                </ol>
            </div>

            <div class="contact-info" id="contact">
                <h3>Contact Us</h3>
                <p>Have questions? <a href="mailto:support@smartparking.com">Email our support team. </a></p>
            </div>

            <footer>
                <p>&copy; 2024 Smart Parking. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a>
                </div>
                <div class="social-media">
                    <a href="#">
                        <span>Email</span>
                        <img src="{{ asset('images/email-icon.png') }}" alt="Email">
                    </a>
                </div>
            </footer>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarLinks = document.querySelectorAll('.sidebar a');
        const sections = document.querySelectorAll('.content > div');
        const contactLink = document.getElementById('link-contact');

        function changeActiveLink() {
            let index = sections.length;

            // Determine if we're near the bottom of the page
            const offset = window.scrollY + window.innerHeight;
            const threshold = 100; // How close to the bottom before highlighting "Contact Us"

            // Check if the user is at the bottom or near the bottom of the page
            if (offset >= document.body.scrollHeight - threshold) {
                // Highlight the "Contact Us" link
                sidebarLinks.forEach((link) => link.classList.remove('active'));
                contactLink.classList.add('active');
            } else {
                // Find the active section normally
                while (--index && window.scrollY + 50 < sections[index].offsetTop) {}

                // Ensure index is within bounds
                if (index >= 0 && index < sidebarLinks.length) {
                    sidebarLinks.forEach((link) => link.classList.remove('active'));
                    sidebarLinks[index].classList.add('active');
                } else {
                    sidebarLinks.forEach((link) => link.classList.remove('active'));
                }
            }
        }

        changeActiveLink();
        window.addEventListener('scroll', changeActiveLink);

        sidebarLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                document.querySelector(link.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });
    });
    </script>
</body>
</html>
