<?php
// admin/product-bewerken.php
$paginaTitel = 'Product bewerken';
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../includes/afbeelding_helper.php';
global $SITE_URL;

$productId = intval($_GET['id'] ?? 0);
$fout      = '';

try {
    $db          = getDB();
    $stmt        = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product     = $stmt->fetch();
    $categorieen = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    error_log('Product ophalen mislukt: ' . $e->getMessage());
    $product     = null;
    $categorieen = [];
}

if (!$product) {
    setFlash('fout', 'Dit product bestaat niet.');
    redirect($SITE_URL . '/admin/producten.php');
}

// Bestaande foto's ophalen
$bestaandeFotos = getProductAfbeeldingen($productId);

// Foto verwijderen via GET
if (isset($_GET['verwijder_foto']) && is_numeric($_GET['verwijder_foto'])) {
    $fotoId = intval($_GET['verwijder_foto']);
    verwijderProductAfbeelding($fotoId);
    setFlash('succes', 'Foto verwijderd.');
    redirect($SITE_URL . '/admin/product-bewerken.php?id=' . $productId);
}

// Formulierwaarden: standaard uit DB, bij validatiefout uit POST
$formNaam         = $product['name'];
$formCategorieId  = $product['category_id'];
$formBeschrijving = $product['description'];
$formPrijs        = $product['price'];
$formOudePrijs    = $product['old_price'] ?? '';
$formVoorraad     = $product['stock'];
$formMerk         = $product['brand'];
$formSku          = $product['sku'];
$formUitgelicht   = $product['is_featured'];
$formBadge        = $product['badge'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formNaam         = trim($_POST['naam'] ?? '');
    $formCategorieId  = intval($_POST['categorie_id'] ?? 0);
    $formBeschrijving = trim($_POST['beschrijving'] ?? '');
    $formPrijs        = trim($_POST['prijs'] ?? '');
    $formOudePrijs    = trim($_POST['oude_prijs'] ?? '');
    $formVoorraad     = intval($_POST['voorraad'] ?? 0);
    $formMerk         = trim($_POST['merk'] ?? '');
    $formSku          = trim($_POST['sku'] ?? '');
    $formUitgelicht   = isset($_POST['uitgelicht']) ? 1 : 0;
    $formBadge        = trim($_POST['badge'] ?? '');

    $prijs     = floatval(str_replace(',', '.', $formPrijs));
    $oudePrijs = $formOudePrijs !== '' ? floatval(str_replace(',', '.', $formOudePrijs)) : null;

    if (!$formNaam || !$formCategorieId || $prijs <= 0) {
        $fout = 'Vul minimaal de naam, categorie en prijs in.';
    } else {
        try {
            $upd = $db->prepare(
                "UPDATE products
                 SET category_id = ?, name = ?, description = ?, price = ?, old_price = ?,
                     stock = ?, brand = ?, sku = ?, is_featured = ?, badge = ?
                 WHERE id = ?"
            );
            $upd->execute([
                $formCategorieId, $formNaam, $formBeschrijving, $prijs, $oudePrijs,
                $formVoorraad, $formMerk, $formSku, $formUitgelicht, $formBadge ?: null,
                $productId
            ]);

            // Nieuwe foto's toevoegen — alleen voor lege volgorde-slots
            $bezetteVolgordes = array_column($bestaandeFotos, 'volgorde');
            $fotoFouten       = [];

            for ($i = 1; $i <= 4; $i++) {
                $veldNaam = 'foto_' . $i;
                if (empty($_FILES[$veldNaam]['tmp_name']) || $_FILES[$veldNaam]['error'] !== UPLOAD_ERR_OK) {
                    continue;
                }

                // Als er al een foto op deze positie staat, eerst de oude verwijderen
                foreach ($bestaandeFotos as $oudeF) {
                    if ($oudeF['volgorde'] == $i) {
                        verwijderProductAfbeelding($oudeF['id']);
                        break;
                    }
                }

                $bestandsnaam = uploadProductAfbeelding($_FILES[$veldNaam], $productId, $i);
                if ($bestandsnaam) {
                    $fotoStmt = $db->prepare(
                        "INSERT INTO product_images (product_id, bestandsnaam, volgorde) VALUES (?, ?, ?)"
                    );
                    $fotoStmt->execute([$productId, $bestandsnaam, $i]);
                } else {
                    $fotoFouten[] = "Foto $i kon niet worden geüpload (alleen JPG, PNG, WEBP, GIF tot 5MB).";
                }
            }

            if (!empty($fotoFouten)) {
                setFlash('succes', "Product bijgewerkt, maar: " . implode(' ', $fotoFouten));
            } else {
                setFlash('succes', "Het product '$formNaam' is bijgewerkt.");
            }
            redirect($SITE_URL . '/admin/producten.php');

        } catch (PDOException $e) {
            error_log('Product bijwerken mislukt: ' . $e->getMessage());
            $fout = 'Er is iets misgegaan bij het opslaan van de wijzigingen. Probeer het opnieuw.';
        }
    }

    // Fotos opnieuw ophalen voor weergave na validatiefout
    $bestaandeFotos = getProductAfbeeldingen($productId);
}
?>

<header class="admin-kop">
    <h1>✏️ Product bewerken</h1>
    <nav style="display:flex;gap:10px;">
        <a href="producten.php" class="knop knop-omlijnd">← Terug</a>
        <a href="<?= $SITE_URL ?>/product.php?id=<?= $productId ?>" class="knop knop-secundair" target="_blank">👁️ Bekijk op site</a>
    </nav>
</header>

<?php if ($fout): ?>
<p class="melding melding-fout">❌ <?= e($fout) ?></p>
<?php endif; ?>

<article style="background:var(--wit);border-radius:var(--radius);padding:32px;box-shadow:var(--schaduw);max-width:860px;">
    <form method="POST" enctype="multipart/form-data" novalidate>
        <section class="formulier-rij">
            <section style="grid-column:1/-1;">
                <label for="naam">Productnaam *</label>
                <input type="text" id="naam" name="naam" required value="<?= e($formNaam) ?>">
            </section>

            <section>
                <label for="categorie_id">Categorie *</label>
                <select id="categorie_id" name="categorie_id" required>
                    <?php foreach ($categorieen as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $formCategorieId == $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <section>
                <label for="merk">Merk</label>
                <input type="text" id="merk" name="merk" value="<?= e($formMerk) ?>">
            </section>

            <section>
                <label for="prijs">Verkoopprijs (€) *</label>
                <input type="text" id="prijs" name="prijs" required value="<?= e($formPrijs) ?>">
            </section>

            <section>
                <label for="oude_prijs">Oude prijs (€)</label>
                <input type="text" id="oude_prijs" name="oude_prijs" value="<?= e($formOudePrijs) ?>">
            </section>

            <section>
                <label for="sku">Artikelnummer (SKU)</label>
                <input type="text" id="sku" name="sku" value="<?= e($formSku) ?>">
            </section>

            <section>
                <label for="voorraad">Voorraad</label>
                <input type="number" id="voorraad" name="voorraad" min="0" value="<?= intval($formVoorraad) ?>">
            </section>

            <section>
                <label for="badge">Badge</label>
                <input type="text" id="badge" name="badge" value="<?= e($formBadge) ?>">
            </section>

            <section style="grid-column:1/-1;">
                <label for="beschrijving">Productbeschrijving</label>
                <textarea id="beschrijving" name="beschrijving"><?= e($formBeschrijving) ?></textarea>
            </section>

            <section style="grid-column:1/-1;">
                <label class="checkbox-label">
                    <input type="checkbox" name="uitgelicht" <?= $formUitgelicht ? 'checked' : '' ?>>
                    Dit product uitlichten op de homepage
                </label>
            </section>
        </section>

        <!-- Foto upload sectie -->
        <section style="margin-top:24px;padding-top:24px;border-top:2px solid var(--rand);">
            <h3 style="font-family:'Barlow Condensed',sans-serif;font-size:20px;font-weight:800;margin-bottom:6px;">📷 Productfoto's</h3>
            <p style="font-size:13px;color:var(--grijs-500);margin-bottom:16px;">
                Upload tot 4 foto's. Foto 1 is de hoofdfoto. Een nieuwe upload vervangt de bestaande foto op die positie.
                Toegestane formaten: JPG, PNG, WEBP, GIF - maximaal 5 MB per foto.
            </p>

            <section class="foto-upload-grid">
                <?php
                // Maak een lookup: volgorde => foto
                $fotoPerVolgorde = [];
                foreach ($bestaandeFotos as $f) {
                    $fotoPerVolgorde[$f['volgorde']] = $f;
                }
                ?>
                <?php for ($i = 1; $i <= 4; $i++): ?>
                <section class="foto-upload-vak" id="vak-<?= $i ?>">
                    <?php if (isset($fotoPerVolgorde[$i])): ?>
                        <?php $huidigeFoto = $fotoPerVolgorde[$i]; ?>
                        <figure class="foto-huidige">
                            <img
                                src="<?= $SITE_URL ?>/uploads/producten/<?= e($huidigeFoto['bestandsnaam']) ?>"
                                alt="Huidige foto <?= $i ?>"
                            >
                            <figcaption><?= $i === 1 ? 'Hoofdfoto' : "Foto $i" ?></figcaption>
                        </figure>
                        <nav style="display:flex;gap:6px;margin-bottom:8px;">
                            <a href="product-bewerken.php?id=<?= $productId ?>&verwijder_foto=<?= $huidigeFoto['id'] ?>"
                               class="knop knop-gevaar knop-sm"
                               onclick="return confirm('Foto verwijderen?')"
                               style="font-size:12px;">
                                🗑️ Verwijderen
                            </a>
                        </nav>
                        <label for="foto_<?= $i ?>" style="font-size:12px;color:var(--grijs-500);cursor:pointer;display:block;">
                            📷 Vervangen door nieuwe foto
                        </label>
                    <?php else: ?>
                        <label for="foto_<?= $i ?>" class="foto-upload-label">
                            <span class="foto-upload-icoon">📷</span>
                            <span class="foto-upload-tekst">
                                <?= $i === 1 ? 'Hoofdfoto' : "Foto $i" ?>
                            </span>
                            <span style="font-size:11px;color:var(--grijs-400);">Klik om te uploaden</span>
                        </label>
                    <?php endif; ?>
                    <input
                        type="file"
                        id="foto_<?= $i ?>"
                        name="foto_<?= $i ?>"
                        accept="image/jpeg,image/png,image/webp,image/gif"
                        class="foto-bestand-invoer"
                        data-vak="vak-<?= $i ?>"
                    >
                </section>
                <?php endfor; ?>
            </section>
        </section>

        <p style="display:flex;gap:10px;margin-top:24px;">
            <button type="submit" class="knop knop-primair" style="padding:12px 28px;">💾 Wijzigingen opslaan</button>
            <a href="producten.php" class="knop knop-omlijnd">Annuleren</a>
        </p>
    </form>
</article>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
