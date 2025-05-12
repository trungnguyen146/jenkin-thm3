<?php
  $title = "Shop Vợt Cầu Lông - Trang chủ";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <title><?php echo $title; ?></title>
  <style>
    body {
      font-family: "Segoe UI", sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f0f4f8;
      color: #333;
    }

    header {
      background-color: #0275d8;
      color: white;
      padding: 20px;
      text-align: center;
    }

    h1, h2 {
      margin: 0;
    }

    main {
      padding: 30px;
      max-width: 1000px;
      margin: auto;
    }

    .product {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      margin-bottom: 20px;
      overflow: hidden;
      display: flex;
      flex-wrap: wrap;
    }

    .product img {
      width: 300px;
      height: auto;
      object-fit: cover;
    }

    .product-info {
      flex: 1;
      padding: 20px;
    }

    .product-info h3 {
      margin-top: 0;
      color: #0275d8;
    }

    footer {
      background-color: #ddd;
      text-align: center;
      padding: 15px;
      font-size: 14px;
      color: #555;
    }

    a {
      color: #0275d8;
      text-decoration: none;
    }
  </style>
</head>
<body>

<header>
  <h1>Shop Vợt Cầu Lông</h1>
  <p>Chuyên cung cấp vợt chất lượng – chính hãng – giá tốt</p>
</header>

<main>
  <h2>Sản phẩm nổi bật</h2>

  <div class="product">
    <img src="images/astrox99.jpg" alt="Vợt Yonex Astrox 99 Pro">
    <div class="product-info">
      <h3>Yonex Astrox 99 Pro</h3>
      <p>Vợt công thủ toàn diện, dành cho người chơi nâng cao. Thiết kế đẹp, màu sắc mạnh mẽ.</p>
      <p><strong>Giá:</strong> 3.990.000₫</p>
    </div>
  </div>

  <div class="product">
    <img src="images/lining75.jpg" alt="Vợt Lining Turbo Charging 75">
    <div class="product-info">
      <h3>Lining Turbo Charging 75</h3>
      <p>Vợt nhẹ, linh hoạt, phù hợp với người chơi yêu thích tốc độ và phản xạ nhanh.</p>
      <p><strong>Giá:</strong> 2.750.000₫</p>
    </div>
  </div>

</main>

<footer>
  © 2025 Shop Vợt Cầu Lông | <a href="mailto:sales@shopvot.com">sales@shopvot.com</a>
</footer>

</body>
</html>
