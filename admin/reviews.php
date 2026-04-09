<?php
// admin/reviews.php
$paginaTitel = 'Reviews beheren';
require_once __DIR__ . '/admin_header.php';
global $SITE_URL;

if (isset($_GET['verwijder']) && is_numeric($_GET['verwijder'])) {
    $reviewId = intval($_GET['verwijder']);
    try {
        $db = getDB();
        $db->prepare("DELETE FROM reviews WHERE id = ?")->execute([$reviewId]);
        setFlash('succes', 'De review is verwijderd.');
    } catch (PDOException $e) {
        error_log('Review verwijderen mislukt: ' . $e->getMessage());
        setFlash('fout', 'Er is iets misgegaan bij het verwijderen van de review. Probeer het opnieuw.');
    }
    redirect($SITE_URL . '/admin/reviews.php');
}

$flash = getFlash();

try {
    $db      = getDB();
    $reviews = $db->query(
        "SELECT r.*, u.name AS gebruiker_naam, p.name AS product_naam, p.id AS product_id
         FROM reviews r
         JOIN users u ON r.user_id = u.id
         JOIN products p ON r.product_id = p.id
         ORDER BY r.created_at DESC"
    )->fetchAll();
} catch (PDOException $e) {
    error_log('Reviews ophalen mislukt: ' . $e->getMessage());
    $reviews = [];
}
?>

<header class="admin-kop">
    <h1>⭐ Reviews (<?= count($reviews) ?>)</h1>
</header>

<?php if ($flash): ?>
<p class="melding melding-<?= $flash['type'] === 'succes' ? 'succes' : 'fout' ?>"><?= e($flash['bericht']) ?></p>
<?php endif; ?>

<section class="data-tabel">
    <table>
        <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">Product</th>
                <th scope="col">Klant</th>
                <th scope="col">Beoordeling</th>
                <th scope="col">Titel</th>
                <th scope="col">Datum</th>
                <th scope="col">Actie</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($reviews)): ?>
            <tr>
                <td colspan="7" style="text-align:center;padding:40px;color:var(--grijs-500);">
                    Nog geen reviews gevonden.
                </td>
            </tr>
            <?php endif; ?>
            <?php foreach ($reviews as $r): ?>
            <tr>
                <td><?= $r['id'] ?></td>
                <td>
                    <a href="<?= $SITE_URL ?>/product.php?id=<?= $r['product_id'] ?>" target="_blank">
                        <?= e(mb_substr($r['product_naam'], 0, 35)) ?>…
                    </a>
                </td>
                <td><?= e($r['gebruiker_naam']) ?></td>
                <td style="color:var(--geel);font-size:16px;" aria-label="<?= $r['rating'] ?> sterren">
                    <?= str_repeat('★', $r['rating']) ?><?= str_repeat('☆', 5 - $r['rating']) ?>
                </td>
                <td><?= e($r['title']) ?></td>
                <td style="font-size:13px;"><?= date('d-m-Y', strtotime($r['created_at'])) ?></td>
                <td>
                    <a href="reviews.php?verwijder=<?= $r['id'] ?>"
                       class="knop knop-gevaar knop-sm"
                       onclick="return confirm('Weet je zeker dat je deze review wilt verwijderen?')"
                       aria-label="Verwijder review van <?= e($r['gebruiker_naam']) ?>">
                        🗑️ Verwijderen
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
