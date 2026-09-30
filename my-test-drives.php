<?php

session_start();

require_once "config/db.php";


/* =========================================================
   CHECK LOGIN
========================================================= */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}

$user_id = (int)$_SESSION["user_id"];


/* =========================================================
   GET LOGGED-IN USER
========================================================= */

$user_stmt = $conn->prepare("
    SELECT id, name, email
    FROM users
    WHERE id = ?
");

if (!$user_stmt) {
    die("Unable to prepare user query.");
}

$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();

$user_result = $user_stmt->get_result();

if ($user_result->num_rows === 0) {

    $user_stmt->close();

    session_destroy();

    header("Location: login.php");
    exit();

}

$user = $user_result->fetch_assoc();

$user_name = $user["name"];
$user_email = $user["email"];

$user_stmt->close();


/* =========================================================
   GET USER TEST DRIVES
========================================================= */

/*
   The test_drives table does not contain user_id.

   Test drives are therefore matched using the
   email address used when the request was submitted.
*/

$stmt = $conn->prepare("
    SELECT
        td.id,
        td.car_id,
        td.name,
        td.email,
        td.phone,
        td.test_drive_date,
        td.test_drive_time,
        td.status,
        td.created_at,

        c.name AS car_name,
        c.brand,
        c.model,
        c.year,
        c.price,
        c.image,
        c.description

    FROM test_drives td

    INNER JOIN cars c
        ON td.car_id = c.id

    WHERE LOWER(TRIM(td.email)) = LOWER(TRIM(?))

    ORDER BY td.id DESC
");


if (!$stmt) {
    die("Unable to prepare test drive query.");
}


$stmt->bind_param("s", $user_email);

$stmt->execute();

$result = $stmt->get_result();

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Test Drives - CarHub</title>


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

    background: #f5f5f5;

    color: #222;
}


/* =========================================================
   NAVIGATION
========================================================= */

.main-nav {

    width: 100%;

    min-height: 70px;

    background: #222;

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 0 50px;

    box-sizing: border-box;
}


.main-nav .logo {

    color: white;

    font-size: 30px;

    font-weight: bold;
}


.main-nav .logo span {

    color: #e63946;
}


.main-nav ul {

    display: flex;

    align-items: center;

    gap: 25px;

    list-style: none;

    margin: 0;

    padding: 0;
}


.main-nav ul li {

    list-style: none;

    margin: 0;

    padding: 0;
}


.main-nav ul li a {

    color: white;

    text-decoration: none;

    font-size: 16px;

    transition: 0.3s;
}


.main-nav ul li a:hover {

    color: #e63946;
}


/* =========================================================
   PAGE
========================================================= */

.my-test-drive-page {

    min-height: calc(100vh - 70px);

    background: #f5f5f5;

    padding: 50px 20px;
}


.page-container {

    max-width: 1150px;

    margin: auto;
}


/* =========================================================
   HEADER
========================================================= */

.page-header {

    text-align: center;

    margin-bottom: 40px;
}


.page-header h1 {

    font-size: 38px;

    color: #222;

    margin: 0 0 10px;
}


.page-header h1 span {

    color: #e63946;
}


.page-header p {

    color: #777;

    font-size: 16px;

    margin: 0;
}


/* =========================================================
   TEST DRIVE CARD
========================================================= */

.test-drive-card {

    background: white;

    border-radius: 16px;

    overflow: hidden;

    margin-bottom: 25px;

    box-shadow:
        0 6px 25px rgba(0,0,0,0.10);

    display: grid;

    grid-template-columns: 300px 1fr;
}


/* =========================================================
   CAR IMAGE
========================================================= */

.car-image {

    width: 100%;

    height: 100%;

    min-height: 280px;

    object-fit: cover;

    display: block;
}


/* =========================================================
   CARD CONTENT
========================================================= */

.card-content {

    padding: 30px;
}


.car-brand {

    color: #e63946;

    font-size: 13px;

    font-weight: bold;

    text-transform: uppercase;

    letter-spacing: 1px;

    margin-bottom: 5px;
}


.card-content h2 {

    color: #222;

    font-size: 27px;

    margin: 0 0 8px;
}


.car-year {

    color: #777;

    font-size: 14px;

    margin-bottom: 8px;
}


.car-price {

    color: #e63946;

    font-size: 20px;

    font-weight: bold;

    margin-bottom: 25px;
}


/* =========================================================
   DETAILS GRID
========================================================= */

.details-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 15px;

    margin-bottom: 20px;
}


.detail-box {

    background: #f7f7f7;

    border-radius: 8px;

    padding: 14px;
}


.detail-label {

    color: #777;

    font-size: 12px;

    text-transform: uppercase;

    margin-bottom: 6px;
}


.detail-value {

    color: #222;

    font-size: 15px;

    font-weight: bold;

    word-break: break-word;
}


/* =========================================================
   STATUS
========================================================= */

.status-row {

    display: flex;

    justify-content: space-between;

    align-items: center;

    flex-wrap: wrap;

    gap: 10px;

    margin-top: 5px;
}


.status {

    display: inline-block;

    padding: 8px 18px;

    border-radius: 20px;

    font-size: 13px;

    font-weight: bold;
}


.status-pending {

    background: #fff3cd;

    color: #856404;
}


.status-approved {

    background: #d4edda;

    color: #155724;
}


.status-confirmed {

    background: #d4edda;

    color: #155724;
}


.status-completed {

    background: #d1ecf1;

    color: #0c5460;
}


.status-cancelled {

    background: #f8d7da;

    color: #721c24;
}


.status-rejected {

    background: #f8d7da;

    color: #721c24;
}


.status-default {

    background: #e2e3e5;

    color: #383d41;
}


.request-date {

    color: #888;

    font-size: 13px;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-box {

    background: white;

    border-radius: 16px;

    padding: 70px 30px;

    text-align: center;

    box-shadow:
        0 6px 25px rgba(0,0,0,0.08);
}


.empty-icon {

    font-size: 55px;

    margin-bottom: 15px;
}


.empty-box h2 {

    color: #333;

    margin-bottom: 10px;
}


.empty-box p {

    color: #777;

    margin-bottom: 25px;
}


.browse-btn {

    display: inline-block;

    background: #e63946;

    color: white;

    padding: 13px 25px;

    border-radius: 8px;

    text-decoration: none;

    font-weight: bold;

    transition: 0.3s;
}


.browse-btn:hover {

    background: #c92f3c;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 850px) {

    .test-drive-card {

        grid-template-columns: 1fr;
    }


    .car-image {

        height: 280px;

        min-height: 0;
    }


    .main-nav {

        padding: 20px;

        flex-direction: column;

        gap: 15px;
    }


    .main-nav ul {

        flex-wrap: wrap;

        justify-content: center;

        gap: 12px;
    }

}


@media (max-width: 550px) {

    .my-test-drive-page {

        padding: 30px 12px;
    }


    .page-header h1 {

        font-size: 30px;
    }


    .card-content {

        padding: 20px;
    }


    .details-grid {

        grid-template-columns: 1fr;
    }


    .status-row {

        align-items: flex-start;

        flex-direction: column;
    }

}

</style>

</head>


<body>


<!-- =======================================================
     NAVIGATION
======================================================= -->

<nav class="main-nav">


    <div class="logo">

        Car<span>Hub</span>

    </div>


    <ul>

        <li>
            <a href="index.php">
                Home
            </a>
        </li>


        <li>
            <a href="cars.php">
                Cars
            </a>
        </li>


        <li>
            <a href="about.php">
                About
            </a>
        </li>


        <li>
            <a href="contact.php">
                Contact
            </a>
        </li>


        <li>
            <a href="my-bookings.php">
                My Bookings
            </a>
        </li>


        <!-- NEW TEST DRIVE LINK -->

        <li>
            <a href="my-test-drives.php">
                My Test Drives
            </a>
        </li>


        <li>
            <a href="logout.php">
                Logout
            </a>
        </li>

    </ul>

</nav>



<!-- =======================================================
     MAIN PAGE
======================================================= -->

<section class="my-test-drive-page">


    <div class="page-container">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <h1>
                My <span>Test Drives</span>
            </h1>


            <p>
                View your test drive requests and their current status.
            </p>

        </div>



        <?php if ($result->num_rows > 0): ?>


            <!-- =================================================
                 DISPLAY TEST DRIVES
            ================================================== -->

            <?php while ($drive = $result->fetch_assoc()): ?>


                <?php

                /* =============================================
                   STATUS
                ============================================= */

                $status = strtolower(
                    trim($drive["status"] ?? "Pending")
                );


                if ($status === "pending") {

                    $status_class = "status-pending";

                }

                elseif ($status === "approved") {

                    $status_class = "status-approved";

                }

                elseif ($status === "confirmed") {

                    $status_class = "status-confirmed";

                }

                elseif ($status === "completed") {

                    $status_class = "status-completed";

                }

                elseif ($status === "cancelled") {

                    $status_class = "status-cancelled";

                }

                elseif ($status === "rejected") {

                    $status_class = "status-rejected";

                }

                else {

                    $status_class = "status-default";

                }


                /* =============================================
                   CAR IMAGE
                ============================================= */

                $image = "";

                if (!empty($drive["image"])) {

                    $db_image = trim($drive["image"]);


                    if (
                        filter_var(
                            $db_image,
                            FILTER_VALIDATE_URL
                        )
                    ) {

                        $image = $db_image;

                    }

                    else {

                        $clean_path =
                            ltrim($db_image, "/\\");


                        if (
                            file_exists(
                                __DIR__ .
                                DIRECTORY_SEPARATOR .
                                $clean_path
                            )
                        ) {

                            $image = $clean_path;

                        }

                    }

                }


                /* =============================================
                   FALLBACK IMAGE
                ============================================= */

                if ($image === "") {

                    $brand_lower =
                        strtolower(
                            $drive["brand"] ?? ""
                        );


                    if (
                        strpos(
                            $brand_lower,
                            "mercedes"
                        ) !== false
                    ) {

                        $image =
                            "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1200&q=85";

                    }

                    elseif (
                        strpos(
                            $brand_lower,
                            "audi"
                        ) !== false
                    ) {

                        $image =
                            "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1200&q=85";

                    }

                    elseif (
                        strpos(
                            $brand_lower,
                            "bmw"
                        ) !== false
                    ) {

                        $image =
                            "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1200&q=85";

                    }

                    else {

                        $image =
                            "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85";

                    }

                }


                /* =============================================
                   CAR NAME
                ============================================= */

                $car_display_name =
                    trim(
                        ($drive["brand"] ?? "") .
                        " " .
                        ($drive["model"] ?? "")
                    );


                if ($car_display_name === "") {

                    $car_display_name =
                        $drive["car_name"] ?? "Car";

                }

                ?>


                <!-- =================================================
                     TEST DRIVE CARD
                ================================================== -->

                <div class="test-drive-card">


                    <!-- CAR IMAGE -->

                    <img
                        class="car-image"

                        src="<?php
                        echo htmlspecialchars($image);
                        ?>"

                        alt="<?php
                        echo htmlspecialchars(
                            $car_display_name
                        );
                        ?>"

                        onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1200&q=85';"
                    >



                    <!-- CARD CONTENT -->

                    <div class="card-content">


                        <div class="car-brand">

                            <?php

                            echo htmlspecialchars(
                                $drive["brand"] ?? "Car"
                            );

                            ?>

                        </div>


                        <h2>

                            <?php

                            echo htmlspecialchars(
                                $car_display_name
                            );

                            ?>

                        </h2>


                        <?php if (!empty($drive["year"])): ?>

                            <div class="car-year">

                                Model Year:
                                <?php

                                echo htmlspecialchars(
                                    $drive["year"]
                                );

                                ?>

                            </div>

                        <?php endif; ?>


                        <div class="car-price">

                            ₹ <?php

                            echo number_format(
                                (float)(
                                    $drive["price"] ?? 0
                                )
                            );

                            ?>

                        </div>



                        <!-- =================================================
                             DETAILS
                        ================================================== -->

                        <div class="details-grid">


                            <!-- TEST DRIVE ID -->

                            <div class="detail-box">

                                <div class="detail-label">
                                    Test Drive ID
                                </div>


                                <div class="detail-value">

                                    #<?php

                                    echo htmlspecialchars(
                                        $drive["id"]
                                    );

                                    ?>

                                </div>

                            </div>



                            <!-- CUSTOMER NAME -->

                            <div class="detail-box">

                                <div class="detail-label">
                                    Customer Name
                                </div>


                                <div class="detail-value">

                                    <?php

                                    echo htmlspecialchars(
                                        $drive["name"]
                                    );

                                    ?>

                                </div>

                            </div>



                            <!-- EMAIL -->

                            <div class="detail-box">

                                <div class="detail-label">
                                    Email
                                </div>


                                <div class="detail-value">

                                    <?php

                                    echo htmlspecialchars(
                                        $drive["email"]
                                    );

                                    ?>

                                </div>

                            </div>



                            <!-- PHONE -->

                            <div class="detail-box">

                                <div class="detail-label">
                                    Phone
                                </div>


                                <div class="detail-value">

                                    <?php

                                    echo htmlspecialchars(
                                        $drive["phone"]
                                    );

                                    ?>

                                </div>

                            </div>



                            <!-- DATE -->

                            <div class="detail-box">

                                <div class="detail-label">
                                    Test Drive Date
                                </div>


                                <div class="detail-value">

                                    <?php

                                    if (
                                        !empty(
                                            $drive[
                                                "test_drive_date"
                                            ]
                                        )
                                    ) {

                                        echo date(
                                            "d M Y",
                                            strtotime(
                                                $drive[
                                                    "test_drive_date"
                                                ]
                                            )
                                        );

                                    } else {

                                        echo "Not specified";

                                    }

                                    ?>

                                </div>

                            </div>



                            <!-- TIME -->

                            <div class="detail-box">

                                <div class="detail-label">
                                    Preferred Time
                                </div>


                                <div class="detail-value">

                                    <?php

                                    if (
                                        !empty(
                                            $drive[
                                                "test_drive_time"
                                            ]
                                        )
                                    ) {

                                        echo date(
                                            "h:i A",
                                            strtotime(
                                                $drive[
                                                    "test_drive_time"
                                                ]
                                            )
                                        );

                                    } else {

                                        echo "Not specified";

                                    }

                                    ?>

                                </div>

                            </div>


                        </div>



                        <!-- =================================================
                             STATUS
                        ================================================== -->

                        <div class="status-row">


                            <span
                                class="status <?php
                                echo $status_class;
                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    ucfirst(
                                        $drive["status"]
                                    )
                                );

                                ?>

                            </span>


                            <span class="request-date">

                                Request submitted:

                                <?php

                                if (
                                    !empty(
                                        $drive["created_at"]
                                    )
                                ) {

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $drive["created_at"]
                                        )
                                    );

                                } else {

                                    echo "N/A";

                                }

                                ?>

                            </span>


                        </div>


                    </div>

                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- =================================================
                 EMPTY STATE
            ================================================== -->

            <div class="empty-box">


                <div class="empty-icon">
                    🚗
                </div>


                <h2>
                    No Test Drives Yet
                </h2>


                <p>

                    You haven't requested a test drive yet.
                    Explore our cars and book your first test drive.

                </p>


                <a
                    href="cars.php"
                    class="browse-btn"
                >
                    Browse Cars
                </a>


            </div>


        <?php endif; ?>


    </div>

</section>


</body>

</html>


<?php

$stmt->close();

$conn->close();

?>