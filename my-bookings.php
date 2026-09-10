<?php
session_start();

/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

$conn = new mysqli("localhost", "root", "", "carhub");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");


/* =========================================================
   LOGIN / USER IDENTIFICATION
   ========================================================= */

$user_email = "";
$user_id = 0;

/*
   Check the common session values used by the login system.
*/

if (!empty($_SESSION['user_id'])) {
    $user_id = (int)$_SESSION['user_id'];
}

if (!empty($_SESSION['id']) && $user_id === 0) {
    $user_id = (int)$_SESSION['id'];
}

if (!empty($_SESSION['email'])) {
    $user_email = trim($_SESSION['email']);
}

if (!empty($_SESSION['user_email']) && $user_email === "") {
    $user_email = trim($_SESSION['user_email']);
}


/*
   Some login systems store the complete user inside
   $_SESSION['user'].
*/

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
        $user_email = trim($_SESSION['user']['email']);
    }
}


/* =========================================================
   FIND USER FROM DATABASE
   ========================================================= */

if ($user_id > 0 && $user_email === "") {

    $user_stmt = $conn->prepare("
        SELECT id, email
        FROM users
        WHERE id = ?
        LIMIT 1
    ");

    if ($user_stmt) {

        $user_stmt->bind_param("i", $user_id);
        $user_stmt->execute();

        $user_result = $user_stmt->get_result();

        if ($user_result->num_rows > 0) {

            $user = $user_result->fetch_assoc();

            $user_email = trim($user['email'] ?? "");

        }

        $user_stmt->close();
    }
}


/*
   If there is no email but we have an ID, we can still
   identify the user from bookings using the user's ID
   only if your users table has matching booking ownership.

   For your current booking structure, bookings are connected
   using the customer's email, so email is required.
*/


/* =========================================================
   LOGIN CHECK
   ========================================================= */

if ($user_email === "") {

    $conn->close();

    header("Location: login.php");
    exit;
}


/* =========================================================
   GET BOOKINGS
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
        c.image

    FROM bookings b

    LEFT JOIN cars c
        ON b.car_id = c.id

    WHERE LOWER(TRIM(b.email)) = LOWER(TRIM(?))

    ORDER BY b.id DESC
");

if (!$stmt) {
    die("Unable to load bookings.");
}

$stmt->bind_param("s", $user_email);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

$stmt->close();

$conn->close();


/* =========================================================
   IMAGE FUNCTION
   ========================================================= */

function getCarImage($booking)
{
    $image = trim($booking['image'] ?? "");

    /*
       If the database contains a full image URL.
    */

    if (
        $image !== "" &&
        (
            strpos($image, "http://") === 0 ||
            strpos($image, "https://") === 0
        )
    ) {
        return $image;
    }


    /*
       Local image from database.
    */

    if ($image !== "") {

        $possible = [
            $image,
            "images/" . $image,
            "uploads/" . $image,
            "assets/images/" . $image
        ];

        foreach ($possible as $path) {

            if (
                file_exists(__DIR__ . "/" . $path) &&
                is_file(__DIR__ . "/" . $path)
            ) {
                return $path;
            }
        }
    }


    /*
       Fallback images.
    */

    $brand = strtolower(
        trim($booking['brand'] ?? "")
    );

    if (strpos($brand, "audi") !== false) {

        return "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85";

    }

    if (strpos($brand, "bmw") !== false) {

        return "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85";

    }

    if (strpos($brand, "mercedes") !== false) {

        return "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85";

    }

    return "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85";
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

<title>My Bookings | CarHub</title>

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


/* =========================================================
   NAVBAR
   ========================================================= */

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
   CONTAINER
   ========================================================= */

.container {
    width: 90%;
    max-width: 1200px;
    margin: 45px auto 70px;
}


/* =========================================================
   HEADER
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
        repeat(2, minmax(0, 1fr));

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
        0 10px 30px rgba(0,0,0,.08);
}


/* =========================================================
   IMAGE
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
   CONTENT
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
    border-top: 1px solid #e5e7eb;

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
}


/* =========================================================
   STATUS
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
   VIEW BOOKED DETAILS
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
   EMPTY
   ========================================================= */

.empty-box {
    background: white;

    border-radius: 20px;

    padding: 70px 30px;

    text-align: center;

    box-shadow:
        0 10px 30px rgba(0,0,0,.07);
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
   MOBILE
   ========================================================= */

@media (max-width: 850px) {

    .navbar {
        padding: 0 5%;
    }

    .nav-links {
        gap: 12px;
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

@media (max-width: 600px) {

    .nav-links a:nth-child(3),
    .nav-links a:nth-child(4) {
        display: none;
    }

    .detail-row {
        align-items: flex-start;
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
            My <span>Bookings</span>
        </h1>

        <p>
            View your vehicle booking requests and their current status.
        </p>

    </div>


    <?php if (count($bookings) === 0): ?>


        <!-- =================================================
             NO BOOKINGS
             ================================================= -->

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
             ================================================= -->

        <div class="bookings-grid">


            <?php foreach ($bookings as $booking): ?>

                <?php

                $brand =
                    $booking['brand'] ?? 'Car';

                $model =
                    $booking['model'] ?? 'Vehicle';

                $price =
                    (float)($booking['price'] ?? 0);

                $image =
                    getCarImage($booking);

                $status =
                    trim($booking['status'] ?? 'Pending');

                $status_class =
                    strtolower(
                        preg_replace(
                            '/[^a-zA-Z0-9]+/',
                            '-',
                            $status
                        )
                    );

                ?>


                <div class="booking-card">


                    <!-- CAR IMAGE -->

                    <div class="car-image-wrapper">

                        <img
                            src="<?php echo htmlspecialchars($image); ?>"
                            alt="<?php echo htmlspecialchars($brand . ' ' . $model); ?>"
                            class="car-image"
                            onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85';"
                        >

                    </div>


                    <!-- BOOKING CONTENT -->

                    <div class="booking-content">


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


                        <div class="details">


                            <!-- BOOKING ID -->

                            <div class="detail-row">

                                <span class="detail-label">
                                    Booking ID
                                </span>

                                <span class="detail-value">

                                    #<?php
                                    echo (int)$booking['id'];
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
                                            $booking['booking_date']
                                        )
                                    ) {

                                        echo htmlspecialchars(
                                            date(
                                                "d M Y",
                                                strtotime(
                                                    $booking['booking_date']
                                                )
                                            )
                                        );

                                    } else {

                                        echo "—";

                                    }

                                    ?>

                                </span>

                            </div>


                            <!-- STATUS -->

                            <div class="detail-row">

                                <span class="detail-label">
                                    Status
                                </span>

                                <span
                                    class="status <?php echo htmlspecialchars($status_class); ?>"
                                >

                                    <?php
                                    echo htmlspecialchars($status);
                                    ?>

                                </span>

                            </div>


                        </div>


                        <!-- =================================================
                             WORKING BOOKED DETAILS BUTTON
                             ================================================= -->

                        <a
                            href="booking-details.php?booking_id=<?php echo (int)$booking['id']; ?>"
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