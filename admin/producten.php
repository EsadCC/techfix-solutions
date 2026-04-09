<?php
// admin/producten.php
$paginaTitel = 'Producten beheren';
require_once __DIR__ . '/admin_header.php';
global $SITE_URL;

// Product verwijderen
if (isset($_GET['verwijder']) && is_numeric($_GET['verwijder'])) {
    $verwijderId = intval($_GET['verwijder']);
    try {
        $db = getDB();
        $db->prepare("DELETE FROM products WHERE id = ?")->execute([$verwijderId]);
        setFlash('succes', 'Het product is verwijderd.');
    } catch (PDOException $e) {
        error_log('Product verwijderen mislukt: ' . $e->getMessage());
        setFlash('fout', 'Er is iets misgegaan bij het verwijderen van het product. Probeer het opnieuw.');
    }
    redirect($SITE_URL . '/admin/producten.php');
}

$flash = getFlash();

try {
    $db       = getDB();
    $producten = $db->query(
        "SELECT p.*, c.name AS cat_naam
         FROM products p
         JOIN categories c ON p.category_id = c.id
         ORDER BY p.id DESC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Producten ophalen mislukt: ' . $e->getMessage());
    $producten = [];
}
?>

<header class="admin-kop">
    <h1>🛍️ Producten (<?= count($producten) ?>)</h1>
    <a href="product-toevoegen.php" class="knop knop-primair">➕ Product toevoegen</a>
</header>

<?php if ($flash): ?>
<p class="melding melding-<?= $flash['type'] === 'succes' ? 'succes' : 'fout' ?>"><?= e($flash['bericht']) ?></p>
<?php endif; ?>

<section class="data-tabel">
    <table>
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Naam</th>
                <th scope="col">Categorie</th>
                <th scope="col">Merk</th>
                <th scope="col">Prijs</th>
                <th scope="col">Voorraad</th>
                <th scope="col">Uitgelicht</th>
                <th scope="col">Acties</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($producten)): ?>
            <tr>
                <td colspan="8" style="text-align:center;padding:40px;color:var(--grijs-500);">
                    Nog geen producten. <a href="product-toevoegen.php">Voeg er een toe →</a>
                </td>
            </tr>
            <?php endif; ?>
            <?php foreach ($producten as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td>
                    <strong><?= e($p['name']) ?></strong>
                    <?php if (!empty($p['badge'])): ?>
                    <span class="status-badge status-nieuw" style="margin-left:6px;"><?= e($p['badge']) ?></span>
                    <?php endif; ?>
                </td>
                <td><?= e($p['cat_naam']) ?></td>
                <td><?= e($p['brand']) ?></td>
                <td>
                    <strong>€ <?= number_format($p['price'], 2, ',', '.') ?></strong>
                    <?php if (!empty($p['old_price'])): ?>
                    <br><small style="text-decoration:line-through;color:var(--grijs-500);">€ <?= number_format($p['old_price'], 2, ',', '.') ?></small>
                    <?php endif; ?>
                </td>
                <td>
                    <span class="status-badge <?= intval($p['stock']) > 0 ? 'status-geleverd' : 'status-geannuleerd' ?>">
                        <?= intval($p['stock']) ?>
                    </span>
                </td>
                <td><?= $p['is_featured'] ? '⭐ Ja' : '—' ?></td>
                <td>
                    <nav style="display:flex;gap:6px;">
                        <a href="product-bewerken.php?id=<?= $p['id'] ?>" class="knop knop-omlijnd knop-sm" aria-label="Bewerk <?= e($p['name']) ?>">✏️ Bewerken</a>
                        <a href="<?= $SITE_URL ?>/product.php?id=<?= $p['id'] ?>" class="knop knop-secundair knop-sm" target="_blank" aria-label="Bekijk <?= e($p['name']) ?> op de site">👁️</a>
                        <a href="producten.php?verwijder=<?= $p['id'] ?>" class="knop knop-gevaar knop-sm" onclick="return confirm('Weet je zeker dat je dit product wilt verwijderen? Dit kan niet ongedaan worden gemaakt.')" aria-label="Verwijder <?= e($p['name']) ?>">🗑️</a>
                    </nav>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
