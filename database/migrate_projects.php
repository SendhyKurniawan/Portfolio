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
                'CUFFOUR VOL2',
                'CUFFOUR is a foreign language extracurricular program at SMAN 54 Jakarta. A dynamic platform showcasing diverse cultures including Japanese, Korean, and German.',
                'HTML, CSS, JS, Bootstrap 5',
                'https://placehold.co/600x300/1a1a2e/00f3ff?text=CUFFOUR+VOL2',
                'https://cuffourvol2.github.io/cuffourvol2/index.html',
                'CUFFOUR_VOL2.EXE'
            ],
            [
                'LYNX STREZZO NFT',
                'A digital oasis of boundless creativity. A veritable gallery of the future where infinite possibilities converge.',
                'HTML, CSS, JS, AOS',
                'https://placehold.co/600x300/1a1a2e/bd00ff?text=LYNX+NFT',
                'https://sendhykurniawan.github.io/Lynx-Strezzo-NFT-V2/',
                'LYNX_NFT.EXE'
            ],
            [
                'LA PIZZERIA',
                'An educational project replicating and reinventing a web presence, honing skills in web development and UX.',
                'HTML, CSS, JS, Bootstrap 5',
                'https://placehold.co/600x300/1a1a2e/00f3ff?text=LA+PIZZERIA',
                'https://sendhykurniawan.github.io/lapizzeria/',
                'PIZZERIA.EXE'
            ],
            [
                'MRS FURNITURE',
                'Crafting an innovative digital platform for a visionary business concept, redefining the furniture retail experience.',
                'HTML, CSS, JS, AOS',
                'https://placehold.co/600x300/1a1a2e/bd00ff?text=MRS+FURNITURE',
                'https://sendhykurniawan.github.io/mrsfurniture/',
                'MRS_FURNITURE.EXE'
            ],
            [
                'IMEDIA 2023',
                'A comprehensive orientation hub for incoming college students. Resources, guidance, and insights for a seamless transition.',
                'HTML, CSS, JS, AOS',
                'https://placehold.co/600x300/1a1a2e/00f3ff?text=IMEDIA+2023',
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
