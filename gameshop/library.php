<?php
    include "db.php";
    include "header.php";

    if (!isset($_SESSION['id'])) {
        header("Location: ../auth/login.php");
        exit;
    }

    $user_id = $_SESSION['id'];

    // Dohvati sve kupljene igre
    $stmt = $conn->prepare("
        SELECT g.* 
        FROM library l
        JOIN games g ON l.game_id = g.id
        WHERE l.user_id = ?
        ORDER BY l.purchased_at DESC
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
?>

<h1 style="text-align:center; margin: 50px 0 30px; color:#ff6600;">Library</h1>

<?php if ($result->num_rows === 0): ?>
    <p style="text-align:center; font-size:1.3rem; color:#94a3b8; margin:80px 0;">
        Još niste kupili nijednu igru.<br>
        <a href="index.php" style="color:#ff6600;">Pogledajte ponudu</a>
    </p>

<?php else: ?>
    <div class="games-grid" style="padding:0 20px; max-width:1440px; margin:0 auto;">
        <?php while ($game = $result->fetch_assoc()): 
            $finalPrice = $game['discount'] > 0 
                ? number_format($game['price'] * (1 - $game['discount']/100), 2) 
                : number_format($game['price'], 2);
        ?>
            <div class="game-card">
                <a href="game.php?id=<?= $game['id'] ?>">
                    <img src="<?= htmlspecialchars($game['image'] ?: 'https://via.placeholder.com/260x240') ?>" 
                         alt="<?= htmlspecialchars($game['name']) ?>">
                    <h3><?= htmlspecialchars($game['name']) ?></h3>
                    
                    <?php if ($game['discount'] > 0): ?>
                        <p class="price">
                            <del><?= number_format($game['price'], 2) ?> €</del> 
                            <strong><?= $finalPrice ?> €</strong>
                        </p>
                    <?php else: ?>
                        <p class="price"><?= number_format($game['price'], 2) ?> €</p>
                    <?php endif; ?>
                </a>

                <p style="text-align:center; color:#27ae60; font-weight:bold; margin-top:10px;">
                    Posjedujete
                </p>
            </div>
        <?php endwhile; ?>
    </div>

<?php endif; ?>

<?php
    $stmt->close();
    include "footer.php";
?>