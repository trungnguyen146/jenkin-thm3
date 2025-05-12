<?php
// register.php

include 'db_con.php';
include 'auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $recaptchaResponse = $_POST['g-recaptcha-response'];


    if (validateRecaptcha($recaptchaResponse)) {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->execute(['username' => $username, 'password' => $password]);
        header('Location: login.php');
        exit;
    } else {
        echo "reCAPTCHA failed. Please try again.";
    }
}
?>
<form method="POST" action="register.php">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <div class="g-recaptcha" data-sitekey="6LdodiwqAAAAAMB8aopsj30k-VaeCduyj0Qr9pkW"></div><br>
    <button type="submit">Register</button>
</form>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

