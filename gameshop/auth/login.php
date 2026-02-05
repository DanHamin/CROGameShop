<?php
    session_start();
    include "../db.php";

    if(isset($_SESSION['id'])){
        header("Location: /gameshop/index.php");
        exit();
    }

    /* Login */

    if(isset($_POST['login'])){

        $email = trim($_POST['email']);
        $pass  = $_POST['password'];

        $stmt = $conn->prepare("
            SELECT *
            FROM users
            WHERE email=?
        ");

        $stmt->bind_param("s",$email);
        $stmt->execute();

        $user = $stmt->get_result()->fetch_assoc();


        if($user && password_verify($pass,$user['password'])){

            $_SESSION['id']       = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            header("Location: /gameshop/index.php");
            exit();

        }else{

            $error = "Wrong email or password!";
        }
    }
?>

<!DOCTYPE html>
<html lang="hr">

<head>

    <meta charset="UTF-8">

    <title>Login | GameShop</title>

    <link rel="stylesheet" href="/gameshop/style.css">
    <link rel="stylesheet" href="/gameshop/auth/auth.css">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>

<body>

    <div class="auth-page">

        <div class="auth-box">

            <div class="auth-icon">
                <i class="fa-solid fa-gamepad"></i>
            </div>

            <h2>Login</h2>

            <?php if(isset($error)): ?>

                <div class="auth-error">
                    <?= $error ?>
                </div>

            <?php endif; ?>

            <form method="post">

                <input type="email"
                    name="email"
                    placeholder="Email"
                    required>

                <input type="password"
                    name="password"
                    placeholder="Password"
                    required>

                <button name="login">
                    Login
                </button>

            </form>

            <div class="auth-links">

                No account?
                <a href="/gameshop/auth/register.php">
                    Register
                </a>

            </div>
        </div>
    </div>

</body>
</html>
