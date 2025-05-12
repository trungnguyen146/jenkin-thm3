<?php
  $title = "Trang chủ";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title><?php echo $title; ?></title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 20px;
    }
    header, footer {
      background-color: #333;
      color: white;
      padding: 10px 20px;
    }
    h1 {
      color: #333;
    }
  </style>
</head>
<body>

<header>
  <h2><?php echo $title; ?></h2>
</header>

<main>
  <h1>Chào mừng bạn đến với website!</h1>
  <p>Đây là một ví dụ về một file PHP tĩnh đơn giản.</p>
</main>

<footer>
  <p>© 2025 - Website PHP tĩnh</p>
</footer>

</body>
</html>
