<?php

$test_drive_id = $_GET['test_drive_id'] ?? '';
$car_name = $_GET['car_name'] ?? 'Your selected car';
$drive_date = $_GET['date'] ?? '';
$drive_time = $_GET['time'] ?? '';

if ($test_drive_id === '') {
    $test_drive_id = 'Pending';
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Test Drive Confirmed | CarHub</title>


<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f5f7;
    color: #172033;
}


/* NAVBAR */

.navbar {
    height: 72px;
    background: #111;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 8%;
}

.logo {
    color: white;
    font-size: 30px;
    font-weight: 800;
}

.logo span {
    color: #ef3340;
}

.nav-links {
    display: flex;
    gap: 28px;
}

.nav-links a {
    color: white;
    text-decoration: none;
    font-size: 16px;
}

.nav-links a:hover {
    color: #ef3340;
}


/* MAIN */

.container {
    width: 90%;
    max-width: 850px;
    margin: 70px auto;
}


/* SUCCESS CARD */

.success-card {
    background: white;
    border-radius: 20px;
    padding: 55px;
    text-align: center;
    box-shadow: 0 12px 35px rgba(0,0,0,0.08);
}


/* CHECK */

.check-circle {
    width: 95px;
    height: 95px;
    margin: 0 auto 25px;

    border-radius: 50%;

    background: #e9f8ee;
    color: #22a447;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 50px;
    font-weight: bold;
}


/* TITLE */

h1 {
    font-size: 38px;
    margin-bottom: 15px;
}

.subtitle {
    color: #667085;
    font-size: 18px;
    line-height: 1.6;
    margin-bottom: 35px;
}


/* DETAILS */

.details-box {
    background: #f7f8fa;
    border-radius: 14px;
    padding: 25px;
    text-align: left;
    margin-bottom: 30px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;

    padding: 15px 0;

    border-bottom: 1px solid #ddd;
}

.detail-row:last-child {
    border-bottom: none;
}

.label {
    color: #667085;
}

.value {
    font-weight: bold;
    text-align: right;
}


/* STATUS */

.status {
    display: inline-block;

    padding: 7px 16px;

    background: #fff2c7;
    color: #a15c00;

    border-radius: 20px;

    font-size: 14px;
}


/* BUTTONS */

.buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.btn {
    display: inline-block;

    padding: 15px 28px;

    border-radius: 9px;

    text-decoration: none;

    font-weight: bold;

    font-size: 16px;
}

.primary {
    background: #ef3340;
    color: white;
}

.primary:hover {
    background: #d92835;
}

.secondary {
    background: #111827;
    color: white;
}

.secondary:hover {
    background: #222b3d;
}


/* NOTE */

.note {
    margin-top: 30px;

    color: #667085;

    font-size: 14px;

    line-height: 1.6;
}


/* MOBILE */

@media(max-width: 700px) {

    .navbar {
        padding: 0 20px;
    }

    .nav-links {
        display: none;
    }

    .container {
        margin: 40px auto;
    }

    .success-card {
        padding: 35px 20px;
    }

    h1 {
        font-size: 30px;
    }

    .detail-row {
        flex-direction: column;
        gap: 5px;
    }

    .value {
        text-align: left;
    }

}

</style>

</head>


<body>


<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">

        CAR<span>HUB</span>

    </div>


    <div class="nav-links">

        <a href="index.php">
            Home
        </a>

        <a href="cars.php">
            Cars
        </a>

        <a href="about.php">
            About
        </a>

        <a href="contact.php">
            Contact
        </a>

        <a href="my-bookings.php">
            My Bookings
        </a>

    </div>

</nav>


<!-- MAIN -->

<div class="container">


    <div class="success-card">


        <!-- SUCCESS ICON -->

        <div class="check-circle">
            ✓
        </div>


        <h1>
            Test Drive Request Submitted!
        </h1>


        <p class="subtitle">

            Thank you for choosing CarHub.
            Your test drive request has been successfully submitted.

        </p>


        <!-- DETAILS -->

        <div class="details-box">


            <div class="detail-row">

                <span class="label">
                    Test Drive ID
                </span>

                <span class="value">

                    #<?php
                    echo htmlspecialchars($test_drive_id);
                    ?>

                </span>

            </div>


            <div class="detail-row">

                <span class="label">
                    Car
                </span>

                <span class="value">

                    <?php
                    echo htmlspecialchars($car_name);
                    ?>

                </span>

            </div>


            <?php if ($drive_date !== ''): ?>

            <div class="detail-row">

                <span class="label">
                    Test Drive Date
                </span>

                <span class="value">

                    <?php
                    echo htmlspecialchars($drive_date);
                    ?>

                </span>

            </div>

            <?php endif; ?>


            <?php if ($drive_time !== ''): ?>

            <div class="detail-row">

                <span class="label">
                    Preferred Time
                </span>

                <span class="value">

                    <?php
                    echo htmlspecialchars($drive_time);
                    ?>

                </span>

            </div>

            <?php endif; ?>


            <div class="detail-row">

                <span class="label">
                    Status
                </span>

                <span class="value">

                    <span class="status">
                        Pending
                    </span>

                </span>

            </div>


        </div>


        <!-- BUTTONS -->

        <div class="buttons">


            <a
                href="cars.php"
                class="btn primary"
            >
                Browse Cars
            </a>


            <a
                href="index.php"
                class="btn secondary"
            >
                Back to Home
            </a>


        </div>


        <p class="note">

            Our team will review your test drive request.
            Please keep your Test Drive ID for reference.

        </p>


    </div>

</div>

</body>

</html>