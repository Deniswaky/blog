<?php
// login.php - Handle login logic

require_once 'db.php'; // Include database connection

session_start(); // Start the session for logged-in users

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Check if the user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct, set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        echo "Login successful!";
        header("Location: index.html"); // Redirect to a dashboard or homepage
        exit();
    } else {
        echo "Invalid email or password!";
    }
}
?>

<!-- The HTML form for the login page -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">

</head>
<body>

    <div class="form-container">
        <h2>Login</h2>
        <form action="login.php" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Login</button>
        </form>
        <div class="link-container">
            <p>Don't have an account? <a href="signup.php">Sign up</a></p>
            <p><a href="forgot_password.html">Forgot Password?</a></p>

        </div>
    </div>

</body>
</html>



