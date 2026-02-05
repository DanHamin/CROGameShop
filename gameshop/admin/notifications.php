<?php
    session_start();
    include "../db.php";

    /* Login check */
    if(!isset($_SESSION['id'])){
        header("Location: /gameshop/auth/login.php");
        exit();
    }

    $user_id = $_SESSION['id'];

    /* Mark all as read */
    $mark = $conn->prepare("
        UPDATE notifications
        SET seen = 1
        WHERE user_id = ?
    ");

    if(!$mark){
        die("SQL error: ".$conn->error);
    }

    $mark->bind_param("i", $user_id);
    $mark->execute();
    $mark->close();

    /* Get notifications with game info */
    $stmt = $conn->prepare("
        SELECT n.message, n.created_at, g.name AS game_name
        FROM notifications n
        LEFT JOIN games g ON n.game_id = g.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="hr">
<head>

    <meta charset="UTF-8">
    <title>Notifications - GameShop</title>
    <link rel="stylesheet" href="/gameshop/style.css">
    <link rel="stylesheet" href="/gameshop/notifications.css">

</head>
<body>

    <h2 class="notif-title">🔔 Your Notifications</h2>

    <div class="notif-box">

        <?php if($result->num_rows === 0): ?>

            <p class="empty">No notifications yet.</p>

        <?php else: ?>

            <?php while($n = $result->fetch_assoc()): ?>

                <div class="notif-item">
                    <div class="notif-msg">
                        <?= htmlspecialchars($n['message']) ?>
                        <?php if(!empty($n['game_name'])): ?>
                            <strong>(<?= htmlspecialchars($n['game_name']) ?>)</strong>
                        <?php endif; ?>
                    </div>
                    <div class="notif-time">
                        <?= date("d.m.Y H:i", strtotime($n['created_at'])) ?>
                    </div>
                </div>
                
            <?php endwhile; ?>
        <?php endif; ?>

    </div>

</body>
</html>
