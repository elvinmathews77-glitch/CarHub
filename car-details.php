<?php

session_start();

/*
|--------------------------------------------------------------------------
| Database Connection
|--------------------------------------------------------------------------
| Use the common CarHub database connection file.
*/

require_once "config/db.php";


/*
|--------------------------------------------------------------------------
| Validate Car ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid car ID.");
}

$car_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Get Car Details
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("SELECT * FROM cars WHERE id = ?");

if (!$stmt) {
    die("Database query preparation failed.");
}

$stmt->bind_param("i", $car_id);
$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows === 0) {
    $stmt->close();
    die("Car not found.");
}


$car = $result->fetch_assoc();


/*
|--------------------------------------------------------------------------
| Car Information
|--------------------------------------------------------------------------
*/

$brand = $car['brand'] ?? 'Car';

$model = $car['model'] ?? 'Vehicle';

$price = (float) ($car['price'] ?? 0);

$year = $car['year'] ?? 'N/A';

$fuel = $car['fuel'] ?? 'Not specified';

$transmission = $car['transmission'] ?? 'Not specified';

$mileage = $car['mileage'] ?? 'Not specified';

$description = $car['description'] ?? '';

$brandLower = strtolower($brand);


/*
|--------------------------------------------------------------------------
| Car Image
|--------------------------------------------------------------------------
| Current project uses brand-based Unsplash images.
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

/*
|--------------------------------------------------------------------------
| RESET
|--------------------------------------------------------------------------
*/

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/*
|--------------------------------------------------------------------------
| BODY
|--------------------------------------------------------------------------
*/

body {
    font-family: Arial, Helvetica, sans-serif;
    background: #f4f5f7;
    color: #172033;
}


/*
|--------------------------------------------------------------------------
| NAVBAR
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| MAIN CONTAINER
|--------------------------------------------------------------------------
*/

.container {
    width: 90%;
    max-width: 1200px;
    margin: 45px auto;
}


/*
|--------------------------------------------------------------------------
| BACK BUTTON
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| DETAILS CARD
|--------------------------------------------------------------------------
*/

.details-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 12px 35px rgba(0, 0, 0, 0.08);
}


/*
|--------------------------------------------------------------------------
| CAR IMAGE
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| CONTENT
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| SPECIFICATIONS
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| DESCRIPTION
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| EMI CALCULATOR
|--------------------------------------------------------------------------
*/

.emi-calculator {
    margin-top: 35px;
    margin-bottom: 35px;
    padding: 30px;
    background: #f5f6f8;
    border-radius: 15px;
    border: 1px solid #e5e7eb;
}

.emi-calculator h2 {
    font-size: 25px;
    margin-bottom: 8px;
}

.emi-note {
    color: #667085;
    margin-bottom: 25px;
    line-height: 1.5;
}

.emi-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
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
    padding: 13px;
    border: 1px solid #d0d5dd;
    border-radius: 8px;
    font-size: 16px;
    background: white;
    box-sizing: border-box;
}

.emi-field input:focus,
.emi-field select:focus {
    outline: none;
    border-color: #ef3340;
}


/*
|--------------------------------------------------------------------------
| EMI BUTTON
|--------------------------------------------------------------------------
*/

.calculate-emi {
    width: 100%;
    margin-top: 25px;
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


/*
|--------------------------------------------------------------------------
| EMI RESULT
|--------------------------------------------------------------------------
*/

.emi-result {
    margin-top: 20px;
    padding: 20px;
    background: white;
    border-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.emi-result span {
    color: #667085;
    font-weight: bold;
}

.emi-result strong {
    color: #ef3340;
    font-size: 25px;
}


/*
|--------------------------------------------------------------------------
| ACTION BUTTONS
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
*/

footer {
    background: #111;
    color: white;
    text-align: center;
    padding: 30px;
    margin-top: 70px;
}


/*
|--------------------------------------------------------------------------
| MOBILE RESPONSIVE
|--------------------------------------------------------------------------
*/

@media(max-width: 800px) {

    .navbar {
        padding: 0 5%;
    }

    .nav-links {
        display: none;
    }

    .container {
        width: 94%;
        margin: 25px auto;
    }

    .car-image {
        height: 300px;
    }

    .content {
        padding: 25px;
    }

    h1 {
        font-size: 32px;
    }

    .price {
        font-size: 28px;
    }

    .specs {
        grid-template-columns: repeat(2, 1fr);
    }

    .actions {
        flex-direction: column;
    }

    .emi-grid {
        grid-template-columns: 1fr;
    }

    .emi-result {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

}


/*
|--------------------------------------------------------------------------
| SMALL MOBILE DEVICES
|--------------------------------------------------------------------------
*/

@media(max-width: 480px) {

    .logo {
        font-size: 25px;
    }

    .content {
        padding: 20px;
    }

    .specs {
        grid-template-columns: 1fr;
    }

    .emi-calculator {
        padding: 20px;
    }

    .emi-calculator h2 {
        font-size: 22px;
    }

    .emi-result strong {
        font-size: 22px;
    }

}

</style>

</head>


<body>


<!-- =========================================================
     NAVBAR
========================================================= -->

<nav class="navbar">

    <div class="logo">
        CAR<span>HUB</span>
    </div>


    <div class="nav-links">

        <a href="index.php">
            Home
        </a>

        <a href="cars.php">
            Cars
        </a>

        <a href="about.php">
            About
        </a>

        <a href="contact.php">
            Contact
        </a>

        <a href="my-bookings.php">
            My Bookings
        </a>

    </div>

</nav>



<!-- =========================================================
     MAIN
========================================================= -->

<div class="container">


    <!-- BACK -->

    <a href="cars.php" class="back">
        ← Back to Cars
    </a>



    <!-- CAR DETAILS CARD -->

    <div class="details-card">


        <!-- =================================================
             CAR IMAGE
        ================================================== -->

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



        <!-- =================================================
             CONTENT
        ================================================== -->

        <div class="content">


            <!-- BRAND -->

            <div class="brand">

                <?php echo htmlspecialchars($brand); ?>

            </div>



            <!-- MODEL -->

            <h1>

                <?php echo htmlspecialchars($model); ?>

            </h1>



            <!-- PRICE -->

            <div class="price">

                ₹<?php echo number_format($price); ?>

            </div>



            <!-- =================================================
                 SPECIFICATIONS
            ================================================== -->

            <div class="specs">


                <!-- YEAR -->

                <div class="spec">

                    <span class="spec-label">
                        Year
                    </span>

                    <span class="spec-value">

                        <?php echo htmlspecialchars($year); ?>

                    </span>

                </div>



                <!-- FUEL -->

                <div class="spec">

                    <span class="spec-label">
                        Fuel
                    </span>

                    <span class="spec-value">

                        <?php echo htmlspecialchars($fuel); ?>

                    </span>

                </div>



                <!-- TRANSMISSION -->

                <div class="spec">

                    <span class="spec-label">
                        Transmission
                    </span>

                    <span class="spec-value">

                        <?php echo htmlspecialchars($transmission); ?>

                    </span>

                </div>



                <!-- MILEAGE -->

                <div class="spec">

                    <span class="spec-label">
                        Mileage
                    </span>

                    <span class="spec-value">

                        <?php echo htmlspecialchars($mileage); ?>

                    </span>

                </div>

            </div>



            <!-- =================================================
                 DESCRIPTION
            ================================================== -->

            <div class="description">

                <h2>
                    About This Car
                </h2>


                <p>

                    <?php

                    if (!empty($description)) {

                        echo nl2br(
                            htmlspecialchars($description)
                        );

                    } else {

                        echo "Experience a premium driving experience with the "
                            . htmlspecialchars($brand . ' ' . $model)
                            . ". Explore its features, specifications and performance before making your purchase.";

                    }

                    ?>

                </p>

            </div>



            <!-- =================================================
                 EMI / LOAN CALCULATOR
            ================================================== -->

            <div class="emi-calculator">


                <h2>
                    EMI / Loan Calculator
                </h2>


                <p class="emi-note">

                    Calculate an estimated monthly EMI for this car.

                </p>



                <div class="emi-grid">


                    <!-- CAR PRICE -->

                    <div class="emi-field">

                        <label for="carPrice">
                            Car Price (₹)
                        </label>


                        <input
                            type="number"
                            id="carPrice"
                            value="<?php echo $price; ?>"
                            readonly
                        >

                    </div>



                    <!-- DOWN PAYMENT -->

                    <div class="emi-field">

                        <label for="downPayment">
                            Down Payment (₹)
                        </label>


                        <input
                            type="number"
                            id="downPayment"
                            value="0"
                            min="0"
                            max="<?php echo $price; ?>"
                            step="1000"
                        >

                    </div>



                    <!-- INTEREST RATE -->

                    <div class="emi-field">

                        <label for="interestRate">
                            Interest Rate (% per year)
                        </label>


                        <input
                            type="number"
                            id="interestRate"
                            value="8.5"
                            min="0"
                            max="100"
                            step="0.1"
                        >

                    </div>



                    <!-- LOAN PERIOD -->

                    <div class="emi-field">

                        <label for="loanYears">
                            Loan Period
                        </label>


                        <select id="loanYears">

                            <option value="1">
                                1 Year
                            </option>

                            <option value="2">
                                2 Years
                            </option>

                            <option value="3">
                                3 Years
                            </option>

                            <option value="4">
                                4 Years
                            </option>

                            <option value="5" selected>
                                5 Years
                            </option>

                            <option value="6">
                                6 Years
                            </option>

                            <option value="7">
                                7 Years
                            </option>

                            <option value="8">
                                8 Years
                            </option>

                            <option value="9">
                                9 Years
                            </option>

                            <option value="10">
                                10 Years
                            </option>

                        </select>

                    </div>

                </div>



                <!-- CALCULATE BUTTON -->

                <button
                    type="button"
                    class="calculate-emi"
                    onclick="calculateEMI()"
                >

                    Calculate EMI

                </button>



                <!-- EMI RESULT -->

                <div class="emi-result">

                    <span>
                        Estimated Monthly EMI
                    </span>


                    <strong id="emiResult">

                        ₹0

                    </strong>

                </div>


            </div>



            <!-- =================================================
                 ACTION BUTTONS
            ================================================== -->

            <div class="actions">


                <!-- BOOK -->

                <a
                    href="booking.php?car_id=<?php echo $car_id; ?>"
                    class="btn book-btn"
                >

                    Book This Car

                </a>



                <!-- TEST DRIVE -->

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



<!-- =========================================================
     FOOTER
========================================================= -->

<footer>

    © <?php echo date('Y'); ?> CarHub.
    All Rights Reserved.

</footer>



<!-- =========================================================
     EMI CALCULATOR JAVASCRIPT
========================================================= -->

<script>

function calculateEMI() {

    /*
    |--------------------------------------------------------------------------
    | Get Values
    |--------------------------------------------------------------------------
    */

    const price = parseFloat(
        document.getElementById("carPrice").value
    );


    const downPayment = parseFloat(
        document.getElementById("downPayment").value
    );


    const annualInterest = parseFloat(
        document.getElementById("interestRate").value
    );


    const years = parseInt(
        document.getElementById("loanYears").value
    );



    /*
    |--------------------------------------------------------------------------
    | Validate Values
    |--------------------------------------------------------------------------
    */

    if (
        isNaN(price) ||
        isNaN(downPayment) ||
        isNaN(annualInterest) ||
        isNaN(years)
    ) {

        alert("Please enter valid values.");

        return;
    }



    if (price <= 0) {

        alert("Invalid car price.");

        return;
    }



    if (downPayment < 0) {

        alert("Down payment cannot be negative.");

        return;
    }



    if (downPayment > price) {

        alert("Down payment cannot be greater than the car price.");

        return;
    }



    if (annualInterest < 0) {

        alert("Interest rate cannot be negative.");

        return;
    }



    /*
    |--------------------------------------------------------------------------
    | Calculate Loan Amount
    |--------------------------------------------------------------------------
    */

    const loanAmount =
        price - downPayment;



    /*
    |--------------------------------------------------------------------------
    | Convert Annual Interest Rate
    | to Monthly Interest Rate
    |--------------------------------------------------------------------------
    */

    const monthlyInterest =
        annualInterest / 12 / 100;



    /*
    |--------------------------------------------------------------------------
    | Total Number of Monthly Payments
    |--------------------------------------------------------------------------
    */

    const numberOfPayments =
        years * 12;



    let emi;



    /*
    |--------------------------------------------------------------------------
    | EMI Calculation
    |--------------------------------------------------------------------------
    |
    | EMI = P × r × (1+r)^n / ((1+r)^n - 1)
    |
    */

    if (monthlyInterest === 0) {

        emi =
            loanAmount / numberOfPayments;

    } else {

        const power =
            Math.pow(
                1 + monthlyInterest,
                numberOfPayments
            );


        emi =
            loanAmount *
            monthlyInterest *
            power /
            (power - 1);

    }



    /*
    |--------------------------------------------------------------------------
    | Display EMI
    |--------------------------------------------------------------------------
    */

    document.getElementById("emiResult").textContent =
        "₹" +
        emi.toLocaleString("en-IN", {
            maximumFractionDigits: 0
        });

}

</script>


</body>

</html>


<?php

/*
|--------------------------------------------------------------------------
| Close Database Resources
|--------------------------------------------------------------------------
*/

$stmt->close();

$conn->close();

?>