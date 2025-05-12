<?php
$greeting = "Hello, welcome to my simple PHP website!";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple PHP Website</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            margin-top: 50px;
            background-color: #f0f0f0;
        }
        h1 {
            color: #333;
        }
        p {
            font-size: 18px;
            color: #666;
        }
    </style>
</head>
<body>
    <h1>Simple PHP Website</h1>
    <p><?php echo $greeting; ?></p>
    <p>This is a basic website built with PHP and HTML.</p>
</body>
</html>
