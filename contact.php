<?php

session_start();

require_once "config/db.php";

/* =========================
   FORM VARIABLES
========================= */

$name = "";
$email = "";
$phone = "";
$subject = "";
$message = "";

$success = "";
$error = "";


/* =========================
   FORM SUBMISSION
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");


    /* VALIDATION */

    if (
        $name === "" ||
        $email === "" ||
        $subject === "" ||
        $message === ""
    ) {

        $error = "Please fill in all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /* DATABASE INSERT */

        $stmt = $conn->prepare(
            "INSERT INTO contact_messages
            (name, email, phone, subject, message)
            VALUES (?, ?, ?, ?, ?)"
        );


        if ($stmt) {

            $stmt->bind_param(
                "sssss",
                $name,
                $email,
                $phone,
                $subject,
                $message
            );


            if ($stmt->execute()) {

                $success = "Thank you! Your message has been sent successfully.";

                /* CLEAR FORM */

                $name = "";
                $email = "";
                $phone = "";
                $subject = "";
                $message = "";

            } else {

                $error = "Unable to send your message. Please try again.";

            }


            $stmt->close();

        } else {

            $error = "Database error. Please check your database connection.";

        }
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact Us - CarHub</title>

    <link rel="stylesheet" href="css/style.css">


    <style>

        * {
            box-sizing: border-box;
        }


        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f6f8;
            color: #222;
        }


        /* =========================
           NAVIGATION
        ========================= */

        .main-nav {
            width: 100%;
            min-height: 72px;

            background: #111827;

            display: flex;
            align-items: center;
            justify-content: space-between;

            padding: 0 55px;
        }


        .logo {
            color: white;
            font-size: 30px;
            font-weight: 700;
        }


        .main-nav ul {
            display: flex;
            align-items: center;
            gap: 22px;

            list-style: none;

            margin: 0;
            padding: 0;
        }


        .main-nav li {
            list-style: none;
        }


        .main-nav a {
            color: white;
            text-decoration: none;

            font-size: 15px;
            font-weight: 500;

            transition: 0.3s;
        }


        .main-nav a:hover {
            color: #ef3340;
        }


        /* =========================
           HERO
        ========================= */

        .contact-hero {

            min-height: 350px;

            background:
                linear-gradient(
                    rgba(0, 0, 0, 0.68),
                    rgba(0, 0, 0, 0.68)
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
        }


        .contact-hero-content {
            max-width: 800px;
        }


        .contact-hero h1 {
            font-size: 52px;
            margin: 0 0 15px;
        }


        .contact-hero p {
            font-size: 19px;
            color: #eeeeee;
            line-height: 1.7;
            margin: 0;
        }


        /* =========================
           CONTACT SECTION
        ========================= */

        .contact-section {
            padding: 70px 20px;
        }


        .contact-container {

            max-width: 1150px;

            margin: auto;

            display: grid;

            grid-template-columns: 0.9fr 1.1fr;

            gap: 35px;
        }


        /* =========================
           CONTACT INFO
        ========================= */

        .contact-info {

            background: #111827;

            color: white;

            padding: 40px;

            border-radius: 16px;

            box-shadow:
                0 10px 35px rgba(0, 0, 0, 0.12);
        }


        .contact-info h2 {

            margin-top: 0;
            margin-bottom: 15px;

            font-size: 31px;
        }


        .contact-info > p {

            color: #cbd5e1;

            line-height: 1.7;

            margin-bottom: 30px;
        }


        .info-item {

            display: flex;

            gap: 18px;

            margin-top: 28px;
        }


        .info-icon {

            width: 45px;
            height: 45px;

            min-width: 45px;

            border-radius: 50%;

            background: #ef3340;

            display: flex;

            align-items: center;
            justify-content: center;

            font-size: 20px;
        }


        .info-content h3 {

            margin: 0 0 6px;

            font-size: 17px;
        }


        .info-content p {

            margin: 0;

            color: #cbd5e1;

            line-height: 1.6;
        }


        /* =========================
           FORM
        ========================= */

        .contact-form {

            background: white;

            padding: 40px;

            border-radius: 16px;

            box-shadow:
                0 10px 35px rgba(0, 0, 0, 0.08);
        }


        .contact-form h2 {

            margin-top: 0;

            margin-bottom: 8px;

            font-size: 31px;
        }


        .form-description {

            color: #6b7280;

            margin-bottom: 28px;

            line-height: 1.6;
        }


        /* =========================
           MESSAGES
        ========================= */

        .success-message {

            background: #dcfce7;

            color: #166534;

            border: 1px solid #bbf7d0;

            padding: 15px 18px;

            border-radius: 8px;

            margin-bottom: 22px;

            font-size: 15px;
        }


        .error-message {

            background: #fee2e2;

            color: #991b1b;

            border: 1px solid #fecaca;

            padding: 15px 18px;

            border-radius: 8px;

            margin-bottom: 22px;

            font-size: 15px;
        }


        /* =========================
           FORM ROW
        ========================= */

        .form-row {

            display: grid;

            grid-template-columns: 1fr 1fr;

            gap: 18px;
        }


        .form-group {

            margin-bottom: 20px;
        }


        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-size: 14px;

            font-weight: 600;

            color: #374151;
        }


        .form-group input,
        .form-group textarea {

            width: 100%;

            padding: 13px 14px;

            border: 1px solid #d1d5db;

            border-radius: 8px;

            outline: none;

            font-size: 15px;

            font-family: Arial, Helvetica, sans-serif;
        }


        .form-group input:focus,
        .form-group textarea:focus {

            border-color: #ef3340;

            box-shadow:
                0 0 0 3px rgba(239, 51, 64, 0.10);
        }


        .form-group textarea {

            min-height: 150px;

            resize: vertical;
        }


        /* =========================
           BUTTON
        ========================= */

        .submit-btn {

            width: 100%;

            border: none;

            padding: 15px;

            border-radius: 8px;

            background: #ef3340;

            color: white;

            font-size: 16px;

            font-weight: 700;

            cursor: pointer;

            transition: 0.3s;
        }


        .submit-btn:hover {

            background: #c92330;

            transform: translateY(-1px);
        }


        /* =========================
           QUICK CONTACT
        ========================= */

        .quick-contact {

            max-width: 1150px;

            margin: 0 auto 70px;

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }


        .quick-card {

            background: white;

            text-align: center;

            padding: 30px 20px;

            border-radius: 14px;

            box-shadow:
                0 5px 20px rgba(0, 0, 0, 0.06);
        }


        .quick-card .icon {

            font-size: 35px;

            margin-bottom: 12px;
        }


        .quick-card h3 {

            margin: 5px 0 8px;

            font-size: 19px;
        }


        .quick-card p {

            margin: 0;

            color: #6b7280;
        }


        /* =========================
           FOOTER
        ========================= */

        .footer {

            background: #111827;

            color: white;

            text-align: center;

            padding: 30px 20px;
        }


        .footer p {

            margin: 5px;

            color: #9ca3af;
        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 900px) {

            .main-nav {

                padding: 20px;

                flex-direction: column;

                gap: 18px;
            }


            .main-nav ul {

                flex-wrap: wrap;

                justify-content: center;

                gap: 12px;
            }


            .contact-container {

                grid-template-columns: 1fr;
            }


            .quick-contact {

                grid-template-columns: 1fr;
            }


            .contact-hero h1 {

                font-size: 42px;
            }
        }


        @media (max-width: 600px) {

            .contact-section {

                padding: 45px 15px;
            }


            .contact-info,
            .contact-form {

                padding: 25px;
            }


            .contact-hero {

                min-height: 300px;
            }


            .contact-hero h1 {

                font-size: 34px;
            }


            .contact-hero p {

                font-size: 16px;
            }


            .form-row {

                grid-template-columns: 1fr;

                gap: 0;
            }


            .contact-info h2,
            .contact-form h2 {

                font-size: 26px;
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

<section class="contact-hero">

    <div class="contact-hero-content">

        <h1>
            Contact CarHub
        </h1>

        <p>
            Have a question about a car, booking or test drive?
            Our team is here to help.
        </p>

    </div>

</section>


<!-- =========================
     CONTACT
========================= -->

<section class="contact-section">

    <div class="contact-container">


        <!-- CONTACT INFORMATION -->

        <div class="contact-info">

            <h2>
                Get In Touch
            </h2>

            <p>
                We would love to hear from you.
                Contact CarHub for information about
                our vehicles, bookings and test drives.
            </p>


            <div class="info-item">

                <div class="info-icon">
                    📍
                </div>

                <div class="info-content">

                    <h3>
                        Our Location
                    </h3>

                    <p>
                        CarHub Main Showroom<br>
                        Your City, India
                    </p>

                </div>

            </div>


            <div class="info-item">

                <div class="info-icon">
                    📞
                </div>

                <div class="info-content">

                    <h3>
                        Phone
                    </h3>

                    <p>
                        +91 98765 43210
                    </p>

                </div>

            </div>


            <div class="info-item">

                <div class="info-icon">
                    ✉
                </div>

                <div class="info-content">

                    <h3>
                        Email
                    </h3>

                    <p>
                        support@carhub.com
                    </p>

                </div>

            </div>


            <div class="info-item">

                <div class="info-icon">
                    🕒
                </div>

                <div class="info-content">

                    <h3>
                        Opening Hours
                    </h3>

                    <p>
                        Monday - Saturday<br>
                        9:00 AM - 7:00 PM
                    </p>

                </div>

            </div>

        </div>


        <!-- =========================
             CONTACT FORM
        ========================= -->

        <div class="contact-form">

            <h2>
                Send Us a Message
            </h2>

            <p class="form-description">
                Fill out the form below and we'll get back
                to you as soon as possible.
            </p>


            <!-- SUCCESS MESSAGE -->

            <?php if ($success !== ""): ?>

                <div class="success-message">

                    ✓
                    <?php echo htmlspecialchars($success); ?>

                </div>

            <?php endif; ?>


            <!-- ERROR MESSAGE -->

            <?php if ($error !== ""): ?>

                <div class="error-message">

                    ✕
                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <form
                action="contact.php"
                method="POST"
            >


                <!-- NAME + EMAIL -->

                <div class="form-row">

                    <div class="form-group">

                        <label for="name">
                            Your Name *
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            placeholder="Enter your name"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="email">
                            Email Address *
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo htmlspecialchars($email); ?>"
                            placeholder="Enter your email"
                            required
                        >

                    </div>

                </div>


                <!-- PHONE -->

                <div class="form-group">

                    <label for="phone">
                        Phone Number
                    </label>

                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="<?php echo htmlspecialchars($phone); ?>"
                        placeholder="Enter your phone number"
                    >

                </div>


                <!-- SUBJECT -->

                <div class="form-group">

                    <label for="subject">
                        Subject *
                    </label>

                    <input
                        type="text"
                        id="subject"
                        name="subject"
                        value="<?php echo htmlspecialchars($subject); ?>"
                        placeholder="What can we help you with?"
                        required
                    >

                </div>


                <!-- MESSAGE -->

                <div class="form-group">

                    <label for="message">
                        Message *
                    </label>

                    <textarea
                        id="message"
                        name="message"
                        placeholder="Write your message here..."
                        required
                    ><?php echo htmlspecialchars($message); ?></textarea>

                </div>


                <!-- SUBMIT -->

                <button
                    type="submit"
                    class="submit-btn"
                >
                    Send Message
                </button>

            </form>

        </div>

    </div>

</section>


<!-- =========================
     QUICK CONTACT
========================= -->

<div class="quick-contact">


    <div class="quick-card">

        <div class="icon">
            🚗
        </div>

        <h3>
            Car Enquiries
        </h3>

        <p>
            Ask us about available vehicles.
        </p>

    </div>


    <div class="quick-card">

        <div class="icon">
            🏁
        </div>

        <h3>
            Test Drives
        </h3>

        <p>
            Schedule a test drive for your favourite car.
        </p>

    </div>


    <div class="quick-card">

        <div class="icon">
            📋
        </div>

        <h3>
            Booking Help
        </h3>

        <p>
            Need help with your car booking?
        </p>

    </div>


</div>


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