<?php
session_start();
// login.php
require 'db_con.php';
require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$username = $_POST['username'];
//	echo $username;
    $password = $_POST['password'];
   $recaptchaResponse = $_POST['g-recaptcha-response'];

/*
if(isset($_POST['g-recaptcha-response'])){
  $captcha = $_POST['g-recaptcha-response'];
}
*/


   if (validateRecaptcha($recaptchaResponse)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        if ($user['password']==$username) {
            
            $_SESSION['user_id'] = 1;
            header('Location: index.php');
	    exit;
	    
        } else {
            echo "Invalid login credentials.";
        }
    } else {
        echo "reCAPTCHA failed. Please try again.";
    }
}  
 

?>

<form method="POST" action="">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <div class="g-recaptcha" data-sitekey="6LdodiwqAAAAAMB8aopsj30k-VaeCduyj0Qr9pkW"></div><br>
    <button type="submit">Login</button>
</form>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
  <p>Don't have an account? <a href="register.php">Register here</a></p>

