<?php
// db.php - Database connection file

$host = 'localhost';   // Server name (usually localhost)
$dbname = 'newsfeed_db'; // Database name
$username = 'root'; // Your database username (default for XAMPP is 'root')
$password = ''; // Your database password (default for XAMPP is empty)

// Create a new PDO instance to handle database connections
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Error handling
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage()); // Error handling in case of failure
}
?>
