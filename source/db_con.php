<?php

// Databtse connection details
$host = 'localhost';
$dbname = 'admin_shopdb';
$username = 'admin_shopdb';
$password = '140620';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}

?>

