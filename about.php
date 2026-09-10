<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Us - CarHub</title>

    <link rel="stylesheet" href="css/style.css">

    <style>

        /* =========================
           NAVIGATION
        ========================= */

        .main-nav {
            width: 100%;
            min-height: 70px;
            background: #222;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 50px;
            box-sizing: border-box;
        }

        .logo {
            color: white;
            font-size: 30px;
            font-weight: bold;
        }

        .main-nav ul {
            display: flex;
            align-items: center;
            gap: 25px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .main-nav ul li {
            list-style: none;
        }

        .main-nav ul li a {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        .main-nav ul li a:hover {
            color: #e63946;
        }


        /* =========================
           HERO
        ========================= */

        .about-hero {
            min-height: 430px;

            background:
                linear-gradient(
                    rgba(0,0,0,0.65),
                    rgba(0,0,0,0.65)
                ),
                url("images/about-car.jpg");

            background-size: cover;
            background-position: center;

            display: flex;
            align-items: center;
            justify-content: center;

            text-align: center;

            color: white;

            padding: 40px 20px;

            box-sizing: border-box;
        }

        .hero-content {
            max-width: 800px;
        }

        .hero-content h1 {
            font-size: 52px;
            margin-bottom: 15px;
        }

        .hero-content p {
            font-size: 19px;
            line-height: 1.7;
            color: #eee;
        }


        /* =========================
           ABOUT SECTION
        ========================= */

        .about-section {
            padding: 70px 20px;
            background: #f7f7f7;
        }

        .about-container {
            max-width: 1100px;
            margin: auto;
        }

        .section-title {
            text-align: center;
            margin-bottom: 45px;
        }

        .section-title h2 {
            font-size: 36px;
            color: #222;
            margin-bottom: 10px;
        }

        .section-title p {
            color: #777;
            font-size: 16px;
        }


        /* =========================
           ABOUT CONTENT
        ========================= */

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 45px;
            align-items: center;
        }

        .about-text h2 {
            font-size: 32px;
            color: #222;
            margin-bottom: 18px;
        }

        .about-text p {
            color: #666;
            line-height: 1.8;
            font-size: 16px;
            margin-bottom: 15px;
        }

        .about-image img {
            width: 100%;
            height: 350px;
            object-fit: cover;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }


        /* =========================
           FEATURES
        ========================= */

        .features-section {
            padding: 70px 20px;
            background: white;
        }

        .features-grid {
            max-width: 1100px;
            margin: auto;

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 25px;
        }

        .feature-card {
            background: #f7f7f7;
            padding: 35px 25px;
            border-radius: 15px;
            text-align: center;

            transition: 0.3s;

            border: 1px solid #eee;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow:
                0 8px 25px rgba(0,0,0,0.10);
        }

        .feature-icon {
            font-size: 45px;
            margin-bottom: 15px;
        }

        .feature-card h3 {
            font-size: 21px;
            color: #222;
            margin-bottom: 10px;
        }

        .feature-card p {
            color: #777;
            line-height: 1.6;
        }


        /* =========================
           STATS
        ========================= */

        .stats-section {
            background: #222;
            color: white;
            padding: 60px 20px;
        }

        .stats-grid {
            max-width: 1000px;
            margin: auto;

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 25px;

            text-align: center;
        }

        .stat h2 {
            color: #e63946;
            font-size: 40px;
            margin-bottom: 5px;
        }

        .stat p {
            color: #ccc;
            margin: 0;
        }


        /* =========================
           CTA
        ========================= */

        .cta-section {
            padding: 70px 20px;
            text-align: center;
            background: #f5f5f5;
        }

        .cta-section h2 {
            font-size: 34px;
            color: #222;
            margin-bottom: 12px;
        }

        .cta-section p {
            color: #777;
            margin-bottom: 25px;
        }

        .cta-button {
            display: inline-block;

            background: #e63946;

            color: white;

            text-decoration: none;

            padding: 14px 30px;

            border-radius: 8px;

            font-weight: bold;

            transition: 0.3s;
        }

        .cta-button:hover {
            background: #c92f3c;
        }


        /* =========================
           FOOTER
        ========================= */

        .footer {
            background: #181818;
            color: white;
            text-align: center;
            padding: 25px 20px;
        }

        .footer p {
            margin: 5px;
            color: #aaa;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 850px) {

            .main-nav {
                padding: 20px;
                flex-direction: column;
                gap: 15px;
            }

            .main-nav ul {
                flex-wrap: wrap;
                justify-content: center;
                gap: 12px;
            }

            .hero-content h1 {
                font-size: 40px;
            }

            .about-grid {
                grid-template-columns: 1fr;
            }

            .features-grid {
                grid-template-columns: 1fr 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
        }


        @media (max-width: 550px) {

            .about-hero {
                min-height: 380px;
            }

            .hero-content h1 {
                font-size: 32px;
            }

            .hero-content p {
                font-size: 16px;
            }

            .about-section,
            .features-section,
            .cta-section {
                padding: 50px 15px;
            }

            .features-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }

            .stat h2 {
                font-size: 30px;
            }
        }

    </style>

</head>


<body>


<!-- =========================
     NAVIGATION
========================= -->

<nav class="main-nav">

    <div class="logo">
        CarHub
    </div>

    <ul>

        <li>
            <a href="index.php">
                Home
            </a>
        </li>

        <li>
            <a href="cars.php">
                Cars
            </a>
        </li>

        <li>
            <a href="about.php">
                About
            </a>
        </li>

        <li>
            <a href="contact.php">
                Contact
            </a>
        </li>

        <?php if (isset($_SESSION["user_id"])): ?>

            <li>
                <a href="my-bookings.php">
                    My Bookings
                </a>
            </li>

            <li>
                <a href="my-test-drives.php">
                    My Test Drives
                </a>
            </li>

            <li>
                <a href="logout.php">
                    Logout
                </a>
            </li>

        <?php else: ?>

            <li>
                <a href="login.php">
                    Login
                </a>
            </li>

            <li>
                <a href="register.php">
                    Register
                </a>
            </li>

        <?php endif; ?>

    </ul>

</nav>



<!-- =========================
     HERO
========================= -->

<section class="about-hero">

    <div class="hero-content">

        <h1>
            About CarHub
        </h1>

        <p>
            Your trusted destination for discovering,
            exploring and booking your dream car.
        </p>

    </div>

</section>



<!-- =========================
     ABOUT CARHUB
========================= -->

<section class="about-section">

    <div class="about-container">

        <div class="section-title">

            <h2>
                Welcome to CarHub
            </h2>

            <p>
                Making your car buying experience simple and convenient.
            </p>

        </div>


        <div class="about-grid">


            <div class="about-text">

                <h2>
                    Your Journey Starts Here
                </h2>

                <p>
                    CarHub is a modern online car selling platform
                    designed to make finding your next vehicle easier.
                    Browse different cars, explore detailed specifications,
                    compare your options and choose the vehicle that
                    suits your needs.
                </p>

                <p>
                    We believe buying a car should be a simple,
                    transparent and enjoyable experience.
                    That's why CarHub brings important vehicle
                    information together in one convenient platform.
                </p>

                <p>
                    Customers can also request a test drive and
                    submit booking requests directly through the website.
                </p>

            </div>


            <div class="about-image">

    <img
        src="images/about-car.jpg"
        alt="CarHub Car"
        onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=900&q=80';"
    >

</div>


        </div>

    </div>

</section>



<!-- =========================
     FEATURES
========================= -->

<section class="features-section">

    <div class="section-title">

        <h2>
            Why Choose CarHub?
        </h2>

        <p>
            Everything you need to find your next car.
        </p>

    </div>


    <div class="features-grid">


        <div class="feature-card">

            <div class="feature-icon">
                🚗
            </div>

            <h3>
                Wide Car Selection
            </h3>

            <p>
                Explore a variety of cars from
                different brands and categories.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                🔍
            </div>

            <h3>
                Easy Car Search
            </h3>

            <p>
                Find vehicles quickly using
                car details and available options.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                📋
            </div>

            <h3>
                Easy Booking
            </h3>

            <p>
                Submit your car booking request
                through our simple online system.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                🏁
            </div>

            <h3>
                Test Drives
            </h3>

            <p>
                Request a test drive and experience
                your selected vehicle before buying.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                💻
            </div>

            <h3>
                Online Platform
            </h3>

            <p>
                Access vehicle information and
                services conveniently online.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                🤝
            </div>

            <h3>
                Customer Focused
            </h3>

            <p>
                We aim to provide a smooth and
                convenient experience for every customer.
            </p>

        </div>


    </div>

</section>



<!-- =========================
     STATS
========================= -->

<section class="stats-section">

    <div class="stats-grid">


        <div class="stat">

            <h2>
                100+
            </h2>

            <p>
                Cars Available
            </p>

        </div>


        <div class="stat">

            <h2>
                50+
            </h2>

            <p>
                Car Models
            </p>

        </div>


        <div class="stat">

            <h2>
                500+
            </h2>

            <p>
                Customers
            </p>

        </div>


        <div class="stat">

            <h2>
                24/7
            </h2>

            <p>
                Online Access
            </p>

        </div>


    </div>

</section>



<!-- =========================
     CALL TO ACTION
========================= -->

<section class="cta-section">

    <h2>
        Ready to Find Your Dream Car?
    </h2>

    <p>
        Explore our available cars and find the one that's right for you.
    </p>

    <a
        href="cars.php"
        class="cta-button"
    >
        Explore Cars
    </a>

</section>



<!-- =========================
     FOOTER
========================= -->

<footer class="footer">

    <p>
        © <?php echo date("Y"); ?> CarHub.
        All Rights Reserved.
    </p>

    <p>
        Your trusted car selling platform.
    </p>

</footer>


</body>

</html>