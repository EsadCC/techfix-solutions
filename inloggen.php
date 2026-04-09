<?php
// inloggen.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

if (isIngelogd()) redirect($SITE_URL . '/');

$fout = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $wachtwoord = $_POST['wachtwoord'] ?? '';

    if (!$email || !$wachtwoord) {
        $fout = 'Vul je e-mailadres en wachtwoord in.';
    } else {
        try {
            $db   = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $gebruiker = $stmt->fetch();

            if ($gebruiker && password_verify($wachtwoord, $gebruiker['password'])) {
                $_SESSION['gebruiker_id']   = $gebruiker['id'];
                $_SESSION['gebruiker_naam'] = $gebruiker['name'];
                $_SESSION['gebruiker_rol']  = $gebruiker['role'];
                redirect($SITE_URL . '/');
            } else {
                $fout = 'Onjuist e-mailadres of wachtwoord. Controleer je gegevens en probeer het opnieuw.';
            }
        } catch (PDOException $e) {
            error_log('Inloggen mislukt: ' . $e->getMessage());
            $fout = 'Er is iets misgegaan. Probeer het later opnieuw.';
        }
    }
}

$paginaTitel = 'Inloggen - Techfix Solutions';
require_once __DIR__ . '/includes/header.php';
?>

<main>
<section class="auth-pagina">
    <article class="auth-kaart">
        <h1>Inloggen</h1>
        <p class="ondertitel">Welkom terug bij Techfix Solutions!</p>

        <?php if ($fout): ?>
        <p class="melding melding-fout">❌ <?= e($fout) ?></p>
        <?php endif; ?>

        <form method="POST" novalidate>
            <label for="email">E-mailadres</label>
            <input type="email" id="email" name="email" placeholder="jouw@email.nl" required autocomplete="email" autofocus>

            <label for="wachtwoord">Wachtwoord</label>
            <input type="password" id="wachtwoord" name="wachtwoord" placeholder="••••••••" required autocomplete="current-password">

            <button type="submit" class="knop knop-primair knop-vol" style="padding:12px;">Inloggen →</button>
        </form>

        <p style="margin-top:16px;text-align:center;font-size:14px;color:var(--grijs-500);">
            Nog geen account? <a href="<?= $SITE_URL ?>/registreren.php">Registreer hier</a>
        </p>

        <section style="margin-top:16px;padding:12px;background:var(--blauw-licht);border-radius:6px;font-size:13px;">
            <strong>Demo accounts (wachtwoord: password):</strong><br>
            Admin: admin@techfix.nl<br>
            Klant: jan@example.nl
        </section>
    </article>
</section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
