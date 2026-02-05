<?php
    include "db.php"; 
    include "header.php";

    if (!isset($_SESSION['id'])) {
        header("Location: auth/login.php");  // ← maknuo sam ../ jer wishlist.php je u rootu
    exit;
    }

    $user_id = $_SESSION['id'];

    $stmt = $conn->prepare("
        SELECT g.* 
        FROM wishlist w
        JOIN games g ON w.game_id = g.id
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC
    ");

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
?>

<h1 style="text-align:center; margin: 30px 0;">Moj Wishlist</h1>

<?php if ($result->num_rows === 0): ?>
    <p style="text-align:center; font-size:1.2rem;">Još nemaš nijednu igru u wish listi.</p>
<?php else: ?>
    <div class="games-grid">
        <?php while ($game = $result->fetch_assoc()): 
            $finalPrice = $game['price'] * (1 - $game['discount']/100);
        ?>
            <div class="game-card">
                <a href="game.php?id=<?= $game['id'] ?>">
                    <img src="<?= htmlspecialchars($game['image'] ?: 'https://via.placeholder.com/300x400') ?>" 
                         alt="<?= htmlspecialchars($game['name']) ?>">
                    <h3><?= htmlspecialchars($game['name']) ?></h3>
                </a>

                <div class="price-row">
                    <?php if ($game['discount'] > 0): ?>
                        <span><del><?= $game['price'] ?> €</del> 
                              <strong><?= number_format($finalPrice, 2) ?> €</strong></span>
                    <?php else: ?>
                        <span><?= $game['price'] ?> €</span>
                    <?php endif; ?>
                </div>

                <!-- Jedna ispravna forma za uklanjanje -->
                <form action="add_to_wishlist.php" method="post" style="margin-top:10px;">
                    <input type="hidden" name="game_id" value="<?= $game['id'] ?>">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="from" value="wishlist">
                    <button type="submit" class="btn-remove">Ukloni</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

    <?php endif; 
?>

<?php
    $stmt->close();
    include "footer.php";
?>