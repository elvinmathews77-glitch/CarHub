<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarHub - Find Your Dream Car</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f6f8;
            color: #111827;
        }

        a {
            text-decoration: none;
        }

        /* NAVBAR */
        .navbar {
            width: 100%;
            height: 78px;
            background: #111111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 8%;
            position: relative;
            z-index: 10;
        }

        .logo {
            font-size: 30px;
            font-weight: 800;
            color: white;
            letter-spacing: -1px;
        }

        .logo span {
            color: #ef3340;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 35px;
        }

        .nav-links a {
            color: white;
            font-size: 16px;
            font-weight: 600;
            transition: 0.3s;
        }

        .nav-links a:hover {
            color: #ef3340;
        }

        .logout {
            color: #ef3340 !important;
        }

        /* HERO */
        .hero {
            min-height: 600px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            overflow: hidden;
            background:
                linear-gradient(
                    rgba(0,0,0,0.62),
                    rgba(0,0,0,0.62)
                ),
                url("images/hero-car.jpg") center center / cover no-repeat;
        }

        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 850px;
            padding: 40px 20px;
            color: white;
        }

        .hero-small {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .hero h1 {
            font-size: 64px;
            line-height: 1.1;
            margin-bottom: 25px;
            font-weight: 800;
        }

        .hero h1 span {
            color: #ef3340;
        }

        .hero p {
            font-size: 20px;
            line-height: 1.7;
            margin-bottom: 35px;
            color: #f3f4f6;
        }

        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 15px 32px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-primary {
            background: #ef3340;
            color: white;
        }

        .btn-primary:hover {
            background: #d92532;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: white;
            color: #111827;
        }

        .btn-secondary:hover {
            background: #eeeeee;
            transform: translateY(-2px);
        }

        /* FEATURES */
        .features {
            padding: 80px 8%;
            background: white;
        }

        .section-title {
            text-align: center;
            margin-bottom: 55px;
        }

        .section-title h2 {
            font-size: 42px;
            margin-bottom: 12px;
        }

        .section-title h2 span {
            color: #ef3340;
        }

        .section-title p {
            color: #64748b;
            font-size: 18px;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            max-width: 1200px;
            margin: auto;
        }

        .feature-card {
            text-align: center;
            padding: 40px 25px;
            border-radius: 15px;
            background: #f8fafc;
            transition: 0.3s;
            border: 1px solid #e5e7eb;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        }

        .feature-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #fee2e2;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
        }

        .feature-card h3 {
            font-size: 23px;
            margin-bottom: 12px;
        }

        .feature-card p {
            color: #64748b;
            line-height: 1.6;
            font-size: 16px;
        }

        /* CALL TO ACTION */
        .cta {
            padding: 90px 20px;
            background: #111827;
            text-align: center;
            color: white;
        }

        .cta h2 {
            font-size: 42px;
            margin-bottom: 18px;
        }

        .cta p {
            color: #cbd5e1;
            font-size: 18px;
            margin-bottom: 30px;
        }

        /* FOOTER */
        footer {
            background: #0b0b0b;
            color: #94a3b8;
            text-align: center;
            padding: 25px;
            font-size: 14px;
        }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .navbar {
                padding: 0 5%;
            }

            .nav-links {
                gap: 18px;
            }

            .hero h1 {
                font-size: 48px;
            }

            .feature-grid {
                grid-template-columns: 1fr;
                max-width: 500px;
            }
        }

        @media (max-width: 650px) {
            .navbar {
                height: auto;
                padding: 20px;
                flex-direction: column;
                gap: 20px;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 15px;
            }

            .hero {
                min-height: 520px;
            }

            .hero h1 {
                font-size: 38px;
            }

            .hero p {
                font-size: 17px;
            }

            .section-title h2,
            .cta h2 {
                font-size: 32px;
            }
        }
    </style>
</head>

<body>

<!-- NAVBAR -->
<header class="navbar">

    <a href="index.php" class="logo">
        CAR<span>HUB</span>
    </a>

    <nav class="nav-links">

        <a href="index.php">Home</a>

        <a href="cars.php">Cars</a>

        <a href="about.php">About</a>

        <a href="contact.php">Contact</a>

        <?php if (isset($_SESSION['user_id'])): ?>

            <a href="my-bookings.php">My Bookings</a>

            <a href="logout.php" class="logout">Logout</a>

        <?php else: ?>

            <a href="login.php">Login</a>

            <a href="register.php">Register</a>

        <?php endif; ?>

    </nav>

</header>


<!-- HERO -->
<section class="hero">

    <div class="hero-content">

        <div class="hero-small">
            Welcome to CarHub
        </div>

        <h1>
            Find Your <span>Dream Car</span>
        </h1>

        <p>
            Discover amazing cars from trusted brands.
            Compare prices, explore features and find
            the perfect car for you.
        </p>

        <div class="hero-buttons">

            <a href="cars.php" class="btn btn-primary">
                Explore Cars
            </a>

            <a href="about.php" class="btn btn-secondary">
                Learn More
            </a>

        </div>

    </div>

</section>


<!-- FEATURES -->
<section class="features">

    <div class="section-title">

        <h2>
            Why Choose <span>CarHub?</span>
        </h2>

        <p>
            Everything you need to make your car buying experience easier.
        </p>

    </div>


    <div class="feature-grid">

        <div class="feature-card">

            <div class="feature-icon">
                🚗
            </div>

            <h3>
                Wide Selection
            </h3>

            <p>
                Explore cars from different brands,
                categories and price ranges.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                🔍
            </div>

            <h3>
                Detailed Information
            </h3>

            <p>
                Check specifications, features,
                pricing and other important vehicle details.
            </p>

        </div>


        <div class="feature-card">

            <div class="feature-icon">
                📅
            </div>

            <h3>
                Easy Booking
            </h3>

            <p>
                Logged-in customers can easily request
                vehicle bookings and test drives.
            </p>

        </div>

    </div>

</section>


<!-- CTA -->
<section class="cta">

    <?php if (isset($_SESSION['user_id'])): ?>

        <h2>
            Ready to Find Your Car?
        </h2>

        <p>
            Browse our available vehicles and choose your next car.
        </p>

        <a href="cars.php" class="btn btn-primary">
            Browse Cars
        </a>

    <?php else: ?>

        <h2>
            Start Your Car Search Today
        </h2>

        <p>
            Create an account to book vehicles and request test drives.
        </p>

        <a href="register.php" class="btn btn-primary">
            Create Account
        </a>

    <?php endif; ?>

</section>


<!-- FOOTER -->
<footer>

    <p>
        © <?php echo date("Y"); ?> CarHub. All Rights Reserved.
    </p>

</footer>

</body>
</html>