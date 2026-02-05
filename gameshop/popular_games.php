<?php
    include "db.php";

    // Lista 100 popularnih igara (primjer stvarnih naziva)
    $popularGames = [
        "Counter-Strike 2","Dota 2","Team Fortress 2","Left 4 Dead 2","PUBG: BATTLEGROUNDS",
        "Rust","Grand Theft Auto V","Unturned","Red Dead Redemption 2","The Witcher 3: Wild Hunt",
        "FIFA 23","Among Us","Cyberpunk 2077","Rainbow Six Siege","Minecraft",
        "ARK: Survival Evolved","Fall Guys","Terraria","GTA IV","Portal 2",
        "Half-Life 2","Hollow Knight","Stardew Valley","Dead by Daylight","No Man's Sky",
        "Sekiro: Shadows Die Twice","Dark Souls III","Dark Souls II","Dark Souls Remastered","Elden Ring",
        "God of War","Horizon Zero Dawn","Spider-Man","The Last of Us Part II","Ghost of Tsushima",
        "Resident Evil Village","Resident Evil 2","Resident Evil 3","Resident Evil 7","Final Fantasy VII Remake",
        "Final Fantasy XV","Assassin's Creed Valhalla","Assassin's Creed Odyssey","Far Cry 6","Far Cry 5",
        "Call of Duty: Modern Warfare","Call of Duty: Warzone","Call of Duty: Black Ops Cold War","Overwatch","Overwatch 2",
        "League of Legends","Valorant","Teamfight Tactics","Genshin Impact","Hades",
        "Celeste","Dead Cells","Slay the Spire","Cuphead","Ori and the Will of the Wisps",
        "Batman: Arkham Knight","Spider-Man Miles Morales","Spider-Man Remastered","Dragon Age Inquisition","Dragon Age II",
        "The Sims 4","The Sims 3","The Sims 2","SimCity","Cities: Skylines",
        "Monster Hunter: World","Monster Hunter Rise","Bloodborne","Dark Souls","Dying Light",
        "Metro Exodus","Metro Last Light","Metro 2033","Hitman 3","Hitman 2",
        "Control","Quantum Break","Alan Wake","Forza Horizon 5","Forza Horizon 4",
        "F1 2021","F1 2020","Project CARS 3","Need for Speed Heat","Need for Speed Payback",
        "Rocket League","Among Trees","Valheim","Phasmophobia","Raft",
        "Subnautica","Subnautica: Below Zero","Slime Rancher","Factorio","Satisfactory",
        "The Forest","Green Hell","Don't Starve","Don't Starve Together","Oxygen Not Included"
    ];

    // Ubaci igre u bazu
    foreach($popularGames as $gameName){
        $price = rand(0,60) + 0.99; // random cijena 0.99 - 60.99 €
        $discount = rand(0,50); // random popust 0-50%
        $image = "https://via.placeholder.com/200x250?text=" . urlencode($gameName);
        $description = "Description for $gameName.";
        $category = "Popular";
        $steam_appid = null; // placeholder

        $stmt = $conn->prepare("INSERT INTO games (name, price, discount, image, description, category, steam_appid) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sddssis", $gameName, $price, $discount, $image, $description, $category, $steam_appid);
        $stmt->execute();

        echo "Dodana igra: $gameName - $price € (popust: $discount%)<br>";
    }

    echo "<br>Import gotov!";

?>
