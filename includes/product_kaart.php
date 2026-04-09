<?php
// includes/product_kaart.php
// Verwacht: $p = productrij met cat_name
global $SITE_URL;

require_once __DIR__ . '/afbeelding_helper.php';

$gemiddeldeScore = 0;
$aantalReviews   = 0;

try {
    $db   = getDB();
    $stmt = $db->prepare("SELECT AVG(rating) AS gemiddelde, COUNT(*) AS aantal FROM reviews WHERE product_id = ?");
    $stmt->execute([$p['id']]);
    $reviewData      = $stmt->fetch();
    $gemiddeldeScore = round($reviewData['gemiddelde'] ?? 0, 1);
    $aantalReviews   = $reviewData['aantal'] ?? 0;
} catch (PDOException $e) {
    error_log('Reviews ophalen mislukt: ' . $e->getMessage());
}

$kortingPercentage = '';
if (!empty($p['old_price']) && $p['old_price'] > 0) {
    $pct               = round((1 - $p['price'] / $p['old_price']) * 100);
    $kortingPercentage = "-{$pct}%";
}

$badge            = $p['badge'] ?? '';
$isOpVerlanglijst = in_array($p['id'], $_SESSION['verlanglijst'] ?? []);
$hoofdFoto        = getHoofdAfbeelding($p['id']);
$fallback         = getCategorieEmoji($p['cat_name'] ?? '');
?>
<article class="product-kaart">

    <?php if ($badge): ?>
        <span class="product-badge <?= in_array($badge, ['NIEUW', 'BESTSELLER']) ? 'groen' : '' ?>">
            <?= e($badge) ?>
        </span>
    <?php elseif ($kortingPercentage): ?>
        <span class="product-badge"><?= e($kortingPercentage) ?></span>
    <?php endif; ?>

    <figure class="product-afbeelding">
        <?php if ($hoofdFoto): ?>
            <img src="<?= $SITE_URL . e($hoofdFoto) ?>" alt="<?= e($p['name']) ?>" loading="lazy">
        <?php else: ?>
            <span aria-hidden="true"><?= $fallback ?></span>
        <?php endif; ?>
        <figcaption class="sr-only"><?= e($p['name']) ?></figcaption>
    </figure>

    <section class="product-info">
        <p class="product-merk"><?= e($p['brand'] ?? $p['cat_name']) ?></p>
        <h3 class="product-naam"><?= e($p['name']) ?></h3>

        <?php if ($aantalReviews > 0): ?>
        <p class="product-sterren">
            <?= str_repeat('⭐', round($gemiddeldeScore)) ?>
            <span>(<?= $aantalReviews ?>)</span>
        </p>
        <?php endif; ?>

        <p class="product-prijs">
            <span class="prijs-huidig">€ <?= number_format($p['price'], 2, ',', '.') ?></span>
            <?php if (!empty($p['old_price'])): ?>
                <span class="prijs-oud">€ <?= number_format($p['old_price'], 2, ',', '.') ?></span>
                <span class="prijs-korting"><?= e($kortingPercentage) ?></span>
            <?php endif; ?>
        </p>

        <p class="product-acties">
            <a href="<?= $SITE_URL ?>/product.php?id=<?= $p['id'] ?>" class="knop knop-secundair" style="flex:1;">
                Bekijk product
            </a>
            <a href="<?= $SITE_URL ?>/verlanglijst.php?toggle=<?= $p['id'] ?>"
               class="knop knop-omlijnd knop-sm"
               title="<?= $isOpVerlanglijst ? 'Verwijder van verlanglijst' : 'Toevoegen aan verlanglijst' ?>"
               aria-label="Verlanglijst">
                <?= $isOpVerlanglijst ? '❤️' : '🤍' ?>
            </a>
        </p>
    </section>

</article>
