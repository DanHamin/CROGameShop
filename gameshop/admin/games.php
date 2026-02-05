<?php
    session_start();
    include "../db.php";

    // Samo admin
    if(!isset($_SESSION['id']) || $_SESSION['role']!=="admin"){
        header("Location: /gameshop/index.php");
        exit();
    }

    /*  ADD GAME  */
    if(isset($_POST['add'])){
        $name  = trim($_POST['name']);
        $price = (float)$_POST['price'];

        $stmt = $conn->prepare("INSERT INTO games (name,price) VALUES (?,?)");
        $stmt->bind_param("sd",$name,$price);
        $stmt->execute();
        $stmt->close();

        $msg = "Game added!";
    }

    /*  DELETE GAME */
    if(isset($_GET['delete'])){
        $id = (int)$_GET['delete'];

        $stmt = $conn->prepare("DELETE FROM games WHERE id=?");
        $stmt->bind_param("i",$id);
        $stmt->execute();
        $stmt->close();

        $msg = "Game deleted!";
    }

    /* UPDATE PRICE  */
    if(isset($_POST['update'])){
        $id    = (int)$_POST['id'];
        $price = (float)$_POST['price'];

        // Dohvati staru cijenu
        $old = $conn->prepare("SELECT price,name FROM games WHERE id=?");
        $old->bind_param("i",$id);
        $old->execute();
        $res = $old->get_result()->fetch_assoc();
        $oldPrice = $res['price'];
        $gameName = $res['name'];
        $old->close();

        // Update cijene
        $up = $conn->prepare("UPDATE games SET price=? WHERE id=?");
        $up->bind_param("di",$price,$id);
        $up->execute();
        $up->close();

        // Obavijesti admina i korisnike ako se cijena promijenila
        if($oldPrice != $price){
            $users = $conn->query("SELECT id FROM users");
            while($u = $users->fetch_assoc()){
                // Sada koristimo ime igre, ne ID
                $text = "Price changed: $oldPrice € → $price €";

                $n = $conn->prepare("INSERT INTO notifications (user_id,game_id,message) VALUES (?,?,?)");
                $n->bind_param("iis",$u['id'],$id,$text);
                $n->execute();
                $n->close();
            }
        }

        $msg = "Price updated!";
    }

    /* Dohvati sve igre */
    $games = $conn->query("SELECT * FROM games");
?>

<!DOCTYPE html>
<html lang="hr">
<head>

    <meta charset="UTF-8">
    <title>Admin - Manage Games</title>
    <link rel="stylesheet" href="/gameshop/style.css">
    <link rel="stylesheet" href="/gameshop/admin/admin.css">
    <link rel="stylesheet" href="/gameshop/admin/admin-pages.css">

</head>

<body>

    <h1 class="admin-title">🎮 Manage Games</h1>

    <div class="admin-box">

    <h3>Add New Game</h3>

    <?php if(isset($msg)): ?>

    <p class="success"><?= htmlspecialchars($msg) ?></p>

    <?php endif; ?>

    <form method="post" class="admin-form">
        <input name="name" placeholder="Game name" required>
        <input type="number" step="0.01" name="price" placeholder="Price €" required>
        <button name="add">Add Game</button>
    </form>

    </div>

        <div class="admin-box">

        <h3>Games List</h3>

        <table class="admin-table">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price (€)</th>
                <th>Actions</th>
            </tr>

            <?php while($g = $games->fetch_assoc()): ?>
                
            <tr>
                <td><?= $g['id'] ?></td>
                <td><?= htmlspecialchars($g['name']) ?></td>
                <td>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="id" value="<?= $g['id'] ?>">
                        <input type="number" step="0.01" name="price" value="<?= $g['price'] ?>">
                        <button name="update">Save</button>
                    </form>
                </td>
                <td>
                    <a href="?delete=<?= $g['id'] ?>" class="btn-delete" onclick="return confirm('Delete this game?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>

    </div>

</body>
</html>
