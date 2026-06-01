<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Start session
}

$host = "localhost";
$user = "root"; 
$pass = ""; 
$dbname = "pet_store_inventory";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
