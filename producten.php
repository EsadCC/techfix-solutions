<?php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

$categorie  = $_GET['cat']   ?? '';
$zoekterm   = $_GET['q']     ?? '';
$badge      = $_GET['badge'] ?? '';
$pagina     = max(1, intval($_GET['pagina'] ?? 1));
$perPagina  = 12;
$offset     = ($pagina - 1) * $perPagina;

$voorwaarden = ['1=1'];
$parameters  = [];

if ($categorie) {
    $voorwaarden[] = 'c.slug = ?';
    $parameters[]  = $categorie;
}
if ($zoekterm) {
    $voorwaarden[] = '(p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)';
    $parameters[]  = "%$zoekterm%";
    $parameters[]  = "%$zoekterm%";
    $parameters[]  = "%$zoekterm%";
}
if ($badge === 'sale') {
    $voorwaarden[] = 'p.old_price IS NOT NULL';
}

$voorwaardenString = implode(' AND ', $voorwaarden);

try {
    $db = getDB();

    $aantalStmt = $db->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id = c.id WHERE $voorwaardenString");
    $aantalStmt->execute($parameters);
    $totaalAantal = $aantalStmt->fetchColumn();
    $totaalPaginas = ceil($totaalAantal / $perPagina);

    $productenStmt = $db->prepare("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug FROM products p JOIN categories c ON p.category_id = c.id WHERE $voorwaardenString ORDER BY p.is_featured DESC, p.id DESC LIMIT $perPagina OFFSET $offset");
    $productenStmt->execute($parameters);
    $producten = $productenStmt->fetchAll();

    $categorieenLijst = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();

} catch (PDOException $e) {
    error_log('Producten ophalen mislukt: ' . $e->getMessage());
    $producten        = [];
    $categorieenLijst = [];
    $totaalAantal     = 0;
    $totaalPaginas    = 0;
}

// Categorienaam ophalen voor paginatitel
$categorieNaam = '';
foreach ($categorieenLijst as $c) {
    if ($c['slug'] === $categorie) {
        $categorieNaam = $c['name'];
        break;
    }
}

$paginaTitel = ($categorieNaam ?: ($zoekterm ? "Zoekresultaten voor '$zoekterm'" : 'Productoverzicht')) . ' - Techfix Solutions';

require_once __DIR__ . '/includes/header.php';
?>

<main>
<section class="container">

    <nav class="broodkruimel" aria-label="Breadcrumb">
        <ol>
            <li><a href="<?= $SITE_URL ?>/">Home</a></li>
            <li><?= e($categorieNaam ?: ($zoekterm ? "Zoeken: $zoekterm" : 'Alle producten')) ?></li>
        </ol>
    </nav>

    <section class="producten-layout">

        <!-- Filters -->
        <aside class="filters-paneel" aria-label="Filters">
            <h2 style="font-family:'Barlow Condensed',sans-serif;font-size:20px;font-weight:800;margin-bottom:16px;">
                Filters
                <a href="producten.php" style="font-size:13px;font-weight:500;margin-left:8px;">Wis alles</a>
            </h2>

            <section class="filter-sectie">
                <h4>Categorieën</h4>
                <?php foreach ($categorieenLijst as $c): ?>
                <label class="filter-optie">
                    <input
                        type="checkbox"
                        <?= $categorie === $c['slug'] ? 'checked' : '' ?>
                        onchange="location='producten.php?cat=<?= e($c['slug']) ?>'"
                    >
                    <?= e($c['name']) ?>
                </label>
                <?php endforeach; ?>
            </section>

            <section class="filter-sectie">
                <h4>Beoordeling</h4>
                <?php for ($s = 5; $s >= 1; $s--): ?>
                <label class="filter-optie">
                    <input type="checkbox">
                    <?= str_repeat('⭐', $s) ?> &amp; hoger
                </label>
                <?php endfor; ?>
            </section>

            <section class="filter-sectie">
                <h4>Voorraad</h4>
                <label class="filter-optie">
                    <input type="checkbox" checked>
                    Op voorraad (<?= $totaalAantal ?>)
                </label>
            </section>

            <section style="background:var(--blauw-licht);border-radius:var(--radius);padding:16px;margin-top:16px;">
                <strong>Hulp nodig?</strong>
                <p style="font-size:13px;margin:8px 0;">Onze productspecialisten staan voor je klaar!</p>
                <a href="#" class="knop knop-secundair knop-sm knop-vol">Start live chat</a>
            </section>
        </aside>

        <!-- Productenlijst -->
        <section>
            <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <section>
                    <h1 style="font-family:'Barlow Condensed',sans-serif;font-size:32px;font-weight:800;">
                        <?= e($categorieNaam ?: ($zoekterm ? 'Zoekresultaten' : 'Productoverzicht')) ?>
                    </h1>
                    <p style="color:var(--grijs-500);font-size:14px;"><?= $totaalAantal ?> producten gevonden</p>
                </section>
                <label>
                    Sorteren op:
                    <select style="margin-bottom:0;">
                        <option>Populariteit</option>
                        <option>Prijs laag–hoog</option>
                        <option>Prijs hoog–laag</option>
                        <option>Nieuwste</option>
                    </select>
                </label>
            </header>

            <?php if (empty($producten)): ?>
                <p class="melding melding-info">
                    Geen producten gevonden. <a href="producten.php">Bekijk alle producten</a>
                </p>
            <?php else: ?>
            <section class="product-grid" style="grid-template-columns:repeat(3,1fr);">
                <?php foreach ($producten as $p): ?>
                    <?php include __DIR__ . '/includes/product_kaart.php'; ?>
                <?php endforeach; ?>
            </section>

            <!-- Paginering -->
            <?php if ($totaalPaginas > 1): ?>
            <nav class="paginering" aria-label="Paginering">
                <?php if ($pagina > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina - 1])) ?>" class="pagina-knop">← Vorige</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= min($totaalPaginas, 6); $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $i])) ?>" class="pagina-knop <?= $i === $pagina ? 'actief' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
                <?php if ($pagina < $totaalPaginas): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['pagina' => $pagina + 1])) ?>" class="pagina-knop">Volgende →</a>
                <?php endif; ?>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </section>

    </section>
</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
