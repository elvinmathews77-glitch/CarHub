```php
<?php
session_start();

require_once __DIR__ . "/config/db.php";

// Escape output safely
function e($value)
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Validate car ID
if (!isset($_GET['id']) || !ctype_digit((string)$_GET['id']) || (int)$_GET['id'] < 1) {
    http_response_code(400);
    exit("Invalid car ID.");
}

$car_id = (int) $_GET['id'];

// Fetch car details
$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");

if (!$stmt) {
    error_log("CarHub query preparation failed: " . $conn->error);
    http_response_code(500);
    exit("Unable to load car details.");
}

$stmt->bind_param("i", $car_id);
$stmt->execute();

$result = $stmt->get_result();
$car = $result->fetch_assoc();

$stmt->close();

if (!$car) {
    http_response_code(404);
    exit("Car not found.");
}

// Car information
$brand = $car['brand'] ?? 'Car';
$model = $car['model'] ?? ($car['name'] ?? 'Vehicle');
$price = (float)($car['price'] ?? 0);
$year = $car['year'] ?? 'N/A';
$fuel = $car['fuel'] ?? ($car['fuel_type'] ?? 'Not specified');
$transmission = $car['transmission'] ?? 'Not specified';
$mileage = $car['mileage'] ?? 'Not specified';
$description = $car['description'] ?? '';

$displayName = trim($brand . ' ' . $model);

// Match the car to the local image filename
$searchText = strtolower($brand . ' ' . $model . ' ' . ($car['name'] ?? ''));

$imageMap = [
    'bmw'        => 'bmw-3-series.jpg',
    'audi'       => 'audi-a4.jpg',
    'swift'      => 'maruti-swift.jpg',
    'i20'        => 'hyundai-i20.jpg',
    'lexus'      => 'lexus-es.jpg',
    'velar'      => 'range-rover-velar.jpg',
    'fortuner'   => 'toyota-fortuner.jpg',
    'gle'        => 'mercedes-gle.jpg',
    'c-class'    => 'mercedes-c-class.jpg',
    'e-class'    => 'mercedes-e-class.jpg'
];

$filename = '';

foreach ($imageMap as $keyword => $mappedFilename) {
    if (strpos($searchText, $keyword) !== false) {
        $filename = $mappedFilename;
        break;
    }
}

// Prefer a matching image filename stored in the database, if present.
foreach (['image', 'image_filename', 'image_name', 'photo', 'car_image'] as $field) {
    if (!empty($car[$field])) {
        $storedFilename = basename(str_replace('\\', '/', (string)$car[$field]));

        if (is_file(__DIR__ . '/images/cars/' . $storedFilename)) {
            $filename = $storedFilename;
            break;
        }
    }
}

// Only use a local image if the file exists.
$image = '';

if ($filename !== '' && is_file(__DIR__ . '/images/cars/' . $filename)) {
    $image = 'images/cars/' . rawurlencode($filename);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo e($displayName); ?> | CarHub</title>

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

        .navbar {
            min-height: 72px;
            background: #111;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 15px 8%;
            flex-wrap: wrap;
        }

        .logo {
            color: white;
            font-size: 30px;
            font-weight: 800;
            text-decoration: none;
        }

        .logo span {
            color: #ef3340;
        }

        .nav-links {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 15px;
        }

        .nav-links a:hover {
            color: #ef3340;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 40px auto;
        }

        .back {
            display: inline-block;
            margin-bottom: 22px;
            color: #172033;
            text-decoration: none;
            font-weight: bold;
        }

        .back:hover {
            color: #ef3340;
        }

        .details-card {
            background: white;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
        }

        .car-image {
            width: 100%;
            height: 480px;
            background: #e7eaee;
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

        .image-fallback {
            color: #667085;
            font-size: 70px;
            text-align: center;
            padding: 20px;
        }

        .image-fallback p {
            font-size: 16px;
            margin-top: 12px;
        }

        .content {
            padding: 38px;
        }

        .brand {
            color: #ef3340;
            text-transform: uppercase;
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        h1 {
            font-size: 40px;
            margin-bottom: 15px;
        }

        .price {
            font-size: 32px;
            font-weight: bold;
            color: #ef3340;
            margin-bottom: 28px;
        }

        .specs {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 15px;
            margin-bottom: 32px;
        }

        .spec {
            background: #f5f6f8;
            padding: 18px;
            border-radius: 10px;
            overflow-wrap: anywhere;
        }

        .spec-label {
            display: block;
            color: #667085;
            font-size: 13px;
            margin-bottom: 8px;
        }

        .spec-value {
            font-size: 16px;
            font-weight: bold;
        }

        .description {
            border-top: 1px solid #e5e7eb;
            padding-top: 28px;
            margin-bottom: 30px;
        }

        .description h2,
        .emi-calculator h2 {
            font-size: 25px;
            margin-bottom: 12px;
        }

        .description p {
            color: #667085;
            line-height: 1.7;
        }

        .emi-calculator {
            margin: 30px 0;
            padding: 28px;
            background: #f5f6f8;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
        }

        .emi-note {
            color: #667085;
            margin-bottom: 22px;
            line-height: 1.5;
        }

        .emi-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
        }

        .emi-field label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .emi-field input,
        .emi-field select {
            width: 100%;
            padding: 12px;
            border: 1px solid #d0d5dd;
            border-radius: 8px;
            font-size: 16px;
            background: white;
        }

        .calculate-emi {
            width: 100%;
            margin-top: 22px;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #111827;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }

        .calculate-emi:hover {
            background: #ef3340;
        }

        .emi-result {
            margin-top: 18px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .emi-result span {
            color: #667085;
            font-weight: bold;
        }

        .emi-result strong {
            color: #ef3340;
            font-size: 25px;
        }

        .actions {
            display: flex;
            gap: 15px;
        }

        .btn {
            flex: 1;
            text-align: center;
            padding: 16px;
            border-radius: 9px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
        }

        .book-btn {
            background: #ef3340;
            color: white;
        }

        .test-btn {
            background: #111827;
            color: white;
        }

        .book-btn:hover,
        .test-btn:hover {
            opacity: 0.88;
        }

        footer {
            background: #111;
            color: white;
            text-align: center;
            padding: 26px;
            margin-top: 60px;
        }

        @media (max-width: 800px) {
            .navbar {
                padding: 16px 5%;
            }

            .nav-links {
                gap: 14px;
            }

            .container {
                width: 94%;
                margin: 25px auto;
            }

            .car-image {
                height: 320px;
            }

            .content {
                padding: 25px;
            }

            h1 {
                font-size: 32px;
            }

            .specs {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .actions {
                flex-direction: column;
            }

            .emi-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .logo {
                font-size: 25px;
            }

            .nav-links {
                gap: 12px;
            }

            .nav-links a {
                font-size: 13px;
            }

            .content {
                padding: 20px;
            }

            .car-image {
                height: 240px;
            }

            .specs {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .spec {
                padding: 13px;
            }

            .emi-calculator {
                padding: 18px;
            }

            .emi-result {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>

<nav class="navbar">
    <a href="index.php" class="logo">CAR<span>HUB</span></a>

    <div class="nav-links">
        <a href="index.php">Home</a>
        <a href="cars.php">Cars</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
        <a href="my-bookings.php">My Bookings</a>
    </div>
</nav>

<main class="container">

    <a href="cars.php" class="back">&larr; Back to Cars</a>

    <div class="details-card">

        <div class="car-image">
            <?php if ($image !== ''): ?>
                <img
                    src="<?php echo e($image); ?>"
                    alt="<?php echo e($displayName); ?>"
                    onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                >
                <div class="image-fallback" style="display:none;">
                    🚘
                    <p>Image unavailable</p>
                </div>
            <?php else: ?>
                <div class="image-fallback">
                    🚘
                    <p>Image unavailable</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="content">

            <div class="brand"><?php echo e($brand); ?></div>

            <h1><?php echo e($model); ?></h1>

            <div class="price">
                <?php echo $price > 0 ? '₹' . number_format($price) : 'Price on request'; ?>
            </div>

            <div class="specs">
                <div class="spec">
                    <span class="spec-label">Year</span>
                    <span class="spec-value"><?php echo e($year); ?></span>
                </div>

                <div class="spec">
                    <span class="spec-label">Fuel</span>
                    <span class="spec-value"><?php echo e($fuel); ?></span>
                </div>

                <div class="spec">
                    <span class="spec-label">Transmission</span>
                    <span class="spec-value"><?php echo e($transmission); ?></span>
                </div>

                <div class="spec">
                    <span class="spec-label">Mileage</span>
                    <span class="spec-value">
                        <?php
                        echo e($mileage);
                        if (is_numeric($mileage)) {
                            echo ' km';
                        }
                        ?>
                    </span>
                </div>
            </div>

            <section class="description">
                <h2>About This Car</h2>
                <p>
                    <?php
                    if (trim((string)$description) !== '') {
                        echo nl2br(e($description));
                    } else {
                        echo 'Explore the features and specifications of '
                            . e($displayName)
                            . ' before making your booking.';
                    }
                    ?>
                </p>
            </section>

            <section class="emi-calculator">
                <h2>EMI / Loan Calculator</h2>

                <p class="emi-note">
                    Calculate an estimated monthly payment. Actual bank rates,
                    fees, and loan approval terms may differ.
                </p>

                <div class="emi-grid">
                    <div class="emi-field">
                        <label for="carPrice">Car Price (₹)</label>
                        <input
                            type="number"
                            id="carPrice"
                            value="<?php echo e($price); ?>"
                            readonly
                        >
                    </div>

                    <div class="emi-field">
                        <label for="downPayment">Down Payment (₹)</label>
                        <input
                            type="number"
                            id="downPayment"
                            value="0"
                            min="0"
                            max="<?php echo e($price); ?>"
                            step="1000"
                        >
                    </div>

                    <div class="emi-field">
                        <label for="interestRate">Interest Rate (% per year)</label>
                        <input
                            type="number"
                            id="interestRate"
                            value="8.5"
                            min="0"
                            max="100"
                            step="0.1"
                        >
                    </div>

                    <div class="emi-field">
                        <label for="loanYears">Loan Period</label>
                        <select id="loanYears">
                            <option value="1">1 Year</option>
                            <option value="2">2 Years</option>
                            <option value="3">3 Years</option>
                            <option value="4">4 Years</option>
                            <option value="5" selected>5 Years</option>
                            <option value="6">6 Years</option>
                            <option value="7">7 Years</option>
                            <option value="8">8 Years</option>
                            <option value="9">9 Years</option>
                            <option value="10">10 Years</option>
                        </select>
                    </div>
                </div>

                <button
                    type="button"
                    class="calculate-emi"
                    onclick="calculateEMI()"
                >
                    Calculate EMI
                </button>

                <div class="emi-result" aria-live="polite">
                    <span>Estimated Monthly EMI</span>
                    <strong id="emiResult">₹0</strong>
                </div>
            </section>

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
</main>

<footer>
    &copy; <?php echo date('Y'); ?> CarHub. All Rights Reserved.
</footer>

<script>
function calculateEMI() {
    const price = Number(document.getElementById('carPrice').value);
    const downPayment = Number(document.getElementById('downPayment').value);
    const annualInterest = Number(document.getElementById('interestRate').value);
    const years = Number(document.getElementById('loanYears').value);
    const result = document.getElementById('emiResult');

    if (
        !Number.isFinite(price) ||
        !Number.isFinite(downPayment) ||
        !Number.isFinite(annualInterest) ||
        !Number.isFinite(years) ||
        price <= 0 ||
        downPayment < 0 ||
        downPayment > price ||
        annualInterest < 0 ||
        annualInterest > 100 ||
        years <= 0
    ) {
        alert('Please enter valid loan values.');
        return;
    }

    const principal = price - downPayment;
    const monthlyRate = annualInterest / 1200;
    const numberOfPayments = years * 12;

    let emi;

    if (principal === 0) {
        emi = 0;
    } else if (monthlyRate === 0) {
        emi = principal / numberOfPayments;
    } else {
        const factor = Math.pow(1 + monthlyRate, numberOfPayments);
        emi = principal * monthlyRate * factor / (factor - 1);
    }

    result.textContent = '₹' + Math.round(emi).toLocaleString('en-IN');
}

// Calculate the default estimate when the page opens.
calculateEMI();
</script>

</body>
</html>

<?php
$conn->close();
?>
```