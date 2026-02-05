<?php
include "db.php";
include "header.php"; // navbar i session_start()

// Provjeri kategoriju iz GET
$category = isset($_GET['name']) ? $_GET['name'] : 'Popular';

// Broj igara po stranici (default 20)
$perPageOptions = [10, 25, 50, 100];
$perPage = isset($_GET['per_page']) && in_array(intval($_GET['per_page']), $perPageOptions)
    ? intval($_GET['per_page'])
    : 20;

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $perPage;

// Ukupno igara u kategoriji
$stmtTotal = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM games g
    JOIN game_categories gc ON g.id = gc.game_id
    JOIN categories c ON gc.category_id = c.id
    WHERE c.name = ?
");
$stmtTotal->bind_param("s", $category);
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result();
$totalRow = $totalResult->fetch_assoc();
$totalGames = $totalRow['total'];
$totalPages = ceil($totalGames / $perPage);

// Dohvati igre za trenutnu stranicu
$stmt = $conn->prepare("
    SELECT g.* 
    FROM games g
    JOIN game_categories gc ON g.id = gc.game_id
    JOIN categories c ON gc.category_id = c.id
    WHERE c.name = ?
    ORDER BY g.id ASC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("sii", $category, $perPage, $offset);
$stmt->execute();
$result = $stmt->get_result();
$games = $result->fetch_all(MYSQLI_ASSOC);
?>

<!-- CSS za kategorije -->
<link rel="stylesheet" href="category.css">

<h1 style="text-align:center; margin-top:20px;"><?= htmlspecialchars($category) ?> Games</h1>

<!-- Filter -->
<form method="get" style="text-align:center; margin-bottom:20px;">
    <input type="hidden" name="name" value="<?= htmlspecialchars($category) ?>">
    <label for="per_page">Games per page:</label>
    <select name="per_page" id="per_page" onchange="this.form.submit()">
        <?php foreach($perPageOptions as $option): ?>
            <option value="<?= $option ?>" <?= $option==$perPage?'selected':'' ?>><?= $option ?></option>
        <?php endforeach; ?>
    </select>
</form>

<!-- Pozadina tipke -->
<div style="text-align:center; margin-bottom:20px;">
    <span>Background:</span>
    <button class="bg-switch" data-color="#2c3e50">Blue</button>
    <button class="bg-switch" data-color="#121821">Dark</button>
    <button class="bg-switch" data-color="#ffffff">White</button>
</div>

<!-- Grid igara -->
<div class="games-container">
<?php
if(!empty($games)){
    foreach($games as $game){
        $imgSrc = !empty($game['image']) ? $game['image'] : "https://via.placeholder.com/220x220?text=" . urlencode($game['name']);
        $finalPrice = $game['price'];
        if($game['discount'] > 0){
            $finalPrice = $finalPrice * (1 - $game['discount']/100);
        }

        echo "<div class='category-game-card'>";
        echo "<a href='game.php?id={$game['id']}' style='text-decoration:none; color:inherit; display:flex; flex-direction:column;'>";
        echo "<img src='{$imgSrc}' alt='" . htmlspecialchars($game['name']) . "' loading='lazy'>";
        echo "<div class='card-bottom'>";
        echo "<h3>" . htmlspecialchars($game['name']) . "</h3>";
        echo "<div class='price-cart'>";
        if($game['discount'] > 0){
            echo "<span class='price'><del>{$game['price']} €</del> <strong>" . number_format($finalPrice,2) . " €</strong></span>";
        } else {
            echo "<span class='price'>{$game['price']} €</span>";
        }
        echo "<button class='add-cart' title='Add to Cart'>🛒</button>";
        echo "</div>"; // price-cart
        echo "</div>"; // card-bottom
        echo "</a>";
        echo "</div>"; // category-game-card
    }
} else {
    echo "<p style='text-align:center;'>No games found!</p>";
}
?>
</div>

<!-- Pagination -->
<div class="pagination" style="text-align:center; margin:20px;">
<?php if($totalPages > 1): 
    $startPage = max(1, $page - 4);
    $endPage = min($totalPages, $startPage + 9); // max 10 stranica
    if($endPage - $startPage < 9) {
        $startPage = max(1, $endPage - 9);
    }
?>
    <?php if($page > 1): ?>
        <a href="?name=<?= urlencode($category) ?>&page=<?= $page-1 ?>&per_page=<?= $perPage ?>" style="margin-right:10px;">« Previous</a>
    <?php endif; ?>

    <?php for($p=$startPage; $p<=$endPage; $p++): ?>
        <?php if($p == $page): ?>
            <strong><?= $p ?></strong>
        <?php else: ?>
            <a href="?name=<?= urlencode($category) ?>&page=<?= $p ?>&per_page=<?= $perPage ?>"><?= $p ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if($page < $totalPages): ?>
        <a href="?name=<?= urlencode($category) ?>&page=<?= $page+1 ?>&per_page=<?= $perPage ?>" style="margin-left:10px;">Next »</a>
    <?php endif; ?>
<?php endif; ?>
</div>

<?php include "footer.php"; ?>

<script>
// Promjena boje pozadine
document.querySelectorAll('.bg-switch').forEach(btn=>{
    btn.addEventListener('click', ()=>{
        document.body.style.backgroundColor = btn.dataset.color;
        localStorage.setItem('bgColor', btn.dataset.color);
    });
});

// Pamćenje boje pozadine
window.addEventListener('load', ()=>{
    const saved = localStorage.getItem('bgColor');
    if(saved) document.body.style.backgroundColor = saved;
});
</script>
