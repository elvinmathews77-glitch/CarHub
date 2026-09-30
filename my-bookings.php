<?php

session_start();

require_once "config/db.php";


/* =========================================================
   LOGIN / USER IDENTIFICATION
========================================================= */

$user_email = "";
$user_id = 0;


/* Get user ID from session */

if (!empty($_SESSION['user_id'])) {

    $user_id = (int)$_SESSION['user_id'];

}


/* Get email directly from session */

if (!empty($_SESSION['email'])) {

    $user_email = trim($_SESSION['email']);

}


if (
    !empty($_SESSION['user_email']) &&
    $user_email === ""
) {

    $user_email = trim($_SESSION['user_email']);

}


/* Check alternative session ID */

if (
    !empty($_SESSION['id']) &&
    $user_id === 0
) {

    $user_id = (int)$_SESSION['id'];

}


/* Check session user array */

if (
    isset($_SESSION['user']) &&
    is_array($_SESSION['user'])
) {

    if (
        $user_id === 0 &&
        !empty($_SESSION['user']['id'])
    ) {

        $user_id = (int)$_SESSION['user']['id'];

    }


    if (
        $user_email === "" &&
        !empty($_SESSION['user']['email'])
    ) {

        $user_email =
            trim($_SESSION['user']['email']);

    }

}


/* =========================================================
   GET USER EMAIL FROM DATABASE
========================================================= */

if (
    $user_id > 0 &&
    $user_email === ""
) {

    $user_stmt = $conn->prepare("
        SELECT id, email
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    if ($user_stmt) {

        $user_stmt->bind_param(
            "i",
            $user_id
        );

        if (!$user_stmt->execute()) {

            $user_stmt->close();

            die("Unable to verify user.");

        }

        $user_result =
            $user_stmt->get_result();


        if (
            $user_result->num_rows > 0
        ) {

            $user =
                $user_result->fetch_assoc();

            $user_email =
                trim(
                    $user['email'] ?? ""
                );

        }


        $user_stmt->close();

    }

}


/* =========================================================
   LOGIN CHECK
========================================================= */

if ($user_email === "") {

    $conn->close();

    header("Location: login.php");

    exit;

}


/* =========================================================
   GET BOOKINGS + PAYMENT DETAILS
========================================================= */

$bookings = [];


$stmt = $conn->prepare("
    SELECT

        b.id,
        b.car_id,
        b.name,
        b.email,
        b.phone,
        b.booking_date,
        b.status,

        c.brand,
        c.model,
        c.price,
        c.image,

        /* PAYMENT DETAILS */

        p.id AS payment_id,
        p.payment_method,
        p.order_reference,
        p.transaction_id,
        p.amount AS payment_amount,
        p.status AS payment_status,
        p.created_at AS payment_created_at

    FROM bookings b

    LEFT JOIN cars c
        ON b.car_id = c.id

    LEFT JOIN payments p
        ON b.id = p.booking_id

    WHERE
        LOWER(TRIM(b.email))
        =
        LOWER(TRIM(?))

    ORDER BY b.id DESC
");


if (!$stmt) {

    die(
        "Unable to load bookings: "
        . $conn->error
    );

}


$stmt->bind_param(
    "s",
    $user_email
);


if (!$stmt->execute()) {

    die(
        "Unable to load bookings: "
        . $stmt->error
    );

}


$result =
    $stmt->get_result();


while (
    $row =
    $result->fetch_assoc()
) {

    $bookings[] = $row;

}


$stmt->close();


/* =========================================================
   IMAGE FUNCTION
========================================================= */

function getCarImage($booking)
{

    $image =
        trim(
            $booking['image'] ?? ""
        );


    /* =====================================================
       DATABASE IMAGE URL
    ===================================================== */

    if (
        $image !== "" &&
        (
            strpos($image, "http://") === 0 ||
            strpos($image, "https://") === 0
        )
    ) {

        return $image;

    }


    /* =====================================================
       LOCAL IMAGE
    ===================================================== */

    if ($image !== "") {

        $possible_paths = [

            $image,

            "images/" . $image,

            "uploads/" . $image,

            "assets/images/" . $image

        ];


        foreach (
            $possible_paths as $path
        ) {

            $full_path =
                __DIR__ .
                DIRECTORY_SEPARATOR .
                $path;


            if (
                file_exists($full_path) &&
                is_file($full_path)
            ) {

                return $path;

            }

        }

    }


    /* =====================================================
       BRAND FALLBACK
    ===================================================== */

    $brand =
        strtolower(
            trim(
                $booking['brand'] ?? ""
            )
        );


    if (
        strpos(
            $brand,
            "audi"
        ) !== false
    ) {

        return
            "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85";

    }


    if (
        strpos(
            $brand,
            "bmw"
        ) !== false
    ) {

        return
            "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85";

    }


    if (
        strpos(
            $brand,
            "mercedes"
        ) !== false
    ) {

        return
            "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85";

    }


    if (
        strpos(
            $brand,
            "toyota"
        ) !== false
    ) {

        return
            "https://images.unsplash.com/photo-1623869675781-80aa31012a5a?auto=format&fit=crop&w=1200&q=85";

    }


    if (
        strpos(
            $brand,
            "ford"
        ) !== false
    ) {

        return
            "https://images.unsplash.com/photo-1542282088-72c9c27ed0cd?auto=format&fit=crop&w=1200&q=85";

    }


    if (
        strpos(
            $brand,
            "porsche"
        ) !== false
    ) {

        return
            "https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1200&q=85";

    }


    /* =====================================================
       GENERIC FALLBACK
    ===================================================== */

    return
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
    My Bookings | CarHub
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

    min-height: 72px;

    background: #111;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 8%;

}


/* LOGO */

.logo {

    color: white;

    font-size: 30px;

    font-weight: 800;

    text-decoration: none;

}


.logo span {

    color: #ef3340;

}


/* NAV LINKS */

.nav-links {

    display: flex;

    align-items: center;

    gap: 25px;

}


.nav-links a {

    color: white;

    text-decoration: none;

    font-size: 15px;

    white-space: nowrap;

}


.nav-links a:hover {

    color: #ef3340;

}


/* =========================================================
   CONTAINER
========================================================= */

.container {

    width: 90%;

    max-width: 1200px;

    margin: 45px auto 70px;

}


/* =========================================================
   PAGE HEADER
========================================================= */

.page-header {

    text-align: center;

    margin-bottom: 35px;

}


.page-header h1 {

    font-size: 42px;

    margin-bottom: 10px;

}


.page-header h1 span {

    color: #ef3340;

}


.page-header p {

    color: #667085;

    font-size: 18px;

}


/* =========================================================
   BOOKING GRID
========================================================= */

.bookings-grid {

    display: grid;

    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );

    gap: 30px;

}


/* =========================================================
   BOOKING CARD
========================================================= */

.booking-card {

    background: white;

    border-radius: 20px;

    overflow: hidden;

    box-shadow:
        0 10px 30px
        rgba(0,0,0,.08);

}


/* =========================================================
   CAR IMAGE
========================================================= */

.car-image-wrapper {

    width: 100%;

    height: 300px;

    background: #e9edf2;

    overflow: hidden;

}


.car-image {

    width: 100%;

    height: 100%;

    display: block;

    object-fit: cover;

}


/* =========================================================
   BOOKING CONTENT
========================================================= */

.booking-content {

    padding: 28px;

}


.brand {

    color: #ef3340;

    font-size: 14px;

    font-weight: bold;

    text-transform: uppercase;

    margin-bottom: 7px;

}


.car-name {

    font-size: 28px;

    margin-bottom: 8px;

}


.price {

    color: #ef3340;

    font-size: 24px;

    font-weight: bold;

    margin-bottom: 22px;

}


/* =========================================================
   DETAILS
========================================================= */

.details {

    border-top:
        1px solid #e5e7eb;

    padding-top: 18px;

}


.detail-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    padding: 10px 0;

}


.detail-label {

    color: #667085;

}


.detail-value {

    font-weight: bold;

    text-align: right;

    word-break: break-word;

}


/* =========================================================
   BOOKING STATUS
========================================================= */

.status {

    display: inline-block;

    padding: 8px 15px;

    border-radius: 20px;

    font-size: 13px;

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
   PAYMENT SECTION
========================================================= */

.payment-section {

    margin-top: 22px;

    padding: 18px;

    background: #f8f9fb;

    border: 1px solid #e5e7eb;

    border-radius: 12px;

}


.payment-title {

    font-size: 17px;

    font-weight: bold;

    margin-bottom: 12px;

    color: #172033;

}


.payment-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    padding: 8px 0;

}


.payment-label {

    color: #667085;

    font-size: 14px;

}


.payment-value {

    font-weight: bold;

    text-align: right;

    font-size: 14px;

    word-break: break-word;

}


/* =========================================================
   PAYMENT STATUS
========================================================= */

.payment-status {

    display: inline-block;

    padding: 7px 12px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: bold;

}


.payment-success {

    background: #dff7e7;

    color: #16733b;

}


.payment-pending {

    background: #fff0c7;

    color: #9a5b00;

}


.payment-failed {

    background: #ffe0e0;

    color: #b42318;

}


.payment-none {

    background: #e4e7ec;

    color: #344054;

}


/* =========================================================
   PAYMENT AMOUNT
========================================================= */

.payment-amount {

    color: #ef3340;

    font-size: 17px;

}


/* =========================================================
   VIEW DETAILS BUTTON
========================================================= */

.view-details-btn {

    display: block;

    width: 100%;

    margin-top: 22px;

    padding: 16px;

    background: #111827;

    color: white;

    text-decoration: none;

    text-align: center;

    border-radius: 10px;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

    transition: .2s;

}


.view-details-btn:hover {

    background: #ef3340;

}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-box {

    background: white;

    border-radius: 20px;

    padding: 70px 30px;

    text-align: center;

    box-shadow:
        0 10px 30px
        rgba(0,0,0,.07);

}


.empty-box h2 {

    font-size: 28px;

    margin-bottom: 12px;

}


.empty-box p {

    color: #667085;

    margin-bottom: 25px;

}


.browse-btn {

    display: inline-block;

    background: #ef3340;

    color: white;

    padding: 14px 28px;

    border-radius: 9px;

    text-decoration: none;

    font-weight: bold;

}


.browse-btn:hover {

    background: #d92835;

}


/* =========================================================
   FOOTER
========================================================= */

footer {

    background: #111;

    color: white;

    text-align: center;

    padding: 30px;

}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 1000px) {

    .navbar {

        padding: 0 5%;

    }


    .nav-links {

        gap: 15px;

    }


    .nav-links a {

        font-size: 14px;

    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 850px) {

    .navbar {

        min-height: auto;

        padding: 18px 5%;

        flex-direction: column;

        gap: 18px;

    }


    .nav-links {

        width: 100%;

        display: flex;

        flex-wrap: wrap;

        justify-content: center;

        gap: 12px 18px;

    }


    .nav-links a {

        font-size: 13px;

    }


    .bookings-grid {

        grid-template-columns: 1fr;

    }


    .page-header h1 {

        font-size: 34px;

    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 550px) {

    .container {

        width: 94%;

        margin-top: 30px;

    }


    .page-header h1 {

        font-size: 32px;

    }


    .page-header p {

        font-size: 15px;

    }


    .booking-content {

        padding: 22px;

    }


    .car-image-wrapper {

        height: 240px;

    }


    .car-name {

        font-size: 26px;

    }


    .detail-row {

        align-items: flex-start;

    }


    .detail-value {

        max-width: 55%;

        word-break: break-word;

    }


    .payment-row {

        align-items: flex-start;

        flex-direction: column;

        gap: 4px;

    }


    .payment-value {

        text-align: left;

    }

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
        CAR<span>HUB</span>
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


<!-- =====================================================
     MAIN
===================================================== -->

<div class="container">


    <!-- PAGE HEADER -->

    <div class="page-header">

        <h1>

            My <span>Bookings</span>

        </h1>


        <p>

            View your vehicle bookings,
            payment details and current status.

        </p>

    </div>



    <?php if (count($bookings) === 0): ?>


        <!-- =================================================
             NO BOOKINGS
        ================================================== -->

        <div class="empty-box">


            <h2>

                No Bookings Yet

            </h2>


            <p>

                You have not booked a vehicle yet.

            </p>


            <a
                href="cars.php"
                class="browse-btn"
            >

                Browse Cars

            </a>


        </div>


    <?php else: ?>


        <!-- =================================================
             BOOKINGS
        ================================================== -->

        <div class="bookings-grid">


            <?php foreach ($bookings as $booking): ?>


                <?php

                /* =================================================
                   BASIC BOOKING INFORMATION
                ================================================= */

                $brand =
                    $booking['brand']
                    ?? 'Car';


                $model =
                    $booking['model']
                    ?? 'Vehicle';


                $price =
                    (float)(
                        $booking['price']
                        ?? 0
                    );


                $image =
                    getCarImage(
                        $booking
                    );


                $status =
                    trim(
                        $booking['status']
                        ?? 'Pending'
                    );


                $status_class =

                    strtolower(

                        preg_replace(

                            '/[^a-zA-Z0-9]+/',

                            '-',

                            $status

                        )

                    );


                /* =================================================
                   PAYMENT INFORMATION
                ================================================= */

                $payment_id =
                    !empty($booking['payment_id'])
                    ? (int)$booking['payment_id']
                    : 0;


                $payment_method =
                    trim(
                        $booking['payment_method']
                        ?? ""
                    );


                $order_reference =
                    trim(
                        $booking['order_reference']
                        ?? ""
                    );


                $transaction_id =
                    trim(
                        $booking['transaction_id']
                        ?? ""
                    );


                $payment_amount =
                    isset($booking['payment_amount'])
                    ? (float)$booking['payment_amount']
                    : 0;


                $payment_status =
                    trim(
                        $booking['payment_status']
                        ?? ""
                    );


                /* Payment status CSS */

                if ($payment_status === "") {

                    $payment_status_class =
                        "payment-none";

                } elseif (
                    strtolower($payment_status)
                    === "test successful"
                ) {

                    $payment_status_class =
                        "payment-success";

                } elseif (
                    strtolower($payment_status)
                    === "pending"
                ) {

                    $payment_status_class =
                        "payment-pending";

                } elseif (
                    strtolower($payment_status)
                    === "failed"
                ) {

                    $payment_status_class =
                        "payment-failed";

                } else {

                    $payment_status_class =
                        "payment-pending";

                }

                ?>


                <!-- =================================================
                     BOOKING CARD
                ================================================== -->

                <div class="booking-card">


                    <!-- CAR IMAGE -->

                    <div class="car-image-wrapper">


                        <img

                            src="<?php
                            echo htmlspecialchars(
                                $image
                            );
                            ?>"

                            alt="<?php
                            echo htmlspecialchars(
                                $brand .
                                ' ' .
                                $model
                            );
                            ?>"

                            class="car-image"


                            onerror="
                                this.onerror=null;
                                this.src='https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85';
                            "

                        >


                    </div>



                    <!-- BOOKING CONTENT -->

                    <div class="booking-content">


                        <!-- BRAND -->

                        <div class="brand">

                            <?php

                            echo htmlspecialchars(
                                $brand
                            );

                            ?>

                        </div>



                        <!-- MODEL -->

                        <h2 class="car-name">

                            <?php

                            echo htmlspecialchars(
                                $model
                            );

                            ?>

                        </h2>



                        <!-- PRICE -->

                        <div class="price">

                            ₹<?php

                            echo number_format(
                                $price
                            );

                            ?>

                        </div>



                        <!-- =================================================
                             BOOKING DETAILS
                        ================================================== -->

                        <div class="details">


                            <!-- BOOKING ID -->

                            <div class="detail-row">

                                <span class="detail-label">

                                    Booking ID

                                </span>


                                <span class="detail-value">

                                    #<?php

                                    echo (int)
                                        $booking['id'];

                                    ?>

                                </span>


                            </div>



                            <!-- CUSTOMER -->

                            <div class="detail-row">

                                <span class="detail-label">

                                    Customer

                                </span>


                                <span class="detail-value">

                                    <?php

                                    echo htmlspecialchars(

                                        $booking['name']
                                        ?? '—'

                                    );

                                    ?>

                                </span>


                            </div>



                            <!-- EMAIL -->

                            <div class="detail-row">

                                <span class="detail-label">

                                    Email

                                </span>


                                <span class="detail-value">

                                    <?php

                                    echo htmlspecialchars(

                                        $booking['email']
                                        ?? '—'

                                    );

                                    ?>

                                </span>


                            </div>



                            <!-- PHONE -->

                            <div class="detail-row">

                                <span class="detail-label">

                                    Phone

                                </span>


                                <span class="detail-value">

                                    <?php

                                    echo htmlspecialchars(

                                        $booking['phone']
                                        ?? '—'

                                    );

                                    ?>

                                </span>


                            </div>



                            <!-- BOOKING DATE -->

                            <div class="detail-row">

                                <span class="detail-label">

                                    Booking Date

                                </span>


                                <span class="detail-value">


                                    <?php

                                    if (
                                        !empty(
                                            $booking[
                                                'booking_date'
                                            ]
                                        )
                                    ) {

                                        echo htmlspecialchars(

                                            date(

                                                "d M Y",

                                                strtotime(

                                                    $booking[
                                                        'booking_date'
                                                    ]

                                                )

                                            )

                                        );

                                    } else {

                                        echo "—";

                                    }

                                    ?>


                                </span>


                            </div>



                            <!-- BOOKING STATUS -->

                            <div class="detail-row">

                                <span class="detail-label">

                                    Booking Status

                                </span>


                                <span
                                    class="status <?php
                                    echo htmlspecialchars(
                                        $status_class
                                    );
                                    ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $status
                                    );

                                    ?>

                                </span>


                            </div>


                        </div>


                        <!-- =================================================
                             PAYMENT INFORMATION
                        ================================================== -->

                        <div class="payment-section">


                            <div class="payment-title">

                                Payment Information

                            </div>


                            <?php if ($payment_id > 0): ?>


                                <!-- PAYMENT METHOD -->

                                <div class="payment-row">

                                    <span class="payment-label">

                                        Payment Method

                                    </span>


                                    <span class="payment-value">

                                        <?php

                                        echo htmlspecialchars(
                                            $payment_method
                                            ?: "—"
                                        );

                                        ?>

                                    </span>

                                </div>


                                <!-- PAYMENT AMOUNT -->

                                <div class="payment-row">

                                    <span class="payment-label">

                                        Amount

                                    </span>


                                    <span
                                        class="payment-value payment-amount"
                                    >

                                        ₹<?php

                                        echo number_format(
                                            $payment_amount,
                                            2
                                        );

                                        ?>

                                    </span>

                                </div>


                                <!-- PAYMENT STATUS -->

                                <div class="payment-row">

                                    <span class="payment-label">

                                        Payment Status

                                    </span>


                                    <span class="payment-value">

                                        <span
                                            class="payment-status <?php
                                            echo htmlspecialchars(
                                                $payment_status_class
                                            );
                                            ?>"
                                        >

                                            <?php

                                            if (
                                                $payment_status ===
                                                "Pending"
                                            ) {

                                                echo "Payment Due at Pickup";

                                            } elseif (
                                                $payment_status !== ""
                                            ) {

                                                echo htmlspecialchars(
                                                    $payment_status
                                                );

                                            } else {

                                                echo "Pending";

                                            }

                                            ?>

                                        </span>

                                    </span>

                                </div>


                                <!-- PAYMENT REFERENCE -->

                                <div class="payment-row">

                                    <span class="payment-label">

                                        Payment Reference

                                    </span>


                                    <span class="payment-value">

                                        <?php

                                        echo htmlspecialchars(
                                            $order_reference
                                            ?: "—"
                                        );

                                        ?>

                                    </span>

                                </div>


                                <?php if ($transaction_id !== ""): ?>


                                    <!-- TRANSACTION ID -->

                                    <div class="payment-row">

                                        <span class="payment-label">

                                            Transaction ID

                                        </span>


                                        <span class="payment-value">

                                            <?php

                                            echo htmlspecialchars(
                                                $transaction_id
                                            );

                                            ?>

                                        </span>

                                    </div>


                                <?php endif; ?>


                                <!-- PAYMENT ID -->

                                <div class="payment-row">

                                    <span class="payment-label">

                                        Payment ID

                                    </span>


                                    <span class="payment-value">

                                        #<?php

                                        echo $payment_id;

                                        ?>

                                    </span>

                                </div>


                            <?php else: ?>


                                <!-- NO PAYMENT -->

                                <div class="payment-row">

                                    <span class="payment-label">

                                        Payment Status

                                    </span>


                                    <span class="payment-value">

                                        <span
                                            class="payment-status payment-none"
                                        >

                                            Not Paid Yet

                                        </span>

                                    </span>

                                </div>


                                <div class="payment-row">

                                    <span class="payment-label">

                                        Amount

                                    </span>


                                    <span
                                        class="payment-value payment-amount"
                                    >

                                        ₹<?php

                                        echo number_format(
                                            $price,
                                            2
                                        );

                                        ?>

                                    </span>

                                </div>


                            <?php endif; ?>


                        </div>


                        <!-- =================================================
                             VIEW BOOKING DETAILS
                        ================================================== -->

                        <a

                            href="booking-details.php?booking_id=<?php
                            echo (int)
                                $booking['id'];
                            ?>"

                            class="view-details-btn"

                        >

                            View Booked Details

                        </a>


                    </div>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


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