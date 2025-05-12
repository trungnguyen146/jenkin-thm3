
<?php
ob_start();
session_start();
include 'db_con.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

echo 'Login thành công!';

// Add product
if (isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = $_POST['name'];
    $price = $_POST['price'];
    addProduct($pdo, $name, $price);
}

// Update product
if (isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    updateProduct($pdo, $id, $name, $price);
}

// Delete product
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    deleteProduct($pdo, $id);
}

// Fetch all
$products = fetchProducts($pdo);

// Function definitions
function addProduct($pdo, $name, $price) {
    $stmt = $pdo->prepare("INSERT INTO products (name, price) VALUES (:name, :price)");
    $stmt->execute(['name' => $name, 'price' => $price]);
}

function updateProduct($pdo, $id, $name, $price) {
    $stmt = $pdo->prepare("UPDATE products SET name = :name, price = :price WHERE id = :id");
    $stmt->execute(['name' => $name, 'price' => $price, 'id' => $id]);
}

function deleteProduct($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
}

function fetchProducts($pdo) {
    $stmt = $pdo->query("SELECT * FROM products");
    return $stmt->fetchAll();
}
?>

<h1>Product Management</h1>

<!-- Add Product -->
<h2>Add Product</h2>
<form method="POST" action="index.php">
    <input type="hidden" name="action" value="add">
    Name: <input type="text" name="name" required><br>
    Price: <input type="number" name="price" required><br>
    <button type="submit" class="button">Add Product</button>
</form>

<style>
    .button {
        display: inline-block;
        padding: 8px 15px;
        font-size: 13px;
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

<a href="logout.php" class="button">Logout</a>
<a href="del.php" class="button">Delete All</a>
<a href="add.php" class="button">Add a lot of products</a>
<a href="home.php" class="button">Go to showroom</a>

<!-- Product List -->
<h2>Product List</h2>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Price</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($products as $product): ?>
    <tr>
        <td><?= htmlspecialchars($product['id']) ?></td>
        <td><?= htmlspecialchars($product['name']) ?></td>
        <td><?= htmlspecialchars($product['price']) ?></td>
        <td>
            <form method="POST" action="index.php" style="display:inline;">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                Name: <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                Price: <input type="number" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
                <button type="submit">Update</button>
            </form>
            <a href="index.php?delete=<?= $product['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
