<?php
// includes/header.php
$huidigePagina = basename($_SERVER['PHP_SELF'], '.php');
global $SITE_URL, $SITE_NAME;
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($paginaTitel ?? $SITE_NAME) ?></title>
    <link rel="stylesheet" href="<?= $SITE_URL ?>/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800&family=Barlow+Condensed:wght@700;800&display=swap" rel="stylesheet">
</head>
<body>

<!-- Topbalk -->
<section class="topbalk">
    <section class="container">
        <ul class="topbalk-info">
            <li> Gratis verzending vanaf €50</li>
            <li>30 dagen bedenktijd</li>
            <li>Voor 23:00 besteld, morgen in huis</li>
            <li>⭐ Klantbeoordeling 4.8/5</li>
        </ul>
        <nav class="topbalk-nav" aria-label="Account navigatie">
            <?php if (isIngelogd()): ?>
                <a href="<?= $SITE_URL ?>/profiel.php">Mijn account</a>
                <?php if (isAdmin()): ?>
                    <a href="<?= $SITE_URL ?>/admin/">Admin</a>
                <?php endif; ?>
                <a href="<?= $SITE_URL ?>/uitloggen.php">Uitloggen</a>
            <?php else: ?>
                <a href="<?= $SITE_URL ?>/inloggen.php">Inloggen</a>
                <a href="<?= $SITE_URL ?>/registreren.php">Registreren</a>
            <?php endif; ?>
        </nav>
    </section>
</section>

<!-- Hoofdheader -->
<header class="hoofdheader">
    <section class="header-inner container">
        <a href="<?= $SITE_URL ?>/" class="logo" aria-label="Techfix Solutions - Home">
           
            <span class="logo-tekst"><strong>Techfix</strong> Solutions</span>
        </a>

        <section class="zoekbalk">
            <form method="GET" action="<?= $SITE_URL ?>/producten.php" role="search">
                <input
                    type="search"
                    name="q"
                    placeholder="Zoek naar producten, merken of categorieën..."
                    value="<?= e($_GET['q'] ?? '') ?>"
                    aria-label="Zoeken"
                >
                <button type="submit" aria-label="Zoeken">🔍</button>
            </form>
        </section>

        <nav class="header-acties" aria-label="Winkel navigatie">
            <a href="<?= $SITE_URL ?>/<?= isIngelogd() ? 'profiel.php' : 'inloggen.php' ?>" class="header-actie">
                <span class="actie-icoon">👤</span>
                <span>Account</span>
            </a>
            <a href="<?= $SITE_URL ?>/verlanglijst.php" class="header-actie">
                <span class="actie-icoon">❤️</span>
                <span>Verlanglijst</span>
            </a>
            <a href="<?= $SITE_URL ?>/winkelwagen.php" class="header-actie">
                <span class="actie-icoon">🛒</span>
                <span>Winkelwagen</span>
                <?php if (winkelwagenAantal() > 0): ?>
                    <span class="winkelwagen-badge"><?= winkelwagenAantal() ?></span>
                <?php endif; ?>
            </a>
        </nav>
    </section>
</header>

<!-- Hoofdnavigatie -->
<nav class="hoofdnav" aria-label="Hoofdmenu">
    <ul>
        <li><a href="<?= $SITE_URL ?>/producten.php" class="<?= $huidigePagina === 'producten' ? 'actief' : '' ?>">☰ Categorieën</a></li>
        <li><a href="<?= $SITE_URL ?>/producten.php?cat=smartphones">Smartphones</a></li>
        <li><a href="<?= $SITE_URL ?>/producten.php?cat=laptops">Laptops</a></li>
        <li><a href="<?= $SITE_URL ?>/producten.php?cat=onderdelen">Onderdelen</a></li>
        <li><a href="<?= $SITE_URL ?>/producten.php?cat=accessoires">Accessoires</a></li>
        <li><a href="<?= $SITE_URL ?>/producten.php?cat=audio">Audio</a></li>
        <li><a href="<?= $SITE_URL ?>/producten.php?badge=sale" class="aanbieding">Aanbiedingen</a></li>
        <li><a href="#">Service &amp; Reparatie</a></li>
    </ul>
</nav>
