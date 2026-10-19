```php
<?php
session_start();
require_once "config/db.php";

$cars = [];

// Fetch cars from the database
$sql = "SELECT * FROM cars ORDER BY id DESC";
$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
}

/**
 * Return a usable database image path or URL.
 */
function getCarImage($car)
{
    $image = trim((string)($car['image'] ?? ''));

    // Accept valid HTTP/HTTPS URLs
    if (
        $image !== '' &&
        filter_var($image, FILTER_VALIDATE_URL) &&
        in_array(
            strtolower((string)parse_url($image, PHP_URL_SCHEME)),
            ['http', 'https'],
            true
        )
    ) {
        return $image;
    }

    // Accept local relative image paths
    if (
        $image !== '' &&
        !preg_match('/^[a-z][a-z0-9+.-]*:/i', $image) &&
        !str_starts_with($image, '//') &&
        !str_contains($image, '..')
    ) {
        return $image;
    }

    return '';
}

/**
 * Brand-specific fallback image.
 * The database image is always tried first.
 */
function getFallbackImage($name, $brand)
{
    $text = strtolower($name . ' ' . $brand);

    if (strpos($text, 'audi') !== false) {
        return 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=900&q=80';
    }

    if (strpos($text, 'mercedes') !== false) {
        return 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=900&q=80';
    }

    if (strpos($text, 'bmw') !== false) {
        return 'https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=900&q=80';
    }

    return '';
}

function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
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

        .navbar {
            min-height: 98px;
            background: #111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 7.5%;
            gap: 20px;
        }

        .logo {
            color: white;
            font-size: 32px;
            font-weight: 800;
            white-space: nowrap;
        }

        .logo span {
            color: #ef3340;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            list-style: none;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 16px;
        }

        .nav-links a:hover {
            color: #ef3340;
        }

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

        .car-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 32px;
        }

        .car-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0, 0, 0, .08);
            transition: transform .25s;
        }

        .car-card:hover {
            transform: translateY(-5px);
        }

        .car-image {
            width: 100%;
            height: 255px;
            background: #e5e7eb;
            overflow: hidden;
            position: relative;
        }

        .car-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .image-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            font-size: 18px;
            background: #e5e7eb;
        }

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
            overflow-wrap: anywhere;
        }

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

        .price {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 20px;
        }

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

        footer {
            background: #111;
            color: white;
            text-align: center;
            padding: 35px;
        }

        footer p {
            color: #9ca3af;
            margin: 5px;
        }

        @media (max-width: 1000px) {
            .car-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 700px) {
            .navbar {
                flex-direction: column;
                padding: 20px;
            }

            .nav-links {
                justify-content: center;
                gap: 15px;
            }

            .car-grid {
                grid-template-columns: 1fr;
            }

            .heading h1 {
                font-size: 36px;
            }

            .page {
                padding: 40px 18px 60px;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <div class="logo">CAR<span>HUB</span></div>

    <ul class="nav-links">
        <li><a href="index.php">Home</a></li>
        <li><a href="cars.php">Cars</a></li>
        <li><a href="about.php">About</a></li>
        <li><a href="contact.php">Contact</a></li>
        <li><a href="my-bookings.php">My Bookings</a></li>

        <?php if (isset($_SESSION['user_id'])): ?>
            <li><a href="logout.php">Logout</a></li>
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>

<main class="page">

    <div class="heading">
        <h1>Explore Our <span>Cars</span></h1>
        <p>Find the perfect car for your needs and budget.</p>
    </div>

    <?php if (!empty($cars)): ?>

        <div class="car-grid">

            <?php foreach ($cars as $car): ?>

                <?php
                $id = (int)($car['id'] ?? 0);
                $name = trim((string)($car['name'] ?? 'Car'));
                $brand = trim((string)($car['brand'] ?? 'Car'));

                $year = $car['year'] ?? 'N/A';
                $fuel = $car['fuel'] ?? 'N/A';
                $transmission = $car['transmission'] ?? 'N/A';
                $mileage = $car['mileage'] ?? 'N/A';
                $price = (float)($car['price'] ?? 0);

                $image = getCarImage($car);
                $fallbackImage = getFallbackImage($name, $brand);
                ?>

                <div class="car-card">

                    <div class="car-image">

                        <?php if ($image !== ''): ?>

                            <img
                                src="<?= e($image) ?>"
                                alt="<?= e($name) ?>"
                                loading="lazy"
                                onerror="handleImageError(this)"
                                data-fallback="<?= e($fallbackImage) ?>"
                            >

                        <?php elseif ($fallbackImage !== ''): ?>

                            <img
                                src="<?= e($fallbackImage) ?>"
                                alt="<?= e($name) ?>"
                                loading="lazy"
                                onerror="handleImageError(this)"
                                data-fallback=""
                            >

                        <?php else: ?>

                            <div class="image-placeholder">Car Image Unavailable</div>

                        <?php endif; ?>

                    </div>

                    <div class="car-content">

                        <div class="brand"><?= e($brand) ?></div>

                        <h2 class="car-name"><?= e($name) ?></h2>

                        <div class="specs">
                            <span class="spec"><?= e($year) ?></span>
                            <span class="spec"><?= e($fuel) ?></span>
                            <span class="spec"><?= e($transmission) ?></span>
                            <span class="spec"><?= e($mileage) ?></span>
                        </div>

                        <div class="price">
                            ₹<?= number_format($price) ?>
                        </div>

                        <a
                            href="car-details.php?id=<?= $id ?>"
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
            <h2>No Cars Available</h2>
            <p>There are currently no cars available.</p>
        </div>

    <?php endif; ?>

</main>

<footer>
    <p>&copy; <?= date('Y') ?> CarHub</p>
    <p>Your trusted car selling platform.</p>
</footer>

<script>
function handleImageError(img) {
    const fallback = img.dataset.fallback || '';

    // If the database image fails, try the fallback image once.
    if (fallback && img.src !== fallback) {
        img.dataset.fallback = '';
        img.src = fallback;
        return;
    }

    // If both images fail, show a clear placeholder.
    const placeholder = document.createElement('div');
    placeholder.className = 'image-placeholder';
    placeholder.textContent = 'Car Image Unavailable';

    img.replaceWith(placeholder);
}
</script>

</body>
</html>
```