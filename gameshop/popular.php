<?php
    include "db.php";
    include "header.php";

    /* Koliko po stranici */
    $perPageOptions = [10, 25, 50, 100];

    $perPage = isset($_GET['per_page']) && in_array((int)$_GET['per_page'], $perPageOptions)
        ? (int)$_GET['per_page']
        : 20;

    /* Trenutna stranica */
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $perPage;


    /* Dohvati ID kategorije Popular */
    $catStmt = $conn->prepare("SELECT id FROM categories WHERE name='Popular'");
    $catStmt->execute();
    $catRes = $catStmt->get_result();

    if($catRes->num_rows == 0){
        echo "<p style='text-align:center;'>Category Popular not found.</p>";
        include "footer.php";
        exit;
    }

    $catId = $catRes->fetch_assoc()['id'];


    /* Ukupno igara */
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM game_categories
        WHERE category_id = ?
    ");
    $countStmt->bind_param("i",$catId);
    $countStmt->execute();
    $totalGames = $countStmt->get_result()->fetch_assoc()['total'];

    $totalPages = ceil($totalGames / $perPage);


    /* Dohvati igre */
    $stmt = $conn->prepare("
        SELECT g.*
        FROM games g
        JOIN game_categories gc ON g.id = gc.game_id
        WHERE gc.category_id = ?
        ORDER BY g.id DESC
        LIMIT ? OFFSET ?
    ");

    $stmt->bind_param("iii",$catId,$perPage,$offset);
    $stmt->execute();

    $games = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<h1 style="text-align:center;">Popular Games</h1>


<!-- Filter -->
<form method="get" style="text-align:center; margin:20px 0;">
    <label>Show:</label>

    <select name="per_page" onchange="this.form.submit()">
        <?php foreach($perPageOptions as $opt): ?>
            <option value="<?= $opt ?>" <?= $opt==$perPage?'selected':'' ?>>
                <?= $opt ?>
            </option>
        <?php endforeach; ?>
    </select>

    games per page
</form>


<!-- GRID -->
<div class="games-container" style="
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    gap:20px;
">


<?php if(!empty($games)): ?>

<?php foreach($games as $game): ?>

<?php
    $img = !empty($game['image'])
        ? $game['image']
        : "https://via.placeholder.com/200x250?text=".urlencode($game['name']);

    $finalPrice = $game['price'];

    if($game['discount'] > 0){
        $finalPrice = $finalPrice * (1 - $game['discount']/100);
    }
?>

<div class="game-card" style="
    width:200px;
    border:1px solid #ccc;
    padding:10px;
    text-align:center;
">

    <img src="<?= $img ?>"
         alt="<?= htmlspecialchars($game['name']) ?>"
         style="width:100%; height:250px; object-fit:cover;"
         loading="lazy">

    <h3><?= htmlspecialchars($game['name']) ?></h3>

    <p style="font-size:14px;">
        <?= htmlspecialchars($game['description']) ?>
    </p>


<?php if($game['discount'] > 0): ?>

    <p>
        <del><?= number_format($game['price'],2) ?> €</del>
        <strong><?= number_format($finalPrice,2) ?> €</strong>
        (<?= $game['discount'] ?>% off)
    </p>

<?php else: ?>

    <p><?= number_format($game['price'],2) ?> €</p>

<?php endif; ?>

</div>

<?php endforeach; ?>

<?php else: ?>

<p>No games found.</p>

<?php endif; ?>

</div>


<!-- PAGINATION -->
<div style="text-align:center; margin:30px 0;">

<?php if($totalPages > 1): ?>

<?php if($page > 1): ?>
    <a href="?page=<?= $page-1 ?>&per_page=<?= $perPage ?>">« Prev</a>
<?php endif; ?>


<?php for($p=1;$p<=$totalPages;$p++): ?>

<?php if($p==$page): ?>
    <strong style="margin:0 5px;"><?= $p ?></strong>
<?php else: ?>
    <a href="?page=<?= $p ?>&per_page=<?= $perPage ?>" style="margin:0 5px;">
        <?= $p ?>
    </a>
<?php endif; ?>

<?php endfor; ?>


<?php if($page < $totalPages): ?>
    <a href="?page=<?= $page+1 ?>&per_page=<?= $perPage ?>">Next »</a>
<?php endif; ?>

<?php endif; ?>

</div>


<?php include "footer.php"; ?>
