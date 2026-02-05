<?php
    session_start();
    include "../db.php";

    /* Login check */
    if(!isset($_SESSION['id'])){
        header("Location: /gameshop/auth/login.php");
        exit();
    }

    $user_id = $_SESSION['id'];

    /* Obrada forme */
    if(isset($_POST['save'])){
        $newUsername = trim($_POST['username']);

        // Provjera da li username već postoji
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE username=? AND id<>?");
        $stmtCheck->bind_param("si", $newUsername, $user_id);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        
        if($resCheck->num_rows > 0){
            $error = "Username već postoji!";
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=? WHERE id=?");
            $stmt->bind_param("si", $newUsername, $user_id);
            if($stmt->execute()){
                $_SESSION['username'] = $newUsername; // update session
                $success = "Username uspješno promijenjen!";
            } else {
                $error = "Došlo je do greške, pokušajte ponovno.";
            }
            $stmt->close();
        }
        $stmtCheck->close();
    }

    /* Dohvati trenutni username */
    $stmt = $conn->prepare("SELECT username FROM users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $username = $stmt->get_result()->fetch_assoc()['username'];
    $stmt->close();
?>

<!DOCTYPE html>
<html lang="hr">
<head>

    <meta charset="UTF-8">
    <title>Edit Username - GameShop</title>
    <link rel="stylesheet" href="/gameshop/auth/auth.css">

</head>

<body>

    <div class="auth-container">

        <h2>Edit Username</h2>

        <?php if(isset($error)): ?>
            <p class="auth-error"><?= $error ?></p>
        <?php endif; ?>

        <?php if(isset($success)): ?>
            <p class="auth-success"><?= $success ?></p>
        <?php endif; ?>

        <form method="post" class="auth-form">
            <label for="username">New Username</label>
            <input type="text" id="username" name="username" value="<?= htmlspecialchars($username) ?>" required>

            <button name="save">Save Changes</button>
        </form>

        <p class="back-link"><a href="/gameshop/index.php">← Back to Home</a></p>

    </div>

</body>
</html>
