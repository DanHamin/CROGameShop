<?php
    include "db.php";

    // Definiraj osnovne kategorije
    $categories = ['Popular','Action','FPS','Indie','RPG','Other'];

    // Ubaci kategorije ako tablica prazna
    $result = $conn->query("SELECT COUNT(*) as total FROM categories");
    $row = $result->fetch_assoc();

    if($row['total'] == 0){
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        foreach($categories as $cat){
            $stmt->bind_param("s", $cat);
            $stmt->execute();
            echo "Dodana kategorija: $cat<br>";
        }
        $stmt->close();
    } else {
        echo "Tablica categories već ima podatke.<br>";
    }

    // Dohvati sve igre
    $gamesResult = $conn->query("SELECT id, name FROM games");
    $games = [];
    while($game = $gamesResult->fetch_assoc()){
        $games[] = $game;
    }

    // Dohvati sve kategorije s id-om
    $catResult = $conn->query("SELECT id, name FROM categories");
    $cats = [];
    while($cat = $catResult->fetch_assoc()){
        $cats[$cat['name']] = $cat['id'];
    }

    // Poveži igre s kategorijama 
    $stmtLink = $conn->prepare("INSERT INTO game_categories (game_id, category_id) VALUES (?, ?)");

    foreach($games as $game){
        $linked = [];

        // Ako naziv igre sadrži "Popular" → dodaj Popular
        if(stripos($game['name'],'Popular') !== false){
            $linked[] = $cats['Popular'];
        }

        // Dodaj random 1-3 kategorije
        $randomCats = $categories;
        shuffle($randomCats);
        $randCount = rand(1,3);
        for($i=0;$i<$randCount;$i++){
            $linked[] = $cats[$randomCats[$i]];
        }

        // Ubaci veze u game_categories
        foreach(array_unique($linked) as $cat_id){
            // provjeri da li veza već postoji
            $check = $conn->prepare("SELECT * FROM game_categories WHERE game_id=? AND category_id=?");
            $check->bind_param("ii", $game['id'], $cat_id);
            $check->execute();
            $res = $check->get_result();
            if($res->num_rows == 0){
                $stmtLink->bind_param("ii", $game['id'], $cat_id);
                $stmtLink->execute();
                echo "Igra '{$game['name']}' povezana s kategorijom ID $cat_id<br>";
            }
            $check->close();
        }
    }

    $stmtLink->close();
    echo "<br>Sve igre povezane s kategorijama!";
?>
