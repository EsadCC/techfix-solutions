<?php

// ─── Database instellingen ───────────────────────────────────
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'techfix solutions';

// ─── Site instellingen ──────────────────────────────────────
$SITE_NAME = 'Techfix Solutions';
$SITE_URL  = 'http://localhost/techfix2';

// ─── Sessie starten ─────────────────────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Database verbinding ─────────────────────────────────────
function getDB() {
    global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
                $DB_USER,
                $DB_PASS,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            // Geef nooit technische details aan de gebruiker
            error_log('Database fout: ' . $e->getMessage());
            include __DIR__ . '/error_page.php';
            exit;
        }
    }

    return $pdo;
}

// ─── Hulpfuncties ────────────────────────────────────────────

// Veilig outputten (voorkomt XSS)
function e($tekst) {
    return htmlspecialchars($tekst ?? '', ENT_QUOTES, 'UTF-8');
}

// Doorsturen naar andere pagina
function redirect($url) {
    header("Location: $url");
    exit;
}

// Controleer of gebruiker ingelogd is
function isIngelogd() {
    return isset($_SESSION['gebruiker_id']);
}

// Controleer of gebruiker admin is
function isAdmin() {
    return isset($_SESSION['gebruiker_rol']) && $_SESSION['gebruiker_rol'] === 'admin';
}

// Huidige ingelogde gebruiker ophalen
function getGebruiker() {
    if (!isIngelogd()) return null;

    try {
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['gebruiker_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Fout bij ophalen gebruiker: ' . $e->getMessage());
        return null;
    }
}

// Aantal items in winkelwagen
function winkelwagenAantal() {
    if (empty($_SESSION['winkelwagen'])) return 0;
    return array_sum(array_column($_SESSION['winkelwagen'], 'aantal'));
}

// Totaalprijs winkelwagen
function winkelwagenTotaal() {
    if (empty($_SESSION['winkelwagen'])) return 0;
    $totaal = 0;
    foreach ($_SESSION['winkelwagen'] as $item) {
        $totaal += $item['prijs'] * $item['aantal'];
    }
    return $totaal;
}

// Flash bericht instellen
function setFlash($type, $bericht) {
    $_SESSION['flash'] = ['type' => $type, 'bericht' => $bericht];
}

// Flash bericht ophalen en verwijderen
function getFlash() {
    if (!isset($_SESSION['flash'])) return null;
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}
