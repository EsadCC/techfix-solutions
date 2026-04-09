<?php
// reviews.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

$productId = intval($_GET['product'] ?? 0);
if (!$productId) redirect($SITE_URL . '/producten.php');

try {
    $db      = getDB();
    $pStmt   = $db->prepare("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $pStmt->execute([$productId]);
    $product = $pStmt->fetch();
} catch (PDOException $e) {
    error_log('Product ophalen mislukt: ' . $e->getMessage());
    $product = null;
}

if (!$product) redirect($SITE_URL . '/producten.php');

$sortering   = $_GET['sort'] ?? 'nieuwste';
switch ($sortering) {
    case 'hoogste': $sorteerSQL = 'r.rating DESC'; break;
    case 'laagste': $sorteerSQL = 'r.rating ASC';  break;
    default:        $sorteerSQL = 'r.created_at DESC';
}

$pagina      = max(1, intval($_GET['pagina'] ?? 1));
$perPagina   = 8;
$offset      = ($pagina - 1) * $perPagina;

try {
    $aantalStmt = $db->prepare("SELECT COUNT(*) FROM reviews WHERE product_id = ?");
    $aantalStmt->execute([$productId]);
    $totaalAantal  = $aantalStmt->fetchColumn();
    $totaalPaginas = ceil($totaalAantal / $perPagina);

    $reviewStmt = $db->prepare("SELECT r.*, u.name AS gebruiker_naam FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY $sorteerSQL LIMIT $perPagina OFFSET $offset");
    $reviewStmt->execute([$productId]);
    $reviews = $reviewStmt->fetchAll();

    $gemStmt = $db->prepare("SELECT AVG(rating) AS gemiddelde FROM reviews WHERE product_id = ?");
    $gemStmt->execute([$productId]);
    $gemiddeldeScore = round($gemStmt->fetchColumn(), 1);

    $verdelingStmt = $db->prepare("SELECT rating, COUNT(*) AS aantal FROM reviews WHERE product_id = ? GROUP BY rating");
    $verdelingStmt->execute([$productId]);
    $verdeling = [];
    foreach ($verdelingStmt->fetchAll() as $rij) {
        $verdeling[$rij['rating']] = $rij['aantal'];
    }

} catch (PDOException $e) {
    error_log('Reviews ophalen mislukt: ' . $e->getMessage());
    $reviews         = [];
    $totaalAantal    = 0;
    $totaalPaginas   = 0;
    $gemiddeldeScore = 0;
    $verdeling       = [];
}

$flash       = getFlash();
$paginaTitel = 'Reviews voor ' . $product['name'] . ' - Techfix Solutions';

require_once __DIR__ . '/includes/header.php';
?>

<style>
.reviews-layout {
    display: grid;
    grid-template-columns: 260px 1fr;
    gap: 24px;
    align-items: start;
}
aside.reviews-zijbalk {
    background: var(--wit);
    border-radius: var(--radius);
    padding: 20px;
    box-shadow: var(--schaduw);
    position: sticky;
    top: 80px;
}
.ster-filter-rij {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    margin-bottom: 6px;
    text-decoration: none;
    color: var(--grijs-700);
}
.ster-filter-rij:hover { color: var(--blauw-mid); }
.ster-balk-achtergrond {
    flex: 1;
    background: var(--grijs-100);
    border-radius: 4px;
    height: 8px;
    overflow: hidden;
}
.ster-balk-vulling {
    height: 100%;
    background: var(--geel);
    border-radius: 4px;
}
section.review-samenvatting-uitgebreid {
    background: var(--wit);
    border-radius: var(--radius);
    padding: 24px;
    box-shadow: var(--schaduw);
    margin-bottom: 20px;
    display: flex;
    gap: 32px;
    align-items: center;
}
.groot-gemiddelde {
    text-align: center;
    min-width: 100px;
}
.groot-gemiddelde .score {
    font-family: 'Barlow Condensed', sans-serif;
    font-size: 72px;
    font-weight: 800;
    color: var(--geel);
    line-height: 1;
}
.groot-gemiddelde .sterren { font-size: 24px; margin: 4px 0; }
.verdeling-balk-rij {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 6px;
    font-size: 13px;
}
.verdeling-achtergrond {
    flex: 1;
    background: var(--grijs-100);
    border-radius: 4px;
    height: 10px;
    overflow: hidden;
}
.verdeling-vulling {
    height: 100%;
    background: var(--geel);
    border-radius: 4px;
}
article.review-kaart-uitgebreid {
    background: var(--wit);
    border-radius: var(--radius);
    padding: 20px;
    box-shadow: var(--schaduw);
    margin-bottom: 12px;
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 20px;
}
.reviewer-links-uitgebreid {
    border-right: 1px solid var(--rand);
    padding-right: 20px;
}
.reviewer-avatar-groot {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: var(--blauw-licht);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    font-weight: 800;
    color: var(--blauw-mid);
    margin-bottom: 8px;
}
.nuttig-knop {
    background: none;
    border: 1px solid var(--rand);
    border-radius: 4px;
    padding: 4px 10px;
    font-size: 12px;
    cursor: pointer;
    color: var(--blauw-mid);
    font-family: 'Barlow', sans-serif;
    transition: background 0.15s;
}
.nuttig-knop:hover { background: var(--blauw-licht); }
@media (max-width: 900px) {
    .reviews-layout { grid-template-columns: 1fr; }
    article.review-kaart-uitgebreid { grid-template-columns: 1fr; }
    .reviewer-links-uitgebreid { border-right: none; border-bottom: 1px solid var(--rand); padding-right: 0; padding-bottom: 12px; }
    section.review-samenvatting-uitgebreid { flex-direction: column; }
}
</style>

<main>
<section class="container">

    <nav class="broodkruimel" aria-label="Breadcrumb">
        <ol>
            <li><a href="<?= $SITE_URL ?>/">Home</a></li>
            <li><a href="<?= $SITE_URL ?>/producten.php?cat=<?= e($product['cat_slug']) ?>"><?= e($product['cat_name']) ?></a></li>
            <li><a href="<?= $SITE_URL ?>/product.php?id=<?= $productId ?>"><?= e($product['name']) ?></a></li>
            <li>Reviews</li>
        </ol>
    </nav>

    <?php if ($flash): ?>
    <p class="melding melding-<?= $flash['type'] === 'succes' ? 'succes' : 'fout' ?>"><?= e($flash['bericht']) ?></p>
    <?php endif; ?>

    <section class="reviews-layout">

        <!-- Zijbalk -->
        <aside class="reviews-zijbalk" aria-label="Review filters">

            <!-- Miniproductkaart -->
            <article style="border:1px solid var(--rand);border-radius:var(--radius);padding:16px;margin-bottom:16px;">
                <figure style="background:var(--grijs-100);border-radius:var(--radius);height:120px;display:flex;align-items:center;justify-content:center;font-size:56px;margin:0 0 12px;">
                    <span aria-hidden="true">📱</span>
                </figure>
                <a href="<?= $SITE_URL ?>/product.php?id=<?= $productId ?>" style="font-weight:700;font-size:13px;color:var(--grijs-900);">
                    <?= e($product['name']) ?>
                </a>
                <p style="color:var(--geel);font-size:16px;margin:4px 0;"><?= str_repeat('⭐', round($gemiddeldeScore)) ?></p>
                <strong style="font-family:'Barlow Condensed',sans-serif;font-size:24px;font-weight:800;color:var(--grijs-900);">
                    € <?= number_format($product['price'], 2, ',', '.') ?>
                </strong>
            </article>

            <!-- Ster filter -->
            <section>
                <h4 style="font-weight:700;font-size:14px;margin-bottom:12px;">Beoordeling</h4>
                <nav aria-label="Filter op aantal sterren">
                    <?php for ($s = 5; $s >= 1; $s--): ?>
                    <?php $aantal = $verdeling[$s] ?? 0; ?>
                    <?php $percentage = $totaalAantal > 0 ? ($aantal / $totaalAantal * 100) : 0; ?>
                    <a href="reviews.php?product=<?= $productId ?>&star=<?= $s ?>" class="ster-filter-rij">
                        <span style="color:var(--geel);font-size:14px;"><?= str_repeat('★', $s) ?></span>
                        <span class="ster-balk-achtergrond">
                            <span class="ster-balk-vulling" style="width:<?= round($percentage) ?>%"></span>
                        </span>
                        <span style="color:var(--grijs-500);width:24px;text-align:right;"><?= $aantal ?></span>
                    </a>
                    <?php endfor; ?>
                </nav>
            </section>

            <!-- Hulp -->
            <section style="background:var(--blauw-licht);border-radius:var(--radius);padding:16px;margin-top:16px;text-align:center;">
                <p style="font-size:28px;margin-bottom:6px;">💬</p>
                <strong style="font-size:14px;">Hulp nodig?</strong>
                <p style="font-size:13px;color:var(--grijs-700);margin:6px 0;">Onze productspecialisten staan voor je klaar!</p>
                <a href="#" class="knop knop-secundair knop-sm knop-vol">Start live chat</a>
            </section>
        </aside>

        <!-- Hoofdinhoud -->
        <section>
            <h1 style="font-family:'Barlow Condensed',sans-serif;font-size:32px;font-weight:800;margin-bottom:20px;">Product reviews</h1>

            <!-- Samenvatting -->
            <section class="review-samenvatting-uitgebreid">
                <section class="groot-gemiddelde">
                    <p class="score"><?= $gemiddeldeScore ?: '—' ?></p>
                    <p class="sterren"><?= $totaalAantal > 0 ? str_repeat('⭐', round($gemiddeldeScore)) : '' ?></p>
                    <p style="color:var(--grijs-500);font-size:13px;"><?= $totaalAantal ?> beoordelingen</p>
                </section>

                <section style="flex:1;">
                    <?php for ($s = 5; $s >= 1; $s--): ?>
                    <?php $aantal = $verdeling[$s] ?? 0; ?>
                    <?php $percentage = $totaalAantal > 0 ? ($aantal / $totaalAantal * 100) : 0; ?>
                    <p class="verdeling-balk-rij">
                        <span style="width:60px;color:var(--grijs-600);"><?= $s ?> sterren</span>
                        <span class="verdeling-achtergrond">
                            <span class="verdeling-vulling" style="width:<?= round($percentage) ?>%"></span>
                        </span>
                        <span style="color:var(--grijs-500);width:30px;text-align:right;"><?= $aantal ?></span>
                    </p>
                    <?php endfor; ?>
                </section>

                <section style="text-align:center;border-left:1px solid var(--rand);padding-left:24px;min-width:160px;">
                    <p style="font-size:14px;font-weight:600;margin-bottom:10px;">Tevreden over dit product?</p>
                    <?php if (isIngelogd()): ?>
                    <a href="<?= $SITE_URL ?>/product.php?id=<?= $productId ?>#review-schrijven" class="knop knop-secundair knop-sm">✍️ Schrijf een review</a>
                    <?php else: ?>
                    <a href="<?= $SITE_URL ?>/inloggen.php" class="knop knop-omlijnd knop-sm">Log in om te reviewen</a>
                    <?php endif; ?>
                </section>
            </section>

            <!-- Sorteer balk -->
            <section style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <p style="color:var(--grijs-500);font-size:14px;"><?= $totaalAantal ?> reviews</p>
                <label>
                    Sorteren op:
                    <select onchange="location='reviews.php?product=<?= $productId ?>&sort='+this.value" style="margin-bottom:0;">
                        <option value="nieuwste"  <?= $sortering === 'nieuwste'  ? 'selected' : '' ?>>Nieuwste</option>
                        <option value="hoogste"   <?= $sortering === 'hoogste'   ? 'selected' : '' ?>>Hoogste beoordeling</option>
                        <option value="laagste"   <?= $sortering === 'laagste'   ? 'selected' : '' ?>>Laagste beoordeling</option>
                    </select>
                </label>
            </section>

            <!-- Reviews -->
            <?php if (empty($reviews)): ?>
            <article style="background:var(--wit);border-radius:var(--radius);padding:48px;text-align:center;box-shadow:var(--schaduw);">
                <p style="font-size:48px;margin-bottom:12px;">⭐</p>
                <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:24px;font-weight:800;margin-bottom:8px;">Nog geen reviews</h2>
                <p style="color:var(--grijs-500);">Wees de eerste die dit product beoordeelt!</p>
                <?php if (isIngelogd()): ?>
                <a href="<?= $SITE_URL ?>/product.php?id=<?= $productId ?>#review-schrijven" class="knop knop-primair" style="margin-top:16px;">✍️ Review schrijven</a>
                <?php endif; ?>
            </article>
            <?php endif; ?>

            <?php foreach ($reviews as $review): ?>
            <article class="review-kaart-uitgebreid">
                <section class="reviewer-links-uitgebreid">
                    <p class="reviewer-avatar-groot"><?= strtoupper(substr($review['gebruiker_naam'], 0, 2)) ?></p>
                    <p style="font-weight:700;font-size:15px;"><?= e($review['gebruiker_naam']) ?></p>
                    <span class="reviewer-badge">✓ Aankoop geverifieerd</span>
                    <p style="font-size:12px;color:var(--grijs-500);margin-top:4px;"><?= date('d M Y', strtotime($review['created_at'])) ?></p>
                </section>
                <section>
                    <p class="review-sterren"><?= str_repeat('⭐', $review['rating']) ?></p>
                    <h3 class="review-titel"><?= e($review['title']) ?></h3>
                    <p class="review-tekst"><?= nl2br(e($review['body'])) ?></p>
                    <p style="margin-top:12px;display:flex;align-items:center;gap:10px;font-size:13px;color:var(--grijs-500);">
                        <span>Nuttig?</span>
                        <button class="nuttig-knop" type="button">👍 Ja</button>
                        <button class="nuttig-knop" type="button">👎 Nee</button>
                        <?php if (isIngelogd() && $_SESSION['gebruiker_id'] == $review['user_id']): ?>
                        <a href="<?= $SITE_URL ?>/review-bewerken.php?id=<?= $review['id'] ?>&product=<?= $productId ?>" class="nuttig-knop" style="text-decoration:none;">✏️ Aanpassen</a>
                        <a href="<?= $SITE_URL ?>/review-verwijderen.php?id=<?= $review['id'] ?>&product=<?= $productId ?>" class="nuttig-knop" style="text-decoration:none;color:var(--rood);" onclick="return confirm('Weet je zeker dat je deze review wilt verwijderen?')">🗑️ Verwijderen</a>
                        <?php endif; ?>
                    </p>
                </section>
            </article>
            <?php endforeach; ?>

            <!-- Paginering -->
            <?php if ($totaalPaginas > 1): ?>
            <nav class="paginering" aria-label="Reviews paginering">
                <?php if ($pagina > 1): ?>
                <a href="?product=<?= $productId ?>&sort=<?= $sortering ?>&pagina=<?= $pagina - 1 ?>" class="pagina-knop">← Vorige</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totaalPaginas; $i++): ?>
                <a href="?product=<?= $productId ?>&sort=<?= $sortering ?>&pagina=<?= $i ?>" class="pagina-knop <?= $i === $pagina ? 'actief' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($pagina < $totaalPaginas): ?>
                <a href="?product=<?= $productId ?>&sort=<?= $sortering ?>&pagina=<?= $pagina + 1 ?>" class="pagina-knop">Volgende →</a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>

        </section>
    </section>
</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
