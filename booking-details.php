<?php

session_start();

include "config/db.php";


/* =========================================================
   LOGIN CHECK
   ========================================================= */

if (!isset($_SESSION["user_id"]) || empty($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = (int)$_SESSION["user_id"];


/* =========================================================
   GET LOGGED-IN USER
   ========================================================= */

$user_stmt = $conn->prepare(
    "SELECT id, name, email
     FROM users
     WHERE id = ?
     LIMIT 1"
);

if (!$user_stmt) {
    die("Unable to load user.");
}

$user_stmt->bind_param("i", $user_id);

$user_stmt->execute();

$user_result = $user_stmt->get_result();

if ($user_result->num_rows !== 1) {

    $user_stmt->close();
    $conn->close();

    session_destroy();

    header("Location: login.php");
    exit();
}

$user = $user_result->fetch_assoc();

$user_email = trim($user["email"]);
$user_name = $user["name"];

$user_stmt->close();


/* =========================================================
   GET BOOKING ID
   ========================================================= */

if (
    !isset($_GET["booking_id"]) ||
    !is_numeric($_GET["booking_id"])
) {

    header("Location: my-bookings.php");
    exit();

}

$booking_id = (int)$_GET["booking_id"];

if ($booking_id <= 0) {

    header("Location: my-bookings.php");
    exit();

}


/* =========================================================
   GET BOOKING
   ========================================================= */

$stmt = $conn->prepare(
    "SELECT
        b.id,
        b.car_id,
        b.name,
        b.email,
        b.phone,
        b.booking_date,
        b.status,

        c.brand,
        c.model,
        c.price

     FROM bookings b

     LEFT JOIN cars c
        ON b.car_id = c.id

     WHERE b.id = ?
       AND LOWER(TRIM(b.email)) = LOWER(TRIM(?))

     LIMIT 1"
);

if (!$stmt) {
    die("Unable to load booking.");
}

$stmt->bind_param(
    "is",
    $booking_id,
    $user_email
);

$stmt->execute();

$result = $stmt->get_result();


/* =========================================================
   BOOKING NOT FOUND
   ========================================================= */

if ($result->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    header("Location: my-bookings.php");
    exit();

}


$booking = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   BOOKING DATA
   ========================================================= */

$brand = $booking["brand"] ?? "Car";

$model = $booking["model"] ?? "Vehicle";

$price = (float)($booking["price"] ?? 0);

$status = trim(
    $booking["status"] ?? "Pending"
);

$car_id = (int)($booking["car_id"] ?? 0);


/* =========================================================
   STATUS CLASS
   ========================================================= */

$status_class = strtolower($status);

$status_class = preg_replace(
    "/[^a-z0-9]+/",
    "-",
    $status_class
);


/* =========================================================
   CHECK IF BOOKING CAN BE CANCELLED
   ========================================================= */

$can_cancel = !in_array(
    $status_class,
    [
        "cancelled",
        "completed",
        "rejected"
    ],
    true
);


/* =========================================================
   CAR IMAGE
   IMPORTANT:
   SAME IMAGE LOGIC AS YOUR BOOKING PAGE
   ========================================================= */

$brand_lower = strtolower(trim($brand));


if (
    strpos($brand_lower, "mercedes") !== false
) {

    $car_image =
        "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85";

}

elseif (
    strpos($brand_lower, "audi") !== false
) {

    $car_image =
        "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85";

}

elseif (
    strpos($brand_lower, "bmw") !== false
) {

    $car_image =
        "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85";

}

else {

    $car_image =
        "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85";

}


$conn->close();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Booking Details | CarHub
</title>


<style>

/* =========================================================
   RESET
   ========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* =========================================================
   BODY
   ========================================================= */

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f4f5f7;

    color: #172033;

}


/* =========================================================
   NAVBAR
   ========================================================= */

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

    font-size: 30px;

    font-weight: 800;

}


.logo span {

    color: #ef3340;

}


.nav-links {

    display: flex;

    align-items: center;

    gap: 25px;

}


.nav-links a {

    color: white;

    text-decoration: none;

    font-size: 15px;

}


.nav-links a:hover {

    color: #ef3340;

}


/* =========================================================
   MAIN CONTAINER
   ========================================================= */

.container {

    width: 90%;

    max-width: 1100px;

    margin: 45px auto 70px;

}


/* =========================================================
   PAGE TITLE
   ========================================================= */

.page-title {

    text-align: center;

    margin-bottom: 35px;

}


.page-title h1 {

    font-size: 42px;

    margin-bottom: 10px;

}


.page-title h1 span {

    color: #ef3340;

}


.page-title p {

    color: #667085;

    font-size: 17px;

}


/* =========================================================
   BOOKING CARD
   ========================================================= */

.booking-card {

    background: white;

    border-radius: 20px;

    overflow: hidden;

    box-shadow:
        0 12px 35px rgba(0,0,0,.08);

}


/* =========================================================
   CAR IMAGE
   ========================================================= */

.car-image-wrapper {

    width: 100%;

    height: 430px;

    background: #e9edf2;

    overflow: hidden;

}


.car-image {

    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;

}


/* =========================================================
   CONTENT
   ========================================================= */

.content {

    padding: 35px;

}


/* =========================================================
   CAR DETAILS
   ========================================================= */

.brand {

    color: #ef3340;

    font-size: 14px;

    font-weight: bold;

    text-transform: uppercase;

    margin-bottom: 8px;

}


.car-name {

    font-size: 34px;

    margin-bottom: 10px;

}


.price {

    color: #ef3340;

    font-size: 26px;

    font-weight: bold;

    margin-bottom: 30px;

}


/* =========================================================
   INFORMATION GRID
   ========================================================= */

.info-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 18px;

    border-top: 1px solid #e5e7eb;

    padding-top: 25px;

}


.info-box {

    background: #f8f9fb;

    border-radius: 12px;

    padding: 18px;

}


.info-label {

    color: #667085;

    font-size: 14px;

    margin-bottom: 7px;

}


.info-value {

    color: #172033;

    font-size: 17px;

    font-weight: bold;

    word-break: break-word;

}


/* =========================================================
   STATUS
   ========================================================= */

.status {

    display: inline-block;

    padding: 9px 15px;

    border-radius: 20px;

    font-size: 14px;

    font-weight: bold;

}


.status.pending {

    background: #fff0c7;

    color: #9a5b00;

}


.status.approved,
.status.confirmed {

    background: #dff7e7;

    color: #16733b;

}


.status.completed {

    background: #e4e7ec;

    color: #344054;

}


.status.cancelled,
.status.rejected {

    background: #ffe0e0;

    color: #b42318;

}


/* =========================================================
   BUTTONS
   ========================================================= */

.buttons {

    display: flex;

    gap: 15px;

    margin-top: 30px;

}


.btn {

    flex: 1;

    min-height: 52px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 15px 20px;

    font-size: 16px;

    font-weight: bold;

    text-decoration: none;

    cursor: pointer;

    transition: .2s;

    border: none;

}


/* =========================================================
   BACK BUTTON
   ========================================================= */

.back-btn {

    background: #111827;

    color: white;

}


.back-btn:hover {

    background: #000000;

}


/* =========================================================
   CAR DETAILS BUTTON
   ========================================================= */

.car-btn {

    background: #e8ebef;

    color: #172033;

}


.car-btn:hover {

    background: #d8dce2;

}


/* =========================================================
   CANCEL BUTTON
   ========================================================= */

.cancel-btn {

    background: #ef3340;

    color: white;

}


.cancel-btn:hover {

    background: #d92835;

}


/* =========================================================
   CANCELLED MESSAGE
   ========================================================= */

.cancelled-message {

    margin-top: 25px;

    background: #ffe5e5;

    color: #b42318;

    padding: 16px 18px;

    border-radius: 10px;

    line-height: 1.5;

}


/* =========================================================
   FOOTER
   ========================================================= */

footer {

    background: #111111;

    color: white;

    text-align: center;

    padding: 30px;

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 800px) {

    .navbar {

        padding: 0 5%;

    }


    .nav-links {

        gap: 12px;

    }


    .nav-links a {

        font-size: 13px;

    }


    .car-image-wrapper {

        height: 300px;

    }


    .info-grid {

        grid-template-columns: 1fr;

    }


    .buttons {

        flex-direction: column;

    }

}


@media (max-width: 550px) {

    .container {

        width: 94%;

    }


    .page-title h1 {

        font-size: 32px;

    }


    .content {

        padding: 24px;

    }


    .car-name {

        font-size: 28px;

    }


    .car-image-wrapper {

        height: 240px;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
     ===================================================== -->

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

        <a href="my-bookings.php">
            My Bookings
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- =====================================================
     MAIN
     ===================================================== -->

<div class="container">


    <div class="page-title">

        <h1>
            Booking <span>Details</span>
        </h1>

        <p>
            Review the information for your vehicle booking.
        </p>

    </div>


    <!-- =================================================
         BOOKING CARD
         ================================================= -->

    <div class="booking-card">


        <!-- =================================================
             CAR IMAGE
             ================================================= -->

        <div class="car-image-wrapper">

            <img
                src="<?php echo htmlspecialchars($car_image); ?>"
                alt="<?php echo htmlspecialchars($brand . ' ' . $model); ?>"
                class="car-image"

                onerror="
                    this.onerror=null;
                    this.src='https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85';
                "
            >

        </div>


        <!-- =================================================
             CONTENT
             ================================================= -->

        <div class="content">


            <div class="brand">

                <?php
                echo htmlspecialchars($brand);
                ?>

            </div>


            <h2 class="car-name">

                <?php
                echo htmlspecialchars($model);
                ?>

            </h2>


            <div class="price">

                ₹<?php
                echo number_format($price);
                ?>

            </div>


            <!-- =================================================
                 BOOKING INFORMATION
                 ================================================= -->

            <div class="info-grid">


                <!-- BOOKING ID -->

                <div class="info-box">

                    <div class="info-label">
                        Booking ID
                    </div>

                    <div class="info-value">

                        #

                        <?php
                        echo (int)$booking["id"];
                        ?>

                    </div>

                </div>


                <!-- CUSTOMER NAME -->

                <div class="info-box">

                    <div class="info-label">
                        Customer Name
                    </div>

                    <div class="info-value">

                        <?php
                        echo htmlspecialchars(
                            $booking["name"] ?? $user_name
                        );
                        ?>

                    </div>

                </div>


                <!-- EMAIL -->

                <div class="info-box">

                    <div class="info-label">
                        Email
                    </div>

                    <div class="info-value">

                        <?php
                        echo htmlspecialchars(
                            $booking["email"] ?? $user_email
                        );
                        ?>

                    </div>

                </div>


                <!-- PHONE -->

                <div class="info-box">

                    <div class="info-label">
                        Phone
                    </div>

                    <div class="info-value">

                        <?php
                        echo htmlspecialchars(
                            $booking["phone"] ?? "—"
                        );
                        ?>

                    </div>

                </div>


                <!-- BOOKING DATE -->

                <div class="info-box">

                    <div class="info-label">
                        Booking Date
                    </div>

                    <div class="info-value">

                        <?php

                        if (
                            !empty(
                                $booking["booking_date"]
                            )
                        ) {

                            echo htmlspecialchars(
                                date(
                                    "d M Y",
                                    strtotime(
                                        $booking["booking_date"]
                                    )
                                )
                            );

                        } else {

                            echo "—";

                        }

                        ?>

                    </div>

                </div>


                <!-- STATUS -->

                <div class="info-box">

                    <div class="info-label">
                        Booking Status
                    </div>

                    <div class="info-value">

                        <span
                            class="status <?php echo htmlspecialchars($status_class); ?>"
                        >

                            <?php
                            echo htmlspecialchars($status);
                            ?>

                        </span>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 ACTION BUTTONS
                 ================================================= -->

            <div class="buttons">


                <!-- BACK -->

                <a
                    href="my-bookings.php"
                    class="btn back-btn"
                >
                    ← Back to My Bookings
                </a>


                <!-- VIEW CAR DETAILS -->

                <?php if ($car_id > 0): ?>

                    <a
                        href="car-details.php?car_id=<?php echo $car_id; ?>"
                        class="btn car-btn"
                    >
                        View Car Details
                    </a>

                <?php endif; ?>


                <!-- CANCEL BOOKING -->

                <?php if ($can_cancel): ?>

                    <a
                        href="cancel-booking.php?booking_id=<?php echo (int)$booking["id"]; ?>"
                        class="btn cancel-btn"
                    >
                        Cancel Booking
                    </a>

                <?php endif; ?>


            </div>


            <!-- =================================================
                 NON-CANCELLABLE MESSAGE
                 ================================================= -->

            <?php if (!$can_cancel): ?>

                <div class="cancelled-message">

                    This booking can no longer be cancelled
                    because its current status is

                    <strong>

                        <?php
                        echo htmlspecialchars($status);
                        ?>

                    </strong>.

                </div>

            <?php endif; ?>


        </div>

    </div>

</div>


<!-- =====================================================
     FOOTER
     ===================================================== -->

<footer>

    © <?php echo date("Y"); ?> CarHub.
    All Rights Reserved.

</footer>


</body>

</html>