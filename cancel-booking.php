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

$user_id = (int) $_SESSION["user_id"];


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
$user_name  = $user["name"];

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

$booking_id = (int) $_GET["booking_id"];

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
        c.price,
        c.image

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


if ($result->num_rows !== 1) {

    $stmt->close();
    $conn->close();

    header("Location: my-bookings.php");
    exit();
}


$booking = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   BOOKING INFORMATION
   ========================================================= */

$brand = $booking["brand"] ?? "Car";

$model = $booking["model"] ?? "Vehicle";

$price = (float) ($booking["price"] ?? 0);

$status = trim(
    $booking["status"] ?? "Pending"
);


/* =========================================================
   CHECK STATUS
   ========================================================= */

$status_lower = strtolower($status);

$can_cancel = !in_array(
    $status_lower,
    [
        "cancelled",
        "completed",
        "rejected"
    ],
    true
);


/* =========================================================
   CANCEL BOOKING
   ========================================================= */

$error = "";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["cancel_booking"])
) {

    if (!$can_cancel) {

        $error =
            "This booking cannot be cancelled because its status is "
            . htmlspecialchars($status);

    } else {

        /*
           IMPORTANT:
           The booking can only be cancelled if its email belongs
           to the currently logged-in user.
        */

        $cancel_stmt = $conn->prepare(
            "UPDATE bookings

             SET status = 'Cancelled'

             WHERE id = ?

               AND LOWER(TRIM(email))
                   = LOWER(TRIM(?))

               AND LOWER(TRIM(status))
                   NOT IN (
                       'cancelled',
                       'completed',
                       'rejected'
                   )"
        );

        if (!$cancel_stmt) {

            $error = "Unable to cancel booking.";

        } else {

            $cancel_stmt->bind_param(
                "is",
                $booking_id,
                $user_email
            );

            $cancel_stmt->execute();


            if ($cancel_stmt->affected_rows > 0) {

                $cancel_stmt->close();
                $conn->close();

                /*
                   Return to My Bookings after successful cancellation.
                */

                header(
                    "Location: my-bookings.php?cancelled=1"
                );

                exit();

            } else {

                $error =
                    "The booking could not be cancelled.";

            }

            $cancel_stmt->close();

        }

    }

}


/* =========================================================
   IMAGE
   ========================================================= */

$database_image = trim(
    $booking["image"] ?? ""
);

$car_image = "";


/*
   External image URL
*/

if (
    $database_image !== "" &&
    (
        strpos($database_image, "http://") === 0 ||
        strpos($database_image, "https://") === 0
    )
) {

    $car_image = $database_image;

}


/*
   Local image
*/

elseif ($database_image !== "") {

    $possible_images = [

        $database_image,

        "images/" . $database_image,

        "uploads/" . $database_image,

        "assets/images/" . $database_image

    ];


    foreach ($possible_images as $possible_image) {

        if (
            file_exists(
                __DIR__ . "/" . $possible_image
            )
        ) {

            $car_image = $possible_image;

            break;
        }

    }


    if ($car_image === "") {

        $car_image = $database_image;

    }

}


/*
   Fallback image
*/

if ($car_image === "") {

    $brand_lower = strtolower($brand);


    if (
        strpos($brand_lower, "audi") !== false
    ) {

        $car_image =
            "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1400&q=85";

    } elseif (
        strpos($brand_lower, "bmw") !== false
    ) {

        $car_image =
            "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1400&q=85";

    } elseif (
        strpos($brand_lower, "mercedes") !== false
    ) {

        $car_image =
            "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1400&q=85";

    } else {

        $car_image =
            "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1400&q=85";

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
    Cancel Booking | CarHub
</title>


<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

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

    gap: 25px;

}


.nav-links a {

    color: white;

    text-decoration: none;

}


.nav-links a:hover {

    color: #ef3340;

}


/* =========================================================
   CONTAINER
   ========================================================= */

.container {

    width: 90%;

    max-width: 850px;

    margin: 55px auto;

}


/* =========================================================
   CARD
   ========================================================= */

.cancel-card {

    background: white;

    border-radius: 20px;

    overflow: hidden;

    box-shadow:
        0 12px 35px rgba(0,0,0,.09);

}


/* =========================================================
   IMAGE
   ========================================================= */

.car-image {

    width: 100%;

    height: 330px;

    object-fit: cover;

    display: block;

}


/* =========================================================
   CONTENT
   ========================================================= */

.content {

    padding: 35px;

}


.content h1 {

    font-size: 34px;

    margin-bottom: 12px;

}


.content h1 span {

    color: #ef3340;

}


.subtitle {

    color: #667085;

    font-size: 17px;

    line-height: 1.6;

    margin-bottom: 30px;

}


/* =========================================================
   CAR DETAILS
   ========================================================= */

.car-name {

    font-size: 26px;

    margin-bottom: 8px;

}


.price {

    color: #ef3340;

    font-size: 23px;

    font-weight: bold;

    margin-bottom: 25px;

}


.booking-info {

    border-top: 1px solid #e5e7eb;

    padding-top: 20px;

    margin-bottom: 30px;

}


.row {

    display: flex;

    justify-content: space-between;

    gap: 20px;

    padding: 11px 0;

}


.label {

    color: #667085;

}


.value {

    font-weight: bold;

    text-align: right;

}


/* =========================================================
   ERROR
   ========================================================= */

.error {

    background: #ffe5e5;

    color: #b42318;

    padding: 15px 18px;

    border-radius: 10px;

    margin-bottom: 25px;

    line-height: 1.5;

}


/* =========================================================
   WARNING
   ========================================================= */

.warning {

    background: #fff4d6;

    color: #8a5700;

    padding: 18px;

    border-radius: 10px;

    margin-bottom: 28px;

    line-height: 1.6;

}


/* =========================================================
   BUTTONS
   ========================================================= */

.buttons {

    display: flex;

    gap: 15px;

}


.btn {

    flex: 1;

    border: none;

    padding: 16px;

    border-radius: 10px;

    text-align: center;

    text-decoration: none;

    font-size: 16px;

    font-weight: bold;

    cursor: pointer;

}


.back-btn {

    background: #111827;

    color: white;

}


.back-btn:hover {

    background: #000000;

}


.cancel-btn {

    background: #ef3340;

    color: white;

}


.cancel-btn:hover {

    background: #d92835;

}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 650px) {

    .navbar {

        padding: 0 5%;

    }


    .nav-links {

        gap: 12px;

    }


    .nav-links a {

        font-size: 13px;

    }


    .container {

        width: 94%;

        margin: 30px auto;

    }


    .car-image {

        height: 250px;

    }


    .content {

        padding: 25px;

    }


    .content h1 {

        font-size: 29px;

    }


    .row {

        flex-direction: column;

        gap: 5px;

    }


    .value {

        text-align: left;

    }


    .buttons {

        flex-direction: column;

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

    <div class="cancel-card">


        <!-- CAR IMAGE -->

        <img
            src="<?php echo htmlspecialchars($car_image); ?>"
            alt="<?php echo htmlspecialchars($brand . " " . $model); ?>"
            class="car-image"

            onerror="
                this.onerror=null;
                this.src='https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1400&q=85';
            "
        >


        <div class="content">


            <h1>

                Cancel <span>Booking</span>

            </h1>


            <p class="subtitle">

                Are you sure you want to cancel this
                vehicle booking?

            </p>


            <?php if ($error !== ""): ?>

                <div class="error">

                    <?php
                    echo $error;
                    ?>

                </div>

            <?php endif; ?>


            <?php if ($can_cancel && $error === ""): ?>

                <div class="warning">

                    <strong>Important:</strong>

                    Cancelling this booking will change
                    its status to

                    <strong>Cancelled</strong>.

                </div>

            <?php endif; ?>


            <!-- CAR -->

            <h2 class="car-name">

                <?php
                echo htmlspecialchars($brand);
                ?>

                <?php
                echo htmlspecialchars($model);
                ?>

            </h2>


            <div class="price">

                ₹<?php
                echo number_format($price);
                ?>

            </div>


            <!-- BOOKING INFO -->

            <div class="booking-info">


                <div class="row">

                    <span class="label">
                        Booking ID
                    </span>

                    <span class="value">

                        #
                        <?php
                        echo (int)$booking["id"];
                        ?>

                    </span>

                </div>


                <div class="row">

                    <span class="label">
                        Customer
                    </span>

                    <span class="value">

                        <?php
                        echo htmlspecialchars(
                            $booking["name"] ?? $user_name
                        );
                        ?>

                    </span>

                </div>


                <div class="row">

                    <span class="label">
                        Email
                    </span>

                    <span class="value">

                        <?php
                        echo htmlspecialchars(
                            $booking["email"]
                        );
                        ?>

                    </span>

                </div>


                <div class="row">

                    <span class="label">
                        Booking Date
                    </span>

                    <span class="value">

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

                    </span>

                </div>


                <div class="row">

                    <span class="label">
                        Current Status
                    </span>

                    <span class="value">

                        <?php
                        echo htmlspecialchars($status);
                        ?>

                    </span>

                </div>


            </div>


            <!-- =================================================
                 BUTTONS
                 ================================================= -->

            <div class="buttons">


                <a
                    href="my-bookings.php"
                    class="btn back-btn"
                >
                    ← Keep Booking
                </a>


                <?php if ($can_cancel): ?>

                    <form
                        method="POST"
                        style="flex: 1;"
                        onsubmit="
                            return confirm(
                                'Are you sure you want to cancel this booking?'
                            );
                        "
                    >

                        <input
                            type="hidden"
                            name="cancel_booking"
                            value="1"
                        >

                        <button
                            type="submit"
                            class="btn cancel-btn"
                            style="width: 100%;"
                        >
                            Confirm Cancel
                        </button>

                    </form>

                <?php endif; ?>


            </div>


        </div>

    </div>

</div>


</body>

</html>