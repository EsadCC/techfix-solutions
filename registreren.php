<?php
// registreren.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

if (isIngelogd()) redirect($SITE_URL . '/');

$fout = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam       = trim($_POST['naam'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';
    $herhaling  = $_POST['herhaling'] ?? '';

    if (!$naam || !$email || !$wachtwoord) {
        $fout = 'Vul alle velden in.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fout = 'Vul een geldig e-mailadres in.';
    } elseif ($wachtwoord !== $herhaling) {
        $fout = 'De wachtwoorden komen niet overeen.';
    } elseif (strlen($wachtwoord) < 6) {
        $fout = 'Je wachtwoord moet minimaal 6 tekens lang zijn.';
    } else {
        try {
            $db  = getDB();
            $chk = $db->prepare("SELECT id FROM users WHERE email = ?");
            $chk->execute([$email]);

            if ($chk->fetch()) {
                $fout = 'Dit e-mailadres is al in gebruik. Probeer in te loggen.';
            } else {
                $hash = password_hash($wachtwoord, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$naam, $email, $hash]);

                $gebruikerId = $db->lastInsertId();
                $_SESSION['gebruiker_id']   = $gebruikerId;
                $_SESSION['gebruiker_naam'] = $naam;
                $_SESSION['gebruiker_rol']  = 'user';
                setFlash('succes', "Welkom bij Techfix, $naam! Je account is aangemaakt.");
                redirect($SITE_URL . '/');
            }
        } catch (PDOException $e) {
            error_log('Registratie mislukt: ' . $e->getMessage());
            $fout = 'Er is iets misgegaan bij het aanmaken van je account. Probeer het later opnieuw.';
        }
    }
}

$paginaTitel = 'Registreren - Techfix Solutions';
require_once __DIR__ . '/includes/header.php';
?>

<main>
<section class="auth-pagina">
    <article class="auth-kaart">
        <h1>Registreren</h1>
        <p class="ondertitel">Maak een gratis account aan bij Techfix Solutions</p>

        <?php if ($fout): ?>
        <p class="melding melding-fout">❌ <?= e($fout) ?></p>
        <?php endif; ?>

        <form method="POST" novalidate>
            <label for="naam">Volledige naam</label>
            <input type="text" id="naam" name="naam" placeholder="Jan de Vries" required autocomplete="name">

            <label for="email">E-mailadres</label>
            <input type="email" id="email" name="email" placeholder="jan@voorbeeld.nl" required autocomplete="email">

            <label for="wachtwoord">Wachtwoord</label>
            <input type="password" id="wachtwoord" name="wachtwoord" placeholder="Minimaal 6 tekens" required autocomplete="new-password">

            <label for="herhaling">Herhaal wachtwoord</label>
            <input type="password" id="herhaling" name="herhaling" placeholder="••••••••" required autocomplete="new-password">

            <button type="submit" class="knop knop-primair knop-vol" style="padding:12px;">Account aanmaken →</button>
        </form>

        <p style="margin-top:16px;text-align:center;font-size:14px;color:var(--grijs-500);">
            Al een account? <a href="<?= $SITE_URL ?>/inloggen.php">Log hier in</a>
        </p>
    </article>
</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
