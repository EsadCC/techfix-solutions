<?php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL, $SITE_NAME;

$paginaTitel = $SITE_NAME . ' - De specialist in tech';

try {
    $db = getDB();

    $categorieen     = $db->query("SELECT * FROM categories ORDER BY id")->fetchAll();
    $uitgelichtProduct = $db->query("SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 ORDER BY p.id LIMIT 4")->fetchAll();
    $aanbiedingen    = $db->query("SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.old_price IS NOT NULL ORDER BY (p.old_price - p.price) DESC LIMIT 4")->fetchAll();

} catch (PDOException $e) {
    error_log('Homepage fout: ' . $e->getMessage());
    $categorieen       = [];
    $uitgelichtProduct = [];
    $aanbiedingen      = [];
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero -->
<section class="hero">
    <section class="container hero-inner">
        <section class="hero-tekst">
            <span class="hero-label">Nieuw binnen</span>
            <h1>Nieuwe Tech<br><span>Deals</span></h1>
            <p>Tot 20% korting op smartphones,<br>laptops en accessoires</p>
            <p class="hero-knoppen">
                <a href="<?= $SITE_URL ?>/producten.php" class="knop knop-wit knop-lg">SHOP NU →</a>
                <a href="<?= $SITE_URL ?>/producten.php?badge=sale" class="knop knop-wit-omlijnd knop-lg">Bekijk aanbiedingen</a>
            </p>
        </section>
        <figure class="hero-afbeelding" aria-hidden="true">
        <img src="images/reclame.png" alt="Promotie van nieuwe tech deals" width="1000" height="1000">
            <span class="hero-korting-badge">-12%<br><small>€699</small></span>
        </figure>
    </section>
</section>

<!-- Vertrouwensbalk -->
<section class="vertrouwensbalk" aria-label="Voordelen">
    <ul>
        <li>
            <span class="vertrouwen-icoon">🚚</span>
            <p class="vertrouwen-tekst"><strong>Gratis verzending</strong><small>Vanaf €50</small></p>
        </li>
        <li>
            <span class="vertrouwen-icoon">🔄</span>
            <p class="vertrouwen-tekst"><strong>30 dagen bedenktijd</strong><small>Gratis retourneren</small></p>
        </li>
        <li>
            <span class="vertrouwen-icoon">🔒</span>
            <p class="vertrouwen-tekst"><strong>Veilig betalen</strong><small>iDEAL, PayPal, Klarna</small></p>
        </li>
        <li>
            <span class="vertrouwen-icoon">⭐</span>
            <p class="vertrouwen-tekst"><strong>Klantbeoordeling 4.8/5</strong><small>2.847 beoordelingen</small></p>
        </li>
    </ul>
</section>

<main>
<section class="container">

    <!-- Categorieën -->
    <section class="sectie">
        <header class="sectie-kop">
            <h2 class="sectie-titel">Categorieën</h2>
        </header>
        <nav class="categorie-grid" aria-label="Productcategorieën">
            <?php foreach ($categorieen as $cat): ?>
            <a href="<?= $SITE_URL ?>/producten.php?cat=<?= e($cat['slug']) ?>" class="categorie-kaart">
                <span class="categorie-icoon"><?= $cat['icon'] ?></span>
                <span class="categorie-naam"><?= e($cat['name']) ?></span>
            </a>
            <?php endforeach; ?>
        </nav>
    </section>

    <!-- Populaire producten -->
    <section class="sectie">
        <header class="sectie-kop">
            <h2 class="sectie-titel">Populaire Producten</h2>
            <a href="<?= $SITE_URL ?>/producten.php" class="sectie-link">Bekijk alle →</a>
        </header>
        <?php if (empty($uitgelichtProduct)): ?>
            <p>Momenteel geen producten beschikbaar.</p>
        <?php else: ?>
        <section class="product-grid">
            <?php foreach ($uitgelichtProduct as $p): ?>
                <?php include __DIR__ . '/includes/product_kaart.php'; ?>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>
    </section>

    <!-- Promo banners -->
    <section class="promo-banners" aria-label="Aanbiedingen">
        <article class="promo-banner promo-blauw">
            <section>
                <span class="promo-label">Aanbieding</span>
                <h2>Samsung Galaxy<br>Buds2 Pro</h2>
                <p>Nu <strong>€199,-</strong> <del>€229,-</del></p>
                <a href="<?= $SITE_URL ?>/producten.php?cat=accessoires" class="knop knop-wit knop-sm">Bekijk deal →</a>
            </section>
            <span class="promo-afbeelding" aria-hidden="true">🎧</span>
        </article>
        <article class="promo-banner promo-donker">
            <section>
                <span class="promo-label">Nieuw binnen</span>
                <h2>iPhone 15 Pro<br>Titanium</h2>
                <p>Vanaf <strong>€1.199,-</strong></p>
                <a href="<?= $SITE_URL ?>/producten.php?cat=smartphones" class="knop knop-wit knop-sm">Bekijk nu →</a>
            </section>
            <span class="promo-afbeelding" aria-hidden="true">📱</span>
        </article>
    </section>

    <!-- Aanbiedingen -->
    <section class="sectie">
        <header class="sectie-kop">
            <h2 class="sectie-titel">🔥 Aanbiedingen</h2>
            <a href="<?= $SITE_URL ?>/producten.php?badge=sale" class="sectie-link">Bekijk alle →</a>
        </header>
        <?php if (empty($aanbiedingen)): ?>
            <p>Momenteel geen aanbiedingen.</p>
        <?php else: ?>
        <section class="product-grid">
            <?php foreach ($aanbiedingen as $p): ?>
                <?php include __DIR__ . '/includes/product_kaart.php'; ?>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>
    </section>

    <!-- USP rij -->
    <section class="usp-rij" aria-label="Onze voordelen">
        <article class="usp-kaart">
            <span class="usp-icoon">🛡️</span>
            <h3>Veilig winkelen</h3>
            <p>SSL beveiligd en betrouwbaar</p>
        </article>
        <article class="usp-kaart">
            <span class="usp-icoon">🚚</span>
            <h3>Snelle levering</h3>
            <p>1-2 werkdagen in huis</p>
        </article>
        <article class="usp-kaart">
            <span class="usp-icoon">💬</span>
            <h3>Expert support</h3>
            <p>7 dagen per week bereikbaar</p>
        </article>
        <article class="usp-kaart">
            <span class="usp-icoon">♻️</span>
            <h3>Duurzame keuze</h3>
            <p>Gerecycled verpakt, klimaatbewust</p>
        </article>
    </section>

    <!-- Nieuwsbrief -->
    <section class="nieuwsbrief-banner">
        <section class="nieuwsbrief-links">
            <span class="nieuwsbrief-icoon" aria-hidden="true">✉️</span>
            <section>
                <h2>Blijf op de hoogte</h2>
                <p>Ontvang de beste deals en nieuwe producten in je inbox.</p>
            </section>
        </section>
        <section class="nieuwsbrief-rechts">
            <form class="nieuwsbrief-formulier" onsubmit="return false;">
                <label for="nieuwsbrief-email" class="sr-only">E-mailadres</label>
                <input type="email" id="nieuwsbrief-email" placeholder="Je e-mailadres">
                <button type="submit" class="knop knop-primair">Aanmelden →</button>
            </form>
            <small>✓ Meld je aan en ontvang 10% korting op je eerste bestelling!</small>
        </section>
    </section>

</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
