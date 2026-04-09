<?php
// winkelwagen.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

// Product verwijderen
if (isset($_GET['verwijder'])) {
    $pid = intval($_GET['verwijder']);
    unset($_SESSION['winkelwagen'][$pid]);
    setFlash('succes', 'Product verwijderd uit winkelwagen.');
    redirect($SITE_URL . '/winkelwagen.php');
}

// Aantallen bijwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bijwerken'])) {
    foreach (($_POST['aantal'] ?? []) as $pid => $aantal) {
        $pid   = intval($pid);
        $aantal = intval($aantal);
        if ($aantal <= 0) {
            unset($_SESSION['winkelwagen'][$pid]);
        } elseif (isset($_SESSION['winkelwagen'][$pid])) {
            $_SESSION['winkelwagen'][$pid]['aantal'] = $aantal;
        }
    }
    setFlash('succes', 'Winkelwagen bijgewerkt.');
    redirect($SITE_URL . '/winkelwagen.php');
}

$winkelwagen = $_SESSION['winkelwagen'] ?? [];
$flash       = getFlash();
$paginaTitel = 'Winkelwagen - Techfix Solutions';

require_once __DIR__ . '/includes/header.php';
?>

<main>
<section class="container">
    <h1 style="font-family:'Barlow Condensed',sans-serif;font-size:36px;font-weight:800;margin-bottom:24px;">🛒 Winkelwagen</h1>

    <?php if ($flash): ?>
    <p class="melding melding-<?= $flash['type'] === 'succes' ? 'succes' : 'fout' ?>"><?= e($flash['bericht']) ?></p>
    <?php endif; ?>

    <?php if (empty($winkelwagen)): ?>
    <article style="background:var(--wit);border-radius:var(--radius);padding:60px;text-align:center;box-shadow:var(--schaduw);">
        <p style="font-size:64px;margin-bottom:16px;">🛒</p>
        <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:28px;font-weight:800;margin-bottom:8px;">Je winkelwagen is leeg</h2>
        <p style="color:var(--grijs-500);margin-bottom:24px;">Voeg producten toe om te beginnen!</p>
        <a href="<?= $SITE_URL ?>/producten.php" class="knop knop-primair">Shop nu →</a>
    </article>

    <?php else: ?>
    <section class="winkelwagen-layout">
        <section>
            <form method="POST">
                <table class="winkelwagen-tabel">
                    <thead>
                        <tr>
                            <th scope="col">Product</th>
                            <th scope="col">Prijs</th>
                            <th scope="col">Aantal</th>
                            <th scope="col">Subtotaal</th>
                            <th scope="col"><span class="sr-only">Verwijderen</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($winkelwagen as $pid => $item): ?>
                        <tr>
                            <td>
                                <section class="winkelwagen-product">
                                    <figure style="width:60px;height:60px;background:var(--grijs-100);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0;">
                                        <span aria-hidden="true">📱</span>
                                    </figure>
                                    <a href="<?= $SITE_URL ?>/product.php?id=<?= intval($pid) ?>" style="font-weight:600;color:var(--grijs-900);">
                                        <?= e($item['naam']) ?>
                                    </a>
                                </section>
                            </td>
                            <td>€ <?= number_format($item['prijs'], 2, ',', '.') ?></td>
                            <td>
                                <section class="aantal-control" style="display:inline-flex;">
                                    <button type="button" class="aantal-min" aria-label="Minder">−</button>
                                    <input type="number" name="aantal[<?= intval($pid) ?>]" value="<?= intval($item['aantal']) ?>" min="1" aria-label="Aantal">
                                    <button type="button" class="aantal-plus" aria-label="Meer">+</button>
                                </section>
                            </td>
                            <td style="font-weight:700;">€ <?= number_format($item['prijs'] * $item['aantal'], 2, ',', '.') ?></td>
                            <td>
                                <a href="<?= $SITE_URL ?>/winkelwagen.php?verwijder=<?= intval($pid) ?>" style="color:var(--rood);font-size:18px;" aria-label="Verwijder <?= e($item['naam']) ?>">🗑️</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p style="display:flex;justify-content:space-between;margin-top:16px;">
                    <a href="<?= $SITE_URL ?>/producten.php" class="knop knop-omlijnd">← Verder winkelen</a>
                    <button type="submit" name="bijwerken" class="knop knop-secundair">🔄 Winkelwagen bijwerken</button>
                </p>
            </form>
        </section>

        <!-- Samenvatting -->
        <aside class="bestelling-samenvatting">
            <h2>Jouw bestelling</h2>
            <?php foreach ($winkelwagen as $pid => $item): ?>
            <section class="samenvatting-product">
                <figure class="samenvatting-product-foto" aria-hidden="true">📱</figure>
                <section style="flex:1;">
                    <p style="font-size:13px;font-weight:600;"><?= e($item['naam']) ?></p>
                    <p style="font-size:12px;color:var(--grijs-500);">Aantal: <?= intval($item['aantal']) ?></p>
                </section>
                <strong>€ <?= number_format($item['prijs'] * $item['aantal'], 2, ',', '.') ?></strong>
            </section>
            <?php endforeach; ?>

            <?php
            $totaal    = winkelwagenTotaal();
            $verzending = $totaal >= 50 ? 0 : 4.99;
            ?>
            <section class="samenvatting-totalen">
                <p class="totaal-rij"><span>Subtotaal</span><span>€ <?= number_format($totaal, 2, ',', '.') ?></span></p>
                <p class="totaal-rij">
                    <span>Verzendkosten</span>
                    <span style="color:var(--groen);"><?= $verzending > 0 ? '€ ' . number_format($verzending, 2, ',', '.') : 'GRATIS' ?></span>
                </p>
                <p class="totaal-rij eindtotaal">
                    <span>Totaal <small style="font-weight:400;">(incl. BTW)</small></span>
                    <span>€ <?= number_format($totaal + $verzending, 2, ',', '.') ?></span>
                </p>
            </section>

            <a href="<?= $SITE_URL ?>/bestellen.php" class="knop knop-primair knop-vol" style="margin-top:16px;padding:14px;font-size:16px;">
                Verder naar bestellen →
            </a>

            <p class="betaalmethoden" style="justify-content:center;margin-top:12px;">
                <span class="betaalmethode">iDEAL</span>
                <span class="betaalmethode">PayPal</span>
                <span class="betaalmethode">Visa</span>
                <span class="betaalmethode">Klarna</span>
            </p>
        </aside>
    </section>
    <?php endif; ?>
</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
