<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    include "db.php";

    if (!isset($_SESSION['id'])) {
        header("Location: auth/login.php?redirect=" . urlencode("game.php?id=" . ($_POST['game_id'] ?? 0)));
        exit;
    }

    if (!isset($_POST['game_id']) || !is_numeric($_POST['game_id'])) {
        header("Location: index.php");
        exit;
    }

    $game_id = (int)$_POST['game_id'];
    $user_id = (int)$_SESSION['id'];

    // Provjeri da li je već kupljeno
    $stmt = $conn->prepare("SELECT id FROM library WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $user_id, $game_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: game.php?id=$game_id&msg=already_owned");
        exit;
    }
    $stmt->close();

    // Provjeri da li je već u košarici
    $stmt = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND game_id = ?");
    $stmt->bind_param("ii", $user_id, $game_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: game.php?id=$game_id&msg=in_cart");
        exit;
    }
    $stmt->close();

    // Dodaj u košaricu
    $stmt = $conn->prepare("INSERT INTO cart (user_id, game_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $game_id);
    $stmt->execute();
    $stmt->close();

    // Redirect nazad na igru sa porukom
    header("Location: game.php?id=$game_id&msg=added_to_cart");
    exit;
?>