<?php
// Database connection settings
$host = 'localhost'; // or your host
$db = 'complete_dt_jobs'; // your database name
$user = 'root'; // your database username
$pass = 'hGzxrmt4cMp8RXbc'; // your database password

try {
    // Create a PDO instance for database connection
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // If connection fails, display an error message
    die("Connection failed: " . $e->getMessage());
}
?>
