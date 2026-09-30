<?php
session_start();

require_once "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

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
$user_stmt->execute();

$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$user_stmt->close();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit();
}


/* =====================================================
   GET BOOKING
===================================================== */

$stmt = $conn->prepare(
    "SELECT
        b.id AS booking_id,
        b.name AS customer_name,
        b.email AS customer_email,
        b.booking_date,
        b.status,

        c.name AS car_name,
        c.brand,
        c.model,
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

$stmt->execute();

$result = $stmt->get_result();
$booking = $result->fetch_assoc();

$stmt->close();

if (!$booking) {
    header("Location: my-bookings.php");
    exit();
}


$amount = (float) $booking["price"];


/* =====================================================
   PAYMENT METHOD DISPLAY
===================================================== */

if ($method === "upi") {

    $method_name = "UPI Payment";

} elseif ($method === "cash") {

    $method_name = "Cash on Pickup";

} else {

    $method_name = "Card Payment";
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

<title>Processing Payment - CarHub</title>


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

    display: flex;

    flex-direction: column;
}


/* =====================================================
   NAVBAR
===================================================== */

.navbar {

    background: #111;

    padding: 16px 6%;

    display: flex;

    justify-content: space-between;

    align-items: center;

    flex-wrap: wrap;

    gap: 20px;
}


.logo {

    color: #e50914;

    text-decoration: none;

    font-size: 27px;

    font-weight: bold;
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


/* =====================================================
   MAIN
===================================================== */

.container {

    width: 90%;

    max-width: 600px;

    margin: auto;

    padding: 50px 0;
}


.payment-card {

    background: white;

    border-radius: 15px;

    padding: 40px 30px;

    text-align: center;

    box-shadow:
        0 8px 30px rgba(0,0,0,0.10);
}


/* =====================================================
   PROCESSING ICON
===================================================== */

.loader {

    width: 70px;

    height: 70px;

    border: 6px solid #eee;

    border-top:
        6px solid #e50914;

    border-radius: 50%;

    margin: 0 auto 25px;

    animation:
        spin 1s linear infinite;
}


@keyframes spin {

    0% {
        transform: rotate(0deg);
    }

    100% {
        transform: rotate(360deg);
    }
}


/* =====================================================
   TEXT
===================================================== */

h1 {

    font-size: 28px;

    margin-bottom: 12px;
}


.subtitle {

    color: #666;

    line-height: 1.6;

    margin-bottom: 25px;
}


/* =====================================================
   DETAILS
===================================================== */

.details {

    background: #f8f8f8;

    border-radius: 10px;

    padding: 18px;

    text-align: left;

    margin-top: 20px;
}


.detail-row {

    display: flex;

    justify-content: space-between;

    gap: 20px;

    padding: 10px 0;

    border-bottom:
        1px solid #e5e5e5;
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
}


.amount {

    color: #e50914;

    font-size: 22px;

    font-weight: bold;
}


/* =====================================================
   SECURITY
===================================================== */

.security {

    margin-top: 25px;

    font-size: 13px;

    color: #777;

    line-height: 1.5;
}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

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

        <a href="my-bookings.php">
            My Bookings
        </a>

        <a href="my-test-drives.php">
            My Test Drives
        </a>

    </div>

</nav>


<!-- =====================================================
     PAYMENT PROCESSING
===================================================== -->

<div class="container">

    <div class="payment-card">


        <div class="loader"></div>


        <h1>
            Processing Payment
        </h1>


        <p class="subtitle">

            Please wait while your payment
            is being processed.

            <br>

            Do not close or refresh this page.

        </p>


        <div class="details">


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


            <div class="detail-row">

                <span class="label">
                    Payment Method
                </span>

                <span class="value">

                    <?php
                    echo htmlspecialchars(
                        $method_name
                    );
                    ?>

                </span>

            </div>


            <div class="detail-row">

                <span class="label">
                    Amount
                </span>

                <span class="value amount">

                    ₹<?php
                    echo number_format(
                        $amount,
                        2
                    );
                    ?>

                </span>

            </div>


        </div>


        <div class="security">

            🔒 Your payment is being securely processed.

            <br>

            CarHub does not store your card number,
            CVV, or UPI credentials.

        </div>

    </div>

</div>


<!-- =====================================================
     AUTOMATIC REDIRECT
===================================================== -->

<script>

setTimeout(function() {

    window.location.href =
        "payment-success.php?booking_id=<?php echo urlencode($booking_id); ?>&method=<?php echo urlencode($method); ?>";

}, 3000);

</script>


</body>

</html>