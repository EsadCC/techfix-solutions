<?php
// admin/admin_header.php
require_once __DIR__ . '/../includes/config.php';
global $SITE_URL;

if (!isIngelogd() || !isAdmin()) {
    setFlash('fout', 'Je hebt geen toegang tot het beheerpaneel. Log in als administrator.');
    redirect($SITE_URL . '/inloggen.php');
}

$huidigePagina = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($paginaTitel ?? 'Admin') ?> - Techfix Beheer</title>
    <link rel="stylesheet" href="<?= $SITE_URL ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&family=Barlow+Condensed:wght@700;800&display=swap" rel="stylesheet">
</head>
<body>

<section class="admin-layout">

    <!-- Zijbalk -->
    <aside class="admin-zijbalk">
        <p class="zijbalk-merk">🔧 Techfix Beheer</p>
        <nav aria-label="Beheer navigatie">
            <ul>
                <li>
                    <a href="<?= $SITE_URL ?>/admin/" class="<?= $huidigePagina === 'index' ? 'actief' : '' ?>">
                        📊 Dashboard
                    </a>
                </li>
                <li>
                    <a href="<?= $SITE_URL ?>/admin/bestellingen.php" class="<?= $huidigePagina === 'bestellingen' ? 'actief' : '' ?>">
                        📦 Bestellingen
                    </a>
                </li>
                <li>
                    <a href="<?= $SITE_URL ?>/admin/producten.php" class="<?= $huidigePagina === 'producten' ? 'actief' : '' ?>">
                        🛍️ Producten
                    </a>
                </li>
                <li>
                    <a href="<?= $SITE_URL ?>/admin/product-toevoegen.php" class="<?= $huidigePagina === 'product-toevoegen' ? 'actief' : '' ?>">
                        ➕ Product toevoegen
                    </a>
                </li>
                <li>
                    <a href="<?= $SITE_URL ?>/admin/reviews.php" class="<?= $huidigePagina === 'reviews' ? 'actief' : '' ?>">
                        ⭐ Reviews
                    </a>
                </li>
                <li>
                    <a href="<?= $SITE_URL ?>/admin/gebruikers.php" class="<?= $huidigePagina === 'gebruikers' ? 'actief' : '' ?>">
                        👥 Gebruikers
                    </a>
                </li>
                <li class="scheiding"></li>
                <li>
                    <a href="<?= $SITE_URL ?>/">← Terug naar shop</a>
                </li>
                <li>
                    <a href="<?= $SITE_URL ?>/uitloggen.php" class="uitloggen">🚪 Uitloggen</a>
                </li>
            </ul>
        </nav>
    </aside>

    <!-- Hoofdinhoud -->
    <main class="admin-hoofd">
