<?php
// add.php
require 'db_con.php';

// Function to generate a random string of 20 characters
function generateRandomString($length = 20) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Hàm tạo giá ngẫu nhiên
function generateRandomPrice($min = 10, $max = 1000) {
    return rand($min, $max) . "." . rand(0, 99); // Tạo giá ngẫu nhiên từ 10.00 đến 1000.99
}


// Insert 500.000 products
for ($i = 0; $i < 5000; $i++) {
    $name = generateRandomString(); 
    $price = generateRandomPrice(); 

    $sql = "INSERT INTO products (name, price) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql); 

    if ($stmt) {
        $stmt->bindParam(1, $name, PDO::PARAM_STR); //dung PDO::PARAM_STR vi gia dc luu bang chuoi
        $stmt->bindParam(2, $price, PDO::PARAM_STR); 

        try {
            if ($stmt->execute()) {
                echo "Insert success: " . $name . " với giá " . $price . "\n";
            } else {
                // error checking
                $errorInfo = $stmt->errorInfo();
                echo "Insert Error: " . $errorInfo[2] . "\n";
            }
        } catch (PDOException $e) {
            echo "Insert Error: " . $e->getMessage() . "\n";
        }

    } else {
        
        $errorInfo = $pdo->errorInfo();
        echo "Systax Error: " . $errorInfo[2] . "\n";
    }
header('Location: index.php');
}
?>













