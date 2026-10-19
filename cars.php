```php
<?php
require_once __DIR__ . '/config/db.php';

/*
|--------------------------------------------------------------------------
| CarHub - Cars Listing Page
|--------------------------------------------------------------------------
| Car images folder: images/cars/
| Database table: cars
*/

// Escape output safely
function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Get the first available field from a car record
function getCarValue($car, $fields, $default = '')
{
    foreach ($fields as $field) {
        if (isset($car[$field]) && $car[$field] !== '') {
            return $car[$field];
        }
    }

    return $default;
}

// Find the car image URL
function getCarImage($car)
{
    $filename = getCarValue($car, [
        'image',
        'image_filename',
        'image_name',
        'image_url',
        'image_path',
        'photo',
        'car_image'
    ]);

    // If the database has no image filename, use the car name
    // to find a matching image in images/cars/.
    if ($filename === '') {
        $name = strtolower(
            getCarValue($car, ['name', 'model'], '')
        );

        $brand = strtolower(
            getCarValue($car, ['brand'], '')
        );

        $search = $brand . ' ' . $name;

        $imageMap = [
            'bmw' => 'bmw-3-series.jpg',
            'audi' => 'audi-a4.jpg',
            'lexus' => 'lexus-es.jpg',
            'swift' => 'maruti-swift.jpg',
            'i20' => 'hyundai-i20.jpg',
            'velar' => 'range-rover-velar.jpg',
            'fortuner' => 'toyota-fortuner.jpg',
            'gle' => 'mercedes-gle.jpg',
            'c-class' => 'mercedes-c-class.jpg',
            'e-class' => 'mercedes-e-class.jpg'
        ];

        foreach ($imageMap as $keyword => $image) {
            if (strpos($search, $keyword) !== false) {
                $filename = $image;
                break;
            }
        }
    }

    // No image found
    if ($filename === '') {
        return '';
    }

    // Allow complete external image URLs when stored in the database
    if (filter_var($filename, FILTER_VALIDATE_URL)) {
        $scheme = strtolower((string)parse_url($filename, PHP_URL_SCHEME));

        if (in_array($scheme, ['http', 'https'], true)) {
            return $filename;
        }

        return '';
    }

    // Only accept a filename, not an arbitrary filesystem path
    $filename = basename(str_replace('\\', '/', $filename));

    $imageDirectory = __DIR__ . '/images/cars/';
    $imageFile = $imageDirectory . $filename;

    if (is_file($imageFile)) {
        return 'images/cars/' . rawurlencode($filename);
    }

    return '';
}

// Fetch cars from the database
$cars = [];
$errorMessage = '';

try {
    $result = $conn->query("SELECT * FROM cars ORDER BY id DESC LIMIT 10");

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $cars[] = $row;
        }

        $result->free();
    }
} catch (Throwable $error) {
    error_log('CarHub cars listing error: ' . $error->getMessage());
    $errorMessage = 'Unable to load cars right now. Please try again later.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Browse Cars | CarHub</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f5f7fb;
            color: #222;
            line-height: 1.6;
        }

        a {
            text-decoration: none;
        }

        .navbar {
            background: #111827;
            color: #fff;
            padding: 16px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .logo {
            color: #fff;
            font-size: 26px;
            font-weight: 800;
        }

        .logo span {
            color: #f59e0b;
        }

        .nav-links {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: #fff;
            font-size: 14px;
        }

        .nav-links a:hover {
            color: #f59e0b;
        }

        .hero {
            background: linear-gradient(135deg, #111827, #263b59);
            color: white;
            padding: 55px 6%;
            text-align: center;
        }

        .hero h1 {
            font-size: clamp(30px, 4vw, 44px);
            margin-bottom: 10px;
        }

        .hero p {
            color: #d1d5db;
            max-width: 650px;
            margin: 0 auto;
        }

        .container {
            width: 88%;
            max-width: 1250px;
            margin: 35px auto 60px;
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 25px;
        }

        .section-heading h2 {
            font-size: 27px;
            color: #111827;
        }

        .car-count {
            color: #64748b;
            font-size: 14px;
        }

        .car-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 25px;
        }

        .car-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 5px 22px rgba(15, 23, 42, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .car-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.14);
        }

        .car-image {
            width: 100%;
            height: 220px;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-placeholder {
            color: #64748b;
            font-size: 15px;
            text-align: center;
            padding: 20px;
        }

        .car-content {
            padding: 20px;
        }

        .car-title {
            color: #111827;
            font-size: 21px;
            line-height: 1.3;
            margin-bottom: 5px;
        }

        .car-brand {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .car-price {
            color: #e58a00;
            font-size: 23px;
            font-weight: 800;
            margin: 10px 0 15px;
        }

        .car-specs {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 18px;
        }

        .spec {
            background: #f1f5f9;
            color: #475569;
            border-radius: 6px;
            padding: 5px 9px;
            font-size: 12px;
        }

        .details-btn {
            display: block;
            background: #111827;
            color: #fff;
            padding: 12px;
            border-radius: 7px;
            text-align: center;
            font-weight: 700;
            transition: background 0.2s ease;
        }

        .details-btn:hover {
            background: #f59e0b;
            color: #111827;
        }

        .empty-state,
        .error-state {
            background: #fff;
            border-radius: 12px;
            padding: 35px 20px;
            text-align: center;
            color: #64748b;
            grid-column: 1 / -1;
        }

        .error-state {
            color: #b91c1c;
        }

        footer {
            background: #111827;
            color: #cbd5e1;
            padding: 24px 6%;
            text-align: center;
            font-size: 14px;
        }

        @media (max-width: 950px) {
            .car-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 600px) {
            .navbar {
                padding: 15px 5%;
            }

            .nav-links {
                gap: 12px;
            }

            .hero {
                padding: 40px 5%;
            }

            .container {
                width: 92%;
                margin-top: 25px;
            }

            .car-grid {
                grid-template-columns: 1fr;
            }

            .car-image {
                height: 230px;
            }
        }
    </style>
</head>

<body>

<header class="navbar">
    <a class="logo" href="index.php">Car<span>Hub</span></a>

    <nav class="nav-links">
        <a href="index.php">Home</a>
        <a href="cars.php">Browse Cars</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="my-bookings.php">My Bookings</a>
    </nav>
</header>

<section class="hero">
    <h1>Find Your Perfect Car</h1>
    <p>
        Explore our collection of vehicles, compare their features,
        and choose a car that suits your needs.
    </p>
</section>

<main class="container">
    <div class="section-heading">
        <h2>Available Cars</h2>
        <span class="car-count">
            <?php echo count($cars); ?> cars displayed
        </span>
    </div>

    <div class="car-grid">

        <?php if ($errorMessage !== ''): ?>

            <div class="error-state">
                <?php echo e($errorMessage); ?>
            </div>

        <?php elseif (empty($cars)): ?>

            <div class="empty-state">
                <h3>No cars available</h3>
                <p>Please check again later.</p>
            </div>

        <?php else: ?>

            <?php foreach ($cars as $car): ?>

                <?php
                    $id = (int) getCarValue($car, ['id'], 0);
                    $name = getCarValue($car, ['name', 'model'], 'Car');

                    $brand = getCarValue($car, ['brand'], '');
                    $model = getCarValue($car, ['model'], '');

                    $year = getCarValue($car, ['year'], '');
                    $price = getCarValue($car, ['price'], '');

                    $fuel = getCarValue(
                        $car,
                        ['fuel', 'fuel_type'],
                        ''
                    );

                    $transmission = getCarValue(
                        $car,
                        ['transmission'],
                        ''
                    );

                    $mileage = getCarValue($car, ['mileage'], '');

                    $image = getCarImage($car);

                    $displayName = trim($brand . ' ' . $model);

                    if ($displayName === '') {
                        $displayName = $name;
                    }
                ?>

                <article class="car-card">

                    <div class="car-image">

                        <?php if ($image !== ''): ?>

                            <img
                                src="<?php echo e($image); ?>"
                                alt="<?php echo e($displayName); ?>"
                                loading="lazy"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                            >

                            <div
                                class="image-placeholder"
                                style="display:none;"
                            >
                                Image unavailable
                            </div>

                        <?php else: ?>

                            <div class="image-placeholder">
                                Image unavailable
                            </div>

                        <?php endif; ?>

                    </div>

                    <div class="car-content">

                        <h3 class="car-title">
                            <?php echo e($displayName); ?>
                        </h3>

                        <?php if ($year !== ''): ?>
                            <p class="car-brand">
                                Model year: <?php echo e($year); ?>
                            </p>
                        <?php endif; ?>

                        <div class="car-price">
                            <?php if ($price !== '' && is_numeric($price)): ?>
                                ₹<?php echo e(number_format((float)$price)); ?>
                            <?php else: ?>
                                Price on request
                            <?php endif; ?>
                        </div>

                        <div class="car-specs">

                            <?php if ($fuel !== ''): ?>
                                <span class="spec">
                                    <?php echo e($fuel); ?>
                                </span>
                            <?php endif; ?>

                            <?php if ($transmission !== ''): ?>
                                <span class="spec">
                                    <?php echo e($transmission); ?>
                                </span>
                            <?php endif; ?>

                            <?php if ($mileage !== ''): ?>
                                <span class="spec">
                                    <?php echo e($mileage); ?> km
                                </span>
                            <?php endif; ?>

                        </div>

                        <a
                            class="details-btn"
                            href="car-details.php?id=<?php echo $id; ?>"
                        >
                            View Details
                        </a>

                    </div>
                </article>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>
</main>

<footer>
    <p>&copy; <?php echo date('Y'); ?> CarHub. All rights reserved.</p>
</footer>

</body>
</html>
```