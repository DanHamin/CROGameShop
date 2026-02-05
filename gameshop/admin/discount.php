<?php
    session_start();
    include "../db.php";

    if(!isset($_SESSION['id']) || $_SESSION['role']!=="admin"){
        header("Location: /gameshop/index.php");
        exit();
    }

    /* Save */
    if(isset($_POST['set'])){

        $id   = (int)$_POST['id'];
        $disc = (int)$_POST['discount'];

        $stmt = $conn->prepare("
            UPDATE games SET discount=?
            WHERE id=?
        ");

        $stmt->bind_param("ii",$disc,$id);
        $stmt->execute();
        $stmt->close();

        /* game name */
        $g = $conn->prepare("
            SELECT name FROM games WHERE id=?
        ");

        $g->bind_param("i",$id);
        $g->execute();

        $name = $g->get_result()
                ->fetch_assoc()['name'];

        $g->close();

        /* notify */
        $users = $conn->query("
            SELECT id FROM users WHERE role='user'
        ");

        while($u=$users->fetch_assoc()){

            $msg = "Discount: $name -$disc%";

            $n = $conn->prepare("
                INSERT INTO notifications
                (user_id,game_id,message)
                VALUES (?,?,?)
            ");

            $n->bind_param("iis",$u['id'],$id,$msg);
            $n->execute();
            $n->close();
        }

        $success = "Discount saved!";
    }

    /* games */
    $games = $conn->query("SELECT id,name FROM games");
?>

<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">
    <title>Manage Discounts</title>

    <link rel="stylesheet" href="/gameshop/style.css">
    <link rel="stylesheet" href="/gameshop/admin/admin.css">
    <link rel="stylesheet" href="/gameshop/admin/admin-pages.css">

</head>

<body>

    <h1 class="admin-title">🏷️ Manage Discounts</h1>

    <div class="admin-box">

        <?php if(isset($success)): ?>

        <p class="success"><?= $success ?></p>

        <?php endif; ?>

        <form method="post" class="admin-form">

            <select name="id" required>

            <option value="">Select Game</option>

            <?php while($g=$games->fetch_assoc()): ?>

            <option value="<?= $g['id'] ?>">
            <?= htmlspecialchars($g['name']) ?>
            </option>

            <?php endwhile; ?>

            </select>

            <input type="number"
                name="discount"
                min="1"
                max="90"
                placeholder="Discount %"
                required>

            <button name="set">Save</button>

        </form>

    </div>

</body>
</html>
