<?php
if ($pdo) {
    // 1. Create table
    $sql = "CREATE TABLE IF NOT EXISTS projects (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        tech_stack VARCHAR(255) NOT NULL,
        image VARCHAR(255) NOT NULL,
        link VARCHAR(255) NOT NULL,
        file_name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    try {
        $pdo->exec($sql);
    } catch (PDOException $e) {
        // Silent fail or log
    }

    // 2. Check if data exists
    $stmt = $pdo->query("SELECT COUNT(*) FROM projects");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $projects_seed = [
            [
                'CUFFOUR Vol. 2',
                'Official site for CUFFOUR Vol. 2, the 2021 culture festival run by XBASS54, the foreign-language club at SMAN 54 Jakarta. I designed and built it end to end: competition pages, a fan art gallery and the guest star line-up, all in Indonesian.',
                'HTML, CSS, JS, Bootstrap 5',
                'img/projects/web/cuffour.jpeg',
                'https://cuffourvol2.github.io/cuffourvol2/index.html',
                'CUFFOUR_VOL2.EXE'
            ],
            [
                'Lynx Strezzo NFT',
                'Landing page for Lynx Strezzo, a 2022 NFT collection on Polygon about a gloomy lynx. I designed and built it end to end: the character story, four rarity tiers and a phased roadmap, with AOS scroll animations.',
                'HTML, CSS, JS, AOS',
                'img/projects/web/lynx.jpeg',
                'https://sendhykurniawan.github.io/Lynx-Strezzo-NFT-V2/',
                'LYNX_NFT.EXE'
            ],
            [
                'La Pizzeria',
                'Practice homepage for a pizza restaurant, with specials, best sellers and a menu. I built it with Bootstrap 5 to sharpen my layout and UX skills.',
                'HTML, CSS, JS, Bootstrap 5',
                'img/projects/web/pizzeria.jpeg',
                'https://sendhykurniawan.github.io/lapizzeria/',
                'PIZZERIA.EXE'
            ],
            [
                'Mr. S Furniture',
                'Concept site for a furniture store, built in 2023 by a team of four. I was one of two web developers; the site covers services, a room-by-room catalog, the team and contact details.',
                'HTML, CSS, JS, AOS',
                'img/projects/web/mrsfurniture.jpeg',
                'https://sendhykurniawan.github.io/mrsfurniture/',
                'MRS_FURNITURE.EXE'
            ],
            [
                'iMEDIA 2023',
                'Orientation site for new students at Polimedia, used during the 2023 intake. I designed and built it end to end: a map of the campus buildings, fun facts, student reviews and the event mascot, Googi, all in Indonesian.',
                'HTML, CSS, JS, AOS',
                'img/projects/web/imedia.jpeg',
                'https://sendhykurniawan.github.io/iMEDIA2023/',
                'IMEDIA_2023.EXE'
            ]
        ];

        $insertParams = [];
        $placeholders = [];
        foreach ($projects_seed as $proj) {
            $placeholders[] = "(?, ?, ?, ?, ?, ?)";
            $insertParams = array_merge($insertParams, $proj);
        }

        $sql = "INSERT INTO projects (title, description, tech_stack, image, link, file_name) VALUES " . implode(", ", $placeholders);
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute($insertParams);
        } catch (PDOException $e) {
            // Log error
        }
    }
}
?>
