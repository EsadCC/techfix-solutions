<?php
// admin/bestellingen.php
$paginaTitel = 'Bestellingen beheren';
require_once __DIR__ . '/admin_header.php';
global $SITE_URL;

// Status bijwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status_bijwerken'])) {
    $bestellingId = intval($_POST['bestelling_id'] ?? 0);
    $nieuweStatus = $_POST['status'] ?? '';
    $toegestaneStatussen = ['Nieuw', 'Verwerking', 'Verzonden', 'Geleverd', 'Geannuleerd'];

    if ($bestellingId && in_array($nieuweStatus, $toegestaneStatussen)) {
        try {
            $db = getDB();
            $db->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$nieuweStatus, $bestellingId]);
            setFlash('succes', 'De status van bestelling #' . $bestellingId . ' is bijgewerkt naar ' . $nieuweStatus . '.');
        } catch (PDOException $e) {
            error_log('Status bijwerken mislukt: ' . $e->getMessage());
            setFlash('fout', 'Er is iets misgegaan bij het bijwerken van de status. Probeer het opnieuw.');
        }
    }
    redirect($SITE_URL . '/admin/bestellingen.php');
}

$filterStatus       = $_GET['status'] ?? '';
$toegestaneStatussen = ['Nieuw', 'Verwerking', 'Verzonden', 'Geleverd', 'Geannuleerd'];
$flash              = getFlash();

try {
    $db = getDB();

    if ($filterStatus && in_array($filterStatus, $toegestaneStatussen)) {
        $stmt = $db->prepare(
            "SELECT o.*, COUNT(oi.id) AS aantal_items
             FROM orders o
             LEFT JOIN order_items oi ON o.id = oi.order_id
             WHERE o.status = ?
             GROUP BY o.id
             ORDER BY o.created_at DESC"
        );
        $stmt->execute([$filterStatus]);
    } else {
        $stmt = $db->query(
            "SELECT o.*, COUNT(oi.id) AS aantal_items
             FROM orders o
             LEFT JOIN order_items oi ON o.id = oi.order_id
             GROUP BY o.id
             ORDER BY o.created_at DESC"
        );
    }

    $bestellingen = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log('Bestellingen ophalen mislukt: ' . $e->getMessage());
    $bestellingen = [];
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
    <h1>📦 Bestellingen</h1>
    <nav style="display:flex;gap:8px;" aria-label="Filter op status">
        <a href="bestellingen.php" class="knop knop-sm <?= !$filterStatus ? 'knop-primair' : 'knop-omlijnd' ?>">Alle</a>
        <?php foreach ($toegestaneStatussen as $s): ?>
        <a href="bestellingen.php?status=<?= urlencode($s) ?>" class="knop knop-sm <?= $filterStatus === $s ? 'knop-primair' : 'knop-omlijnd' ?>">
            <?= e($s) ?>
        </a>
        <?php endforeach; ?>
    </nav>
</header>

<?php if ($flash): ?>
<p class="melding melding-<?= $flash['type'] === 'succes' ? 'succes' : 'fout' ?>"><?= e($flash['bericht']) ?></p>
<?php endif; ?>

<section class="data-tabel">
    <table>
        <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Klant</th>
                <th scope="col">E-mail</th>
                <th scope="col">Stad</th>
                <th scope="col">Artikelen</th>
                <th scope="col">Totaal</th>
                <th scope="col">Status</th>
                <th scope="col">Datum</th>
                <th scope="col">Status wijzigen</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bestellingen)): ?>
            <tr>
                <td colspan="9" style="text-align:center;padding:40px;color:var(--grijs-500);">
                    Geen bestellingen gevonden.
                </td>
            </tr>
            <?php endif; ?>
            <?php foreach ($bestellingen as $b): ?>
            <tr>
                <td><strong>#<?= $b['id'] ?></strong></td>
                <td><?= e($b['first_name'] . ' ' . $b['last_name']) ?></td>
                <td style="font-size:13px;"><?= e($b['email']) ?></td>
                <td><?= e($b['city']) ?></td>
                <td><?= intval($b['aantal_items']) ?></td>
                <td><strong>€ <?= number_format($b['total'], 2, ',', '.') ?></strong></td>
                <td>
                    <span class="status-badge <?= $statusKleuren[$b['status']] ?? '' ?>">
                        <?= e($b['status']) ?>
                    </span>
                </td>
                <td style="font-size:13px;"><?= date('d-m-Y H:i', strtotime($b['created_at'])) ?></td>
                <td>
                    <form method="POST" style="display:flex;gap:6px;align-items:center;">
                        <input type="hidden" name="bestelling_id" value="<?= $b['id'] ?>">
                        <label for="status-<?= $b['id'] ?>" class="sr-only">Nieuwe status voor bestelling #<?= $b['id'] ?></label>
                        <select id="status-<?= $b['id'] ?>" name="status" style="padding:4px 8px;border:1px solid var(--rand);border-radius:4px;font-family:'Barlow',sans-serif;font-size:13px;margin-bottom:0;">
                            <?php foreach ($toegestaneStatussen as $s): ?>
                            <option value="<?= e($s) ?>" <?= $b['status'] === $s ? 'selected' : '' ?>>
                                <?= e($s) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" name="status_bijwerken" class="knop knop-primair knop-sm" aria-label="Status opslaan">
                            ✓
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
