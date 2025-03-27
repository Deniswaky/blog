<?php
session_start();
include("db.php"); // Ensure this file exists and is correct

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];

    // Check if email exists using PDO
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $otp = rand(100000, 999999); // Generate a 6-digit OTP
        $_SESSION["otp"] = $otp;
        $_SESSION["email"] = $email;

        // Store OTP in database
        $stmt = $pdo->prepare("UPDATE users SET otp = :otp WHERE email = :email");
        $stmt->execute(['otp' => $otp, 'email' => $email]);

        // Send OTP via email
        $subject = "Password Reset OTP";
        $message = "Your OTP for password reset is: $otp";
        $headers = "From: no-reply@yourwebsite.com";

        if (mail($email, $subject, $message, $headers)) {
            header("Location: verify_otp.html");
            exit();
        } else {
            echo "Failed to send OTP. Try again!";
        }
    } else {
        echo "Email not found!";
    }
}
?>
