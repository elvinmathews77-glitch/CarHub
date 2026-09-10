<?php
$conn = new mysqli("localhost", "root", "", "carhub");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid car ID.");
}

$car_id = (int) $_GET['id'];

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
$year = $car['year'] ?? '2025';
$fuel = $car['fuel_type'] ?? ($car['fuel'] ?? 'Petrol');
$transmission = $car['transmission'] ?? 'Automatic';
$mileage = $car['mileage'] ?? '15 km/l';

$brandLower = strtolower($brand);

/*
|--------------------------------------------------------------------------
| Car images
|--------------------------------------------------------------------------
| No image files need to be downloaded.
*/

if (strpos($brandLower, 'mercedes') !== false) {

    $image = "https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=1400&q=85";

} elseif (strpos($brandLower, 'audi') !== false) {

    $image = "https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=1400&q=85";

} elseif (strpos($brandLower, 'bmw') !== false) {

    $image = "https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=1400&q=85";

} else {

    $image = "https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1400&q=85";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
<?php echo htmlspecialchars($brand . ' ' . $model); ?> | CarHub
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

/* NAVBAR */

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

/* MAIN */

.container {
    width: 90%;
    max-width: 1200px;
    margin: 45px auto;
}

/* BACK */

.back {
    display: inline-block;
    margin-bottom: 25px;
    color: #172033;
    text-decoration: none;
    font-weight: bold;
}

.back:hover {
    color: #ef3340;
}

/* CARD */

.details-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 12px 35px rgba(0,0,0,0.08);
}

/* IMAGE */

.car-image {
    width: 100%;
    height: 500px;
    background: #e7eaee;
    position: relative;
}

.car-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.image-fallback {
    display: none;
    position: absolute;
    inset: 0;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    background: linear-gradient(135deg, #e9edf2, #d9dee6);
}

.image-fallback .icon {
    font-size: 100px;
    margin-bottom: 15px;
}

.image-fallback h3 {
    color: #667085;
    font-size: 25px;
}

/* CONTENT */

.content {
    padding: 40px;
}

.brand {
    color: #ef3340;
    text-transform: uppercase;
    font-size: 15px;
    font-weight: bold;
    margin-bottom: 8px;
}

h1 {
    font-size: 42px;
    margin-bottom: 15px;
}

.price {
    font-size: 34px;
    font-weight: bold;
    color: #ef3340;
    margin-bottom: 30px;
}

/* SPECS */

.specs {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 35px;
}

.spec {
    background: #f5f6f8;
    padding: 20px;
    border-radius: 12px;
}

.spec-label {
    display: block;
    color: #667085;
    font-size: 14px;
    margin-bottom: 8px;
}

.spec-value {
    font-size: 17px;
    font-weight: bold;
}

/* DESCRIPTION */

.description {
    border-top: 1px solid #e5e7eb;
    padding-top: 30px;
    margin-bottom: 30px;
}

.description h2 {
    font-size: 25px;
    margin-bottom: 12px;
}

.description p {
    color: #667085;
    line-height: 1.7;
    font-size: 16px;
}

/* BUTTONS */

.actions {
    display: flex;
    gap: 15px;
}

.btn {
    flex: 1;
    text-align: center;
    padding: 17px;
    border-radius: 9px;
    text-decoration: none;
    font-weight: bold;
    font-size: 17px;
}

.book-btn {
    background: #ef3340;
    color: white;
}

.test-btn {
    background: #111827;
    color: white;
}

.book-btn:hover {
    background: #d92835;
}

.test-btn:hover {
    background: #ef3340;
}

/* FOOTER */

footer {
    background: #111;
    color: white;
    text-align: center;
    padding: 30px;
    margin-top: 70px;
}

/* MOBILE */

@media(max-width: 800px) {

    .nav-links {
        display: none;
    }

    .car-image {
        height: 300px;
    }

    h1 {
        font-size: 32px;
    }

    .specs {
        grid-template-columns: repeat(2, 1fr);
    }

    .actions {
        flex-direction: column;
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

    <div class="nav-links">

        <a href="index.php">Home</a>

        <a href="cars.php">Cars</a>

        <a href="about.php">About</a>

        <a href="contact.php">Contact</a>

        <a href="my-bookings.php">My Bookings</a>

    </div>

</nav>


<!-- MAIN -->

<div class="container">

    <a href="cars.php" class="back">
        ← Back to Cars
    </a>


    <div class="details-card">


        <!-- CAR IMAGE -->

        <div class="car-image">

            <img
                src="<?php echo htmlspecialchars($image); ?>"
                alt="<?php echo htmlspecialchars($brand . ' ' . $model); ?>"
                onerror="this.style.display='none'; document.getElementById('fallback').style.display='flex';"
            >

            <div class="image-fallback" id="fallback">

                <div class="icon">
                    🚘
                </div>

                <h3>
                    <?php echo htmlspecialchars($brand); ?>
                </h3>

            </div>

        </div>


        <!-- CONTENT -->

        <div class="content">

            <div class="brand">
                <?php echo htmlspecialchars($brand); ?>
            </div>

            <h1>
                <?php echo htmlspecialchars($model); ?>
            </h1>

            <div class="price">
                ₹<?php echo number_format((float)$price); ?>
            </div>


            <!-- SPECIFICATIONS -->

            <div class="specs">

                <div class="spec">

                    <span class="spec-label">
                        Year
                    </span>

                    <span class="spec-value">
                        <?php echo htmlspecialchars($year); ?>
                    </span>

                </div>


                <div class="spec">

                    <span class="spec-label">
                        Fuel
                    </span>

                    <span class="spec-value">
                        <?php echo htmlspecialchars($fuel); ?>
                    </span>

                </div>


                <div class="spec">

                    <span class="spec-label">
                        Transmission
                    </span>

                    <span class="spec-value">
                        <?php echo htmlspecialchars($transmission); ?>
                    </span>

                </div>


                <div class="spec">

                    <span class="spec-label">
                        Mileage
                    </span>

                    <span class="spec-value">
                        <?php echo htmlspecialchars($mileage); ?>
                    </span>

                </div>

            </div>


            <!-- DESCRIPTION -->

            <div class="description">

                <h2>
                    About This Car
                </h2>

                <p>
                    Experience a premium driving experience with
                    the <?php echo htmlspecialchars($brand . ' ' . $model); ?>.
                    Explore its features, specifications and performance
                    before making your purchase.
                </p>

            </div>


            <!-- ACTION BUTTONS -->

            <div class="actions">

                <a
                    href="booking.php?car_id=<?php echo $car_id; ?>"
                    class="btn book-btn"
                >
                    Book This Car
                </a>

                <a
                    href="test-drive.php?car_id=<?php echo $car_id; ?>"
                    class="btn test-btn"
                >
                    Request Test Drive
                </a>

            </div>

        </div>

    </div>

</div>


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