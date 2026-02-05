<?php
    session_start();
    include "db.php";

    if (!isset($_SESSION['id'])) {
        header("Location: auth/login.php");
        exit;
    }

    $game_id = (int)($_POST['game_id'] ?? 0);
    $action  = $_POST['action'] ?? 'add';
    $from    = $_POST['from'] ?? 'game';   

    if ($game_id <= 0) {
        header("Location: index.php");
        exit;
    }

    $user_id = (int)$_SESSION['id'];

    if ($action === 'add') {
        $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, game_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $game_id);
        $stmt->execute();
        $status = 'added';
        $stmt->close();
    } else {
        $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND game_id = ?");
        $stmt->bind_param("ii", $user_id, $game_id);
        $stmt->execute();
        $status = 'removed';
        $stmt->close();
    }

    // Redirect ovisi o tome odakle je došao zahtjev
    if ($from === 'wishlist') {
        header("Location: wishlist.php");
    } else {
        header("Location: game.php?id=$game_id&wl=$status");
    }

    exit;