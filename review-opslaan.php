<?php
// review-opslaan.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

if (!isIngelogd()) redirect($SITE_URL . '/inloggen.php');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect($SITE_URL . '/');

$productId  = intval($_POST['product_id'] ?? 0);
$beoordeling = intval($_POST['rating'] ?? 0);
$titel       = trim($_POST['title'] ?? '');
$tekst       = trim($_POST['body'] ?? '');
$gebruikerId = $_SESSION['gebruiker_id'];

if (!$productId || $beoordeling < 1 || $beoordeling > 5 || !$titel || !$tekst) {
    setFlash('fout', 'Vul alle velden in en geef een beoordeling van 1 tot 5 sterren.');
    redirect($SITE_URL . '/product.php?id=' . $productId);
}

try {
    $db   = getDB();
    $stmt = $db->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    if (!$stmt->fetch()) redirect($SITE_URL . '/producten.php');

    $insert = $db->prepare("INSERT INTO reviews (product_id, user_id, rating, title, body) VALUES (?, ?, ?, ?, ?)");
    $insert->execute([$productId, $gebruikerId, $beoordeling, $titel, $tekst]);

    setFlash('succes', 'Jouw review is geplaatst!');
} catch (PDOException $e) {
    error_log('Review opslaan mislukt: ' . $e->getMessage());
    setFlash('fout', 'Er is iets misgegaan bij het plaatsen van je review. Probeer het opnieuw.');
}

redirect($SITE_URL . '/product.php?id=' . $productId . '#reviews');
