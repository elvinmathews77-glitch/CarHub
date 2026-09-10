<?php
session_start();

/* =========================================================
   CARHUB - BOOKING PAGE
   Only logged-in users can book a vehicle.
   ========================================================= */


/* =========================================================
   1. CHECK LOGIN
   ========================================================= */

/*
   This accepts the common session names used by the project.
   If any one exists, the user is considered logged in.
*/
$logged_in = false;

if (
    isset($_SESSION['user_id']) ||
    isset($_SESSION['id']) ||
    isset($_SESSION['user']) ||
    isset($_SESSION['email'])
) {
    $logged_in = true;
}

/*
   User must be logged in to book.
*/
if (!$logged_in) {
    header("Location: login.php?redirect=" . urlencode($_SERVER['REQUEST_URI']));
    exit;
}


/* =========================================================
   2. DATABASE CONNECTION
   ========================================================= */

$host = "localhost";
$db_user = "root";
$db_password = "";
$db_name = "carhub";

$conn = new mysqli(
    $host,
    $db_user,
    $db_password,
    $db_name
);

if ($conn->connect_error) {
    die("Database connection failed: " . htmlspecialchars($conn->connect_error));
}

$conn->set_charset("utf8mb4");


/* =========================================================
   3. GET CAR ID
   ========================================================= */

$car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;

if ($car_id <= 0) {
    die("Invalid car selected.");
}


/* =========================================================
   4. GET CAR DETAILS
   ========================================================= */

$stmt = $conn->prepare("
    SELECT *
    FROM cars
    WHERE id = ?
    LIMIT 1
");

if (!$stmt) {
    die("Unable to prepare car query.");
}

$stmt->bind_param("i", $car_id);
$stmt->execute();

$result = $stmt->get_result();
$car = $result->fetch_assoc();

$stmt->close();

if (!$car) {
    die("Car not found.");
}


/* =========================================================
   5. CAR INFORMATION
   ========================================================= */

$brand = $car['brand'] ?? 'Car';
$model = $car['model'] ?? 'Vehicle';
$price = $car['price'] ?? 0;


/* =========================================================
   6. CAR IMAGE
   ========================================================= */

/*
   Your database may contain an image filename, but your
   current project has also been using remote images.

   We first use the database image if it is a valid URL/path.
   Otherwise we use a reliable brand-based fallback.
*/

$db_image = trim($car['image'] ?? '');

$brand_lower = strtolower($brand);

if ($db_image !== '') {

    if (
        filter_var($db_image, FILTER_VALIDATE_URL)
    ) {
        $car_image = $db_image;
    } else {

        /*
           If database stores something like:
           uploads/audi.jpg
           images/audi.jpg
           audi.jpg
        */
        $possible_paths = [
            $db_image,
            "uploads/" . $db_image,
            "images/" . $db_image,
            "assets/images/" . $db_image
        ];

        $found_image = "";

        foreach ($possible_paths as $possible) {

            if (
                file_exists(__DIR__ . "/" . $possible) &&
                is_file(__DIR__ . "/" . $possible)
            ) {
                $found_image = $possible;
                break;
            }
        }

        if ($found_image !== "") {
            $car_image = $found_image;
        } else {

            /*
               Fallback images
            */

            if (strpos($brand_lower, 'mercedes') !== false) {

                $car_image =
                    "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85";

            } elseif (strpos($brand_lower, 'audi') !== false) {

                $car_image =
                    "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85";

            } elseif (strpos($brand_lower, 'bmw') !== false) {

                $car_image =
                    "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85";

            } else {

                $car_image =
                    "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85";
            }
        }

    }

} else {

    if (strpos($brand_lower, 'mercedes') !== false) {

        $car_image =
            "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85";

    } elseif (strpos($brand_lower, 'audi') !== false) {

        $car_image =
            "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85";

    } elseif (strpos($brand_lower, 'bmw') !== false) {

        $car_image =
            "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85";

    } else {

        $car_image =
            "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85";
    }
}


/* =========================================================
   7. GET LOGGED-IN USER INFORMATION
   ========================================================= */

$user_id = 0;
$user_name = "";
$user_email = "";
$user_phone = "";


/*
   Try to get the user ID from the session.
*/
if (isset($_SESSION['user_id'])) {

    $user_id = (int)$_SESSION['user_id'];

} elseif (isset($_SESSION['id'])) {

    $user_id = (int)$_SESSION['id'];
}


/*
   Try to get name/email from session first.
*/
if (isset($_SESSION['name'])) {
    $user_name = $_SESSION['name'];
} elseif (isset($_SESSION['user_name'])) {
    $user_name = $_SESSION['user_name'];
}

if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
}


/*
   If we have a user ID, try to get more information from users table.
*/
if ($user_id > 0) {

    $user_stmt = $conn->prepare("
        SELECT *
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    if ($user_stmt) {

        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();

        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();

        $user_stmt->close();

        if ($user) {

            if ($user_name === "") {
                $user_name =
                    $user['name']
                    ?? $user['full_name']
                    ?? $user['username']
                    ?? "";
            }

            if ($user_email === "") {
                $user_email =
                    $user['email']
                    ?? "";
            }

            if ($user_phone === "") {
                $user_phone =
                    $user['phone']
                    ?? $user['phone_number']
                    ?? "";
            }
        }
    }
}


/* =========================================================
   8. BOOKING FORM
   ========================================================= */

$success = "";
$error = "";


/*
   Preserve submitted values.
*/
$name = $user_name;
$email = $user_email;
$phone = $user_phone;
$booking_date = "";


/* =========================================================
   9. SUBMIT BOOKING
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $booking_date = trim($_POST['booking_date'] ?? '');


    /* Basic validation */

    if (
        $name === '' ||
        $email === '' ||
        $phone === '' ||
        $booking_date === ''
    ) {

        $error = "Please fill in all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif ($booking_date < date('Y-m-d')) {

        $error = "Please select a valid booking date.";

    } else {

        /*
           Make sure bookings table exists.
        */

        $table_check = $conn->query("
            SHOW TABLES LIKE 'bookings'
        ");

        if (!$table_check || $table_check->num_rows === 0) {

            $error = "The bookings table does not exist. Please create the bookings table first.";

        } else {

            /*
               Insert booking.

               This matches the booking structure already
               used by your CarHub project:
               car_id, name, email, phone, booking_date, status
            */

            $insert = $conn->prepare("
                INSERT INTO bookings
                (
                    car_id,
                    name,
                    email,
                    phone,
                    booking_date,
                    status
                )
                VALUES (?, ?, ?, ?, ?, 'Pending')
            ");

            if (!$insert) {

                $error = "Unable to prepare the booking request.";

            } else {

                $insert->bind_param(
                    "issss",
                    $car_id,
                    $name,
                    $email,
                    $phone,
                    $booking_date
                );

                if ($insert->execute()) {

                    /*
                       Save booking ID so the success page can
                       display it if needed.
                    */

                    $booking_id = $conn->insert_id;

                    /*
                       Redirect to success page.
                    */

                    header(
                        "Location: booking-success.php?booking_id=" .
                        (int)$booking_id
                    );

                    exit;

                } else {

                    $error =
                        "Unable to submit booking request. " .
                        htmlspecialchars($insert->error);
                }

                $insert->close();
            }
        }
    }
}


/* =========================================================
   10. SAFE DISPLAY VALUES
   ========================================================= */

$car_name = trim($brand . " " . $model);

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
    Book <?php echo htmlspecialchars($car_name); ?> | CarHub
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
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f5f7;
    color: #172033;
    min-height: 100vh;
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
    letter-spacing: -1px;
}


.logo span {
    color: #ef3340;
}


.nav-links {
    display: flex;
    align-items: center;
    gap: 28px;
}


.nav-links a {
    color: white;
    text-decoration: none;
    font-size: 15px;
    transition: 0.2s;
}


.nav-links a:hover {
    color: #ef3340;
}


/* =========================================================
   MAIN CONTAINER
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
    margin-bottom: 38px;
}


.page-header h1 {
    font-size: 42px;
    line-height: 1.15;
    margin-bottom: 12px;
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

.booking-grid {
    display: grid;
    grid-template-columns: 1fr 1.15fr;
    gap: 35px;
    align-items: start;
}


/* =========================================================
   CAR CARD
   ========================================================= */

.car-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;

    box-shadow:
        0 12px 35px rgba(0, 0, 0, 0.08);
}


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

    transition: transform 0.4s ease;
}


.car-card:hover .car-image {
    transform: scale(1.03);
}


.car-info {
    padding: 28px;
}


.brand {
    color: #ef3340;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 14px;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}


.car-info h2 {
    font-size: 30px;
    margin-bottom: 15px;
}


.price {
    color: #ef3340;
    font-size: 28px;
    font-weight: bold;
}


/* =========================================================
   FORM CARD
   ========================================================= */

.form-card {
    background: white;
    border-radius: 20px;

    padding: 42px;

    box-shadow:
        0 12px 35px rgba(0, 0, 0, 0.08);
}


.form-card h2 {
    font-size: 32px;
    margin-bottom: 8px;
}


.subtitle {
    color: #667085;
    font-size: 17px;
    margin-bottom: 30px;
    line-height: 1.5;
}


/* =========================================================
   ALERTS
   ========================================================= */

.alert {
    padding: 16px 18px;
    border-radius: 10px;
    margin-bottom: 25px;
    line-height: 1.5;
}


.error {
    background: #fff0f0;
    border: 1px solid #ffb5b5;
    color: #c62828;
}


.success {
    background: #eaf8ee;
    border: 1px solid #b7e4c7;
    color: #18733a;
}


/* =========================================================
   FORM
   ========================================================= */

.form-group {
    margin-bottom: 22px;
}


.form-group label {
    display: block;

    font-size: 16px;
    font-weight: bold;

    margin-bottom: 9px;
}


.form-group input {
    width: 100%;

    padding: 16px;

    border: 1px solid #ccd2da;
    border-radius: 10px;

    font-size: 16px;

    outline: none;

    transition: 0.2s;
}


.form-group input:focus {
    border-color: #ef3340;

    box-shadow:
        0 0 0 3px rgba(239, 51, 64, 0.10);
}


.form-group input[readonly] {
    background: #f8f9fb;
}


/* =========================================================
   BUTTON
   ========================================================= */

.submit-btn {
    width: 100%;

    border: none;

    background: #ef3340;
    color: white;

    padding: 17px;

    border-radius: 10px;

    font-size: 18px;
    font-weight: bold;

    cursor: pointer;

    transition: 0.2s;
}


.submit-btn:hover {
    background: #d92835;
    transform: translateY(-1px);
}


/* =========================================================
   LOGIN MESSAGE
   ========================================================= */

.login-note {
    margin-top: 22px;

    padding-top: 22px;

    border-top: 1px solid #e5e7eb;

    color: #667085;

    line-height: 1.6;
}


/* =========================================================
   FOOTER
   ========================================================= */

footer {
    background: #111111;
    color: white;

    text-align: center;

    padding: 30px;

    margin-top: 70px;
}


/* =========================================================
   MOBILE
   ========================================================= */

@media (max-width: 850px) {

    .navbar {
        padding: 0 5%;
    }

    .nav-links {
        display: none;
    }

    .container {
        width: 92%;
        margin-top: 30px;
    }

    .page-header h1 {
        font-size: 34px;
    }

    .booking-grid {
        grid-template-columns: 1fr;
    }

    .car-image-wrapper {
        height: 350px;
    }

    .form-card {
        padding: 30px;
    }

    .car-info h2 {
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

        <a href="logout.php">
            Logout
        </a>

    </div>

</nav>


<!-- =====================================================
     MAIN
     ===================================================== -->

<div class="container">


    <div class="page-header">

        <h1>
            Book Your <span>Car</span>
        </h1>

        <p>
            Complete the form below to submit your booking request.
        </p>

    </div>


    <div class="booking-grid">


        <!-- =================================================
             CAR INFORMATION
             ================================================= -->

        <div class="car-card">


            <div class="car-image-wrapper">

                <img
                    src="<?php echo htmlspecialchars($car_image); ?>"
                    alt="<?php echo htmlspecialchars($car_name); ?>"
                    class="car-image"
                    onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85';"
                >

            </div>


            <div class="car-info">

                <div class="brand">

                    <?php
                    echo htmlspecialchars($brand);
                    ?>

                </div>


                <h2>

                    <?php
                    echo htmlspecialchars($model);
                    ?>

                </h2>


                <div class="price">

                    ₹<?php
                    echo number_format((float)$price);
                    ?>

                </div>

            </div>

        </div>


        <!-- =================================================
             BOOKING FORM
             ================================================= -->

        <div class="form-card">


            <h2>
                Booking Details
            </h2>


            <p class="subtitle">
                Enter your information and preferred booking date.
            </p>


            <?php if ($error !== ""): ?>

                <div class="alert error">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- NAME -->

                <div class="form-group">

                    <label for="name">
                        Your Name *
                    </label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="<?php echo htmlspecialchars($name); ?>"
                        placeholder="Enter your full name"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label for="email">
                        Email Address *
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="Enter your email address"
                        required
                    >

                </div>


                <!-- PHONE -->

                <div class="form-group">

                    <label for="phone">
                        Phone Number *
                    </label>

                    <input
                        type="tel"
                        id="phone"
                        name="phone"
                        value="<?php echo htmlspecialchars($phone); ?>"
                        placeholder="Enter your phone number"
                        required
                    >

                </div>


                <!-- DATE -->

                <div class="form-group">

                    <label for="booking_date">
                        Booking Date *
                    </label>

                    <input
                        type="date"
                        id="booking_date"
                        name="booking_date"
                        value="<?php echo htmlspecialchars($booking_date); ?>"
                        min="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                </div>


                <!-- SUBMIT -->

                <button
                    type="submit"
                    class="submit-btn"
                >
                    Submit Booking Request
                </button>

            </form>


            <div class="login-note">

                <strong>
                    Logged-in customer
                </strong>

                <br>

                Your booking request will be connected to your
                current CarHub account.

            </div>


        </div>

    </div>

</div>


<!-- =====================================================
     FOOTER
     ===================================================== -->

<footer>

    © <?php echo date('Y'); ?> CarHub.
    All Rights Reserved.

</footer>


</body>

</html>

<?php

$conn->close();

?>