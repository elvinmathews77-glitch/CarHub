<?php
session_start();

require_once "config/db.php";

/* =====================================================
   LOGIN CHECK
===================================================== */

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];


/* =====================================================
   GET BOOKING ID AND PAYMENT METHOD
===================================================== */

$booking_id = isset($_GET["booking_id"])
    ? (int) $_GET["booking_id"]
    : 0;

$method = isset($_GET["method"])
    ? strtolower(trim($_GET["method"]))
    : "card";

if ($booking_id <= 0) {
    header("Location: my-bookings.php");
    exit();
}


/* =====================================================
   VALID PAYMENT METHODS
===================================================== */

$allowed_methods = ["card", "upi", "cash"];

if (!in_array($method, $allowed_methods, true)) {
    $method = "card";
}


/* =====================================================
   GET USER
===================================================== */

$user_stmt = $conn->prepare(
    "SELECT id, name, email
     FROM users
     WHERE id = ?
     LIMIT 1"
);

if (!$user_stmt) {
    die("Database error: " . $conn->error);
}

$user_stmt->bind_param("i", $user_id);

if (!$user_stmt->execute()) {
    die("User query failed: " . $user_stmt->error);
}

$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$user_stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}


/* =====================================================
   GET BOOKING + CAR
===================================================== */

$stmt = $conn->prepare(
    "SELECT
        b.id AS booking_id,
        b.name AS customer_name,
        b.email AS customer_email,
        b.phone AS customer_phone,
        b.booking_date,
        b.status AS booking_status,

        c.id AS car_id,
        c.name AS car_name,
        c.brand,
        c.model,
        c.year,
        c.price

     FROM bookings b

     INNER JOIN cars c
        ON b.car_id = c.id

     WHERE b.id = ?
       AND b.email = ?

     LIMIT 1"
);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "is",
    $booking_id,
    $user["email"]
);

if (!$stmt->execute()) {
    die("Booking query failed: " . $stmt->error);
}

$result = $stmt->get_result();
$booking = $result->fetch_assoc();

$stmt->close();

if (!$booking) {
    header("Location: my-bookings.php");
    exit();
}


/* =====================================================
   PAYMENT AMOUNT
===================================================== */

$amount = (float) $booking["price"];


/* =====================================================
   PAYMENT INFORMATION
===================================================== */

if ($method === "upi") {

    $payment_method = "UPI";
    $payment_status = "Test Successful";

} elseif ($method === "cash") {

    $payment_method = "Cash on Pickup";
    $payment_status = "Pending";

} else {

    $payment_method = "Card";
    $payment_status = "Test Successful";
}


/* =====================================================
   CREATE CARHUB ORDER REFERENCE
===================================================== */

/*
   This is a project-generated reference.
   It is NOT a real Razorpay transaction ID.
*/

$order_reference =
    "CARHUB_" .
    $booking_id .
    "_" .
    time();


/* =====================================================
   CHECK EXISTING PAYMENT
===================================================== */

$check_stmt = $conn->prepare(
    "SELECT id
     FROM payments
     WHERE booking_id = ?
     LIMIT 1"
);

if (!$check_stmt) {
    die("Payment check preparation failed: " . $conn->error);
}

$check_stmt->bind_param(
    "i",
    $booking_id
);

if (!$check_stmt->execute()) {
    die("Payment check failed: " . $check_stmt->error);
}

$check_result = $check_stmt->get_result();

$existing_payment = $check_result->fetch_assoc();

$check_stmt->close();


/* =====================================================
   INSERT OR UPDATE PAYMENT
===================================================== */

if ($existing_payment) {

    /* ================================================
       UPDATE EXISTING PAYMENT
    ================================================ */

    $payment_id = (int) $existing_payment["id"];

    $update_stmt = $conn->prepare(
        "UPDATE payments
         SET payment_method = ?,
             order_reference = ?,
             amount = ?,
             status = ?
         WHERE id = ?"
    );

    if (!$update_stmt) {
        die("Payment update preparation failed: " . $conn->error);
    }

    $update_stmt->bind_param(
        "ssdsi",
        $payment_method,
        $order_reference,
        $amount,
        $payment_status,
        $payment_id
    );

    if (!$update_stmt->execute()) {
        die("Payment update failed: " . $update_stmt->error);
    }

    $update_stmt->close();

} else {

    /* ================================================
       INSERT NEW PAYMENT
    ================================================ */

    $insert_stmt = $conn->prepare(
        "INSERT INTO payments
        (
            booking_id,
            payment_method,
            order_reference,
            transaction_id,
            amount,
            status
        )
        VALUES (?, ?, ?, NULL, ?, ?)"
    );

    if (!$insert_stmt) {
        die("Payment insert preparation failed: " . $conn->error);
    }

    $insert_stmt->bind_param(
        "issds",
        $booking_id,
        $payment_method,
        $order_reference,
        $amount,
        $payment_status
    );

    if (!$insert_stmt->execute()) {
        die("Payment insert failed: " . $insert_stmt->error);
    }

    $payment_id = $insert_stmt->insert_id;

    $insert_stmt->close();
}


/* =====================================================
   VERIFY PAYMENT RECORD
===================================================== */

$verify_stmt = $conn->prepare(
    "SELECT
        id,
        booking_id,
        payment_method,
        order_reference,
        transaction_id,
        amount,
        status,
        created_at

     FROM payments

     WHERE id = ?

     LIMIT 1"
);

if (!$verify_stmt) {
    die("Payment verification preparation failed: " . $conn->error);
}

$verify_stmt->bind_param(
    "i",
    $payment_id
);

if (!$verify_stmt->execute()) {
    die("Payment verification failed: " . $verify_stmt->error);
}

$verify_result = $verify_stmt->get_result();

$saved_payment = $verify_result->fetch_assoc();

$verify_stmt->close();

if (!$saved_payment) {
    die("Payment record was not saved in the database.");
}


/* =====================================================
   DISPLAY STATUS
===================================================== */

if ($method === "cash") {

    $display_status = "Payment Due at Pickup";

} else {

    $display_status = "Payment Successful";
}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Payment Successful - CarHub</title>

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f5f5;
            color: #222;
            min-height: 100vh;
        }

        /* =========================
           NAVBAR
        ========================= */

        .navbar {
            background: #111;
            color: white;
            padding: 16px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .logo {
            color: #e50914;
            font-size: 27px;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 15px;
        }

        .nav-links a:hover {
            color: #e50914;
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {
            width: 90%;
            max-width: 700px;
            margin: 45px auto 60px;
        }


        /* =========================
           SUCCESS CARD
        ========================= */

        .success-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.10);
            text-align: center;
        }


        /* =========================
           SUCCESS ICON
        ========================= */

        .success-icon {
            width: 85px;
            height: 85px;
            margin: 0 auto 20px;
            border-radius: 50%;
            background: #e8f8ed;
            color: #1b9e4b;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 48px;
            font-weight: bold;
        }


        /* =========================
           TITLE
        ========================= */

        .success-card h1 {
            font-size: 30px;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            line-height: 1.6;
            margin-bottom: 30px;
        }


        /* =========================
           DETAILS
        ========================= */

        .booking-details {
            background: #f8f8f8;
            border-radius: 10px;
            padding: 20px;
            text-align: left;
            margin-bottom: 25px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            padding: 12px 0;
            border-bottom: 1px solid #e5e5e5;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .label {
            color: #777;
        }

        .value {
            font-weight: bold;
            text-align: right;
            word-break: break-word;
        }

        .amount {
            color: #e50914;
            font-size: 20px;
        }


        /* =========================
           STATUS
        ========================= */

        .status {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 20px;
            background: #e8f8ed;
            color: #1b7f3b;
            font-weight: bold;
            font-size: 13px;
        }

        .cash-status {
            background: #fff3cd;
            color: #856404;
        }


        /* =========================
           MESSAGE
        ========================= */

        .message {
            background: #f7f7f7;
            border-left: 4px solid #e50914;
            border-radius: 6px;
            padding: 15px;
            text-align: left;
            line-height: 1.6;
            margin-bottom: 25px;
            font-size: 14px;
        }


        /* =========================
           BUTTONS
        ========================= */

        .buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 13px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }

        .btn-primary {
            background: #e50914;
            color: white;
        }

        .btn-primary:hover {
            background: #b80710;
        }

        .btn-secondary {
            background: #eee;
            color: #333;
        }

        .btn-secondary:hover {
            background: #ddd;
        }


        /* =========================
           FOOTER NOTE
        ========================= */

        .footer-note {
            margin-top: 25px;
            color: #888;
            font-size: 13px;
            line-height: 1.5;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 600px) {

            .navbar {
                flex-direction: column;
                text-align: center;
            }

            .container {
                width: 94%;
            }

            .success-card {
                padding: 25px 18px;
            }

            .success-card h1 {
                font-size: 25px;
            }

            .detail-row {
                flex-direction: column;
                gap: 5px;
            }

            .value {
                text-align: left;
            }

            .buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }

    </style>

</head>

<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar">

    <a
        href="index.php"
        class="logo"
    >
        CarHub
    </a>

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

        <a href="my-test-drives.php">
            My Test Drives
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- =========================
     SUCCESS SECTION
========================= -->

<div class="container">

    <div class="success-card">


        <!-- SUCCESS ICON -->

        <div class="success-icon">
            ✓
        </div>


        <!-- TITLE -->

        <h1>

            <?php if ($method === "cash"): ?>

                Car Booked Successfully!

            <?php else: ?>

                Payment Successful!

            <?php endif; ?>

        </h1>


        <!-- SUBTITLE -->

        <p class="subtitle">

            <?php if ($method === "cash"): ?>

                Your vehicle booking has been confirmed.

            <?php else: ?>

                Your payment has been recorded
                and your vehicle booking has been confirmed.

            <?php endif; ?>

        </p>


        <!-- =========================
             PAYMENT DETAILS
        ========================= -->

        <div class="booking-details">


            <!-- BOOKING ID -->

            <div class="detail-row">

                <span class="label">
                    Booking ID
                </span>

                <span class="value">

                    #
                    <?php
                    echo htmlspecialchars(
                        $booking["booking_id"]
                    );
                    ?>

                </span>

            </div>


            <!-- PAYMENT ID -->

            <div class="detail-row">

                <span class="label">
                    Payment ID
                </span>

                <span class="value">

                    #
                    <?php
                    echo htmlspecialchars(
                        $payment_id
                    );
                    ?>

                </span>

            </div>


            <!-- ORDER REFERENCE -->

            <div class="detail-row">

                <span class="label">
                    Payment Reference
                </span>

                <span class="value">

                    <?php
                    echo htmlspecialchars(
                        $saved_payment["order_reference"]
                    );
                    ?>

                </span>

            </div>


            <!-- VEHICLE -->

            <div class="detail-row">

                <span class="label">
                    Vehicle
                </span>

                <span class="value">

                    <?php
                    echo htmlspecialchars(
                        $booking["car_name"]
                    );
                    ?>

                </span>

            </div>


            <!-- CUSTOMER -->

            <div class="detail-row">

                <span class="label">
                    Customer
                </span>

                <span class="value">

                    <?php
                    echo htmlspecialchars(
                        $booking["customer_name"]
                    );
                    ?>

                </span>

            </div>


            <!-- BOOKING DATE -->

            <div class="detail-row">

                <span class="label">
                    Booking Date
                </span>

                <span class="value">

                    <?php
                    echo date(
                        "d M Y",
                        strtotime(
                            $booking["booking_date"]
                        )
                    );
                    ?>

                </span>

            </div>


            <!-- PAYMENT METHOD -->

            <div class="detail-row">

                <span class="label">
                    Payment Method
                </span>

                <span class="value">

                    <?php
                    echo htmlspecialchars(
                        $saved_payment["payment_method"]
                    );
                    ?>

                </span>

            </div>


            <!-- AMOUNT -->

            <div class="detail-row">

                <span class="label">
                    Amount
                </span>

                <span class="value amount">

                    ₹<?php
                    echo number_format(
                        $saved_payment["amount"],
                        2
                    );
                    ?>

                </span>

            </div>


            <!-- PAYMENT STATUS -->

            <div class="detail-row">

                <span class="label">
                    Payment Status
                </span>

                <span class="value">

                    <?php if ($saved_payment["status"] === "Pending"): ?>

                        <span class="status cash-status">
                            Payment Due at Pickup
                        </span>

                    <?php else: ?>

                        <span class="status">
                            Payment Successful
                        </span>

                    <?php endif; ?>

                </span>

            </div>


        </div>


        <!-- =========================
             MESSAGE
        ========================= -->

        <div class="message">

            <?php if ($method === "cash"): ?>

                <strong>
                    Cash on Pickup
                </strong>

                <br><br>

                Your vehicle has been successfully
                booked. Payment will be collected
                when you pick up the vehicle.

            <?php else: ?>

                <strong>
                    Payment Confirmed
                </strong>

                <br><br>

                Your payment record has been created
                successfully and your vehicle booking
                has been confirmed.

            <?php endif; ?>

        </div>


        <!-- =========================
             BUTTONS
        ========================= -->

        <div class="buttons">

            <a
                href="booking-details.php?booking_id=<?php echo urlencode($booking_id); ?>"
                class="btn btn-primary"
            >
                View Booking Details
            </a>


            <a
                href="my-bookings.php"
                class="btn btn-secondary"
            >
                My Bookings
            </a>


            <a
                href="cars.php"
                class="btn btn-secondary"
            >
                Browse More Cars
            </a>

        </div>


        <!-- FOOTER NOTE -->

        <div class="footer-note">

            Thank you for choosing
            <strong>CarHub</strong>.

            <br>

            Please keep your Booking ID
            for future reference.

        </div>


    </div>

</div>


</body>
</html>