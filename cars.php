
```php
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

function e($value)
{
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
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
            font-size: 16px;
            text-align: center;
            padding: 15px;
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
                $brand = trim((string)($car['brand'] ?? ''));
                $model = trim((string)($car['model'] ?? $name));

                $year = $car['year'] ?? 'N/A';
                $fuel = $car['fuel'] ?? 'N/A';
                $transmission = $car['transmission'] ?? 'N/A';
                $mileage = $car['mileage'] ?? 'N/A';
                $price = (float)($car['price'] ?? 0);

                // Do not trust old image URLs in the database: some point to
                // pictures of different cars. Search for a matching model instead.
                $imageUrl = '';
                ?>

                <div class="car-card">

                    <div class="car-image">

                        <img
                            class="online-car-image"
                            src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="
                            data-brand="<?= e($brand) ?>"
                            data-model="<?= e($model) ?>"
                            data-name="<?= e($name) ?>"
                            alt="<?= e($name) ?>"
                            loading="lazy"
                            onerror="showPlaceholder(this)"
                        >
                        <div class="image-placeholder">
                            Finding a matching image for <?= e($name) ?>...
                        </div>

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
function showPlaceholder(img) {
    const container = img.closest('.car-image');
    if (!container) return;

    img.style.display = 'none';

    let placeholder = container.querySelector('.image-placeholder');

    if (!placeholder) {
        placeholder = document.createElement('div');
        placeholder.className = 'image-placeholder';
        container.appendChild(placeholder);
    }

    placeholder.textContent =
        'Image unavailable for ' + (img.dataset.name || img.alt);
    placeholder.style.display = 'flex';
}

async function findOnlineCarImage(img) {
    const brand = (img.dataset.brand || '').trim();
    const model = (img.dataset.model || img.dataset.name || '').trim();
    const container = img.closest('.car-image');
    const placeholder = container?.querySelector('.image-placeholder');

    if (!model) {
        showPlaceholder(img);
        return;
    }

    const normalize = value => value.toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ').trim();

    // Keep short model identifiers such as A4, ES, i20 and E-Class.
    // These were previously discarded, causing valid searches to fail.
    const normalize = value => value.toLowerCase()
        .replace(/[^a-z0-9]+/g, ' ').trim();

    const normalizedModel = normalize(model);
    const normalizedBrand = normalize(brand);
    const modelWords = normalizedModel.split(' ').filter(Boolean);
    const brandWords = normalizedBrand.split(' ').filter(Boolean);

    if (!normalizedModel) {
        showPlaceholder(img);
        return;
    }

    const queries = [
        `${brand} ${model} automobile`,
        `${brand} ${model} vehicle`
    ];

    try {
        let pages = [];

        for (const query of queries) {
            const params = new URLSearchParams({
                action: 'query',
                generator: 'search',
                gsrsearch: query,
                gsrnamespace: '6',
                gsrlimit: '20',
                prop: 'imageinfo',
                iiprop: 'url|mime',
                iiurlwidth: '900',
                format: 'json',
                origin: '*'
            });

            const response = await fetch(
                'https://commons.wikimedia.org/w/api.php?' + params.toString()
            );
            if (!response.ok) continue;

            const data = await response.json();
            pages = Object.values(data.query?.pages || {});
            if (pages.length) break;
        }

        const candidates = pages.map(page => {
            const info = page.imageinfo?.[0];
            if (!info?.thumburl) return null;

            const title = normalize((page.title || '').replace(/^File:/i, ''));
            const mime = (info.mime || '').toLowerCase();
            if (!mime.startsWith('image/') || mime === 'image/svg+xml') return null;

            const modelMatches = modelWords.filter(word => title.split(' ').includes(word)).length;
            const brandMatches = brandWords.filter(word => title.split(' ').includes(word)).length;

            // Match the full model phrase when possible. For model names with
            // multiple words, require all model tokens in the filename.
            // Short identifiers (A4, ES, i20) are allowed but require a brand
            // match too, reducing the chance of selecting an unrelated image.
            const exactModelPhrase = title.includes(normalizedModel);
            const allModelTokens = modelMatches === modelWords.length;
            const shortModelNeedsBrand = modelWords.some(word => word.length <= 2);
            if (!(exactModelPhrase || allModelTokens)) return null;
            if (shortModelNeedsBrand && brandWords.length && brandMatches === 0) return null;

            return {
                url: info.thumburl,
                title: page.title,
                score: (exactModelPhrase ? 100 : modelMatches * 10) + brandMatches
            };
        }).filter(Boolean).sort((a, b) => b.score - a.score);

        const best = candidates[0];

        // If the filename doesn't clearly identify the model, don't show a
        // potentially unrelated vehicle.
        if (!best) {
            showPlaceholder(img);
            return;
        }

        img.onload = () => {
            if (placeholder) placeholder.style.display = 'none';
            img.style.display = 'block';
        };
        img.onerror = () => showPlaceholder(img);
        img.src = best.url;

        console.info('CarHub image source:', best.title);
    } catch (error) {
        console.error('Image lookup failed for ' + model, error);
        showPlaceholder(img);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.online-car-image').forEach(img => {
        findOnlineCarImage(img);
    });
});
</script>

</body>
</html>
```

