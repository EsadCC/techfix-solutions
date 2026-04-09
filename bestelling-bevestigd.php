<?php
// bestelling-bevestigd.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

$flash       = getFlash();
$bestellingId = intval($_GET['id'] ?? 0);
$paginaTitel  = 'Bestelling bevestigd! - Techfix Solutions';

require_once __DIR__ . '/includes/header.php';
?>
<main>
<section class="container" style="max-width:600px;text-align:center;padding-top:40px;">
    <article style="background:var(--wit);border-radius:var(--radius-lg);padding:48px;box-shadow:var(--schaduw-lg);">
        <span style="font-size:72px;display:block;margin-bottom:16px;">✅</span>
        <h1 style="font-family:'Barlow Condensed',sans-serif;font-size:36px;font-weight:800;color:var(--groen);margin-bottom:12px;">Bestelling bevestigd!</h1>
        <?php if ($flash): ?>
        <p style="font-size:16px;color:var(--grijs-700);margin-bottom:24px;"><?= e($flash['bericht']) ?></p>
        <?php endif; ?>
        <p style="color:var(--grijs-500);margin-bottom:32px;">Bestelnummer: <strong>#<?= $bestellingId ?></strong></p>
        <p style="display:flex;gap:12px;justify-content:center;">
            <a href="<?= $SITE_URL ?>/producten.php" class="knop knop-primair">Verder winkelen →</a>
            <?php if (isIngelogd()): ?>
            <a href="<?= $SITE_URL ?>/profiel.php" class="knop knop-omlijnd">Mijn bestellingen</a>
            <?php endif; ?>
        </p>
    </article>
</section>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
