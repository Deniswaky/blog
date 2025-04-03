<?php
require 'vendor/autoload.php'; // Load PHPMailer via Composer
require 'db.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Function to send password reset email
function sendResetEmail($email, $reset_link) {
    $mail = new PHPMailer(true);
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Change if using another provider
        $mail->SMTPAuth = true;
        $mail->Username = 'deniswakalanga@gmail.com'; // Your email
        $mail->Password = ''; // Use App Password if 2FA is enabled
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email Settings
        $mail->setFrom('deniswakalanga@gmail.com', 'Password Reset Link');
        $mail->addAddress($email);
        $mail->Subject = "Password Reset Request";
        $mail->Body = "Click the following link to reset your password (Valid for 5 minutes): $reset_link";

        // Send Email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if email exists in the database
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate unique reset token
        $token = bin2hex(random_bytes(32));  // Generate token
        $token_expires = date("Y-m-d H:i:s", strtotime("+5 minutes")); // Set expiry to 5 minutes
        
        $stmt = $pdo->prepare("UPDATE users SET reset_token = ?, token_expires = ? WHERE email = ?");
        $stmt->execute([$token, $token_expires, $email]);

        // Create password reset link
        $reset_link = "http://localhost/Blog/reset_password.php?token=" . urlencode($token);
      
        // Send reset email
        if (sendResetEmail($email, $reset_link)) {
            echo "<script>alert('Password reset email has been sent. The link is valid for 5 minutes.');</script>";
            echo "<p id='countdown'></p>";
        } else {
            echo "<script>alert('Failed to send email.');</script>";
        }
        } else {
            echo "<script>alert('Email not found.');</script>";
        }
        
}
?>

<!-- HTML Form -->
 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Password Reset</title>
    <link rel="stylesheet" href="styles.css">
 </head>
 <body>
    
 </body>
 </html>

 <div class="form-container">
<form method="POST">
    <input type="email" name="email" required placeholder="Enter your email">
    <button type="submit">Request Password Reset</button>
</form>
</div>

<script>
// Countdown Timer for 5 minutes
let timeLeft = 5 * 60; // 5 minutes in seconds
let countdownInterval;
let popupWindow = null;

function showCountdownPopup() {
    // Close previous popup if it exists
    if (popupWindow && !popupWindow.closed) {
        popupWindow.close();
    }
    
    // Create popup content
    const popupContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Token Expiration</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    padding: 20px;
                    text-align: center;
                }
                .countdown {
                    font-size: 18px;
                    margin: 10px 0;
                    font-weight: bold;
                }
                button {
                    padding: 8px 16px;
                    background: #4CAF50;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                }
            </style>
        </head>
        <body>
            <div class="countdown" id="popupCountdown"></div>
            <button onclick="window.close()">OK</button>
            <script>
                let timeLeft = ${timeLeft};
                const countdownElement = document.getElementById('popupCountdown');
                
                function updatePopupCountdown() {
                    let minutes = Math.floor(timeLeft / 60);
                    let seconds = timeLeft % 60;
                    countdownElement.innerHTML = \`Token expires in: \${minutes}m \${seconds}s\`;
                    timeLeft--;
                    
                    if (timeLeft < 0) {
                        countdownElement.innerHTML = "Token has expired! Request a new one.";
                        clearInterval(window.parent.countdownInterval);
                    }
                }
                
                // Update immediately and then every second
                updatePopupCountdown();
                setInterval(updatePopupCountdown, 1000);
            <\/script>
        </body>
        </html>
    `;

    // Open popup window
    popupWindow = window.open('', 'TokenExpiration', 'width=350,height=200');
    popupWindow.document.write(popupContent);
    popupWindow.focus();
}

function updateCountdown() {
    let minutes = Math.floor(timeLeft / 60);
    let seconds = timeLeft % 60;
    
    // Update the popup if it's open
    if (popupWindow && !popupWindow.closed) {
        try {
            const popupCountdown = popupWindow.document.getElementById('popupCountdown');
            if (timeLeft >= 0) {
                popupCountdown.innerHTML = `Token expires in: ${minutes}m ${seconds}s`;
            } else {
                popupCountdown.innerHTML = "Token has expired! Request a new one.";
            }
        } catch (e) {
            // If popup was closed, reopen it
            showCountdownPopup();
        }
    }
    
    timeLeft--;
    
    if (timeLeft < 0) {
        clearInterval(countdownInterval);
        if (popupWindow && !popupWindow.closed) {
            popupWindow.focus();
        } else {
            alert("Token has expired! Request a new one.");
        }
    }
}

// Start the countdown
showCountdownPopup();
countdownInterval = setInterval(updateCountdown, 1000);
</script>