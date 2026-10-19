```php
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config/db.php';

// Get car ID from URL
$car_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$car_id || $car_id < 1) {
    http_response_code(400);
    exit('Invalid car ID. Please return to the cars page.');
}

// Fetch car details
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");

if (!$stmt) {
    error_log("CarHub database error: " . $conn->error);
    http_response_code(500);
    exit('Unable to load car details right now.');
}

$stmt->bind_param("i", $car_id);
$stmt->execute();

$result = $stmt->get_result();
$car = $result->fetch_assoc();

$stmt->close();

if (!$car) {
    http_response_code(404);
    exit('Vehicle not found. <a href="cars.php">Back to Cars</a>');
}

// Safely read vehicle information
$brand = $car['brand'] ?? '';
$model = $car['model'] ?? ($car['name'] ?? 'Vehicle');
$name = trim($brand . ' ' . $model);

if ($name === '') {
    $name = 'Vehicle';
}

$price = (float) ($car['price'] ?? 0);
$year = $car['year'] ?? 'N/A';
$fuel = $car['fuel'] ?? ($car['fuel_type'] ?? 'N/A');
$transmission = $car['transmission'] ?? 'N/A';
$mileage = $car['mileage'] ?? 'N/A';
$description = $car['description'] ?? 'Contact us for more information about this vehicle.';

$formatPrice = number_format($price, 0, '.', ',');

// Match database vehicle names to the exact image filenames
$searchName = strtolower($brand . ' ' . $model);

$imageMap = [
    'bmw 3 series' => 'bmw-3-series.jpg.avif',
    'audi a4' => 'audi-a4.avif',
    'maruti swift' => 'maruti-swift.jpg.avif',
    'maruti suzuki swift' => 'maruti-swift.jpg.avif',
    'hyundai i20' => 'hyundai-i20.jpg.avif',
    'lexus es' => 'lexus-es.jpg.avif',
    'mercedes c-class' => 'mercedes-c-class.jpg.avif',
    'mercedes-benz c-class' => 'mercedes-c-class.jpg.avif',
    'mercedes e-class' => 'mercedes-e-class.jpg.avif',
    'mercedes-benz e-class' => 'mercedes-e-class.jpg.avif',
    'mercedes gle' => 'mercedes-gle.jpg.avif',
    'mercedes-benz gle' => 'mercedes-gle.jpg.avif',
    'range rover velar' => 'range-rover-velar.jpg.avif',
    'land rover range rover velar' => 'range-rover-velar.jpg.avif',
    'toyota fortuner' => '- toyota-fortuner.jpg.avif'
];

$imageFile = null;

// Match the full brand and model first
foreach ($imageMap as $vehicle => $filename) {
    if ($searchName === $vehicle) {
        $imageFile = $filename;
        break;
    }
}

// Try a partial match for variations in database naming
if ($imageFile === null) {
    foreach ($imageMap as $vehicle => $filename) {
        if (strpos($searchName, $vehicle) !== false ||
            strpos($vehicle, $searchName) !== false) {
            $imageFile = $filename;
            break;
        }
    }
}

$imagePath = $imageFile
    ? 'images/cars/' . rawurlencode($imageFile)
    : null;

$imageAvailable = $imageFile !== null
    && is_file(__DIR__ . '/images/cars/' . $imageFile);

// Escape text before displaying it in HTML
function h($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($name) ?> | CarHub</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f5f8;
            color: #1e293b;
        }

        header {
            background: #111;
            color: white;
            padding: 18px 7%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 15px;
        }

        .logo {
            font-size: 30px;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }

        .logo span {
            color: #ef3340;
        }

        nav {
            display: flex;
            gap: 25px;
            flex-wrap: wrap;
        }

        nav a {
            color: white;
            text-decoration: none;
            font-size: 15px;
        }

        nav a:hover {
            color: #ef3340;
        }

        .container {
            max-width: 1300px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 22px;
            color: #111827;
            font-weight: bold;
            text-decoration: none;
        }

        .car-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
        }

        .car-image {
            width: 100%;
            height: 500px;
            background: #e7eaf0;
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }

        .car-image img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .image-fallback {
            color: #64748b;
            text-align: center;
            font-size: 16px;
        }

        .image-fallback .car-icon {
            font-size: 55px;
            display: block;
            margin-bottom: 12px;
        }

        .car-content {
            padding: 35px 40px;
        }

        .brand {
            color: #ef3340;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        h1 {
            font-size: 42px;
            margin: 0 0 12px;
        }

        .price {
            color: #ef3340;
            font-size: 28px;
            font-weight: bold;
            margin: 15px 0 25px;
        }

        .specifications {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 15px;
            margin: 25px 0;
        }

        .spec {
            background: #f5f6f8;
            padding: 18px;
            border-radius: 10px;
        }

        .spec-label {
            display: block;
            color: #64748b;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .spec-value {
            font-weight: bold;
            overflow-wrap: anywhere;
        }

        .description {
            line-height: 1.7;
            color: #475569;
            white-space: pre-line;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin: 25px 0 35px;
        }

        .button {
            display: inline-block;
            padding: 14px 22px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            text-align: center;
            border: none;
            cursor: pointer;
            font-size: 15px;
        }

        .button-primary {
            background: #ef3340;
            color: white;
        }

        .button-secondary {
            background: #111827;
            color: white;
        }

        .emi-section {
            margin-top: 25px;
            padding: 28px;
            background: #f5f6f8;
            border-radius: 12px;
        }

        .emi-section h2 {
            margin-top: 0;
        }

        .emi-form {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            background: white;
            font-size: 15px;
        }

        .emi-result {
            margin-top: 22px;
            padding: 18px;
            border-radius: 9px;
            background: white;
        }

        .emi-result strong {
            color: #ef3340;
            font-size: 26px;
        }

        .emi-note {
            color: #64748b;
            font-size: 13px;
            line-height: 1.5;
        }

        footer {
            background: #111;
            color: white;
            text-align: center;
            padding: 22px;
            margin-top: 45px;
        }

        @media (max-width: 700px) {
            header {
                padding: 18px 5%;
            }

            nav {
                gap: 14px;
            }

            .container {
                margin-top: 22px;
                padding: 0 12px;
            }

            .car-image {
                height: 280px;
            }

            .car-content {
                padding: 24px 18px;
            }

            h1 {
                font-size: 32px;
            }

            .specifications,
            .emi-form {
                grid-template-columns: 1fr;
            }

            .emi-section {
                padding: 20px;
            }

            .button {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<header>
    <a class="logo" href="index.php">CAR<span>HUB</span></a>

    <nav>
        <a href="index.php">Home</a>
        <a href="cars.php">Cars</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="my-bookings.php">My Bookings</a>
    </nav>
</header>

<main class="container">

    <a class="back-link" href="cars.php">&larr; Back to Cars</a>

    <section class="car-card">

        <div class="car-image">
            <?php if ($imageAvailable): ?>
                <img
                    src="<?= h($imagePath) ?>"
                    alt="<?= h($name) ?>"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                >
                <div class="image-fallback" style="display:none;">
                    <span class="car-icon">🚘</span>
                    Image unavailable
                </div>
            <?php else: ?>
                <div class="image-fallback">
                    <span class="car-icon">🚘</span>
                    Image unavailable
                </div>
            <?php endif; ?>
        </div>

        <div class="car-content">

            <div class="brand"><?= h($brand ?: 'CarHub') ?></div>

            <h1><?= h($model) ?></h1>

            <div class="price">
                ₹<?= h($formatPrice) ?>
            </div>

            <div class="specifications">
                <div class="spec">
                    <span class="spec-label">Manufacturing Year</span>
                    <span class="spec-value"><?= h($year) ?></span>
                </div>

                <div class="spec">
                    <span class="spec-label">Fuel Type</span>
                    <span class="spec-value"><?= h($fuel) ?></span>
                </div>

                <div class="spec">
                    <span class="spec-label">Transmission</span>
                    <span class="spec-value"><?= h($transmission) ?></span>
                </div>

                <div class="spec">
                    <span class="spec-label">Mileage</span>
                    <span class="spec-value">
                        <?= h($mileage) ?><?= is_numeric($mileage) ? ' km/l' : '' ?>
                    </span>
                </div>
            </div>

            <h2>About This Vehicle</h2>
            <p class="description"><?= h($description) ?></p>

            <div class="actions">
                <a
                    class="button button-primary"
                    href="booking.php?car_id=<?= (int) $car['id'] ?>"
                >
                    Book This Car
                </a>

                <a
                    class="button button-secondary"
                    href="test-drive.php?car_id=<?= (int) $car['id'] ?>"
                >
                    Book a Test Drive
                </a>
            </div>

            <section class="emi-section">
                <h2>EMI Calculator</h2>
                <p>Estimate your monthly car loan payment.</p>

                <div class="emi-form">
                    <div class="form-group">
                        <label for="downPayment">Down Payment (₹)</label>
                        <input
                            type="number"
                            id="downPayment"
                            min="0"
                            max="<?= h($price) ?>"
                            value="<?= h(round($price * 0.20)) ?>"
                        >
                    </div>

                    <div class="form-group">
                        <label for="interestRate">Annual Interest Rate (%)</label>
                        <input
                            type="number"
                            id="interestRate"
                            min="0"
                            max="50"
                            step="0.1"
                            value="8.5"
                        >
                    </div>

                    <div class="form-group">
                        <label for="loanYears">Loan Period</label>
                        <select id="loanYears">
                            <option value="1">1 year</option>
                            <option value="2">2 years</option>
                            <option value="3">3 years</option>
                            <option value="4">4 years</option>
                            <option value="5" selected>5 years</option>
                            <option value="6">6 years</option>
                            <option value="7">7 years</option>
                            <option value="8">8 years</option>
                            <option value="9">9 years</option>
                            <option value="10">10 years</option>
                        </select>
                    </div>
                </div>

                <div class="emi-result" aria-live="polite">
                    <p>Estimated Monthly EMI</p>
                    <strong id="emiAmount">₹0</strong>
                    <p id="loanSummary"></p>
                </div>

                <p class="emi-note">
                    This is an estimate for planning purposes. Actual EMI,
                    interest, fees, and loan eligibility depend on the lender.
                </p>
            </section>

        </div>
    </section>

</main>

<footer>
    &copy; <?= date('Y') ?> CarHub. All rights reserved.
</footer>

<script>
(function () {
    const price = <?= json_encode($price) ?>;
    const downPaymentInput = document.getElementById('downPayment');
    const interestInput = document.getElementById('interestRate');
    const yearsInput = document.getElementById('loanYears');
    const emiAmount = document.getElementById('emiAmount');
    const loanSummary = document.getElementById('loanSummary');

    const formatRupees = amount =>
        '₹' + Math.round(amount).toLocaleString('en-IN');

    function calculateEMI() {
        const downPayment = Number(downPaymentInput.value);
        const annualRate = Number(interestInput.value);
        const years = Number(yearsInput.value);

        if (
            !Number.isFinite(price) ||
            price <= 0 ||
            !Number.isFinite(downPayment) ||
            downPayment < 0 ||
            downPayment > price ||
            !Number.isFinite(annualRate) ||
            annualRate < 0 ||
            annualRate > 50 ||
            !Number.isFinite(years) ||
            years <= 0
        ) {
            emiAmount.textContent = 'Check your values';
            loanSummary.textContent =
                'Enter a valid down payment, interest rate, and loan period.';
            return;
        }

        const principal = price - downPayment;
        const months = years * 12;
        const monthlyRate = annualRate / 1200;

        let emi;

        if (principal === 0) {
            emi = 0;
        } else if (monthlyRate === 0) {
            emi = principal / months;
        } else {
            const factor = Math.pow(1 + monthlyRate, months);
            emi = principal * monthlyRate * factor / (factor - 1);
        }

        const totalPayment = emi * months;
        const totalInterest = totalPayment - principal;

        emiAmount.textContent = formatRupees(emi);
        loanSummary.textContent =
            'Loan amount: ' + formatRupees(principal) +
            ' | Total interest: ' + formatRupees(totalInterest);
    }

    downPaymentInput.addEventListener('input', calculateEMI);
    interestInput.addEventListener('input', calculateEMI);
    yearsInput.addEventListener('change', calculateEMI);

    calculateEMI();
})();
</script>

</body>
</html>
```