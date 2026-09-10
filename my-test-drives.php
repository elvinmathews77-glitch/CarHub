<?php

session_start();

include "config/db.php";


/* =========================
   CHECK LOGIN
========================= */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit();

}


$user_id = $_SESSION["user_id"];


/* =========================
   GET USER TEST DRIVES
========================= */

$stmt = $conn->prepare("
    SELECT
        td.id,
        td.phone,
        td.test_drive_date,
        td.test_drive_time,
        td.location,
        td.message,
        td.status,
        td.created_at,

        c.name AS car_name,
        c.brand,
        c.model,
        c.year,
        c.price,
        c.image

    FROM test_drives td

    INNER JOIN cars c
        ON td.car_id = c.id

    WHERE td.user_id = ?

    ORDER BY td.id DESC
");


$stmt->bind_param("i", $user_id);

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


    <link rel="stylesheet"
          href="css/style.css">


    <style>

        /* =========================
           NAVIGATION
        ========================= */

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


        .main-nav ul {

            display: flex;

            align-items: center;

            gap: 25px;

            list-style: none !important;

            margin: 0 !important;

            padding: 0 !important;

        }


        .main-nav ul li {

            list-style: none !important;

            margin: 0;

            padding: 0;

        }


        .main-nav ul li a {

            color: white !important;

            text-decoration: none !important;

            font-size: 16px;

        }


        .main-nav ul li a:hover {

            color: #e63946 !important;

        }


        /* =========================
           PAGE
        ========================= */

        .my-test-drive-page {

            min-height: calc(100vh - 70px);

            background: #f5f5f5;

            padding: 50px 20px;

        }


        .page-container {

            max-width: 1150px;

            margin: auto;

        }


        /* =========================
           HEADER
        ========================= */

        .page-header {

            text-align: center;

            margin-bottom: 40px;

        }


        .page-header h1 {

            font-size: 38px;

            color: #222;

            margin: 0 0 10px;

        }


        .page-header p {

            color: #777;

            font-size: 16px;

            margin: 0;

        }


        /* =========================
           TEST DRIVE CARD
        ========================= */

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


        /* =========================
           CAR IMAGE
        ========================= */

        .car-image {

            width: 100%;

            height: 100%;

            min-height: 280px;

            object-fit: cover;

        }


        /* =========================
           CARD CONTENT
        ========================= */

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


        .car-price {

            color: #e63946;

            font-size: 20px;

            font-weight: bold;

            margin-bottom: 25px;

        }


        /* =========================
           DETAILS GRID
        ========================= */

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

        }


        /* =========================
           MESSAGE
        ========================= */

        .user-message {

            background: #fafafa;

            border-left: 4px solid #e63946;

            padding: 12px 15px;

            border-radius: 5px;

            color: #555;

            margin-bottom: 20px;

        }


        /* =========================
           STATUS
        ========================= */

        .status-row {

            display: flex;

            justify-content: space-between;

            align-items: center;

            flex-wrap: wrap;

            gap: 10px;

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


        .status-completed {

            background: #d1ecf1;

            color: #0c5460;

        }


        .status-cancelled {

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


        /* =========================
           EMPTY STATE
        ========================= */

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

        }


        .browse-btn:hover {

            background: #c92f3c;

        }


        /* =========================
           BACK BUTTON
        ========================= */

        .back-btn {

            display: inline-block;

            margin-top: 10px;

            color: #555;

            text-decoration: none;

        }


        .back-btn:hover {

            color: #e63946;

        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 850px) {

            .test-drive-card {

                grid-template-columns: 1fr;

            }


            .car-image {

                height: 250px;

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

        }

    </style>

</head>


<body>


<!-- =========================
     NAVIGATION
========================= -->

<nav class="main-nav">


    <div class="logo">
        CarHub
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



<!-- =========================
     MAIN PAGE
========================= -->

<section class="my-test-drive-page">


    <div class="page-container">


        <!-- PAGE HEADER -->

        <div class="page-header">

            <h1>
                My Test Drives
            </h1>

            <p>
                View your test drive requests and their current status.
            </p>

        </div>



        <?php if ($result->num_rows > 0): ?>


            <!-- =========================
                 DISPLAY REQUESTS
            ========================= -->

            <?php while ($drive = $result->fetch_assoc()): ?>


                <?php

                $status = strtolower($drive["status"]);


                if ($status == "pending") {

                    $status_class = "status-pending";

                }
                elseif ($status == "approved") {

                    $status_class = "status-approved";

                }
                elseif ($status == "completed") {

                    $status_class = "status-completed";

                }
                elseif ($status == "cancelled") {

                    $status_class = "status-cancelled";

                }
                else {

                    $status_class = "status-default";

                }

                ?>


                <div class="test-drive-card">


                    <!-- CAR IMAGE -->

                    <img
                        class="car-image"

                        src="<?php
                        echo htmlspecialchars($drive["image"]);
                        ?>"

                        alt="<?php
                        echo htmlspecialchars($drive["car_name"]);
                        ?>"
                    >



                    <!-- CARD CONTENT -->

                    <div class="card-content">


                        <div class="car-brand">

                            <?php
                            echo htmlspecialchars($drive["brand"]);
                            ?>

                        </div>


                        <h2>

                            <?php
                            echo htmlspecialchars($drive["car_name"]);
                            ?>

                        </h2>


                        <div class="car-price">

                            ₹ <?php
                            echo number_format($drive["price"]);
                            ?>

                        </div>



                        <!-- DETAILS -->

                        <div class="details-grid">


                            <div class="detail-box">

                                <div class="detail-label">
                                    Test Drive Date
                                </div>

                                <div class="detail-value">

                                    <?php

                                    echo date(
                                        "d M Y",
                                        strtotime(
                                            $drive["test_drive_date"]
                                        )
                                    );

                                    ?>

                                </div>

                            </div>



                            <div class="detail-box">

                                <div class="detail-label">
                                    Preferred Time
                                </div>

                                <div class="detail-value">

                                    <?php

                                    echo date(
                                        "h:i A",
                                        strtotime(
                                            $drive["test_drive_time"]
                                        )
                                    );

                                    ?>

                                </div>

                            </div>



                            <div class="detail-box">

                                <div class="detail-label">
                                    Location
                                </div>

                                <div class="detail-value">

                                    <?php

                                    echo htmlspecialchars(
                                        $drive["location"]
                                    );

                                    ?>

                                </div>

                            </div>



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


                        </div>



                        <!-- MESSAGE -->

                        <?php if (!empty($drive["message"])): ?>

                            <div class="user-message">

                                <strong>
                                    Your Message:
                                </strong>

                                <br>

                                <?php

                                echo htmlspecialchars(
                                    $drive["message"]
                                );

                                ?>

                            </div>

                        <?php endif; ?>



                        <!-- STATUS -->

                        <div class="status-row">


                            <span
                                class="status
                                <?php echo $status_class; ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $drive["status"]
                                );

                                ?>

                            </span>


                            <span class="request-date">

                                Request submitted:

                                <?php

                                echo date(
                                    "d M Y",
                                    strtotime(
                                        $drive["created_at"]
                                    )
                                );

                                ?>

                            </span>


                        </div>


                    </div>

                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <!-- =========================
                 NO TEST DRIVES
            ========================= -->

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