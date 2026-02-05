<?php
    session_start();
    include "../db.php";

    if(!isset($_SESSION['id']) || $_SESSION['role'] !== "admin"){
        header("Location: /gameshop/index.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="hr">
<head>

    <meta charset="UTF-8">
    <title>Admin Panel</title>

    <link rel="stylesheet" href="/gameshop/style.css">
    <link rel="stylesheet" href="/gameshop/admin/admin.css">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

    <h1 class="admin-title">
        <i class="fa-solid fa-shield-halved"></i>
        Admin Dashboard
    </h1>

    <div class="admin-grid">

        <!-- Manage games -->
        <a href="games.php" class="admin-card">

            <i class="fa-solid fa-gamepad"></i>

            <h3>Manage Games</h3>

            <p>Add, edit and delete games</p>

        </a>

        <!-- Discounts -->
        <a href="discount.php" class="admin-card">

            <i class="fa-solid fa-tags"></i>

            <h3>Manage Discounts</h3>

            <p>Set discounts and notify users</p>

        </a>

        <!-- Notifications -->
        <a href="notifications.php" class="admin-card">

            <i class="fa-solid fa-bell"></i>

            <h3>Notifications</h3>

            <p>View price change alerts</p>

        </a>

    </div>

</body>
</html>
