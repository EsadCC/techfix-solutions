<?php
// bestellen.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

$winkelwagen = $_SESSION['winkelwagen'] ?? [];
if (empty($winkelwagen)) redirect($SITE_URL . '/winkelwagen.php');

$fout     = '';
$gebruiker = getGebruiker();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $voornaam  = trim($_POST['voornaam'] ?? '');
    $achternaam = trim($_POST['achternaam'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telefoon  = trim($_POST['telefoon'] ?? '');
    $straat    = trim($_POST['straat'] ?? '');
    $postcode  = trim($_POST['postcode'] ?? '');
    $stad      = trim($_POST['stad'] ?? '');
    $land      = trim($_POST['land'] ?? 'Nederland');

    if (!$voornaam || !$achternaam || !$email || !$straat || !$postcode || !$stad) {
        $fout = 'Vul alle verplichte velden in.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fout = 'Vul een geldig e-mailadres in.';
    } else {
        try {
            $db        = getDB();
            $totaal    = winkelwagenTotaal();
            $verzending = $totaal >= 50 ? 0 : 4.99;
            $eindtotaal = $totaal + $verzending;
            $gebruikerId = isIngelogd() ? $_SESSION['gebruiker_id'] : null;

            $stmt = $db->prepare("INSERT INTO orders (user_id, first_name, last_name, email, phone, street, postal_code, city, country, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$gebruikerId, $voornaam, $achternaam, $email, $telefoon, $straat, $postcode, $stad, $land, $eindtotaal]);
            $bestellingId = $db->lastInsertId();

            $regelStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($winkelwagen as $pid => $item) {
                $regelStmt->execute([$bestellingId, intval($pid), $item['aantal'], $item['prijs']]);
            }

            unset($_SESSION['winkelwagen']);
            setFlash('succes', "Bestelling #$bestellingId is geplaatst! Je ontvangt een bevestiging op $email.");
            redirect($SITE_URL . '/bestelling-bevestigd.php?id=' . $bestellingId);

        } catch (PDOException $e) {
            error_log('Bestelling plaatsen mislukt: ' . $e->getMessage());
            $fout = 'Er is iets misgegaan bij het plaatsen van je bestelling. Probeer het opnieuw.';
        }
    }
}

$totaal    = winkelwagenTotaal();
$verzending = $totaal >= 50 ? 0 : 4.99;
$paginaTitel = 'Bestelling plaatsen - Techfix Solutions';

require_once __DIR__ . '/includes/header.php';
?>

<main>
<section class="container">
    <nav class="broodkruimel" aria-label="Breadcrumb">
        <ol>
            <li><a href="<?= $SITE_URL ?>/">Home</a></li>
            <li><a href="<?= $SITE_URL ?>/winkelwagen.php">Winkelwagen</a></li>
            <li>Bestelling plaatsen</li>
        </ol>
    </nav>

    <!-- Stappen indicator -->
    <ol class="stappen-indicator" aria-label="Bestelproces">
        <li class="actief">① Persoonlijke gegevens</li>
        <li>② Verzendmethode</li>
        <li>③ Betaalmethode</li>
    </ol>

    <?php if ($fout): ?>
    <p class="melding melding-fout">❌ <?= e($fout) ?></p>
    <?php endif; ?>

    <section class="checkout-layout">

        <!-- Formulier -->
        <section>
            <form method="POST" novalidate>

                <section class="checkout-sectie">
                    <fieldset>
                        <legend>Persoonlijke gegevens</legend>
                        <section class="formulier-rij">
                            <section>
                                <label for="voornaam">Voornaam *</label>
                                <input type="text" id="voornaam" name="voornaam" placeholder="Willem" required autocomplete="given-name" value="<?= e($_POST['voornaam'] ?? '') ?>">
                            </section>
                            <section>
                                <label for="achternaam">Achternaam *</label>
                                <input type="text" id="achternaam" name="achternaam" placeholder="Jansen" required autocomplete="family-name" value="<?= e($_POST['achternaam'] ?? '') ?>">
                            </section>
                        </section>
                        <section class="formulier-rij">
                            <section>
                                <label for="email">E-mailadres *</label>
                                <input type="email" id="email" name="email" placeholder="w.jansen@voorbeeld.nl" required autocomplete="email" value="<?= e($_POST['email'] ?? $gebruiker['email'] ?? '') ?>">
                            </section>
                            <section>
                                <label for="telefoon">Telefoonnummer</label>
                                <input type="tel" id="telefoon" name="telefoon" placeholder="+31 6 12345678" autocomplete="tel" value="<?= e($_POST['telefoon'] ?? '') ?>">
                            </section>
                        </section>
                    </fieldset>
                </section>

                <section class="checkout-sectie">
                    <fieldset>
                        <legend>Verzendadres</legend>
                        <label for="straat">Straat en huisnummer *</label>
                        <input type="text" id="straat" name="straat" placeholder="Voorbeeldstraat 12" required autocomplete="street-address" value="<?= e($_POST['straat'] ?? '') ?>">
                        <section class="formulier-rij">
                            <section>
                                <label for="postcode">Postcode *</label>
                                <input type="text" id="postcode" name="postcode" placeholder="1234 AB" required autocomplete="postal-code" value="<?= e($_POST['postcode'] ?? '') ?>">
                            </section>
                            <section>
                                <label for="stad">Stad *</label>
                                <input type="text" id="stad" name="stad" placeholder="Amsterdam" required autocomplete="address-level2" value="<?= e($_POST['stad'] ?? '') ?>">
                            </section>
                        </section>
                        <label for="land">Land</label>
                        <select id="land" name="land" autocomplete="country-name">
                            <option value="Nederland" selected>Nederland</option>
                            <option value="België">België</option>
                            <option value="Duitsland">Duitsland</option>
                        </select>
                    </fieldset>
                </section>

                <section class="checkout-sectie" style="background:var(--groen-licht);display:flex;align-items:center;gap:12px;">
                    <span style="font-size:32px;">🔒</span>
                    <section>
                        <strong>Veilig afrekenen</strong>
                        <p style="font-size:13px;">Jouw gegevens worden versleuteld verstuurd via SSL beveiliging.</p>
                    </section>
                </section>

                <button type="submit" class="knop knop-primair knop-vol" style="padding:16px;font-size:17px;margin-top:8px;">
                    Verder naar verzendmethode →
                </button>
            </form>
            <p style="margin-top:12px;">
                <a href="<?= $SITE_URL ?>/winkelwagen.php" style="color:var(--grijs-500);font-size:14px;">← Terug naar winkelwagen</a>
            </p>
        </section>

        <!-- Samenvatting -->
        <aside class="bestelling-samenvatting">
            <h2>Jouw bestelling</h2>
            <?php foreach ($winkelwagen as $pid => $item): ?>
            <section class="samenvatting-product">
                <figure class="samenvatting-product-foto" aria-hidden="true">📱</figure>
                <section style="flex:1;">
                    <p style="font-size:13px;font-weight:600;"><?= e($item['naam']) ?></p>
                    <p style="font-size:12px;color:var(--grijs-500);">× <?= intval($item['aantal']) ?></p>
                </section>
                <strong>€ <?= number_format($item['prijs'] * $item['aantal'], 2, ',', '.') ?></strong>
            </section>
            <?php endforeach; ?>

            <section class="samenvatting-totalen">
                <p class="totaal-rij"><span>Subtotaal</span><span>€ <?= number_format($totaal, 2, ',', '.') ?></span></p>
                <p class="totaal-rij">
                    <span>Verzendkosten</span>
                    <span style="color:var(--groen);"><?= $verzending > 0 ? '€ ' . number_format($verzending, 2, ',', '.') : 'GRATIS' ?></span>
                </p>
                <p class="totaal-rij eindtotaal">
                    <span>Totaal <small style="font-weight:400;">(incl. BTW)</small></span>
                    <span>€ <?= number_format($totaal + $verzending, 2, ',', '.') ?></span>
                </p>
            </section>

            <p class="betaalmethoden" style="justify-content:center;margin-top:12px;">
                <span class="betaalmethode">iDEAL</span>
                <span class="betaalmethode">PayPal</span>
                <span class="betaalmethode">Visa</span>
                <span class="betaalmethode">Klarna</span>
            </p>
        </aside>

    </section>
</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
