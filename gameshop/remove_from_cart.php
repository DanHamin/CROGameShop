<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    include "db.php";

    if (!isset($_SESSION['id']) || !isset($_GET['cart_id']) || !is_numeric($_GET['cart_id'])) {
        header("Location: cart.php");
        exit;
    }

    $cart_id = (int)$_GET['cart_id'];
    $user_id = (int)$_SESSION['id'];

    
    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();

    header("Location: cart.php");
    exit;
?>