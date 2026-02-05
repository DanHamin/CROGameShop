<?php
    session_start();
    include "db.php";

    if(!isset($_SESSION['id'])){
        header("Location: auth/login.php");
        exit();
    }

    if(isset($_POST['game_id'])){
        $game_id = intval($_POST['game_id']);
        $user_id = $_SESSION['id'];

        // Provjera da ga ima u library
        $stmt = $conn->prepare("SELECT 1 FROM library WHERE user_id=? AND game_id=?");
        $stmt->bind_param("ii", $user_id, $game_id);
        $stmt->execute();
        $exists = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        if(!$exists){
            // Dodaj u library
            $stmt = $conn->prepare("INSERT INTO library(user_id, game_id) VALUES(?, ?)");
            $stmt->bind_param("ii", $user_id, $game_id);
            $stmt->execute();
            $stmt->close();
        }
    }

    header("Location: game.php?id=$game_id&msg=purchased");
    exit();
?>
