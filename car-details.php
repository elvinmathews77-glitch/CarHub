<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';

// Connect to the CarHub database.
require_once __DIR__ . '/config/db.php';

// Escape output to prevent HTML injection.
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Format prices in Indian numbering format.
function formatIndianPrice($amount): string
{
    $amount = (int) round((float) $amount);

    if ($amount < 0) {
        return '₹' . number_format($amount);
    }

    $number = (string) $amount;

    if (strlen($number) <= 3) {
        return '₹' . $number;
    }

    $lastThree = substr($number, -3);
    $remaining = substr($number, 0, -3);
    $remaining = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $remaining);

    return '₹' . $remaining . ',' . $lastThree;
}

// Get the car ID from the URL.
$carId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$carId || $carId < 1) {
    http_response_code(400);
    $pageError = 'Invalid car ID. Please select a car from the listings.';
    $car = null;
} else {
    $pageError = '';
    $car = null;

    if (!isset($conn) || !($conn instanceof mysqli)) {
        error_log('CarHub: Database connection variable $conn is unavailable.');
        http_response_code(500);
        $pageError = 'Unable to connect to the database. Please try again later.';
    } else {
        try {
            $stmt = $conn->prepare('SELECT * FROM cars WHERE id = ? LIMIT 1');

            if (!$stmt) {
                throw new RuntimeException('Unable to prepare the car query.');
            }

            $stmt->bind_param('i', $carId);
            $stmt->execute();

            $result = $stmt->get_result();
            $car = $result->fetch_assoc();

            $stmt->close();

            if (!$car) {
                http_response_code(404);
                $pageError = 'Sorry, this car could not be found.';
            }
        } catch (Throwable $exception) {
            error_log('CarHub car details error: ' . $exception->getMessage());
            http_response_code(500);
            $pageError = 'Unable to load the car details. Please try again later.';
        }
    }
}

// Read values safely in case some database fields are empty.
$name = '';
$brand = '';
$model = '';
$year = '';
$price = 0;
$fuel = '';
$transmission = '';
$mileage = '';
$imageUrl = '';

if ($car) {
    $name = trim((string)($car['name'] ?? ''));
    $brand = trim((string)($car['brand'] ?? ''));
    $model = trim((string)($car['model'] ?? ''));
    $year = $car['year'] ?? '';
    $price = (float)($car['price'] ?? 0);
    $fuel = $car['fuel'] ?? $car['fuel_type'] ?? '';
    $transmission = $car['transmission'] ?? '';
    $mileage = $car['mileage'] ?? '';

    // Use the image filenames configured for your CarHub project.
    $imageMap = [
        'bmw'      => 'bmw-3-series.jpg.avif',
        'audi'     => 'audi-a4.avif',
        'swift'    => 'maruti-swift.jpg.avif',
        'i20'      => 'hyundai-i20.jpg.avif',
        'lexus'    => 'lexus-es.jpg.avif',
        'velar'    => 'range-rover-velar.jpg.avif',
        'fortuner' => '- toyota-fortuner.jpg.avif',
        'gle'      => 'mercedes-gle.jpg.avif',
        'c-class'  => 'mercedes-c-class.jpg.avif',
        'e-class'  => 'mercedes-e-class.jpg.avif'
    ];

    $searchText = strtolower($brand . ' ' . $model . ' ' . $name);

    foreach ($imageMap as $keyword => $filename) {
        if (strpos($searchText, $keyword) !== false) {
            $imagePath = __DIR__ . '/images/cars/' . $filename;

            if (is_file($imagePath)) {
                $imageUrl = 'images/cars/' . rawurlencode($filename);
            }

            break;
        }
    }
}

$pageTitle = $car
    ? (($name !== '' ? $name : trim($brand . ' ' . $model)) . ' | CarHub')
    : 'Car Details | CarHub';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f6f9;
            color: #20252b;
        }

        header {
            background: #101820;
            color: #fff;
            padding: 17px 6%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo {
            color: #fff;
            text-decoration: none;
            font-size: 26px;
            font-weight: bold;
        }

        nav {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            font-size: 15px;
        }

        nav a:hover {
            color: #ffb703;
        }

        .container {
            max-width: 1150px;
            width: 92%;
            margin: 35px auto;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #1769aa;
            text-decoration: none;
            font-weight: bold;
        }

        .car-card {
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr);
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 5px 24px rgba(0, 0, 0, 0.08);
        }

        .car-image {
            min-height: 380px;
            background: #eef1f4;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .car-image img {
            display: block;
            width: 100%;
            height: 100%;
            max-height: 450px;
            object-fit: contain;
        }

        .no-image {
            color: #697586;
            text-align: center;
            padding: 25px;
        }

        .car-info {
            padding: 32px;
        }

        .car-info h1 {
            font-size: 30px;
            margin: 0 0 10px;
            overflow-wrap: anywhere;
        }

        .subtitle {
            color: #667085;
            margin-bottom: 20px;
        }

        .price {
            color: #16803c;
            font-size: 29px;
            font-weight: bold;
            margin: 20px 0;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 13px;
            margin: 25px 0;
        }

        .detail {
            padding: 14px;
            background: #f5f7fa;
            border-radius: 8px;
            overflow-wrap: anywhere;
        }

        .detail-label {
            display: block;
            color: #667085;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .detail-value {
            font-weight: bold;
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 25px;
        }

        .btn {
            display: inline-block;
            border: none;
            border-radius: 7px;
            padding: 13px 18px;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
        }

        .btn-primary {
            color: #fff;
            background: #1769aa;
        }

        .btn-secondary {
            color: #20252b;
            background: #e9edf2;
        }

        .btn:hover {
            opacity: 0.88;
        }

        .message {
            background: #fff;
            padding: 28px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
        }

        footer {
            margin-top: 45px;
            padding: 22px;
            text-align: center;
            background: #101820;
            color: #fff;
        }

        @media (max-width: 760px) {
            header {
                padding: 16px 5%;
            }

            nav {
                gap: 12px;
            }

            .car-card {
                grid-template-columns: 1fr;
            }

            .car-image {
                min-height: 250px;
            }

            .car-info {
                padding: 23px;
            }

            .car-info h1 {
                font-size: 25px;
            }

            .price {
                font-size: 25px;
            }
        }

        @media (max-width: 400px) {
            .details-grid {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
<header>
    <a class="logo" href="index.php">CarHub</a>

    <nav>
        <a href="index.php">Home</a>
        <a href="cars.php">Cars</a>

        <?php if (!empty($_SESSION['user_id'])): ?>
            <a href="my-bookings.php">My Bookings</a>
            <a href="my-test-drives.php">My Test Drives</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<main class="container">
    <a class="back-link" href="cars.php">&larr; Back to Cars</a>

    <?php if (!$car): ?>
        <section class="message">
            <h1>Car Details Unavailable</h1>
            <p><?= e($pageError) ?></p>
            <a class="btn btn-primary" href="cars.php">Browse Cars</a>
        </section>
    <?php else: ?>
        <section class="car-card">
            <div class="car-image">
                <?php if ($imageUrl !== ''): ?>
                    <img
                        src="<?= e($imageUrl) ?>"
                        alt="<?= e(trim($brand . ' ' . $model . ' ' . $name)) ?>"
                    >
                <?php else: ?>
                    <div class="no-image">
                        <p>Image is currently unavailable.</p>
                        <p>Please check the image filename in <code>images/cars/</code>.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="car-info">
                <h1><?= e($name !== '' ? $name : trim($brand . ' ' . $model)) ?></h1>

                <p class="subtitle">
                    <?= e($brand) ?>
                    <?= $model !== '' ? ' ' . e($model) : '' ?>
                    <?= $year !== '' ? ' · ' . e($year) : '' ?>
                </p>

                <div class="price">
                    <?= e(formatIndianPrice($price)) ?>
                </div>

                <h2>Vehicle Specifications</h2>

                <div class="details-grid">
                    <div class="detail">
                        <span class="detail-label">Brand</span>
                        <span class="detail-value"><?= e($brand ?: 'Not specified') ?></span>
                    </div>

                    <div class="detail">
                        <span class="detail-label">Model</span>
                        <span class="detail-value"><?= e($model ?: 'Not specified') ?></span>
                    </div>

                    <div class="detail">
                        <span class="detail-label">Manufacturing Year</span>
                        <span class="detail-value"><?= e($year ?: 'Not specified') ?></span>
                    </div>

                    <div class="detail">
                        <span class="detail-label">Fuel Type</span>
                        <span class="detail-value"><?= e($fuel ?: 'Not specified') ?></span>
                    </div>

                    <div class="detail">
                        <span class="detail-label">Transmission</span>
                        <span class="detail-value"><?= e($transmission ?: 'Not specified') ?></span>
                    </div>

                    <div class="detail">
                        <span class="detail-label">Mileage</span>
                        <span class="detail-value">
                            <?= e($mileage !== '' && $mileage !== null ? $mileage : 'Not specified') ?>
                        </span>
                    </div>
                </div>

                <div class="actions">
                    <a
                        class="btn btn-primary"
                        href="booking.php?car_id=<?= (int)$car['id'] ?>"
                    >Book This Car</a>

                    <a
                        class="btn btn-secondary"
                        href="test-drive.php?car_id=<?= (int)$car['id'] ?>"
                    >Book a Test Drive</a>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> CarHub. All rights reserved.</p>
</footer>
</body>
</html>
