
<?php
// home.php
include 'db_con.php';

// Fetch all products
$products = fetchProducts($pdo);

function fetchProducts($pdo) {
    $stmt = $pdo->query("SELECT * FROM products");
    return $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Showroom</title>
    <style>
        body {
            font-family: sans-serif;
        }

        .product-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .product {
            width: 300px;
            margin: 10px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: center;
        }

        .product img {
            max-width: 100%;
            height: auto;
            margin-bottom: 10px;
        }

        .product h3 {
            margin-bottom: 5px;
        }

        .product p {
            font-size: 14px;
        }
    </style>
    
<style>
    .button {
        display: inline-block;
        padding: 10px 20px;
        font-size: 16px;
        color: #fff;
        background-color: #007bff;
        text-align: center;
        text-decoration: none;
        border-radius: 5px;
        margin: 5px;
        transition: background-color 0.3s ease;
    }

    .button:hover {
        background-color: #0056b3;
    }
    </style>  
    <a href="login.php" class="button">LogIn</a>
    

    
    
    
</head>
<body>
    <h1>Products</h1>
    <div class="product-container">
        <?php foreach ($products as $product): ?>
            <div class="product">
                <h3><?= htmlspecialchars($product['name']) ?></h3>
                <!-- <img src="placeholder.jpg" alt="<?= htmlspecialchars($product['name']) ?>"> -->
                <p>Price: <?= htmlspecialchars($product['price']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
