<?php
    include "db.php";
    include "header.php";

    // Dohvati ID igre iz GET
    $gameId = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $stmt = $conn->prepare("SELECT * FROM games WHERE id = ?");
    $stmt->bind_param("i", $gameId);
    $stmt->execute();
    $result = $stmt->get_result();
    $game = $result->fetch_assoc();

    if (!$game) {
        echo "<p style='text-align:center; margin-top:50px;'>Game not found!</p>";
        include "footer.php";
        exit;
    }

    // Izračun finalne cijene
    $finalPrice = $game['price'];
    if ($game['discount'] > 0) {
        $finalPrice = $finalPrice * (1 - $game['discount']/100);
    }

    // Dohvat trenutnih igrača (Steam API)
    $currentPlayers = null;
    if (!empty($game['steam_appid'])) {
        $steamAppId = $game['steam_appid'];
        $apiUrl = "https://api.steampowered.com/ISteamUserStats/GetNumberOfCurrentPlayers/v1/?appid={$steamAppId}";

        $json = @file_get_contents($apiUrl);
        if ($json) {
            $data = json_decode($json, true);
            if (isset($data['response']['player_count'])) {
                $currentPlayers = $data['response']['player_count'];
            }
        }
    }

    // Provjere za prijavljenog korisnika
    $in_wishlist = false;
    $in_cart = false;
    $already_owned = false;
    $wl_feedback = '';
    $cart_feedback = '';

    if (isset($_SESSION['id'])) {
        $user_id = $_SESSION['id'];

        // Wishlist
        $stmt_wl = $conn->prepare("SELECT 1 FROM wishlist WHERE user_id = ? AND game_id = ? LIMIT 1");
        $stmt_wl->bind_param("ii", $user_id, $gameId);
        $stmt_wl->execute();
        $in_wishlist = $stmt_wl->get_result()->num_rows > 0;
        $stmt_wl->close();

        // Košarica
        $stmt_cart = $conn->prepare("SELECT 1 FROM cart WHERE user_id = ? AND game_id = ? LIMIT 1");
        $stmt_cart->bind_param("ii", $user_id, $gameId);
        $stmt_cart->execute();
        $in_cart = $stmt_cart->get_result()->num_rows > 0;
        $stmt_cart->close();

        // Već kupljeno (u library)
        $stmt_owned = $conn->prepare("SELECT 1 FROM library WHERE user_id = ? AND game_id = ? LIMIT 1");
        $stmt_owned->bind_param("ii", $user_id, $gameId);
        $stmt_owned->execute();
        $already_owned = $stmt_owned->get_result()->num_rows > 0;
        $stmt_owned->close();
    }

    // Feedback poruke (wishlist, cart, itd.)
    if (isset($_GET['wl'])) {
        if ($_GET['wl'] === 'added') {
            $wl_feedback = '<div class="wl-msg success">Dodano u wishlist!</div>';
        } elseif ($_GET['wl'] === 'removed') {
            $wl_feedback = '<div class="wl-msg success">Uklonjeno iz wishlista!</div>';
        }
    }

    if (isset($_GET['msg'])) {
        if ($_GET['msg'] === 'added_to_cart') {
            //$cart_feedback = '<div class="cart-msg success">Dodano u košaricu! <a href="cart.php">Pogledaj košaricu</a></div>';
        } elseif ($_GET['msg'] === 'in_cart') {
            $cart_feedback = '<div class="cart-msg warning">Igra je već u košarici!</div>';
        } elseif ($_GET['msg'] === 'already_owned') {
            $cart_feedback = '<div class="cart-msg error">Već posjedujete ovu igru!</div>';
        }
    }
    ?>

    <!-- CSS za igru -->
    <link rel="stylesheet" href="game.css">

    <div class="game-detail-container">
        <!-- Slika igre -->
        <div class="game-image">
            <img src="<?= !empty($game['image']) ? $game['image'] : 'https://via.placeholder.com/700x700?text=' . urlencode($game['name']) ?>" 
                alt="<?= htmlspecialchars($game['name']) ?>">
        </div>

        <!-- Info o igri -->
        <div class="game-info">
            <h1><?= htmlspecialchars($game['name']) ?></h1>
            <p><?= htmlspecialchars($game['description']) ?></p>

            <div class="info-row">
                <!-- Broj igrača -->
                <?php if ($currentPlayers !== null): ?>
                    <span class="current-players">Trenutno igra: <?= number_format($currentPlayers) ?></span>
                <?php endif; ?>

                <!-- Cijena i dugmad -->
                <div class="price-cart">
                    <?php if ($game['discount'] > 0): ?>
                        <span class="price">
                            <del><?= number_format($game['price'], 2) ?> €</del> 
                            <strong><?= number_format($finalPrice, 2) ?> €</strong>
                        </span>
                    <?php else: ?>
                        <span class="price"><?= number_format($game['price'], 2) ?> €</span>
                    <?php endif; ?>

                    <!-- Košarica i kupi dugmad -->
                    <?php if (isset($_SESSION['id'])): ?>
                        <?php if ($already_owned): ?>
                            <span style="background:#27ae60; color:white; padding:8px 16px; border-radius:6px; margin-left:15px;">
                                Posjedujete
                            </span>
                        <?php elseif ($in_cart): ?>
                            <a href="cart.php" style="background:#f39c12; color:white; padding:8px 16px; border-radius:6px; margin-left:15px; text-decoration:none;">
                                U košarici
                            </a>
                        <?php else: ?>
                            <!-- Dodaj u košaricu -->
                            <form action="add_to_cart.php" method="post" style="display:inline-block; margin-left:15px;">
                                <input type="hidden" name="game_id" value="<?= $gameId ?>">
                                <button type="submit" class="btn-cart" style="background:#3498db; color:white; padding:10px 18px; border:none; border-radius:6px; cursor:pointer;">
                                    Dodaj u košaricu
                                </button>
                            </form>

                            <!-- Kupi odmah -->
                            <form action="buy_game.php" method="post" style="display:inline-block; margin-left:10px;">
                                <input type="hidden" name="game_id" value="<?= $gameId ?>">
                                <button type="submit" class="btn-buy" style="background:#27ae60; color:white; padding:10px 18px; border:none; border-radius:6px; cursor:pointer; font-weight:bold;">
                                    Kupi odmah
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="auth/login.php?redirect=<?= urlencode("game.php?id=$gameId") ?>" 
                        style="background:#7f8c8d; color:white; padding:10px 18px; border-radius:6px; margin-left:15px; text-decoration:none;">
                            Prijavite se za kupovinu
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Wishlist sekcija -->
            <div class="wishlist-section" style="margin-top:25px;">
                <?php if (isset($_SESSION['id'])): ?>
                    <form action="add_to_wishlist.php" method="post">
                        <input type="hidden" name="game_id" value="<?= $gameId ?>">
                        <input type="hidden" name="action" value="<?= $in_wishlist ? 'remove' : 'add' ?>">
                        <input type="hidden" name="from" value="game">
                        <button type="submit" class="btn-wishlist <?= $in_wishlist ? 'active' : '' ?>">
                            <i class="fa-solid fa-heart"></i>
                            <?= $in_wishlist ? 'U wishlistu' : 'Dodaj u wishlist' ?>
                        </button>
                    </form>
                <?php else: ?>
                    <a href="auth/login.php?redirect=<?= urlencode("game.php?id=$gameId") ?>" class="btn-wishlist guest">
                        <i class="fa-solid fa-heart"></i> Dodaj u wishlist (prijavi se)
                    </a>
                <?php endif; ?>

                <?= $wl_feedback ?>
                <?= $cart_feedback ?>
            </div>
        </div>
    </div>

    <?php include "footer.php"; ?>