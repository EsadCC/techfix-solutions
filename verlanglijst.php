<?php
// verlanglijst.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

// Product toevoegen of verwijderen
if (isset($_GET['toggle'])) {
    $productId = intval($_GET['toggle']);

    if (!isset($_SESSION['verlanglijst'])) {
        $_SESSION['verlanglijst'] = [];
    }

    if (in_array($productId, $_SESSION['verlanglijst'])) {
        $_SESSION['verlanglijst'] = array_values(
            array_diff($_SESSION['verlanglijst'], [$productId])
        );
        setFlash('succes', 'Product verwijderd uit je verlanglijst.');
    } else {
        $_SESSION['verlanglijst'][] = $productId;
        setFlash('succes', 'Product toegevoegd aan je verlanglijst!');
    }

    $terug = $_SERVER['HTTP_REFERER'] ?? $SITE_URL . '/verlanglijst.php';
    redirect($terug);
}

$verlanglijstIds = $_SESSION['verlanglijst'] ?? [];
$producten       = [];

if (!empty($verlanglijstIds)) {
    try {
        $db           = getDB();
        $plaatshouders = implode(',', array_fill(0, count($verlanglijstIds), '?'));
        $stmt         = $db->prepare("SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id IN ($plaatshouders)");
        $stmt->execute($verlanglijstIds);
        $producten = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Verlanglijst ophalen mislukt: ' . $e->getMessage());
    }
}

$flash       = getFlash();
$paginaTitel = 'Verlanglijst - Techfix Solutions';

require_once __DIR__ . '/includes/header.php';
?>

<main>
<section class="container">

    <h1 style="font-family:'Barlow Condensed',sans-serif;font-size:36px;font-weight:800;margin-bottom:24px;">❤️ Verlanglijst</h1>

    <?php if ($flash): ?>
    <p class="melding melding-<?= $flash['type'] === 'succes' ? 'succes' : 'fout' ?>">
        <?= e($flash['bericht']) ?>
    </p>
    <?php endif; ?>

    <?php if (empty($producten)): ?>
    <article style="background:var(--wit);border-radius:var(--radius);padding:64px;text-align:center;box-shadow:var(--schaduw);">
        <p style="font-size:64px;margin-bottom:16px;">❤️</p>
        <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:28px;font-weight:800;margin-bottom:8px;">Je verlanglijst is leeg</h2>
        <p style="color:var(--grijs-500);margin-bottom:24px;">Klik op 🤍 bij een product om het toe te voegen.</p>
        <a href="<?= $SITE_URL ?>/producten.php" class="knop knop-primair">Bekijk alle producten →</a>
    </article>

    <?php else: ?>
    <section class="product-grid">
        <?php foreach ($producten as $p): ?>
        <article class="product-kaart">
            <figure class="product-afbeelding">
                <span aria-hidden="true">📱</span>
            </figure>
            <section class="product-info">
                <p class="product-merk"><?= e($p['brand'] ?? $p['cat_name']) ?></p>
                <h3 class="product-naam"><?= e($p['name']) ?></h3>
                <p class="product-prijs">
                    <span class="prijs-huidig">€ <?= number_format($p['price'], 2, ',', '.') ?></span>
                    <?php if (!empty($p['old_price'])): ?>
                    <span class="prijs-oud">€ <?= number_format($p['old_price'], 2, ',', '.') ?></span>
                    <?php endif; ?>
                </p>
                <p class="product-acties">
                    <a href="<?= $SITE_URL ?>/product.php?id=<?= $p['id'] ?>" class="knop knop-secundair" style="flex:1;">Bekijk product</a>
                    <a href="<?= $SITE_URL ?>/verlanglijst.php?toggle=<?= $p['id'] ?>" class="knop knop-gevaar knop-sm" aria-label="Verwijder uit verlanglijst">🗑️</a>
                </p>
            </section>
        </article>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
