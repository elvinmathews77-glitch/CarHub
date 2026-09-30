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
   BOOKING ID
===================================================== */

$booking_id = isset($_GET["booking_id"])
    ? (int) $_GET["booking_id"]
    : 0;

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
        c.price,
        c.image

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


/* =====================================================
   PRICE
===================================================== */

$price = (float) $booking["price"];


/* =====================================================
   IMAGE
===================================================== */

$image = trim((string) $booking["image"]);

$brand = strtolower(
    trim((string) $booking["brand"])
);

$brandImages = [

    "bmw" =>
        "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1000&q=80",

    "mercedes" =>
        "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1000&q=80",

    "audi" =>
        "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1000&q=80",

    "toyota" =>
        "https://images.unsplash.com/photo-1623869675781-80aa31012a5a?auto=format&fit=crop&w=1000&q=80",

    "ford" =>
        "https://images.unsplash.com/photo-1584345604476-8ec5e12e42dd?auto=format&fit=crop&w=1000&q=80",

    "honda" =>
        "https://images.unsplash.com/photo-1606611013016-969c19ba27bb?auto=format&fit=crop&w=1000&q=80"
];

if ($image === "") {

    if (isset($brandImages[$brand])) {
        $image = $brandImages[$brand];
    } else {
        $image =
            "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1000&q=80";
    }
}


/* =====================================================
   FORM PROCESSING
===================================================== */

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $payment_method =
        $_POST["payment_method"] ?? "";


    /* =================================================
       CARD
    ================================================= */

    if ($payment_method === "card") {

        $card_number = preg_replace(
            '/\D/',
            '',
            $_POST["card_number"] ?? ""
        );

        $expiry =
            trim($_POST["expiry"] ?? "");

        $cvv = preg_replace(
            '/\D/',
            '',
            $_POST["cvv"] ?? ""
        );


        if (
            strlen($card_number) < 12 ||
            strlen($card_number) > 19
        ) {

            $error =
                "Please enter a valid card number.";

        } elseif (
            !preg_match(
                '/^(0[1-9]|1[0-2])\/([0-9]{2})$/',
                $expiry
            )
        ) {

            $error =
                "Please enter the expiry date in MM/YY format.";

        } elseif (
            strlen($cvv) < 3 ||
            strlen($cvv) > 4
        ) {

            $error =
                "Please enter a valid CVV.";

        } else {

            header(
                "Location: payment-processing.php?booking_id=" .
                urlencode($booking_id) .
                "&method=card"
            );

            exit();
        }
    }


    /* =================================================
       UPI
    ================================================= */

    elseif ($payment_method === "upi") {

        $upi_id =
            trim($_POST["upi_id"] ?? "");


        if (
            !preg_match(
                '/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+$/',
                $upi_id
            )
        ) {

            $error =
                "Please enter a valid UPI ID.";

        } else {

            header(
                "Location: payment-processing.php?booking_id=" .
                urlencode($booking_id) .
                "&method=upi"
            );

            exit();
        }
    }


    /* =================================================
       CASH ON PICKUP
    ================================================= */

    elseif ($payment_method === "cash") {

        header(
            "Location: payment-processing.php?booking_id=" .
            urlencode($booking_id) .
            "&method=cash"
        );

        exit();
    }


    else {

        $error =
            "Please select a payment method.";
    }
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

<title>Secure Checkout - CarHub</title>

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


/* =====================================================
   NAVBAR
===================================================== */

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
    align-items: center;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
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
    max-width: 1150px;
    margin: 40px auto 60px;
}

.page-title {
    text-align: center;
    margin-bottom: 30px;
}

.page-title h1 {
    font-size: 34px;
    margin-bottom: 8px;
}

.page-title p {
    color: #666;
}


/* =====================================================
   LAYOUT
===================================================== */

.payment-layout {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 25px;
    align-items: start;
}

.card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
}

.card h2 {
    margin-bottom: 20px;
    font-size: 22px;
}


/* =====================================================
   CAR
===================================================== */

.car-image {
    width: 100%;
    height: 230px;
    object-fit: cover;
    border-radius: 10px;
    display: block;
    margin-bottom: 18px;
}

.car-name {
    font-size: 25px;
    font-weight: bold;
    margin-bottom: 8px;
}

.car-info {
    color: #666;
    margin-bottom: 20px;
}


/* =====================================================
   DETAILS
===================================================== */

.info-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 12px 0;
    border-bottom: 1px solid #eee;
}

.info-label {
    color: #777;
    font-size: 14px;
}

.info-value {
    font-weight: bold;
    text-align: right;
    font-size: 14px;
}

.price {
    color: #e50914;
    font-size: 29px;
    font-weight: bold;
    margin-top: 20px;
}


/* =====================================================
   ERROR
===================================================== */

.error {
    background: #ffe5e5;
    border: 1px solid #ffb3b3;
    color: #b00000;
    padding: 13px;
    border-radius: 7px;
    margin-bottom: 20px;
    text-align: center;
}


/* =====================================================
   PAYMENT METHODS
===================================================== */

.payment-methods {
    display: flex;
    gap: 10px;
    margin-bottom: 25px;
}

.method-label {
    flex: 1;
    border: 2px solid #ddd;
    border-radius: 9px;
    padding: 15px 8px;
    text-align: center;
    cursor: pointer;
    font-weight: bold;
    transition: 0.3s;
}

.method-label:hover {
    border-color: #e50914;
}

.method-label input {
    display: none;
}

.method-label.active {
    border-color: #e50914;
    background: #fff5f5;
    color: #e50914;
}


/* =====================================================
   PAYMENT SECTIONS
===================================================== */

.payment-section {
    display: none;
}

.payment-section.active {
    display: block;
}


/* =====================================================
   FORM
===================================================== */

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 7px;
    font-weight: bold;
    font-size: 14px;
}

.form-group input {
    width: 100%;
    padding: 13px;
    border: 1px solid #ccc;
    border-radius: 7px;
    font-size: 15px;
    outline: none;
}

.form-group input:focus {
    border-color: #e50914;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}


/* =====================================================
   CASH
===================================================== */

.cash-box {
    background: #f7f7f7;
    border-left: 4px solid #e50914;
    padding: 18px;
    border-radius: 7px;
    line-height: 1.6;
    margin-bottom: 20px;
}


/* =====================================================
   BUTTON
===================================================== */

.pay-btn {
    width: 100%;
    border: none;
    background: #e50914;
    color: white;
    padding: 15px;
    border-radius: 8px;
    font-size: 17px;
    font-weight: bold;
    cursor: pointer;
}

.pay-btn:hover {
    background: #b80710;
}

.back-btn {
    display: block;
    text-align: center;
    margin-top: 15px;
    padding: 13px;
    border-radius: 8px;
    background: #eee;
    color: #333;
    text-decoration: none;
}

.back-btn:hover {
    background: #ddd;
}


/* =====================================================
   SECURITY
===================================================== */

.security {
    text-align: center;
    color: #777;
    font-size: 12px;
    line-height: 1.5;
    margin-top: 18px;
}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 850px) {

    .navbar {
        flex-direction: column;
        text-align: center;
    }

    .payment-layout {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 550px) {

    .container {
        width: 94%;
    }

    .payment-methods {
        flex-direction: column;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .info-row {
        flex-direction: column;
        gap: 5px;
    }

    .info-value {
        text-align: left;
    }

    .page-title h1 {
        font-size: 27px;
    }
}

</style>

</head>

<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar">

    <a href="index.php" class="logo">
        CarHub
    </a>

    <div class="nav-links">

        <a href="index.php">Home</a>

        <a href="cars.php">Cars</a>

        <a href="about.php">About</a>

        <a href="contact.php">Contact</a>

        <a href="my-bookings.php">My Bookings</a>

        <a href="my-test-drives.php">My Test Drives</a>

        <a href="logout.php">Logout</a>

    </div>

</nav>


<!-- =====================================================
     CONTENT
===================================================== -->

<div class="container">

    <div class="page-title">

        <h1>
            Secure Checkout
        </h1>

        <p>
            Choose your preferred payment method
        </p>

    </div>


    <?php if ($error !== ""): ?>

        <div class="error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <div class="payment-layout">


        <!-- =================================================
             BOOKING SUMMARY
        ================================================= -->

        <div class="card">

            <h2>
                Booking Summary
            </h2>

            <img
                src="<?php echo htmlspecialchars($image); ?>"
                alt="<?php echo htmlspecialchars($booking["car_name"]); ?>"
                class="car-image"
                onerror="this.src='https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1000&q=80';"
            >


            <div class="car-name">

                <?php
                echo htmlspecialchars(
                    $booking["car_name"]
                );
                ?>

            </div>


            <div class="car-info">

                <?php
                echo htmlspecialchars(
                    $booking["brand"]
                );
                ?>

                <?php if (!empty($booking["model"])): ?>

                    •
                    <?php
                    echo htmlspecialchars(
                        $booking["model"]
                    );
                    ?>

                <?php endif; ?>

                <?php if (!empty($booking["year"])): ?>

                    •
                    <?php
                    echo htmlspecialchars(
                        $booking["year"]
                    );
                    ?>

                <?php endif; ?>

            </div>


            <div class="info-row">

                <span class="info-label">
                    Booking ID
                </span>

                <span class="info-value">

                    #
                    <?php
                    echo htmlspecialchars(
                        $booking["booking_id"]
                    );
                    ?>

                </span>

            </div>


            <div class="info-row">

                <span class="info-label">
                    Customer
                </span>

                <span class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $booking["customer_name"]
                    );
                    ?>

                </span>

            </div>


            <div class="info-row">

                <span class="info-label">
                    Booking Date
                </span>

                <span class="info-value">

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


            <div class="info-row">

                <span class="info-label">
                    Booking Status
                </span>

                <span class="info-value">

                    <?php
                    echo htmlspecialchars(
                        $booking["booking_status"]
                    );
                    ?>

                </span>

            </div>


            <div class="price">

                ₹<?php
                echo number_format(
                    $price,
                    2
                );
                ?>

            </div>

        </div>


        <!-- =================================================
             PAYMENT
        ================================================= -->

        <div class="card">

            <h2>
                Payment Method
            </h2>


            <form
                method="POST"
                action=""
                id="paymentForm"
            >


                <div class="payment-methods">


                    <label
                        class="method-label active"
                        id="cardLabel"
                    >

                        <input
                            type="radio"
                            name="payment_method"
                            value="card"
                            checked
                        >

                        💳 Card

                    </label>


                    <label
                        class="method-label"
                        id="upiLabel"
                    >

                        <input
                            type="radio"
                            name="payment_method"
                            value="upi"
                        >

                        📱 UPI

                    </label>


                    <label
                        class="method-label"
                        id="cashLabel"
                    >

                        <input
                            type="radio"
                            name="payment_method"
                            value="cash"
                        >

                        💵 Cash

                    </label>

                </div>


                <!-- CARD -->

                <div
                    class="payment-section active"
                    id="cardSection"
                >

                    <div class="form-group">

                        <label>
                            Card Number
                        </label>

                        <input
                            type="text"
                            name="card_number"
                            id="cardNumber"
                            placeholder="1234 5678 9012 3456"
                            maxlength="19"
                            inputmode="numeric"
                            autocomplete="off"
                        >

                    </div>


                    <div class="form-row">

                        <div class="form-group">

                            <label>
                                Expiry Date
                            </label>

                            <input
                                type="text"
                                name="expiry"
                                id="expiry"
                                placeholder="MM/YY"
                                maxlength="5"
                                autocomplete="off"
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                CVV
                            </label>

                            <input
                                type="password"
                                name="cvv"
                                id="cvv"
                                placeholder="CVV"
                                maxlength="4"
                                inputmode="numeric"
                                autocomplete="off"
                            >

                        </div>

                    </div>


                    <button
                        type="submit"
                        class="pay-btn"
                    >

                        Proceed to Payment

                    </button>

                </div>


                <!-- UPI -->

                <div
                    class="payment-section"
                    id="upiSection"
                >

                    <div class="form-group">

                        <label>
                            UPI ID
                        </label>

                        <input
                            type="text"
                            name="upi_id"
                            id="upiId"
                            placeholder="example@upi"
                            autocomplete="off"
                        >

                    </div>


                    <button
                        type="submit"
                        class="pay-btn"
                    >

                        Proceed to Payment

                    </button>

                </div>


                <!-- CASH -->

                <div
                    class="payment-section"
                    id="cashSection"
                >

                    <div class="cash-box">

                        <strong>
                            Cash on Pickup
                        </strong>

                        <br><br>

                        Pay the amount at the showroom
                        when you collect the vehicle.

                        <br><br>

                        Click the button below to confirm
                        your booking.

                    </div>


                    <button
                        type="submit"
                        class="pay-btn"
                    >

                        Confirm Booking

                    </button>

                </div>


            </form>


            <a
                href="booking-details.php?booking_id=<?php echo urlencode($booking_id); ?>"
                class="back-btn"
            >
                Back to Booking Details
            </a>


            <div class="security">

                🔒 Card and CVV information is not stored
                in the CarHub database.

            </div>

        </div>

    </div>

</div>


<script>

/* =====================================================
   PAYMENT METHOD SWITCHING
===================================================== */

const cardLabel =
    document.getElementById("cardLabel");

const upiLabel =
    document.getElementById("upiLabel");

const cashLabel =
    document.getElementById("cashLabel");


const cardSection =
    document.getElementById("cardSection");

const upiSection =
    document.getElementById("upiSection");

const cashSection =
    document.getElementById("cashSection");


const radios =
    document.querySelectorAll(
        'input[name="payment_method"]'
    );


radios.forEach(function(radio) {

    radio.addEventListener(
        "change",
        function() {

            cardLabel.classList.remove("active");
            upiLabel.classList.remove("active");
            cashLabel.classList.remove("active");

            cardSection.classList.remove("active");
            upiSection.classList.remove("active");
            cashSection.classList.remove("active");


            if (this.value === "card") {

                cardLabel.classList.add("active");

                cardSection.classList.add("active");

            }

            else if (this.value === "upi") {

                upiLabel.classList.add("active");

                upiSection.classList.add("active");

            }

            else if (this.value === "cash") {

                cashLabel.classList.add("active");

                cashSection.classList.add("active");

            }

        }
    );

});


/* =====================================================
   CARD NUMBER FORMAT
===================================================== */

const cardNumber =
    document.getElementById("cardNumber");


cardNumber.addEventListener(
    "input",
    function() {

        let value =
            this.value.replace(/\D/g, "");

        value =
            value.substring(0, 19);

        let formatted = "";

        for (
            let i = 0;
            i < value.length;
            i++
        ) {

            if (
                i > 0 &&
                i % 4 === 0
            ) {

                formatted += " ";

            }

            formatted += value[i];

        }

        this.value = formatted;

    }
);


/* =====================================================
   EXPIRY FORMAT
===================================================== */

const expiry =
    document.getElementById("expiry");


expiry.addEventListener(
    "input",
    function() {

        let value =
            this.value.replace(/\D/g, "");

        value =
            value.substring(0, 4);

        if (value.length > 2) {

            this.value =
                value.substring(0, 2) +
                "/" +
                value.substring(2);

        } else {

            this.value = value;

        }

    }
);


/* =====================================================
   CVV
===================================================== */

const cvv =
    document.getElementById("cvv");


cvv.addEventListener(
    "input",
    function() {

        this.value =
            this.value
            .replace(/\D/g, "")
            .substring(0, 4);

    }
);

</script>

</body>

</html>