<?php
    include "db.php";
    include "header.php";

    if (!isset($_SESSION['id'])) {
        header("Location: auth/login.php");
        exit;
    }

    $user_id = $_SESSION['id'];

    // Dohvati igre u košarici
    $stmt = $conn->prepare("
        SELECT g.*, c.id AS cart_id 
        FROM cart c
        JOIN games g ON c.game_id = g.id
        WHERE c.user_id = ?
        ORDER BY c.added_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $total = 0;
    $items = [];
    while ($row = $result->fetch_assoc()) {
        $price = $row['discount'] > 0 
            ? $row['price'] * (1 - $row['discount']/100) 
            : $row['price'];
        $total += $price;
        $items[] = $row;
    }
    $stmt->close();
    ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'purchased'): ?>
        <div style="background:#27ae60; color:white; padding:15px; margin:20px auto; max-width:800px; border-radius:6px; text-align:center;">
            Uspješno ste kupili igre! Sada su u vašoj <a href="pages/library.php" style="color:white; text-decoration:underline;">Library</a>.
        </div>
    <?php endif; ?>

    <h1 style="text-align:center; margin: 40px 0;">Košarica</h1>

    <?php if (empty($items)): ?>
        <p style="text-align:center; font-size:1.3rem; margin:60px 0;">
            Košarica je prazna. <a href="index.php" style="color:#ff6600;">Dodajte igre</a>
        </p>
    <?php else: ?>
        <div style="max-width:1000px; margin:0 auto 40px; padding:0 20px;">
            <?php foreach ($items as $game): 
                $finalPrice = $game['discount'] > 0 
                    ? number_format($game['price'] * (1 - $game['discount']/100), 2) 
                    : number_format($game['price'], 2);
            ?>
                <div style="display:flex; align-items:center; background:#1f2a35; margin-bottom:15px; padding:15px; border-radius:8px;">
                    <img src="<?= htmlspecialchars($game['image'] ?: 'https://via.placeholder.com/100x100') ?>" 
                        alt="<?= htmlspecialchars($game['name']) ?>" 
                        style="width:100px; height:100px; object-fit:cover; border-radius:6px; margin-right:20px;">
                    <div style="flex:1;">
                        <h3 style="margin:0 0 8px;"><?= htmlspecialchars($game['name']) ?></h3>
                        <p style="margin:0; color:#27ae60; font-weight:bold;"><?= $finalPrice ?> €</p>
                    </div>
                    <a href="remove_from_cart.php?cart_id=<?= $game['cart_id'] ?>" 
                    style="color:#e74c3c; text-decoration:none; font-weight:bold;">
                        Ukloni
                    </a>
                </div>
            <?php endforeach; ?>

            <div style="text-align:right; margin-top:30px; font-size:1.4rem;">
                <strong>Ukupno: <?= number_format($total, 2) ?> €</strong>
            </div>

            <form action="checkout.php" method="post" style="text-align:right; margin-top:20px;">
                <button type="submit" style="background:#27ae60; color:white; padding:12px 30px; border:none; border-radius:6px; cursor:pointer; font-size:1.1rem; font-weight:bold;">
                    Završi kupovinu
                </button>
            </form>
        </div>
    <?php endif; 
?>

<?php include "footer.php"; ?>