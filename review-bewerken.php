<?php
// review-bewerken.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

if (!isIngelogd()) redirect($SITE_URL . '/inloggen.php');

$reviewId  = intval($_GET['id'] ?? 0);
$productId = intval($_GET['product'] ?? 0);
$fout      = '';
$review    = null;

// Ophalen van de review
try {
    $db   = getDB();
    $stmt = $db->prepare("SELECT r.*, p.name AS product_naam FROM reviews r JOIN products p ON r.product_id = p.id WHERE r.id = ? AND r.user_id = ?");
    $stmt->execute([$reviewId, $_SESSION['gebruiker_id']]);
    $review = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Review ophalen mislukt: ' . $e->getMessage());
}

if (!$review) redirect($SITE_URL . '/producten.php');

// Bijwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $beoordeling = intval($_POST['rating'] ?? 0);
    $titel       = trim($_POST['title'] ?? '');
    $tekst       = trim($_POST['body'] ?? '');

    if (!$beoordeling || $beoordeling < 1 || $beoordeling > 5 || !$titel || !$tekst) {
        $fout = 'Vul alle velden in.';
    } else {
        try {
            $db2 = getDB(); // getDB() geeft altijd dezelfde static connectie terug
            $upd = $db2->prepare("UPDATE reviews SET rating = ?, title = ?, body = ? WHERE id = ? AND user_id = ?");
            $upd->execute([$beoordeling, $titel, $tekst, $reviewId, $_SESSION['gebruiker_id']]);
            setFlash('succes', 'Je review is bijgewerkt!');
            redirect($SITE_URL . '/product.php?id=' . intval($review['product_id']) . '#reviews');
        } catch (PDOException $e) {
            error_log('Review bijwerken mislukt: ' . $e->getMessage());
            $fout = 'Er is iets misgegaan. Probeer het opnieuw.';
        }
    }
}

$paginaTitel = 'Review aanpassen - Techfix Solutions';
require_once __DIR__ . '/includes/header.php';
?>

<main>
<section class="container" style="max-width:640px;">
    <nav class="broodkruimel" aria-label="Breadcrumb">
        <ol>
            <li><a href="<?= $SITE_URL ?>/">Home</a></li>
            <li><a href="<?= $SITE_URL ?>/product.php?id=<?= intval($review['product_id']) ?>"><?= e($review['product_naam']) ?></a></li>
            <li>Review aanpassen</li>
        </ol>
    </nav>

    <article style="background:var(--wit);border-radius:var(--radius);padding:32px;box-shadow:var(--schaduw);">
        <h1 style="font-family:'Barlow Condensed',sans-serif;font-size:28px;font-weight:800;margin-bottom:6px;">✏️ Review aanpassen</h1>
        <p style="color:var(--grijs-500);margin-bottom:20px;">Jouw review voor: <strong><?= e($review['product_naam']) ?></strong></p>

        <?php if ($fout): ?>
        <p class="melding melding-fout">❌ <?= e($fout) ?></p>
        <?php endif; ?>

        <form method="POST">
            <label>Beoordeling</label>
            <p class="ster-beoordeling" role="group" aria-label="Beoordeling">
                <?php for ($s = 1; $s <= 5; $s++): ?>
                <button type="button" class="ster <?= $s <= intval($review['rating']) ? 'actief' : '' ?>" data-waarde="<?= $s ?>" aria-label="<?= $s ?> sterren">⭐</button>
                <?php endfor; ?>
            </p>
            <input type="hidden" name="rating" id="rating_input" value="<?= intval($review['rating']) ?>" required>

            <label for="review-titel">Titel</label>
            <input type="text" id="review-titel" name="title" value="<?= e($review['title']) ?>" required>

            <label for="review-tekst">Jouw review</label>
            <textarea id="review-tekst" name="body" required><?= e($review['body']) ?></textarea>

            <p style="display:flex;gap:10px;">
                <button type="submit" class="knop knop-primair">💾 Opslaan</button>
                <a href="<?= $SITE_URL ?>/product.php?id=<?= intval($review['product_id']) ?>" class="knop knop-omlijnd">Annuleren</a>
            </p>
        </form>
    </article>
</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
