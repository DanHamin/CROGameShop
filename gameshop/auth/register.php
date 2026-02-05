<?php
    session_start();
    include "../db.php";

    if(isset($_SESSION['id'])){
        header("Location: /gameshop/index.php");
        exit();
    }

    /* Register */

    if(isset($_POST['register'])){

        $username = trim($_POST['username']);
        $email    = trim($_POST['email']);
        $pass     = $_POST['password'];

        if(strlen($pass) < 5){
            $error = "Password must be at least 5 characters!";
        }
        else{

            $hash = password_hash($pass,PASSWORD_DEFAULT);

            /* Check existing */

            $check = $conn->prepare("
                SELECT id
                FROM users
                WHERE username=? OR email=?
            ");

            $check->bind_param("ss",$username,$email);
            $check->execute();

            $exists = $check->get_result();

            if($exists->num_rows > 0){

                $error = "Username or email already exists!";

            }else{

                $stmt = $conn->prepare("
                    INSERT INTO users
                    (username,email,password,role)
                    VALUES (?,?,?,'user')
                ");

                $stmt->bind_param("sss",$username,$email,$hash);

                if($stmt->execute()){

                    header("Location: /gameshop/auth/login.php");
                    exit();

                }else{
                    $error = "Registration failed!";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="hr">

<head>

    <meta charset="UTF-8">

    <title>Register | GameShop</title>

    <link rel="stylesheet" href="/gameshop/style.css">
    <link rel="stylesheet" href="/gameshop/auth/auth.css">

    <link rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>


<body>

    <div class="auth-page">

        <div class="auth-box">

            <div class="auth-icon">
                <i class="fa-solid fa-user-plus"></i>
            </div>

            <h2>Register</h2>

            <?php if(isset($error)): ?>

                <div class="auth-error">
                    <?= $error ?>
                </div>

            <?php endif; ?>

            <form method="post">

                <input type="text"
                    name="username"
                    placeholder="Username"
                    required>

                <input type="email"
                    name="email"
                    placeholder="Email"
                    required>

                <input type="password"
                    name="password"
                    placeholder="Password"
                    required>

                <button name="register">
                    Register
                </button>

            </form>

            <div class="auth-links">

                Already have account?
                <a href="/gameshop/auth/login.php">
                    Login
                </a>

            </div>
        </div>
    </div>

</body>
</html>
