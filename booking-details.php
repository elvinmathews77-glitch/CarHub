<?php

session_start();

require_once "config/db.php";


/* =========================================================
   1. LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION["user_id"]) ||
    empty($_SESSION["user_id"])
) {

    header("Location: login.php");
    exit();

}

$user_id = (int)$_SESSION["user_id"];


/* =========================================================
   2. GET LOGGED-IN USER
========================================================= */

$user_stmt = $conn->prepare("
    SELECT id, name, email
    FROM users
    WHERE id = ?
    LIMIT 1
");

if (!$user_stmt) {
    die("Unable to load user.");
}

$user_stmt->bind_param("i", $user_id);

if (!$user_stmt->execute()) {
    die("Unable to load user: " . $user_stmt->error);
}

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
   3. GET BOOKING ID
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
   4. GET BOOKING + CAR + PAYMENT
========================================================= */

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

        /* PAYMENT INFORMATION */

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

    WHERE b.id = ?
      AND LOWER(TRIM(b.email)) =
          LOWER(TRIM(?))

    LIMIT 1
");

if (!$stmt) {
    die(
        "Unable to load booking: "
        . $conn->error
    );
}

$stmt->bind_param(
    "is",
    $booking_id,
    $user_email
);

if (!$stmt->execute()) {
    die(
        "Unable to load booking: "
        . $stmt->error
    );
}

$result = $stmt->get_result();


/* =========================================================
   5. BOOKING NOT FOUND
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
   6. BOOKING DATA
========================================================= */

$brand =
    $booking["brand"] ?? "Car";

$model =
    $booking["model"] ?? "Vehicle";

$price =
    (float)(
        $booking["price"] ?? 0
    );

$status =
    trim(
        $booking["status"] ?? "Pending"
    );

$car_id =
    (int)(
        $booking["car_id"] ?? 0
    );


/* =========================================================
   7. PAYMENT DATA
========================================================= */

$payment_id =
    !empty($booking["payment_id"])
    ? (int)$booking["payment_id"]
    : 0;

$payment_method =
    trim(
        $booking["payment_method"] ?? ""
    );

$order_reference =
    trim(
        $booking["order_reference"] ?? ""
    );

$transaction_id =
    trim(
        $booking["transaction_id"] ?? ""
    );

$payment_amount =
    isset($booking["payment_amount"])
    ? (float)$booking["payment_amount"]
    : 0;

$payment_status =
    trim(
        $booking["payment_status"] ?? ""
    );


/* =========================================================
   8. STATUS CLASS
========================================================= */

$status_class =
    strtolower($status);

$status_class =
    preg_replace(
        "/[^a-z0-9]+/",
        "-",
        $status_class
    );


/* =========================================================
   9. PAYMENT STATUS CLASS
========================================================= */

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


/* =========================================================
   10. CHECK IF BOOKING CAN BE CANCELLED
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
   11. CAR IMAGE
========================================================= */

$db_image =
    trim(
        $booking["image"] ?? ""
    );

$car_image = "";

$brand_lower =
    strtolower(
        trim($brand)
    );


/* Database image */

if ($db_image !== "") {

    if (
        filter_var(
            $db_image,
            FILTER_VALIDATE_URL
        )
    ) {

        $car_image = $db_image;

    } else {

        $possible_paths = [

            $db_image,

            "uploads/" . $db_image,

            "images/" . $db_image,

            "assets/images/" . $db_image

        ];


        foreach (
            $possible_paths as $path
        ) {

            if (
                file_exists(
                    __DIR__ . "/" . $path
                ) &&
                is_file(
                    __DIR__ . "/" . $path
                )
            ) {

                $car_image = $path;

                break;

            }

        }

    }

}


/* Fallback images */

if ($car_image === "") {

    if (
        strpos(
            $brand_lower,
            "mercedes"
        ) !== false
    ) {

        $car_image =
            "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85";

    } elseif (
        strpos(
            $brand_lower,
            "audi"
        ) !== false
    ) {

        $car_image =
            "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85";

    } elseif (
        strpos(
            $brand_lower,
            "bmw"
        ) !== false
    ) {

        $car_image =
            "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85";

    } else {

        $car_image =
            "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85";

    }

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

    min-height: 72px;

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

    text-decoration: none;

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
        0 12px 35px
        rgba(0,0,0,.08);

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
        repeat(
            2,
            minmax(0, 1fr)
        );

    gap: 18px;

    border-top:
        1px solid #e5e7eb;

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
   BOOKING STATUS
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
   PAYMENT SECTION
========================================================= */

.payment-section {

    margin-top: 30px;

    padding-top: 25px;

    border-top:
        1px solid #e5e7eb;

}


.payment-title {

    font-size: 24px;

    font-weight: bold;

    margin-bottom: 18px;

}


.payment-grid {

    display: grid;

    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );

    gap: 18px;

}


.payment-box {

    background: #f8f9fb;

    border-radius: 12px;

    padding: 18px;

}


.payment-label {

    color: #667085;

    font-size: 14px;

    margin-bottom: 7px;

}


.payment-value {

    color: #172033;

    font-size: 16px;

    font-weight: bold;

    word-break: break-word;

}


.payment-amount {

    color: #ef3340;

    font-size: 20px;

}


/* =========================================================
   PAYMENT STATUS
========================================================= */

.payment-status {

    display: inline-block;

    padding: 9px 15px;

    border-radius: 20px;

    font-size: 13px;

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
   NOT PAID MESSAGE
========================================================= */

.not-paid-message {

    margin-top: 18px;

    padding: 15px 18px;

    background: #fff8e5;

    color: #856404;

    border-radius: 10px;

    line-height: 1.5;

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


/* Back */

.back-btn {

    background: #111827;

    color: white;

}


.back-btn:hover {

    background: #000000;

}


/* Car details */

.car-btn {

    background: #e8ebef;

    color: #172033;

}


.car-btn:hover {

    background: #d8dce2;

}


/* Cancel */

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

        padding: 18px 5%;

        flex-direction: column;

        gap: 18px;

        height: auto;

    }


    .nav-links {

        gap: 12px;

        flex-wrap: wrap;

        justify-content: center;

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


    .payment-grid {

        grid-template-columns: 1fr;

    }


    .buttons {

        flex-direction: column;

    }

}


@media (max-width: 550px) {

    .container {

        width: 94%;

        margin-top: 30px;

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


    <div class="page-title">

        <h1>
            Booking <span>Details</span>
        </h1>

        <p>
            Review your vehicle booking and payment information.
        </p>

    </div>


    <!-- =================================================
         BOOKING CARD
    ================================================== -->

    <div class="booking-card">


        <!-- CAR IMAGE -->

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


        <!-- CONTENT -->

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
            ================================================== -->

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
                            $booking["name"]
                            ?? $user_name
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
                            $booking["email"]
                            ?? $user_email
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
                            $booking["phone"]
                            ?? "—"
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


                <!-- BOOKING STATUS -->

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
                 PAYMENT INFORMATION
            ================================================== -->

            <div class="payment-section">

                <div class="payment-title">
                    Payment Information
                </div>


                <?php if ($payment_id > 0): ?>


                    <div class="payment-grid">


                        <!-- PAYMENT ID -->

                        <div class="payment-box">

                            <div class="payment-label">
                                Payment ID
                            </div>

                            <div class="payment-value">

                                #

                                <?php
                                echo $payment_id;
                                ?>

                            </div>

                        </div>


                        <!-- PAYMENT METHOD -->

                        <div class="payment-box">

                            <div class="payment-label">
                                Payment Method
                            </div>

                            <div class="payment-value">

                                <?php
                                echo htmlspecialchars(
                                    $payment_method
                                    ?: "—"
                                );
                                ?>

                            </div>

                        </div>


                        <!-- PAYMENT AMOUNT -->

                        <div class="payment-box">

                            <div class="payment-label">
                                Payment Amount
                            </div>

                            <div class="payment-value payment-amount">

                                ₹<?php
                                echo number_format(
                                    $payment_amount,
                                    2
                                );
                                ?>

                            </div>

                        </div>


                        <!-- PAYMENT STATUS -->

                        <div class="payment-box">

                            <div class="payment-label">
                                Payment Status
                            </div>

                            <div class="payment-value">

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

                            </div>

                        </div>


                        <!-- PAYMENT REFERENCE -->

                        <div class="payment-box">

                            <div class="payment-label">
                                Payment Reference
                            </div>

                            <div class="payment-value">

                                <?php
                                echo htmlspecialchars(
                                    $order_reference
                                    ?: "—"
                                );
                                ?>

                            </div>

                        </div>


                        <!-- TRANSACTION ID -->

                        <div class="payment-box">

                            <div class="payment-label">
                                Transaction ID
                            </div>

                            <div class="payment-value">

                                <?php

                                if (
                                    $transaction_id !== ""
                                ) {

                                    echo htmlspecialchars(
                                        $transaction_id
                                    );

                                } else {

                                    echo "Not available";

                                }

                                ?>

                            </div>

                        </div>


                        <!-- PAYMENT DATE -->

                        <div class="payment-box">

                            <div class="payment-label">
                                Payment Record Date
                            </div>

                            <div class="payment-value">

                                <?php

                                if (
                                    !empty(
                                        $booking[
                                            "payment_created_at"
                                        ]
                                    )
                                ) {

                                    echo htmlspecialchars(
                                        date(
                                            "d M Y, h:i A",
                                            strtotime(
                                                $booking[
                                                    "payment_created_at"
                                                ]
                                            )
                                        )
                                    );

                                } else {

                                    echo "—";

                                }

                                ?>

                            </div>

                        </div>


                    </div>


                    <?php if ($payment_status === "Pending"): ?>

                        <div class="not-paid-message">

                            <strong>
                                Cash on Pickup
                            </strong>

                            <br>

                            Your booking has been recorded.
                            Payment will be collected when
                            you pick up the vehicle.

                        </div>

                    <?php elseif (
                        $payment_status === "Test Successful"
                    ): ?>

                        <div class="not-paid-message"
                             style="
                                background:#e8f8ed;
                                color:#16733b;
                             ">

                            <strong>
                                Payment Recorded
                            </strong>

                            <br>

                            Your payment record has been
                            successfully stored in the
                            CarHub database.

                        </div>

                    <?php endif; ?>


                <?php else: ?>


                    <!-- NO PAYMENT RECORD -->

                    <div class="payment-grid">


                        <div class="payment-box">

                            <div class="payment-label">
                                Payment Status
                            </div>

                            <div class="payment-value">

                                <span class="payment-status payment-none">
                                    Not Paid Yet
                                </span>

                            </div>

                        </div>


                        <div class="payment-box">

                            <div class="payment-label">
                                Amount Due
                            </div>

                            <div class="payment-value payment-amount">

                                ₹<?php
                                echo number_format(
                                    $price,
                                    2
                                );
                                ?>

                            </div>

                        </div>


                    </div>


                    <div class="not-paid-message">

                        <strong>
                            Payment Pending
                        </strong>

                        <br>

                        No payment record has been created
                        for this booking yet.

                    </div>


                    <!-- PROCEED TO PAYMENT -->

                    <a
                        href="payment.php?booking_id=<?php echo (int)$booking["id"]; ?>"
                        class="btn cancel-btn"
                        style="
                            margin-top:18px;
                            width:100%;
                        "
                    >
                        Proceed to Payment
                    </a>


                <?php endif; ?>


            </div>


            <!-- =================================================
                 ACTION BUTTONS
            ================================================== -->

            <div class="buttons">


                <!-- BACK -->

                <a
                    href="my-bookings.php"
                    class="btn back-btn"
                >
                    ← Back to My Bookings
                </a>


                <!-- VIEW CAR -->

                <?php if ($car_id > 0): ?>

                    <a
                        href="car-details.php?id=<?php echo $car_id; ?>"
                        class="btn car-btn"
                    >
                        View Car Details
                    </a>

                <?php endif; ?>


                <!-- CANCEL -->

                <?php if ($can_cancel): ?>

                    <a
                        href="cancel-booking.php?booking_id=<?php echo (int)$booking["id"]; ?>"
                        class="btn cancel-btn"
                        onclick="return confirm('Are you sure you want to cancel this booking?');"
                    >
                        Cancel Booking
                    </a>

                <?php endif; ?>


            </div>


            <!-- NON-CANCELLABLE MESSAGE -->

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