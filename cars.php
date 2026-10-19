
<?php
require_once __DIR__ . '/config/db.php';

/**
 * Safely display text in HTML.
 */
function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Get the first available value from a car database row.
 */
function getCarValue($car, $keys, $default = '')
{
    foreach ($keys as $key) {
        if (isset($car[$key]) && $car[$key] !== '') {
            return $car[$key];
        }
    }

    return $default;
}

/**
 * Format prices using Indian number grouping.
 * Example: 1000000 becomes 10,00,000.
 */
function formatIndianPrice($price)
{
    if ($price === '' || !is_numeric($price)) {
        return 'Price on request';
    }

    $amount = (int) round((float)$price);
    $negative = $amount < 0;
    $digits = (string)abs($amount);

    if (strlen($digits) <= 3) {
        return ($negative ? '-' : '') . $digits;
    }

    $lastThree = substr($digits, -3);
    $remaining = substr($digits, 0, -3);

    $remaining = preg_replace(
        '/\B(?=(\d{2})+(?!\d))/',
        ',',
        $remaining
    );

    return ($negative ? '-' : '') . $remaining . ',' . $lastThree;
}

/**
 * Return the appropriate image for a car.
 *
 * Filenames must match the files inside images/cars/.
 */
function getCarImage($car)
{
    $name = strtolower(trim(
        ($car['brand'] ?? '') . ' ' .
        ($car['name'] ?? '') . ' ' .
        ($car['model'] ?? '')
    ));

    // Exact filenames from the CarHub project.
    $imageMap = [
        'lexus'          => 'lexus-es.avif',
        'fortuner'       => '- toyota-fortuner.jpg.avif',
        'bmw'            => 'bmw-3-series.jpg.avif',
        'audi'           => 'audi-a4.avif',
        'hyundai'        => 'hyundai-i20.jpg.avif',
        'mercedes'       => 'mercedes-benz.jpg.avif',
        'maruti'         => 'maruti-suzuki.jpg.avif',
        'range rover'    => 'range-rover.jpg.avif',
    ];

    // Check specific models first.
    if (strpos($name, 'lexus') !== false) {
        $filename = 'lexus-es.avif';
    } elseif (strpos($name, 'fortuner') !== false) {
        $filename = '- toyota-fortuner.jpg.avif';
    } elseif (strpos($name, 'bmw') !== false) {
        $filename = 'bmw-3-series.jpg.avif';
    } elseif (strpos($name, 'audi') !== false) {
        $filename = 'audi-a4.avif';
    } elseif (strpos($name, 'i20') !== false) {
        $filename = 'hyundai-i20.jpg.avif';
    } elseif (strpos($name, 'mercedes') !== false ||
              strpos($name, 'benz') !== false) {
        $filename = 'mercedes-benz.jpg.avif';
    } elseif (strpos($name, 'maruti') !== false ||
              strpos($name, 'suzuki') !== false) {
        $filename = 'maruti-suzuki.jpg.avif';
    } elseif (strpos($name, 'range rover') !== false) {
        $filename = 'range-rover.jpg.avif';
    } else {
        // Try a filename based on the car name.
        $slug = strtolower(trim(
            ($car['brand'] ?? '') . '-' .
            ($car['model'] ?? '')
        ));

        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = trim(preg_replace('/-+/', '-', $slug), '-');

        $candidates = [
            $slug . '.jpg.avif',
            $slug . '.avif',
            $slug . '.jpg',
            $slug . '.png',
            $slug . '.webp',
        ];

        foreach ($candidates as $candidate) {
            if (is_file(__DIR__ . '/images/cars/' . $candidate)) {
                return 'images/cars/' . rawurlencode($candidate);
            }
        }

        return 'images/cars/car-placeholder.png';
    }

    $fullPath = __DIR__ . '/images/cars/' . $filename;

    if (is_file($fullPath)) {
        // Encode spaces and special characters in the URL.
        return 'images/cars/' . implode(
            '/',
            array_map('rawurlencode', explode('/', $filename))
        );
    }

    return 'images/cars/car-placeholder.png';
}

// Load cars from the connected database.
$cars = [];
$errorMessage = '';

try {
    $result = $conn->query("SELECT * FROM cars ORDER BY id ASC LIMIT 10");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $cars[] = $row;
        }
        $result->free();
    }
} catch (Throwable $e) {
    $errorMessage = 'Unable to load cars right now. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Cars | CarHub</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            color: #222;
        }

        header {
            background: #111827;
            color: white;
            padding: 18px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo {
            color: white;
            font-size: 27px;
            font-weight: bold;
            text-decoration: none;
        }

        .logo span {
            color: #f59e0b;
        }

        nav {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-size: 15px;
        }

        nav a:hover {
            color: #f59e0b;
        }

        .hero {
            background: linear-gradient(120deg, #111827, #374151);
            color: white;
            text-align: center;
            padding: 55px 20px;
        }

        .hero h1 {
            font-size: 36px;
            margin-bottom: 12px;
        }

        .hero p {
            color: #e5e7eb;
            font-size: 16px;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 35px auto;
        }

        .section-heading {
            margin-bottom: 25px;
        }

        .section-heading h2 {
            font-size: 27px;
            margin-bottom: 8px;
        }

        .section-heading p {
            color: #666;
        }

        .car-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 25px;
        }

        .car-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 22px rgba(0, 0, 0, 0.12);
        }

        .car-image {
            width: 100%;
            height: 220px;
            object-fit: cover;
            display: block;
            background: #eee;
        }

        .car-info {
            padding: 20px;
        }

        .car-info h3 {
            font-size: 21px;
            margin-bottom: 10px;
        }

        .car-details {
            color: #666;
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 12px;
        }

        .car-price {
            font-size: 23px;
            font-weight: bold;
            color: #e67e22;
            margin-bottom: 18px;
        }

        .view-button {
            display: block;
            text-align: center;
            text-decoration: none;
            background: #111827;
            color: white;
            padding: 12px;
            border-radius: 6px;
            font-weight: bold;
        }

        .view-button:hover {
            background: #f59e0b;
            color: #111827;
        }

        .message {
            background: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            color: #555;
        }

        footer {
            background: #111827;
            color: #ddd;
            text-align: center;
            padding: 22px;
            margin-top: 50px;
        }

        @media (max-width: 900px) {
            .car-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            header {
                justify-content: center;
            }

            nav {
                justify-content: center;
                gap: 12px;
            }

            .hero h1 {
                font-size: 29px;
            }

            .car-grid {
                grid-template-columns: 1fr;
            }

            .car-image {
                height: 210px;
            }
        }
    </style>
</head>
<body>

<header>
    <a href="index.php" class="logo">Car<span>Hub</span></a>

    <nav>
        <a href="index.php">Home</a>
        <a href="cars.php">Cars</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="my-bookings.php">My Bookings</a>
    </nav>
</header>

<section class="hero">
    <h1>Explore Our Cars</h1>
    <p>Find your preferred car and discover your next drive.</p>
</section>

<main class="container">
    <div class="section-heading">
        <h2>Available Cars</h2>
        <p>Explore our collection and view the details of each vehicle.</p>
    </div>

    <?php if ($errorMessage !== ''): ?>

        <div class="message">
            <?= e($errorMessage) ?>
        </div>

    <?php elseif (empty($cars)): ?>

        <div class="message">
            No cars are currently available.
        </div>

    <?php else: ?>

        <div class="car-grid">

            <?php foreach ($cars as $car): ?>
                <?php
                    $id = (int)($car['id'] ?? 0);

                    $brand = getCarValue($car, ['brand'], '');
                    $name = getCarValue($car, ['name'], '');
                    $model = getCarValue($car, ['model'], '');

                    $displayName = trim($brand . ' ' . $name);

                    if ($displayName === '') {
                        $displayName = $model !== '' ? $model : 'Car';
                    }

                    $year = getCarValue($car, ['year'], 'N/A');
                    $fuel = getCarValue(
                        $car,
                        ['fuel', 'fuel_type'],
                        'N/A'
                    );
                    $transmission = getCarValue(
                        $car,
                        ['transmission'],
                        'N/A'
                    );
                    $price = getCarValue($car, ['price'], '');
                    $image = getCarImage($car);
                ?>

                <article class="car-card">
                    <img
                        class="car-image"
                        src="<?= e($image) ?>"
                        alt="<?= e($displayName) ?>"
                        loading="lazy"
                        onerror="this.onerror=null;this.src='images/cars/car-placeholder.png';"
                    >

                    <div class="car-info">
                        <h3><?= e($displayName) ?></h3>

                        <div class="car-details">
                            <div>Year: <?= e($year) ?></div>
                            <div>Fuel: <?= e($fuel) ?></div>
                            <div>Transmission: <?= e($transmission) ?></div>
                        </div>

                        <div class="car-price">
                            <?php if ($price !== '' && is_numeric($price)): ?>
                                ₹<?= e(formatIndianPrice($price)) ?>
                            <?php else: ?>
                                Price on request
                            <?php endif; ?>
                        </div>

                        <a
                            class="view-button"
                            href="car-details.php?id=<?= $id ?>"
                        >
                            View Details
                        </a>
                    </div>
                </article>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>
</main>

<footer>
    <p>&copy; <?= date('Y') ?> CarHub. All rights reserved.</p>
</footer>

</body>
</html>
