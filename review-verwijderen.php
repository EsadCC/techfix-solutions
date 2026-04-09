<?php
// review-verwijderen.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

if (!isIngelogd()) redirect($SITE_URL . '/inloggen.php');

$reviewId  = intval($_GET['id'] ?? 0);
$productId = intval($_GET['product'] ?? 0);
$review    = null;

try {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM reviews WHERE id = ?");
    $stmt->execute([$reviewId]);
    $review = $stmt->fetch();

    if ($review && ($review['user_id'] == $_SESSION['gebruiker_id'] || isAdmin())) {
        $db->prepare("DELETE FROM reviews WHERE id = ?")->execute([$reviewId]);
        setFlash('succes', 'Je review is verwijderd.');

        // Gebruik product_id uit de review als $productId niet via URL meegegeven
        if (!$productId) {
            $productId = intval($review['product_id']);
        }
    } else {
        setFlash('fout', 'Je hebt geen toestemming om deze review te verwijderen.');
    }
} catch (PDOException $e) {
    error_log('Review verwijderen mislukt: ' . $e->getMessage());
    setFlash('fout', 'Er is iets misgegaan. Probeer het opnieuw.');
}

redirect($SITE_URL . '/product.php?id=' . $productId . '#reviews');
