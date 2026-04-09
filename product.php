<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/afbeelding_helper.php';
global $SITE_URL;

$productId = intval($_GET['id'] ?? 0);
if (!$productId) redirect($SITE_URL . '/producten.php');

try {
    $db   = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Product ophalen mislukt: ' . $e->getMessage());
    $product = null;
}

if (!$product) redirect($SITE_URL . '/producten.php');

// Toevoegen aan winkelwagen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toevoegen'])) {
    $aantal = max(1, intval($_POST['aantal'] ?? 1));

    if (!isset($_SESSION['winkelwagen'][$productId])) {
        $_SESSION['winkelwagen'][$productId] = [
            'product_id' => $productId,
            'naam'       => $product['name'],
            'prijs'      => $product['price'],
            'aantal'     => 0,
        ];
    }
    $_SESSION['winkelwagen'][$productId]['aantal'] += $aantal;
    setFlash('succes', 'Product toegevoegd aan winkelwagen!');
    redirect($SITE_URL . '/product.php?id=' . $productId);
}

// Reviews ophalen
try {
    $reviewStmt = $db->prepare("SELECT r.*, u.name AS gebruiker_naam FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.created_at DESC");
    $reviewStmt->execute([$productId]);
    $reviews = $reviewStmt->fetchAll();

    $avgStmt = $db->prepare("SELECT AVG(rating) AS gemiddelde FROM reviews WHERE product_id = ?");
    $avgStmt->execute([$productId]);
    $gemiddeldeScore = round($avgStmt->fetchColumn(), 1);
    $aantalReviews   = count($reviews);

    // Gerelateerde producten
    $gerelateerdStmt = $db->prepare("SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? LIMIT 4");
    $gerelateerdStmt->execute([$product['category_id'], $productId]);
    $gerelateerd = $gerelateerdStmt->fetchAll();

} catch (PDOException $e) {
    error_log('Reviews ophalen mislukt: ' . $e->getMessage());
    $reviews         = [];
    $gemiddeldeScore = 0;
    $aantalReviews   = 0;
    $gerelateerd     = [];
}

$kortingPercentage = '';
if (!empty($product['old_price']) && $product['old_price'] > 0) {
    $pct               = round((1 - $product['price'] / $product['old_price']) * 100);
    $kortingPercentage = "-{$pct}%";
}

// Productafbeeldingen ophalen (max 4)
$productFotos = getProductAfbeeldingen($productId);
$fallbackEmoji = getCategorieEmoji($product['cat_name'] ?? '');

$flash = getFlash();
$paginaTitel = e($product['name']) . ' - Techfix Solutions';

require_once __DIR__ . '/includes/header.php';
?>

<main>
<section class="container">

    <!-- Flash melding -->
    <?php if ($flash): ?>
    <p class="melding melding-<?= $flash['type'] === 'succes' ? 'succes' : 'fout' ?>">
        <?= $flash['type'] === 'succes' ? '✅' : '❌' ?> <?= e($flash['bericht']) ?>
    </p>
    <?php endif; ?>

    <!-- Broodkruimel -->
    <nav class="broodkruimel" aria-label="Breadcrumb">
        <ol>
            <li><a href="<?= $SITE_URL ?>/">Home</a></li>
            <li><a href="<?= $SITE_URL ?>/producten.php">Producten</a></li>
            <li><a href="<?= $SITE_URL ?>/producten.php?cat=<?= e($product['cat_slug']) ?>"><?= e($product['cat_name']) ?></a></li>
            <li><?= e($product['name']) ?></li>
        </ol>
    </nav>

    <!-- Productdetail -->
    <article class="product-detail-grid">

        <!-- Gallerij -->
        <section class="product-gallerij">
            <figure class="gallerij-hoofd" aria-label="Productafbeelding" id="gallerij-hoofd">
                <?php if (!empty($productFotos)): ?>
                    <img
                        src="<?= $SITE_URL ?>/uploads/producten/<?= e($productFotos[0]['bestandsnaam']) ?>"
                        alt="<?= e($product['name']) ?>"
                        id="hoofdfoto-img"
                    >
                <?php else: ?>
                    <span aria-hidden="true" id="hoofdfoto-fallback"><?= $fallbackEmoji ?></span>
                <?php endif; ?>
            </figure>

            <?php if (count($productFotos) > 1): ?>
            <section class="gallerij-miniaturen" role="list" aria-label="Productfoto's">
                <?php foreach ($productFotos as $index => $foto): ?>
                <button
                    class="gallerij-miniatuur <?= $index === 0 ? 'actief' : '' ?>"
                    role="listitem"
                    aria-label="Foto <?= $index + 1 ?>"
                    data-src="<?= $SITE_URL ?>/uploads/producten/<?= e($foto['bestandsnaam']) ?>"
                    onclick="wisselFoto(this)"
                >
                    <img
                        src="<?= $SITE_URL ?>/uploads/producten/<?= e($foto['bestandsnaam']) ?>"
                        alt="Foto <?= $index + 1 ?> van <?= e($product['name']) ?>"
                        loading="lazy"
                    >
                </button>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>

            <p style="font-size:12px;color:var(--grijs-500);text-align:center;">🔍 Klik om in te zoomen</p>
        </section>

        <!-- Productinfo -->
        <section class="product-info-kolom">
            <?php if (!empty($product['badge'])): ?>
            <p><span class="product-badge groen" style="position:static;display:inline-block;"><?= e($product['badge']) ?></span></p>
            <?php endif; ?>

            <p class="product-meta">
                Merk: <?= e($product['brand']) ?> | SKU: <?= e($product['sku']) ?>
            </p>

            <h1 class="product-titel"><?= e($product['name']) ?></h1>

            <?php if ($aantalReviews > 0): ?>
            <p class="product-sterren" style="font-size:16px;">
                <?= str_repeat('⭐', round($gemiddeldeScore)) ?>
                <a href="<?= $SITE_URL ?>/reviews.php?product=<?= $productId ?>" style="color:var(--grijs-500);font-size:14px;">
                    <?= $gemiddeldeScore ?>/5 (<?= $aantalReviews ?> reviews)
                </a>
            </p>
            <?php endif; ?>

            <p class="detail-prijs">
                <span class="prijs-huidig">€ <?= number_format($product['price'], 2, ',', '.') ?></span>
                <?php if (!empty($product['old_price'])): ?>
                    <span class="prijs-oud" style="font-size:20px;">€ <?= number_format($product['old_price'], 2, ',', '.') ?></span>
                    <span class="prijs-korting" style="font-size:16px;"><?= e($kortingPercentage) ?></span>
                <?php endif; ?>
            </p>

            <p class="voorraad-badge">✅ Op voorraad · Morgen in huis</p>

            <section>
                <strong style="font-size:14px;display:block;margin-bottom:8px;">Opslag:</strong>
                <p class="opslag-opties">
                    <button class="opslag-optie actief">128 GB</button>
                    <button class="opslag-optie">256 GB</button>
                    <button class="opslag-optie">512 GB</button>
                </p>
            </section>

            <form method="POST">
                <section class="aantal-rij">
                    <fieldset>
                        <legend style="font-size:14px;font-weight:600;border:none;padding:0;margin-bottom:6px;">Aantal</legend>
                        <p class="aantal-control">
                            <button type="button" class="aantal-min" aria-label="Minder">−</button>
                            <input type="number" name="aantal" value="1" min="1" max="<?= intval($product['stock']) ?>" aria-label="Aantal">
                            <button type="button" class="aantal-plus" aria-label="Meer">+</button>
                        </p>
                    </fieldset>
                </section>

                <p class="actie-rij">
                    <button type="submit" name="toevoegen" class="knop knop-primair" style="flex:2;padding:14px;">
                        🛒 In winkelwagen
                    </button>
                    <a href="<?= $SITE_URL ?>/verlanglijst.php?toggle=<?= $productId ?>" class="knop knop-omlijnd" style="flex:1;">
                        ❤️ Verlanglijst
                    </a>
                </p>
            </form>

            <section class="garanties">
                <p class="garantie-item">🚚 Gratis verzending vanaf €50</p>
                <p class="garantie-item">🔄 30 dagen bedenktijd</p>
                <p class="garantie-item">🛡️ 2 jaar garantie</p>
                <p class="garantie-item">💬 Deskundig advies</p>
            </section>

            <section style="background:var(--grijs-100);border-radius:var(--radius);padding:16px;">
                <p style="display:flex;gap:20px;font-size:13px;margin-bottom:10px;">
                    <span>🔒 Veilig betalen</span>
                    <span>⚡ Snelle levering</span>
                    <span>♻️ Duurzaam verpakt</span>
                </p>
                <p class="betaalmethoden">
                    <span class="betaalmethode">iDEAL</span>
                    <span class="betaalmethode">PayPal</span>
                    <span class="betaalmethode">Visa</span>
                    <span class="betaalmethode">Klarna</span>
                </p>
            </section>
        </section>

    </article>

    <!-- Tabs -->
    <section style="background:var(--wit);border-radius:var(--radius);padding:24px;box-shadow:var(--schaduw);margin-bottom:32px;">
        <nav class="tab-navigatie" role="tablist" aria-label="Productinformatie">
            <button class="tab-knop actief" role="tab" data-tab="tab-beschrijving" aria-selected="true">Beschrijving</button>
            <button class="tab-knop" role="tab" data-tab="tab-specs" aria-selected="false">Specificaties</button>
            <button class="tab-knop" role="tab" data-tab="tab-reviews" aria-selected="false">
                Reviews (<?= $aantalReviews ?>)
            </button>
            <button class="tab-knop" role="tab" data-tab="tab-verzending" aria-selected="false">Verzending &amp; Retourneren</button>
        </nav>

        <!-- Beschrijving -->
        <section id="tab-beschrijving" class="tab-inhoud actief" role="tabpanel">
            <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:22px;font-weight:800;margin-bottom:12px;">Productbeschrijving</h2>
            <p><?= nl2br(e($product['description'])) ?></p>
            <ul style="margin-top:12px;margin-left:20px;line-height:2;list-style:none;">
                <li>✅ 6.2" Dynamic AMOLED 2X scherm</li>
                <li>✅ 50 MP hoofdcamera + 12 MP ultragroothoek</li>
                <li>✅ Snapdragon 8 Gen 3 processor</li>
                <li>✅ 4000 mAh batterij met snelladen</li>
                <li>✅ Water- en stofbestendig (IP68)</li>
            </ul>
        </section>

        <!-- Specs -->
        <section id="tab-specs" class="tab-inhoud" role="tabpanel">
            <table style="width:100%;border-collapse:collapse;">
                <caption style="text-align:left;font-family:'Barlow Condensed',sans-serif;font-size:22px;font-weight:800;margin-bottom:12px;">Technische specificaties</caption>
                <tbody>
                    <tr style="border-bottom:1px solid var(--rand);">
                        <th style="padding:10px;font-weight:600;width:200px;text-align:left;">Merk</th>
                        <td style="padding:10px;"><?= e($product['brand']) ?></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--rand);">
                        <th style="padding:10px;font-weight:600;text-align:left;">SKU</th>
                        <td style="padding:10px;"><?= e($product['sku']) ?></td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--rand);">
                        <th style="padding:10px;font-weight:600;text-align:left;">Categorie</th>
                        <td style="padding:10px;"><?= e($product['cat_name']) ?></td>
                    </tr>
                    <tr>
                        <th style="padding:10px;font-weight:600;text-align:left;">Voorraad</th>
                        <td style="padding:10px;"><?= intval($product['stock']) ?> stuks</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- Reviews -->
        <section id="tab-reviews" class="tab-inhoud" role="tabpanel">
            <section class="review-samenvatting" id="reviews">
                <section class="review-score">
                    <p class="groot-getal"><?= $gemiddeldeScore ?: '—' ?></p>
                    <p class="sterren-groot"><?= $aantalReviews > 0 ? str_repeat('⭐', round($gemiddeldeScore)) : '' ?></p>
                    <p style="color:var(--grijs-500);font-size:13px;"><?= $aantalReviews ?> reviews</p>
                </section>

                <section style="flex:1;">
                    <?php for ($s = 5; $s >= 1; $s--): ?>
                    <?php $cnt = count(array_filter($reviews, function($r) use ($s) { return $r['rating'] === $s; })); ?>
                    <p style="display:flex;align-items:center;gap:8px;margin-bottom:4px;font-size:13px;">
                        <span style="width:20px;"><?= $s ?>⭐</span>
                        <span style="flex:1;background:var(--rand);border-radius:4px;height:8px;overflow:hidden;">
                            <span style="display:block;width:<?= $aantalReviews > 0 ? ($cnt / $aantalReviews * 100) : 0 ?>%;background:var(--geel);height:100%;"></span>
                        </span>
                        <span style="color:var(--grijs-500);width:20px;"><?= $cnt ?></span>
                    </p>
                    <?php endfor; ?>
                </section>

                <section style="text-align:center;">
                    <?php if (isIngelogd()): ?>
                    <a href="#review-schrijven" class="knop knop-secundair">✍️ Schrijf een review</a>
                    <br><br>
                    <a href="<?= $SITE_URL ?>/reviews.php?product=<?= $productId ?>" class="knop knop-omlijnd knop-sm">Bekijk alle reviews →</a>
                    <?php else: ?>
                    <a href="<?= $SITE_URL ?>/inloggen.php" class="knop knop-omlijnd">Log in om te reviewen</a>
                    <?php endif; ?>
                </section>
            </section>

            <!-- Reviewlijst -->
            <?php if (empty($reviews)): ?>
                <p style="color:var(--grijs-500);">Nog geen reviews voor dit product. Wees de eerste!</p>
            <?php endif; ?>

            <?php foreach ($reviews as $review): ?>
            <article class="review-kaart">
                <section class="reviewer-links">
                    <p class="reviewer-avatar"><?= strtoupper(substr($review['gebruiker_naam'], 0, 2)) ?></p>
                    <p class="reviewer-naam"><?= e($review['gebruiker_naam']) ?></p>
                    <span class="reviewer-badge">✓ Aankoop geverifieerd</span>
                    <p style="font-size:12px;color:var(--grijs-500);"><?= date('d M Y', strtotime($review['created_at'])) ?></p>
                </section>
                <section>
                    <p class="review-sterren"><?= str_repeat('⭐', $review['rating']) ?></p>
                    <h3 class="review-titel"><?= e($review['title']) ?></h3>
                    <p class="review-tekst"><?= nl2br(e($review['body'])) ?></p>
                    <?php if (isIngelogd() && $_SESSION['gebruiker_id'] == $review['user_id']): ?>
                    <p class="review-acties">
                        <a href="<?= $SITE_URL ?>/review-bewerken.php?id=<?= $review['id'] ?>&product=<?= $productId ?>" class="knop knop-omlijnd knop-sm">✏️ Aanpassen</a>
                        <a href="<?= $SITE_URL ?>/review-verwijderen.php?id=<?= $review['id'] ?>&product=<?= $productId ?>" class="knop knop-gevaar knop-sm" onclick="return confirm('Weet je zeker dat je deze review wilt verwijderen?')">🗑️ Verwijderen</a>
                    </p>
                    <?php endif; ?>
                </section>
            </article>
            <?php endforeach; ?>

            <!-- Review schrijven -->
            <?php if (isIngelogd()): ?>
            <section id="review-schrijven" style="background:var(--grijs-100);border-radius:var(--radius);padding:24px;margin-top:24px;">
                <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:22px;font-weight:800;margin-bottom:16px;">✍️ Review schrijven</h2>
                <form method="POST" action="<?= $SITE_URL ?>/review-opslaan.php">
                    <input type="hidden" name="product_id" value="<?= $productId ?>">
                    <label>Beoordeling</label>
                    <p class="ster-beoordeling" role="group" aria-label="Geef een beoordeling">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                        <button type="button" class="ster" data-waarde="<?= $s ?>" aria-label="<?= $s ?> ster<?= $s > 1 ? 'ren' : '' ?>">⭐</button>
                        <?php endfor; ?>
                    </p>
                    <input type="hidden" name="rating" id="rating_input" value="0" required>
                    <label for="review-titel">Titel</label>
                    <input type="text" id="review-titel" name="title" placeholder="Geef je review een titel" required>
                    <label for="review-tekst">Jouw review</label>
                    <textarea id="review-tekst" name="body" placeholder="Beschrijf je ervaring met dit product..." required></textarea>
                    <button type="submit" class="knop knop-primair">Review plaatsen →</button>
                </form>
            </section>
            <?php endif; ?>
        </section>

        <!-- Verzending -->
        <section id="tab-verzending" class="tab-inhoud" role="tabpanel">
            <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:22px;font-weight:800;margin-bottom:12px;">Verzending &amp; Retourneren</h2>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:8px;">
                <li>✅ <strong>Gratis verzending</strong> bij bestellingen vanaf €50</li>
                <li>⚡ Voor 23:00 besteld = <strong>morgen in huis</strong></li>
                <li>🔄 <strong>30 dagen bedenktijd</strong> — gratis retourneren</li>
                <li>📦 Veilig verpakt en verzekerd verzonden</li>
            </ul>
        </section>
    </section>

    <!-- Gerelateerde producten -->
    <?php if (!empty($gerelateerd)): ?>
    <section class="sectie">
        <header class="sectie-kop">
            <h2 class="sectie-titel">Gerelateerde producten</h2>
        </header>
        <section class="product-grid">
            <?php foreach ($gerelateerd as $p): ?>
                <?php include __DIR__ . '/includes/product_kaart.php'; ?>
            <?php endforeach; ?>
        </section>
    </section>
    <?php endif; ?>

</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
