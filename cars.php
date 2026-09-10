<?php
session_start();
require_once "config/db.php";

$cars = [];

$sql = "SELECT * FROM cars ORDER BY id DESC";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
}

/*
|--------------------------------------------------------------------------
| Car Images
|--------------------------------------------------------------------------
| These are used instead of the broken image values in the database.
*/

function getCarImage($name)
{
    $name = strtolower(trim($name));

    if (strpos($name, 'audi') !== false) {
        return "https://commons.wikimedia.org/wiki/Special:Redirect/file/Audi_A4_B8.jpg";
    }

    if (strpos($name, 'mercedes') !== false) {
        return "https://commons.wikimedia.org/wiki/Special:Redirect/file/2025_Mercedes-Benz_C-Class_-_01.jpg";
    }

    if (strpos($name, 'bmw') !== false) {
        return "https://commons.wikimedia.org/wiki/Special:Redirect/file/BMW_3_Series_3.jpg";
    }

    return "";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cars - CarHub</title>

<style>

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f5f5f5;
    color: #171717;
}

/* NAVBAR */

.navbar {
    height: 98px;
    background: #111111;

    display: flex;
    align-items: center;
    justify-content: space-between;

    padding: 0 7.5%;
}

.logo {
    color: white;
    font-size: 32px;
    font-weight: 800;
}

.logo span {
    color: #ef3340;
}

.nav-links {
    display: flex;
    gap: 30px;
    list-style: none;
}

.nav-links a {
    color: white;
    text-decoration: none;
    font-size: 16px;
}

.nav-links a:hover {
    color: #ef3340;
}

/* PAGE */

.page {
    max-width: 1400px;
    margin: auto;

    padding: 65px 30px 80px;
}

.heading {
    text-align: center;

    margin-bottom: 50px;
}

.heading h1 {
    font-size: 48px;

    margin-bottom: 12px;
}

.heading h1 span {
    color: #ef3340;
}

.heading p {
    color: #64748b;

    font-size: 19px;
}

/* GRID */

.car-grid {
    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 32px;
}

/* CARD */

.car-card {
    background: white;

    border-radius: 12px;

    overflow: hidden;

    box-shadow:
        0 8px 25px rgba(0,0,0,.08);

    transition: .25s;
}

.car-card:hover {
    transform: translateY(-5px);
}

/* IMAGE */

.car-image {
    width: 100%;

    height: 255px;

    background: #e5e7eb;

    overflow: hidden;
}

.car-image img {
    width: 100%;

    height: 100%;

    object-fit: cover;

    display: block;
}

/* CONTENT */

.car-content {
    padding: 24px;
}

.brand {
    color: #ef3340;

    font-size: 14px;

    font-weight: 700;

    text-transform: uppercase;

    margin-bottom: 10px;
}

.car-name {
    font-size: 27px;

    margin-bottom: 16px;
}

/* SPECS */

.specs {
    display: flex;

    gap: 8px;

    flex-wrap: wrap;

    margin-bottom: 20px;
}

.spec {
    background: #f1f1f1;

    color: #475569;

    padding: 8px 11px;

    border-radius: 4px;

    font-size: 13px;
}

/* PRICE */

.price {
    font-size: 24px;

    font-weight: 800;

    margin-bottom: 20px;
}

/* BUTTON */

.view-button {
    display: block;

    width: 100%;

    padding: 14px;

    background: #ef3340;

    color: white;

    text-decoration: none;

    text-align: center;

    border-radius: 5px;

    font-size: 16px;

    font-weight: 600;
}

.view-button:hover {
    background: #d92735;
}

/* EMPTY */

.no-cars {
    text-align: center;

    background: white;

    padding: 70px 20px;

    border-radius: 12px;
}

.no-cars h2 {
    margin-bottom: 10px;
}

.no-cars p {
    color: #64748b;
}

/* FOOTER */

footer {
    background: #111111;

    color: white;

    text-align: center;

    padding: 35px;
}

footer p {
    color: #9ca3af;

    margin: 5px;
}

/* MOBILE */

@media (max-width: 1000px) {

    .car-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

}

@media (max-width: 700px) {

    .navbar {
        height: auto;

        padding: 20px;

        flex-direction: column;

        gap: 20px;
    }

    .nav-links {
        flex-wrap: wrap;

        justify-content: center;

        gap: 15px;
    }

    .car-grid {
        grid-template-columns: 1fr;
    }

    .heading h1 {
        font-size: 36px;
    }

}

</style>

</head>

<body>


<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">
        CAR<span>HUB</span>
    </div>

    <ul class="nav-links">

        <li>
            <a href="index.php">Home</a>
        </li>

        <li>
            <a href="cars.php">Cars</a>
        </li>

        <li>
            <a href="about.php">About</a>
        </li>

        <li>
            <a href="contact.php">Contact</a>
        </li>

        <li>
            <a href="my-bookings.php">My Bookings</a>
        </li>

        <?php if (isset($_SESSION["user_id"])): ?>

            <li>
                <a href="logout.php">Logout</a>
            </li>

        <?php else: ?>

            <li>
                <a href="login.php">Login</a>
            </li>

        <?php endif; ?>

    </ul>

</nav>


<!-- MAIN -->

<main class="page">


    <div class="heading">

        <h1>
            Explore Our
            <span>Cars</span>
        </h1>

        <p>
            Find the perfect car for your needs and budget.
        </p>

    </div>


    <?php if (!empty($cars)): ?>

        <div class="car-grid">


            <?php foreach ($cars as $car): ?>

                <?php

                $id = (int)$car['id'];

                $name = !empty($car['name'])
                    ? $car['name']
                    : 'Car';

                $brand = !empty($car['brand'])
                    ? $car['brand']
                    : 'Car';

                $year = !empty($car['year'])
                    ? $car['year']
                    : '2025';

                $fuel = !empty($car['fuel'])
                    ? $car['fuel']
                    : 'Petrol';

                $transmission = !empty($car['transmission'])
                    ? $car['transmission']
                    : 'Automatic';

                $mileage = !empty($car['mileage'])
                    ? $car['mileage']
                    : 'N/A';

                $price = isset($car['price'])
                    ? (float)$car['price']
                    : 0;

                $image = getCarImage($name);

                ?>


                <div class="car-card">


                    <!-- CAR IMAGE -->

                    <div class="car-image">

                        <?php if ($image != ""): ?>

                            <img
                                src="<?php echo htmlspecialchars($image); ?>"
                                alt="<?php echo htmlspecialchars($name); ?>"
                            >

                        <?php else: ?>

                            <div style="
                                width:100%;
                                height:100%;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                color:#64748b;
                                font-size:18px;
                            ">
                                Car Image
                            </div>

                        <?php endif; ?>

                    </div>


                    <!-- CAR CONTENT -->

                    <div class="car-content">


                        <div class="brand">

                            <?php
                            echo htmlspecialchars($brand);
                            ?>

                        </div>


                        <h2 class="car-name">

                            <?php
                            echo htmlspecialchars($name);
                            ?>

                        </h2>


                        <div class="specs">


                            <span class="spec">

                                <?php
                                echo htmlspecialchars($year);
                                ?>

                            </span>


                            <span class="spec">

                                <?php
                                echo htmlspecialchars($fuel);
                                ?>

                            </span>


                            <span class="spec">

                                <?php
                                echo htmlspecialchars($transmission);
                                ?>

                            </span>


                            <span class="spec">

                                <?php
                                echo htmlspecialchars($mileage);
                                ?>

                            </span>


                        </div>


                        <div class="price">

                            ₹<?php
                            echo number_format($price);
                            ?>

                        </div>


                        <a
                            href="car-details.php?id=<?php echo $id; ?>"
                            class="view-button"
                        >
                            View Details
                        </a>


                    </div>

                </div>


            <?php endforeach; ?>


        </div>


    <?php else: ?>


        <div class="no-cars">

            <h2>
                No Cars Available
            </h2>

            <p>
                There are currently no cars available.
            </p>

        </div>


    <?php endif; ?>


</main>


<!-- FOOTER -->

<footer>

    <p>
        © <?php echo date("Y"); ?> CarHub
    </p>

    <p>
        Your trusted car selling platform.
    </p>

</footer>


</body>

</html>