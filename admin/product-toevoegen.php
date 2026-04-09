<?php
// admin/product-toevoegen.php
$paginaTitel = 'Product toevoegen';
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/../includes/afbeelding_helper.php';
global $SITE_URL;

$fout = '';

try {
    $db          = getDB();
    $categorieen = $db->query("SELECT * FROM categories ORDER BY name")->fetchAll();
} catch (PDOException $e) {
    error_log('Categorieën ophalen mislukt: ' . $e->getMessage());
    $categorieen = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam         = trim($_POST['naam'] ?? '');
    $categorieId  = intval($_POST['categorie_id'] ?? 0);
    $beschrijving = trim($_POST['beschrijving'] ?? '');
    $prijs        = floatval(str_replace(',', '.', $_POST['prijs'] ?? 0));
    $oudePrijs    = !empty($_POST['oude_prijs']) ? floatval(str_replace(',', '.', $_POST['oude_prijs'])) : null;
    $voorraad     = intval($_POST['voorraad'] ?? 0);
    $merk         = trim($_POST['merk'] ?? '');
    $sku          = trim($_POST['sku'] ?? '');
    $uitgelicht   = isset($_POST['uitgelicht']) ? 1 : 0;
    $badge        = trim($_POST['badge'] ?? '');
    $slug         = strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', $naam), '-'));

    if (!$naam || !$categorieId || $prijs <= 0) {
        $fout = 'Vul minimaal de naam, categorie en prijs in.';
    } else {
        try {
            // Unieke slug
            $basisSlug = $slug;
            $teller    = 1;
            $telStmt   = $db->prepare("SELECT COUNT(*) FROM products WHERE slug = ?");
            $telStmt->execute([$slug]);
            while ($telStmt->fetchColumn() > 0) {
                $slug = $basisSlug . '-' . $teller++;
                $telStmt->execute([$slug]);
            }

            $stmt = $db->prepare(
                "INSERT INTO products (category_id, name, slug, description, price, old_price, stock, brand, sku, is_featured, badge)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $categorieId, $naam, $slug, $beschrijving,
                $prijs, $oudePrijs, $voorraad,
                $merk, $sku, $uitgelicht,
                $badge ?: null
            ]);

            $nieuwProductId = $db->lastInsertId();

            // Foto's verwerken (max 4, volgorde 1–4)
            $fotoFouten = [];
            for ($i = 1; $i <= 4; $i++) {
                $veldNaam = 'foto_' . $i;
                if (!empty($_FILES[$veldNaam]['tmp_name']) && $_FILES[$veldNaam]['error'] === UPLOAD_ERR_OK) {
                    $bestandsnaam = uploadProductAfbeelding($_FILES[$veldNaam], $nieuwProductId, $i);
                    if ($bestandsnaam) {
                        $fotoStmt = $db->prepare(
                            "INSERT INTO product_images (product_id, bestandsnaam, volgorde) VALUES (?, ?, ?)"
                        );
                        $fotoStmt->execute([$nieuwProductId, $bestandsnaam, $i]);
                    } else {
                        $fotoFouten[] = "Foto $i kon niet worden geüpload (alleen JPG, PNG, WEBP, GIF tot 5MB).";
                    }
                }
            }

            if (!empty($fotoFouten)) {
                setFlash('succes', "Product '$naam' toegevoegd, maar: " . implode(' ', $fotoFouten));
            } else {
                setFlash('succes', "Het product '$naam' is succesvol toegevoegd!");
            }
            redirect($SITE_URL . '/admin/producten.php');

        } catch (PDOException $e) {
            error_log('Product toevoegen mislukt: ' . $e->getMessage());
            $fout = 'Er is iets misgegaan bij het opslaan van het product. Probeer het opnieuw.';
        }
    }
}
?>

<header class="admin-kop">
    <h1>➕ Product toevoegen</h1>
    <a href="producten.php" class="knop knop-omlijnd">← Terug naar producten</a>
</header>

<?php if ($fout): ?>
<p class="melding melding-fout">❌ <?= e($fout) ?></p>
<?php endif; ?>

<article style="background:var(--wit);border-radius:var(--radius);padding:32px;box-shadow:var(--schaduw);max-width:860px;">
    <form method="POST" enctype="multipart/form-data" novalidate>
        <section class="formulier-rij">
            <section style="grid-column:1/-1;">
                <label for="naam">Productnaam *</label>
                <input type="text" id="naam" name="naam" required placeholder="Samsung Galaxy S24 128GB — Zwart" value="<?= e($_POST['naam'] ?? '') ?>">
            </section>

            <section>
                <label for="categorie_id">Categorie *</label>
                <select id="categorie_id" name="categorie_id" required>
                    <option value="">Selecteer een categorie</option>
                    <?php foreach ($categorieen as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($_POST['categorie_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                        <?= e($c['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </section>

            <section>
                <label for="merk">Merk</label>
                <input type="text" id="merk" name="merk" placeholder="Samsung" value="<?= e($_POST['merk'] ?? '') ?>">
            </section>

            <section>
                <label for="prijs">Verkoopprijs (€) *</label>
                <input type="text" id="prijs" name="prijs" required placeholder="699.00" value="<?= e($_POST['prijs'] ?? '') ?>">
            </section>

            <section>
                <label for="oude_prijs">Oude prijs (€) <small style="font-weight:400;">— laat leeg als er geen korting is</small></label>
                <input type="text" id="oude_prijs" name="oude_prijs" placeholder="799.00" value="<?= e($_POST['oude_prijs'] ?? '') ?>">
            </section>

            <section>
                <label for="sku">Artikelnummer (SKU)</label>
                <input type="text" id="sku" name="sku" placeholder="S24-128-BLK" value="<?= e($_POST['sku'] ?? '') ?>">
            </section>

            <section>
                <label for="voorraad">Voorraad</label>
                <input type="number" id="voorraad" name="voorraad" min="0" value="<?= intval($_POST['voorraad'] ?? 0) ?>">
            </section>

            <section>
                <label for="badge">Badge <small style="font-weight:400;">— bijv. NIEUW, SALE, BESTSELLER</small></label>
                <input type="text" id="badge" name="badge" placeholder="NIEUW" value="<?= e($_POST['badge'] ?? '') ?>">
            </section>

            <section style="grid-column:1/-1;">
                <label for="beschrijving">Productbeschrijving</label>
                <textarea id="beschrijving" name="beschrijving" placeholder="Omschrijf het product..."><?= e($_POST['beschrijving'] ?? '') ?></textarea>
            </section>

            <section style="grid-column:1/-1;">
                <label class="checkbox-label">
                    <input type="checkbox" name="uitgelicht" <?= isset($_POST['uitgelicht']) ? 'checked' : '' ?>>
                    Dit product uitlichten op de homepage
                </label>
            </section>
        </section>

        <!-- Foto upload sectie -->
        <section style="margin-top:24px;padding-top:24px;border-top:2px solid var(--rand);">
            <h3 style="font-family:'Barlow Condensed',sans-serif;font-size:20px;font-weight:800;margin-bottom:6px;">📷 Productfoto's</h3>
            <p style="font-size:13px;color:var(--grijs-500);margin-bottom:16px;">
                Upload tot 4 foto's. Foto 1 wordt de hoofdfoto in het overzicht en bovenaan de productpagina.
                Toegestane formaten: JPG, PNG, WEBP, GIF — maximaal 5 MB per foto.
            </p>
            <section class="foto-upload-grid">
                <?php for ($i = 1; $i <= 4; $i++): ?>
                <section class="foto-upload-vak" id="vak-<?= $i ?>">
                    <label for="foto_<?= $i ?>" class="foto-upload-label">
                        <span class="foto-upload-icoon">📷</span>
                        <span class="foto-upload-tekst">
                            <?= $i === 1 ? 'Hoofdfoto *' : "Foto $i" ?>
                        </span>
                        <span style="font-size:11px;color:var(--grijs-400);">Klik om te uploaden</span>
                    </label>
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
            <button type="submit" class="knop knop-primair" style="padding:12px 28px;">💾 Product opslaan</button>
            <a href="producten.php" class="knop knop-omlijnd">Annuleren</a>
        </p>
    </form>
</article>

<?php require_once __DIR__ . '/admin_footer.php'; ?>
