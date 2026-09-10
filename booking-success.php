<?php
session_start();

$booking_id = $_GET['booking_id'] ?? '';
$car_name = $_GET['car_name'] ?? 'Your selected car';
$booking_date = $_GET['date'] ?? '';

if ($booking_id === '') {
    $booking_id = 'Pending';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed | CarHub</title>

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
            color: #172033;
        }

        .navbar {
            height: 72px;
            background: #111111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 8%;
        }

        .logo {
            color: white;
            font-size: 28px;
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
            font-size: 15px;
        }

        .nav-links a:hover {
            color: #ef3340;
        }

        .container {
            max-width: 850px;
            margin: 70px auto;
            padding: 20px;
        }

        .success-card {
            background: white;
            border-radius: 20px;
            padding: 55px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.08);
        }

        .check-circle {
            width: 90px;
            height: 90px;
            margin: 0 auto 25px;
            border-radius: 50%;
            background: #e9f8ee;
            color: #22a447;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
        }

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

        .booking-box {
            background: #f7f8fa;
            border-radius: 14px;
            padding: 25px;
            text-align: left;
            margin-bottom: 30px;
        }

        .booking-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
        }

        .booking-row:last-child {
            border-bottom: none;
        }

        .label {
            color: #667085;
        }

        .value {
            font-weight: 700;
            text-align: right;
        }

        .status {
            display: inline-block;
            padding: 7px 16px;
            background: #fff2c7;
            color: #a15c00;
            border-radius: 20px;
            font-size: 14px;
        }

        .buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 15px 28px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }

        .btn-primary {
            background: #ef3340;
            color: white;
        }

        .btn-primary:hover {
            background: #d92734;
        }

        .btn-secondary {
            background: #111827;
            color: white;
        }

        .btn-secondary:hover {
            background: #222b3d;
        }

        .note {
            margin-top: 30px;
            color: #667085;
            font-size: 14px;
            line-height: 1.6;
        }

        @media (max-width: 700px) {
            .navbar {
                padding: 0 20px;
            }

            .nav-links {
                gap: 12px;
            }

            .nav-links a {
                font-size: 13px;
            }

            .success-card {
                padding: 35px 20px;
            }

            h1 {
                font-size: 30px;
            }

            .booking-row {
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

<nav class="navbar">
    <div class="logo">
        CAR<span>HUB</span>
    </div>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="cars.php">Cars</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="my-bookings.php">My Bookings</a>
    </div>
</nav>

<div class="container">

    <div class="success-card">

        <div class="check-circle">
            ✓
        </div>

        <h1>Booking Request Submitted!</h1>

        <p class="subtitle">
            Thank you for choosing CarHub.
            Your booking request has been successfully submitted.
        </p>

        <div class="booking-box">

            <div class="booking-row">
                <span class="label">Booking ID</span>
                <span class="value">
                    #<?php echo htmlspecialchars($booking_id); ?>
                </span>
            </div>

            <div class="booking-row">
                <span class="label">Car</span>
                <span class="value">
                    <?php echo htmlspecialchars($car_name); ?>
                </span>
            </div>

            <?php if ($booking_date !== ''): ?>
            <div class="booking-row">
                <span class="label">Booking Date</span>
                <span class="value">
                    <?php echo htmlspecialchars($booking_date); ?>
                </span>
            </div>
            <?php endif; ?>

            <div class="booking-row">
                <span class="label">Status</span>
                <span class="value">
                    <span class="status">Pending</span>
                </span>
            </div>

        </div>

        <div class="buttons">
            <a href="my-bookings.php" class="btn btn-primary">
                View My Bookings
            </a>

            <a href="cars.php" class="btn btn-secondary">
                Browse More Cars
            </a>
        </div>

        <p class="note">
            Our team will review your booking request.
            You can check your booking status anytime from
            <strong>My Bookings</strong>.
        </p>

    </div>

</div>

</body>
</html>