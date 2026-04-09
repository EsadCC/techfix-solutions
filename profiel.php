<?php
// profiel.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

if (!isIngelogd()) redirect($SITE_URL . '/inloggen.php');

$gebruiker = getGebruiker();
$flash     = getFlash();

try {
    $db = getDB();

    $bestellingen = $db->prepare("SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS aantal_items FROM orders o WHERE o.user_id = ? ORDER BY o.created_at DESC");
    $bestellingen->execute([$gebruiker['id']]);
    $bestellingen = $bestellingen->fetchAll();

    $reviews = $db->prepare("SELECT r.*, p.name AS product_naam, p.id AS product_id FROM reviews r JOIN products p ON r.product_id = p.id WHERE r.user_id = ? ORDER BY r.created_at DESC");
    $reviews->execute([$gebruiker['id']]);
    $reviews = $reviews->fetchAll();

} catch (PDOException $e) {
    error_log('Profiel ophalen mislukt: ' . $e->getMessage());
    $bestellingen = [];
    $reviews      = [];
}

$statusKleuren = [
    'Nieuw'       => 'status-nieuw',
    'Verwerking'  => 'status-verwerking',
    'Verzonden'   => 'status-verzonden',
    'Geleverd'    => 'status-geleverd',
    'Geannuleerd' => 'status-geannuleerd',
];

$paginaTitel = 'Mijn account - Techfix Solutions';
require_once __DIR__ . '/includes/header.php';
?>

<main>
<section class="container">
    <h1 style="font-family:'Barlow Condensed',sans-serif;font-size:36px;font-weight:800;margin-bottom:24px;">👤 Mijn Account</h1>

    <?php if ($flash): ?>
    <p class="melding melding-<?= $flash['type'] === 'succes' ? 'succes' : 'fout' ?>"><?= e($flash['bericht']) ?></p>
    <?php endif; ?>

    <section style="display:grid;grid-template-columns:260px 1fr;gap:24px;align-items:start;">

        <!-- Zijbalk -->
        <aside style="background:var(--wit);border-radius:var(--radius);padding:24px;box-shadow:var(--schaduw);">
            <section style="text-align:center;padding-bottom:20px;border-bottom:1px solid var(--rand);margin-bottom:16px;">
                <p style="width:72px;height:72px;background:var(--blauw-licht);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;font-weight:800;color:var(--blauw-mid);margin:0 auto 10px;">
                    <?= strtoupper(substr($gebruiker['name'], 0, 2)) ?>
                </p>
                <strong style="display:block;font-size:16px;"><?= e($gebruiker['name']) ?></strong>
                <span style="color:var(--grijs-500);font-size:13px;"><?= e($gebruiker['email']) ?></span>
            </section>
            <nav aria-label="Account menu">
                <ul>
                    <li><a href="#bestellingen" style="display:block;padding:10px 12px;border-radius:6px;font-size:14px;font-weight:600;color:var(--grijs-900);">📦 Mijn bestellingen</a></li>
                    <li><a href="#reviews" style="display:block;padding:10px 12px;border-radius:6px;font-size:14px;font-weight:600;color:var(--grijs-900);">⭐ Mijn reviews</a></li>
                    <li><a href="<?= $SITE_URL ?>/verlanglijst.php" style="display:block;padding:10px 12px;border-radius:6px;font-size:14px;font-weight:600;color:var(--grijs-900);">❤️ Verlanglijst</a></li>
                    <?php if (isAdmin()): ?>
                    <li><a href="<?= $SITE_URL ?>/admin/" style="display:block;padding:10px 12px;border-radius:6px;font-size:14px;font-weight:600;color:var(--blauw-mid);">🔧 Admin paneel</a></li>
                    <?php endif; ?>
                    <li><a href="<?= $SITE_URL ?>/uitloggen.php" style="display:block;padding:10px 12px;border-radius:6px;font-size:14px;font-weight:600;color:var(--rood);">🚪 Uitloggen</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Hoofdinhoud -->
        <section>

            <!-- Bestellingen -->
            <section id="bestellingen" style="background:var(--wit);border-radius:var(--radius);padding:24px;box-shadow:var(--schaduw);margin-bottom:24px;">
                <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:24px;font-weight:800;margin-bottom:16px;">📦 Mijn bestellingen</h2>
                <?php if (empty($bestellingen)): ?>
                <p style="color:var(--grijs-500);text-align:center;padding:40px 0;">
                    Je hebt nog geen bestellingen geplaatst. <a href="<?= $SITE_URL ?>/producten.php">Shop nu →</a>
                </p>
                <?php else: ?>
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--rand);">
                            <th style="padding:8px 12px;text-align:left;font-size:12px;text-transform:uppercase;color:var(--grijs-500);" scope="col">Bestelling</th>
                            <th style="padding:8px 12px;text-align:left;font-size:12px;text-transform:uppercase;color:var(--grijs-500);" scope="col">Datum</th>
                            <th style="padding:8px 12px;text-align:left;font-size:12px;text-transform:uppercase;color:var(--grijs-500);" scope="col">Artikelen</th>
                            <th style="padding:8px 12px;text-align:left;font-size:12px;text-transform:uppercase;color:var(--grijs-500);" scope="col">Totaal</th>
                            <th style="padding:8px 12px;text-align:left;font-size:12px;text-transform:uppercase;color:var(--grijs-500);" scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bestellingen as $b): ?>
                        <tr style="border-bottom:1px solid var(--rand);">
                            <td style="padding:12px;font-weight:700;">#<?= $b['id'] ?></td>
                            <td style="padding:12px;font-size:14px;"><?= date('d-m-Y', strtotime($b['created_at'])) ?></td>
                            <td style="padding:12px;font-size:14px;"><?= $b['aantal_items'] ?> artikel(en)</td>
                            <td style="padding:12px;font-weight:700;">€ <?= number_format($b['total'], 2, ',', '.') ?></td>
                            <td style="padding:12px;">
                                <span class="status-badge <?= $statusKleuren[$b['status']] ?? '' ?>"><?= e($b['status']) ?></span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </section>

            <!-- Reviews -->
            <section id="reviews" style="background:var(--wit);border-radius:var(--radius);padding:24px;box-shadow:var(--schaduw);">
                <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:24px;font-weight:800;margin-bottom:16px;">⭐ Mijn reviews</h2>
                <?php if (empty($reviews)): ?>
                <p style="color:var(--grijs-500);">Je hebt nog geen reviews geplaatst.</p>
                <?php else: ?>
                <?php foreach ($reviews as $r): ?>
                <article style="border:1px solid var(--rand);border-radius:var(--radius);padding:16px;margin-bottom:12px;">
                    <header style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
                        <section>
                            <a href="<?= $SITE_URL ?>/product.php?id=<?= $r['product_id'] ?>" style="font-weight:700;color:var(--grijs-900);"><?= e($r['product_naam']) ?></a>
                            <p style="color:var(--geel);font-size:16px;"><?= str_repeat('⭐', $r['rating']) ?></p>
                            <strong><?= e($r['title']) ?></strong>
                            <p style="font-size:14px;color:var(--grijs-700);margin-top:4px;"><?= e(substr($r['body'], 0, 120)) ?>…</p>
                            <small style="color:var(--grijs-500);"><?= date('d-m-Y', strtotime($r['created_at'])) ?></small>
                        </section>
                        <p style="display:flex;gap:8px;flex-shrink:0;">
                            <a href="<?= $SITE_URL ?>/review-bewerken.php?id=<?= $r['id'] ?>&product=<?= $r['product_id'] ?>" class="knop knop-omlijnd knop-sm">✏️ Aanpassen</a>
                            <a href="<?= $SITE_URL ?>/review-verwijderen.php?id=<?= $r['id'] ?>&product=<?= $r['product_id'] ?>" class="knop knop-gevaar knop-sm" onclick="return confirm('Review verwijderen?')">🗑️</a>
                        </p>
                    </header>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>
            </section>

        </section>
    </section>
</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
