<?php

session_start();

include "config/db.php";

$message = "";

if (isset($_GET["registered"])) {
    $message = "Registration successful! Please login.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT id, name, password FROM users WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows == 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];

                header("Location: index.php");
                exit();

            } else {

                $message = "Incorrect password.";

            }

        } else {

            $message = "No account found with this email.";

        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | CarHub</title>

    <link rel="stylesheet" href="css/login.css">

</head>

<body>

<div class="login-page">

    <div class="login-box">

        <div class="logo">
            CAR<span>HUB</span>
        </div>

        <h1>Welcome Back</h1>

        <p class="subtitle">
            Login to your CarHub account
        </p>


        <?php if (!empty($message)) { ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php } ?>


        <form method="POST" action="login.php">

            <div class="input-group">

                <label>Email Address</label>

                <input
                    type="email"
                    name="email"
                    placeholder="Enter your email"
                    required
                >

            </div>


            <div class="input-group">

                <label>Password</label>

                <input
                    type="password"
                    name="password"
                    placeholder="Enter your password"
                    required
                >

            </div>


            <button type="submit">
                Login
            </button>

        </form>


        <p class="register-text">

            Don't have an account?

            <a href="register.php">
                Create Account
            </a>

        </p>


        <a href="index.php" class="home-link">
            ← Back to Home
        </a>

    </div>

</div>

</body>

</html>