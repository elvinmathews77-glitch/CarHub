<?php

$conn = new mysqli("localhost", "root", "", "carhub");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


/* ==============================
   GET CAR ID
============================== */

$car_id = isset($_GET['car_id']) ? (int)$_GET['car_id'] : 0;

if ($car_id <= 0) {
    die("Invalid car selected.");
}


/* ==============================
   GET CAR
============================== */

$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");
$stmt->bind_param("i", $car_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Car not found.");
}

$car = $result->fetch_assoc();

$brand = $car['brand'] ?? 'Car';
$model = $car['model'] ?? 'Vehicle';
$price = $car['price'] ?? 0;


/* ==============================
   CAR IMAGE
============================== */

$brand_lower = strtolower($brand);

if (strpos($brand_lower, "mercedes") !== false) {

    $car_image =
        "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85";

} elseif (strpos($brand_lower, "audi") !== false) {

    $car_image =
        "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85";

} elseif (strpos($brand_lower, "bmw") !== false) {

    $car_image =
        "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85";

} else {

    $car_image =
        "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85";
}


/* ==============================
   FORM VALUES
============================== */

$error = "";

$name = "";
$email = "";
$phone = "";
$test_drive_date = "";
$test_drive_time = "";


/* ==============================
   SUBMIT TEST DRIVE
============================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $test_drive_date = trim($_POST["test_drive_date"] ?? "");
    $test_drive_time = trim($_POST["test_drive_time"] ?? "");


    /* VALIDATION */

    if (
        $name === "" ||
        $email === "" ||
        $phone === "" ||
        $test_drive_date === "" ||
        $test_drive_time === ""
    ) {

        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {


        /* ==============================
           CHECK TABLE
        ============================== */

        $table_check = $conn->query("SHOW TABLES LIKE 'test_drives'");


        if (!$table_check || $table_check->num_rows === 0) {

            $error = "Test drive table could not be accessed.";

        } else {


            /* ==============================
               INSERT
            ============================== */

            $insert = $conn->prepare("
                INSERT INTO test_drives
                (
                    car_id,
                    name,
                    email,
                    phone,
                    test_drive_date,
                    test_drive_time,
                    status
                )
                VALUES
                (?, ?, ?, ?, ?, ?, 'Pending')
            ");


            if (!$insert) {

                $error = "Unable to prepare the test drive request.";

            } else {

                $insert->bind_param(
                    "isssss",
                    $car_id,
                    $name,
                    $email,
                    $phone,
                    $test_drive_date,
                    $test_drive_time
                );


                if ($insert->execute()) {

                    $test_drive_id = $conn->insert_id;

                    /*
                     * Redirect to confirmation page.
                     */

                    header(
                        "Location: test-drive-success.php?" .
                        "test_drive_id=" . urlencode($test_drive_id) .
                        "&car_name=" . urlencode($brand . " " . $model) .
                        "&date=" . urlencode($test_drive_date) .
                        "&time=" . urlencode($test_drive_time)
                    );

                    exit;

                } else {

                    $error = "Unable to submit your test drive request.";

                }

                $insert->close();
            }
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
Test Drive - <?php echo htmlspecialchars($brand . " " . $model); ?> | CarHub
</title>


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


/* ==============================
   NAVBAR
============================== */

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


/* ==============================
   MAIN
============================== */

.container {
    width: 90%;
    max-width: 1200px;
    margin: 50px auto;
}


.page-title {
    text-align: center;
    margin-bottom: 40px;
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


/* ==============================
   GRID
============================== */

.test-drive-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 35px;
}


/* ==============================
   CAR CARD
============================== */

.car-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;

    box-shadow: 0 10px 30px rgba(0,0,0,.08);
}


.car-image {
    width: 100%;
    height: 430px;

    object-fit: cover;

    display: block;
}


.car-info {
    padding: 28px;
}


.car-brand {
    color: #ef3340;
    font-size: 14px;
    font-weight: bold;
    text-transform: uppercase;

    margin-bottom: 8px;
}


.car-info h2 {
    font-size: 30px;
    margin-bottom: 12px;
}


.price {
    color: #ef3340;
    font-size: 28px;
    font-weight: bold;
}


/* ==============================
   FORM CARD
============================== */

.form-card {
    background: white;
    border-radius: 20px;

    padding: 40px;

    box-shadow: 0 10px 30px rgba(0,0,0,.08);
}


.form-card h2 {
    font-size: 30px;
    margin-bottom: 8px;
}


.subtitle {
    color: #667085;
    margin-bottom: 30px;
    line-height: 1.5;
}


/* ==============================
   ERROR
============================== */

.error {
    background: #fff0f0;
    border: 1px solid #ffb8b8;

    color: #c62828;

    padding: 15px;

    border-radius: 9px;

    margin-bottom: 22px;
}


/* ==============================
   FORM
============================== */

.form-group {
    margin-bottom: 22px;
}


.form-group label {
    display: block;

    font-weight: bold;

    margin-bottom: 8px;
}


.form-group input {
    width: 100%;

    padding: 15px;

    border: 1px solid #ccd2da;

    border-radius: 9px;

    font-size: 16px;

    outline: none;
}


.form-group input:focus {
    border-color: #ef3340;

    box-shadow:
        0 0 0 3px rgba(239,51,64,.10);
}


/* ==============================
   SUBMIT
============================== */

.submit-btn {
    width: 100%;

    padding: 17px;

    border: none;

    border-radius: 9px;

    background: #ef3340;

    color: white;

    font-size: 17px;

    font-weight: bold;

    cursor: pointer;
}


.submit-btn:hover {
    background: #d92835;
}


/* ==============================
   FOOTER
============================== */

footer {
    background: #111;

    color: white;

    text-align: center;

    padding: 30px;

    margin-top: 70px;
}


/* ==============================
   MOBILE
============================== */

@media(max-width: 850px) {

    .nav-links {
        display: none;
    }


    .test-drive-grid {
        grid-template-columns: 1fr;
    }


    .car-image {
        height: 300px;
    }


    .form-card {
        padding: 28px;
    }


    .page-title h1 {
        font-size: 32px;
    }

}

</style>

</head>


<body>


<!-- ==============================
     NAVBAR
============================== -->

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


<!-- ==============================
     MAIN
============================== -->

<div class="container">


    <div class="page-title">

        <h1>
            Book a <span>Test Drive</span>
        </h1>

        <p>
            Experience your selected car before making your decision.
        </p>

    </div>


    <div class="test-drive-grid">


        <!-- ==============================
             CAR
        ============================== -->

        <div class="car-card">

            <img
                src="<?php echo htmlspecialchars($car_image); ?>"
                alt="<?php echo htmlspecialchars($brand . " " . $model); ?>"
                class="car-image"
            >


            <div class="car-info">

                <div class="car-brand">

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


        <!-- ==============================
             FORM
        ============================== -->

        <div class="form-card">


            <h2>
                Test Drive Details
            </h2>


            <p class="subtitle">

                Choose your preferred date and time and we'll contact you to confirm the appointment.

            </p>


            <?php if ($error !== ""): ?>

                <div class="error">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <!-- NAME -->

                <div class="form-group">

                    <label>
                        Full Name *
                    </label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Enter your full name"
                        value="<?php echo htmlspecialchars($name); ?>"
                        required
                    >

                </div>


                <!-- EMAIL -->

                <div class="form-group">

                    <label>
                        Email Address *
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="Enter your email address"
                        value="<?php echo htmlspecialchars($email); ?>"
                        required
                    >

                </div>


                <!-- PHONE -->

                <div class="form-group">

                    <label>
                        Phone Number *
                    </label>

                    <input
                        type="tel"
                        name="phone"
                        placeholder="Enter your phone number"
                        value="<?php echo htmlspecialchars($phone); ?>"
                        required
                    >

                </div>


                <!-- DATE -->

                <div class="form-group">

                    <label>
                        Preferred Date *
                    </label>

                    <input
                        type="date"
                        name="test_drive_date"
                        min="<?php echo date('Y-m-d'); ?>"
                        value="<?php echo htmlspecialchars($test_drive_date); ?>"
                        required
                    >

                </div>


                <!-- TIME -->

                <div class="form-group">

                    <label>
                        Preferred Time *
                    </label>

                    <input
                        type="time"
                        name="test_drive_time"
                        value="<?php echo htmlspecialchars($test_drive_time); ?>"
                        required
                    >

                </div>


                <!-- BUTTON -->

                <button
                    type="submit"
                    class="submit-btn"
                >
                    Submit Test Drive Request
                </button>


            </form>

        </div>

    </div>

</div>


<!-- ==============================
     FOOTER
============================== -->

<footer>

    © <?php echo date('Y'); ?> CarHub.
    All Rights Reserved.

</footer>


</body>

</html>

<?php

$stmt->close();

$conn->close();

?>