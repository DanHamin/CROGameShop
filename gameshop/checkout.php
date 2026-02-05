<?php
session_start();
include "db.php";

if (!isset($_SESSION['id'])) {
    header("Location: auth/login.php");
    exit;
}

$user_id = $_SESSION['id'];

// Dohvati sve iz košarice
$stmt = $conn->prepare("SELECT game_id FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $game_id = $row['game_id'];
    
    // Dodaj u library
    $stmt_insert = $conn->prepare("INSERT IGNORE INTO library (user_id, game_id) VALUES (?, ?)");
    $stmt_insert->bind_param("ii", $user_id, $game_id);
    $stmt_insert->execute();
    $stmt_insert->close();
}

$stmt->close();

// Prazni košaricu
$stmt_clear = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
$stmt_clear->bind_param("i", $user_id);
$stmt_clear->execute();
$stmt_clear->close();

header("Location: cart.php?msg=purchased");
exit;