<?php
include "db.php";
include "header.php";

// Definiraj kategorije za slidere (ovo mora biti prije svega!)
$sliders = ['Popular', 'Action', 'FPS', 'Indie'];

// Dohvati igre za slidere (samo ako nema pretrage)
$gamesByCategory = [];

// Pretraga
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchActive = $search !== '';
$searchResults = [];

if (!$searchActive) {
    
    foreach ($sliders as $cat) {
        $stmt = $conn->prepare("
            SELECT g.* FROM games g
            JOIN game_categories gc ON g.id = gc.game_id
            JOIN categories c ON gc.category_id = c.id
            WHERE c.name = ?
            ORDER BY g.id ASC
            LIMIT 20
        ");
        $stmt->bind_param("s", $cat);
        $stmt->execute();
        $result = $stmt->get_result();
        $gamesByCategory[$cat] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
} else {

    $searchTerm = "%" . $search . "%";

    $sql = "SELECT DISTINCT g.*
            FROM games g
            LEFT JOIN game_categories gc ON g.id = gc.game_id
            LEFT JOIN categories c ON gc.category_id = c.id
            WHERE g.name LIKE ? 
               OR g.description LIKE ?
               OR c.name LIKE ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    $searchResults = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<h1 style="text-align:center; margin: 40px 0 20px;">CRO GameShop</h1>

<?php if ($searchActive): ?>

    <h2 style="text-align:center; margin: 40px 0 20px; color: #ff6600;">
        Rezultati pretrage za: "<?= htmlspecialchars($search) ?>"
        (<?= count($searchResults) ?> igara pronađeno)
    </h2>

    <?php if (empty($searchResults)): ?>
        <p style="text-align:center; font-size:1.3rem; color:#94a3b8; margin:80px 0;">
            Nema igara koje odgovaraju traženom pojmu...
        </p>
    <?php else: ?>
        <div class="games-grid">
            <?php foreach ($searchResults as $game): 
                $finalPrice = $game['discount'] > 0 
                    ? number_format($game['price'] * (1 - $game['discount']/100), 2) 
                    : $game['price'];
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
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

<?php else: ?>


<?php foreach ($sliders as $cat): ?>

    <?php if (!empty($gamesByCategory[$cat])): ?>
        <h2 class="category-title">
            <a href="category.php?name=<?= urlencode($cat) ?>"><?= htmlspecialchars($cat) ?> Games</a>
        </h2>

        <div class="carousel-container" id="carousel-<?= $cat ?>">
            <button class="carousel-btn prev" type="button">❮</button>
            <div class="carousel-wrapper">
                <div class="carousel-slide"></div>
            </div>
            <button class="carousel-btn next" type="button">❯</button>
        </div>

    <?php endif; ?>

<?php endforeach; ?>

<?php endif; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const sliders = <?= json_encode($gamesByCategory) ?>;

        const visibleCards = 4;
        const cardWidth = 260 + 20;  

        Object.keys(sliders).forEach(cat => {
            const container = document.querySelector(`#carousel-${cat}`);
            if (!container) return;

            const wrapper = container.querySelector(".carousel-wrapper");
            const prev = container.querySelector(".prev");
            const next = container.querySelector(".next");

            if (!wrapper || !prev || !next) {
                console.warn(`Elementi nedostaju za kategoriju: ${cat}`);
                return;
            }

            // Ubaci kartice u slide
            sliders[cat].slice(0, 20).forEach(game => {
                const finalPrice = game.discount > 0
                    ? (game.price * (1 - game.discount / 100)).toFixed(2)
                    : game.price;

                const div = document.createElement("div");
                div.className = "game-card";
                div.innerHTML = `
                    <a href="game.php?id=${game.id}">
                        <img src="${game.image || 'https://via.placeholder.com/260x240'}" alt="${game.name}">
                        <h3>${game.name}</h3>
                        ${game.discount > 0
                            ? `<p class="price"><del>${game.price} €</del> <strong>${finalPrice} €</strong></p>`
                            : `<p class="price">${game.price} €</p>`}
                    </a>
                `;
                wrapper.querySelector(".carousel-slide").appendChild(div);
            });

            // Scroll logika
            const scrollAmount = cardWidth * visibleCards;

            prev.addEventListener("click", () => {
                wrapper.scrollBy({ left: -scrollAmount, behavior: "smooth" });
            });

            next.addEventListener("click", () => {
                wrapper.scrollBy({ left: scrollAmount, behavior: "smooth" });
            });
        });
    });
</script>

<?php include "footer.php"; ?>