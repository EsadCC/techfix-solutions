<?php
// admin/index.php
$paginaTitel = 'Dashboard';
require_once __DIR__ . '/admin_header.php';
global $SITE_URL;

try {
    $db = getDB();

    $totaalBestellingen = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
    $totaalProducten    = $db->query("SELECT COUNT(*) FROM products")->fetchColumn();
    $totaalGebruikers   = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
    $totaalOmzet        = $db->query("SELECT SUM(total) FROM orders WHERE status != 'Geannuleerd'")->fetchColumn();
    $nieuweBestellingen = $db->query("SELECT COUNT(*) FROM orders WHERE status = 'Nieuw'")->fetchColumn();

    $recenteBestellingen = $db->query(
        "SELECT o.*, COUNT(oi.id) AS aantal_items
         FROM orders o
         LEFT JOIN order_items oi ON o.id = oi.order_id
         GROUP BY o.id
         ORDER BY o.created_at DESC
         LIMIT 8"
    )->fetchAll();

} catch (PDOException $e) {
    error_log('Dashboard ophalen mislukt: ' . $e->getMessage());
    $totaalBestellingen  = 0;
    $totaalProducten     = 0;
    $totaalGebruikers    = 0;
    $totaalOmzet         = 0;
    $nieuweBestellingen  = 0;
    $recenteBestellingen = [];
}

$statusKleuren = [
    'Nieuw'       => 'status-nieuw',
    'Verwerking'  => 'status-verwerking',
    'Verzonden'   => 'status-verzonden',
    'Geleverd'    => 'status-geleverd',
    'Geannuleerd' => 'status-geannuleerd',
];
?>

<header class="admin-kop">
    <h1>📊 Dashboard</h1>
    <nav style="display:flex;gap:10px;">
        <a href="product-toevoegen.php" class="knop knop-primair">➕ Product toevoegen</a>
        <a href="bestellingen.php" class="knop knop-secundair">📦 Bestellingen</a>
    </nav>
</header>

<?php if ($nieuweBestellingen > 0): ?>
<p class="melding melding-info">
    ⚡ Er <?= $nieuweBestellingen === 1 ? 'is' : 'zijn' ?> <strong><?= $nieuweBestellingen ?> nieuwe bestelling<?= $nieuweBestellingen > 1 ? 'en' : '' ?></strong> die aandacht nodig <?= $nieuweBestellingen === 1 ? 'heeft' : 'hebben' ?>.
    <a href="bestellingen.php?status=Nieuw">Bekijk nu →</a>
</p>
<?php endif; ?>

<!-- Statistieken -->
<section class="statistieken-grid" aria-label="Overzicht statistieken">
    <article class="statistiek-kaart">
        <span class="statistiek-icoon">📦</span>
        <section>
            <p class="statistiek-waarde"><?= intval($totaalBestellingen) ?></p>
            <p class="statistiek-label">Totaal bestellingen</p>
        </section>
    </article>
    <article class="statistiek-kaart">
        <span class="statistiek-icoon" style="background:#fef3cd;">💶</span>
        <section>
            <p class="statistiek-waarde">€ <?= number_format($totaalOmzet ?? 0, 0, ',', '.') ?></p>
            <p class="statistiek-label">Totale omzet</p>
        </section>
    </article>
    <article class="statistiek-kaart">
        <span class="statistiek-icoon" style="background:#d4edda;">🛍️</span>
        <section>
            <p class="statistiek-waarde"><?= intval($totaalProducten) ?></p>
            <p class="statistiek-label">Producten</p>
        </section>
    </article>
    <article class="statistiek-kaart">
        <span class="statistiek-icoon" style="background:#f8d7da;">👥</span>
        <section>
            <p class="statistiek-waarde"><?= intval($totaalGebruikers) ?></p>
            <p class="statistiek-label">Klanten</p>
        </section>
    </article>
</section>

<!-- Recente bestellingen -->
<section class="data-tabel">
    <header style="padding:16px 20px;border-bottom:1px solid var(--rand);display:flex;justify-content:space-between;align-items:center;">
        <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:20px;font-weight:800;">Recente bestellingen</h2>
        <a href="bestellingen.php" class="knop knop-omlijnd knop-sm">Alle bestellingen →</a>
    </header>
    <table>
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Klant</th>
                <th scope="col">E-mail</th>
                <th scope="col">Artikelen</th>
                <th scope="col">Totaal</th>
                <th scope="col">Status</th>
                <th scope="col">Datum</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($recenteBestellingen)): ?>
            <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:var(--grijs-500);">
                    Nog geen bestellingen.
                </td>
            </tr>
            <?php endif; ?>
            <?php foreach ($recenteBestellingen as $b): ?>
            <tr>
                <td><strong>#<?= $b['id'] ?></strong></td>
                <td><?= e($b['first_name'] . ' ' . $b['last_name']) ?></td>
                <td style="font-size:13px;"><?= e($b['email']) ?></td>
                <td><?= intval($b['aantal_items']) ?></td>
                <td><strong>€ <?= number_format($b['total'], 2, ',', '.') ?></strong></td>
                <td><span class="status-badge <?= $statusKleuren[$b['status']] ?? '' ?>"><?= e($b['status']) ?></span></td>
                <td style="font-size:13px;"><?= date('d-m-Y H:i', strtotime($b['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
