<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>GameShop</title>

    <!-- Global CSS -->
    <link rel="stylesheet" href="/gameshop/style.css">

    <!-- Header CSS -->
    <link rel="stylesheet" href="/gameshop/header.css">

    <!-- Index slider -->
    <link rel="stylesheet" href="/gameshop/index.css">

    <!-- Category CSS -->
    <link rel="stylesheet" href="/gameshop/category.css">

    <link rel="stylesheet" href="/gameshop/wishlist.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>


    <!--  NAVBAR  -->

    <nav class="navbar">

        <!-- LEFT -->

        <div class="navbar-left">

            <a href="/gameshop/index.php">
                <i class="fa-solid fa-house"></i> Home
            </a>

            <?php if(isset($_SESSION['id'])): ?>

                <a href="/gameshop/library.php">
                    <i class="fa-solid fa-book"></i> Library
                </a>

                <!-- Categories -->

                <div class="dropdown">

                    <a href="#" class="dropbtn">
                        <i class="fa-solid fa-layer-group"></i> Categories
                    </a>

                    <div class="dropdown-content">

                        <a href="/gameshop/category.php?name=Action">Action</a>
                        <a href="/gameshop/category.php?name=FPS">FPS</a>
                        <a href="/gameshop/category.php?name=RPG">RPG</a>
                        <a href="/gameshop/category.php?name=Indie">Indie</a>
                        <a href="/gameshop/category.php?name=Other">Other</a>

                    </div>

                </div>

                <a href="/gameshop/wishlist.php">
                    <i class="fa-solid fa-heart"></i> Wishlist
                </a>

            <?php else: ?>

                <a href="/gameshop/auth/login.php">Login</a>
                <a href="/gameshop/auth/register.php">Register</a>

            <?php endif; ?>

        </div>

        <!-- RIGHT -->

        <?php if(isset($_SESSION['id'])): ?>

        <div class="navbar-right">

            <!-- Search -->

            <form method="get"
                action="/gameshop/index.php"
                class="navbar-search">

                <input type="text"
                    name="search"
                    placeholder="Search games..."
                    value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">

                <button type="submit">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>

            </form>

            <?php
        if(isset($_SESSION['id'])){

            $user_id = $_SESSION['id'];

            $stmt = $conn->prepare("
                SELECT COUNT(*) AS cnt 
                FROM notifications 
                WHERE user_id = ? AND seen = 0
            ");

            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $count = $stmt->get_result()->fetch_assoc()['cnt'];

            $stmt->close();
        }
        ?>

        <a href="/gameshop/admin/notifications.php" class="notif-icon">

            <i class="fa-solid fa-bell"></i>

            <?php if($count > 0): ?>
                <span class="notif-badge"><?= $count ?></span>
            <?php endif; ?>

        </a>

        <?php
            // Broj artikala u košarici – samo ako je prijavljen
            $cart_count = 0;
            if (isset($_SESSION['id'])) {
                $user_id = $_SESSION['id'];
                $stmt_cart_count = $conn->prepare("SELECT COUNT(*) AS count FROM cart WHERE user_id = ?");
                $stmt_cart_count->bind_param("i", $user_id);
                $stmt_cart_count->execute();
                $cart_count = $stmt_cart_count->get_result()->fetch_assoc()['count'];
                $stmt_cart_count->close();
            }
        ?>

        <!-- Ikona košarice u navigaciji -->
        <a href="/gameshop/cart.php" class="cart-icon" style="position:relative; display:inline-block; margin:0 15px; color:#fff; text-decoration:none;">
            <i class="fa-solid fa-cart-shopping" style="font-size:1.4rem;"></i>
            
            <?php if ($cart_count > 0): ?>
                <span class="cart-badge" style="position:absolute; top:-8px; right:-8px; background:#e74c3c; color:white; font-size:0.75rem; font-weight:bold; width:18px; height:18px; line-height:18px; text-align:center; border-radius:50%; box-shadow:0 2px 4px rgba(0,0,0,0.3);">
                    <?= $cart_count ?>
                </span>
            <?php endif; ?>
        </a>

        <!-- User -->

            <div class="dropdown user-dropdown">
                <span class="dropbtn">👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
                <div class="dropdown-content">
                    <a href="/gameshop/auth/edit_profile.php">Edit Username</a>
                    <a href="/gameshop/auth/logout.php">Logout</a>
                </div>
            </div>

        <!-- Logout -->

            <a href="/gameshop/auth/logout.php">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>

            <?php if($_SESSION['role'] === "admin"): ?>

                <a href="/gameshop/admin/dashboard.php">
                    <i class="fa-solid fa-shield-halved"></i> Admin
                </a>

            <?php endif; ?>

        </div>

        <?php endif; ?>

    </nav>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dropdowns = document.querySelectorAll('.dropdown');

            dropdowns.forEach(dropdown => {
                let timeout;

                dropdown.addEventListener('mouseenter', () => {
                    clearTimeout(timeout);
                    dropdown.querySelector('.dropdown-content').style.display = 'block';
                });

                dropdown.addEventListener('mouseleave', () => {
                    timeout = setTimeout(() => {
                        dropdown.querySelector('.dropdown-content').style.display = 'none';
                    }, 250);
                });
            });
        });
    </script>

</body>


<main>
