<?php
// admin/gebruikers.php
$paginaTitel = 'Gebruikers';
require_once __DIR__ . '/admin_header.php';
global $SITE_URL;

try {
    $db         = getDB();
    $gebruikers = $db->query(
        "SELECT u.*, COUNT(o.id) AS aantal_bestellingen
         FROM users u
         LEFT JOIN orders o ON u.id = o.user_id
         GROUP BY u.id
         ORDER BY u.created_at DESC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Gebruikers ophalen mislukt: ' . $e->getMessage());
    $gebruikers = [];
}
?>

<header class="admin-kop">
    <h1>👥 Gebruikers (<?= count($gebruikers) ?>)</h1>
</header>

<section class="data-tabel">
    <table>
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Naam</th>
                <th scope="col">E-mail</th>
                <th scope="col">Rol</th>
                <th scope="col">Bestellingen</th>
                <th scope="col">Geregistreerd</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($gebruikers)): ?>
            <tr>
                <td colspan="6" style="text-align:center;padding:40px;color:var(--grijs-500);">
                    Geen gebruikers gevonden.
                </td>
            </tr>
            <?php endif; ?>
            <?php foreach ($gebruikers as $g): ?>
            <tr>
                <td><?= $g['id'] ?></td>
                <td>
                    <section style="display:flex;align-items:center;gap:10px;">
                        <p style="width:36px;height:36px;background:var(--blauw-licht);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:var(--blauw-mid);flex-shrink:0;">
                            <?= strtoupper(substr($g['name'], 0, 2)) ?>
                        </p>
                        <strong><?= e($g['name']) ?></strong>
                    </section>
                </td>
                <td><?= e($g['email']) ?></td>
                <td>
                    <span class="status-badge <?= $g['role'] === 'admin' ? 'status-admin' : 'status-gebruiker' ?>">
                        <?= e($g['role']) ?>
                    </span>
                </td>
                <td><?= intval($g['aantal_bestellingen']) ?> bestelling(en)</td>
                <td style="font-size:13px;"><?= date('d-m-Y', strtotime($g['created_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
