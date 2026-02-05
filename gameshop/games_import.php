<?php
    include "db.php";

    // Random placeholder igre ako nema Steam API
    $placeholderGames = [
        "Random Game 1","Random Game 2","Random Game 3","Random Game 4","Random Game 5"
    ];

    // Steam appid lista (primjer)
    $steamApps = [
        730,    // CS:GO
        570,    // Dota 2
        440,    // Team Fortress 2
        578080, // PUBG
        271590  // GTA V
    ];

    // Funkcija za dodavanje kategorije ako ne postoji i vraća ID
    function getCategoryId($conn, $catName){
        $stmt = $conn->prepare("SELECT id FROM categories WHERE name=?");
        $stmt->bind_param("s",$catName);
        $stmt->execute();
        $res = $stmt->get_result();

        if($res->num_rows === 0){
            $stmt2 = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt2->bind_param("s",$catName);
            $stmt2->execute();
            return $stmt2->insert_id;
        } else {
            $row = $res->fetch_assoc();
            return $row['id'];
        }
    }

    // Steam igre

    foreach($steamApps as $appid){
        $detailsJson = @file_get_contents("https://store.steampowered.com/api/appdetails?appids=$appid&cc=us&l=en");
        if($detailsJson === false) continue;

        $detailsData = json_decode($detailsJson,true);
        if(!isset($detailsData[$appid]['success']) || !$detailsData[$appid]['success']) continue;

        $data = $detailsData[$appid]['data'];
        $name = $data['name'];
        $price = isset($data['price_overview']['final']) ? $data['price_overview']['final']/100 : 0;
        $image = $data['header_image'] ?? '';
        $description = $data['short_description'] ?? '';

        // Provjeri postoji li igra
        $stmt = $conn->prepare("SELECT id FROM games WHERE steam_appid=?");
        $stmt->bind_param("i",$appid);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 0){
            // Dodaj igru
            $stmt2 = $conn->prepare("INSERT INTO games (steam_appid,name,price,image,description) VALUES (?,?,?,?,?)");
            $stmt2->bind_param("isdss",$appid,$name,$price,$image,$description);
            $stmt2->execute();
            $gameId = $stmt2->insert_id;

            // Dodaj kategorije iz Steam genres i categories

            $genres = $data['genres'] ?? [];
            $steamCats = $data['categories'] ?? [];
            $allCats = [];

            foreach($genres as $g) $allCats[] = $g['description'];
            foreach($steamCats as $c) $allCats[] = $c['description'];

            foreach(array_unique($allCats) as $catName){
                $catId = getCategoryId($conn, $catName);
                $stmt3 = $conn->prepare("INSERT INTO game_categories (game_id, category_id) VALUES (?,?)");
                $stmt3->bind_param("ii",$gameId,$catId);
                $stmt3->execute();
            }

            echo "Steam igra dodana: $name - $price €<br>";
        }
    }

    $allCategories = ['Action','FPS','RPG','Indie','Multiplayer','Singleplayer','Adventure','Simulation'];

    foreach($placeholderGames as $name){
        $price = rand(0,60)+0.99;
        $discount = rand(0,50);
        $image = "https://via.placeholder.com/200x250?text=".urlencode($name);
        $description = "Description for $name";

        // Dodaj igru
        $stmt = $conn->prepare("INSERT INTO games (name, price, discount, image, description) VALUES (?,?,?,?,?)");
        $stmt->bind_param("sddss",$name,$price,$discount,$image,$description);
        $stmt->execute();
        $gameId = $stmt->insert_id;

        // Dodaj 1-3 random kategorije
        shuffle($allCategories);
        $assigned = array_slice($allCategories,0,rand(1,3));
        foreach($assigned as $catName){
            $catId = getCategoryId($conn, $catName);
            $stmt2 = $conn->prepare("INSERT INTO game_categories (game_id, category_id) VALUES (?,?)");
            $stmt2->bind_param("ii",$gameId,$catId);
            $stmt2->execute();
        }

        echo "Placeholder igra dodana: $name - $price € (popust: $discount%)<br>";
    }

    echo "<br>Import gotov!";

?>
