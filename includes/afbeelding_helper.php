<?php
// includes/afbeelding_helper.php
// Hulpfuncties voor productafbeeldingen

/**
 * Haal alle afbeeldingen op voor een product, gesorteerd op volgorde.
 * Geeft een lege array terug bij geen foto's of bij een DB-fout.
 */
function getProductAfbeeldingen($productId) {
    try {
        $db   = getDB();
        $stmt = $db->prepare(
            "SELECT * FROM product_images WHERE product_id = ? ORDER BY volgorde ASC LIMIT 4"
        );
        $stmt->execute([intval($productId)]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Afbeeldingen ophalen mislukt: ' . $e->getMessage());
        return [];
    }
}

/**
 * Geeft het pad naar de hoofdafbeelding terug,
 * of een lege string als er geen foto is.
 */
function getHoofdAfbeelding($productId) {
    $fotos = getProductAfbeeldingen($productId);
    if (empty($fotos)) return '';
    return '/uploads/producten/' . $fotos[0]['bestandsnaam'];
}

/**
 * Geeft een fallback-emoji terug op basis van categorienaam.
 */
function getCategorieEmoji($catNaam) {
    $catNaam = strtolower($catNaam ?? '');
    if (strpos($catNaam, 'smartphone') !== false || strpos($catNaam, 'phone') !== false) return '📱';
    if (strpos($catNaam, 'laptop')      !== false) return '💻';
    if (strpos($catNaam, 'audio')       !== false) return '🎧';
    if (strpos($catNaam, 'accessoire')  !== false) return '🔌';
    if (strpos($catNaam, 'onderdeel')   !== false) return '🔧';
    return '📦';
}

/**
 * Verwerkt het uploaden van een productafbeelding.
 * Geeft de opgeslagen bestandsnaam terug of false bij een fout.
 *
 * @param array  $bestand     Eén bestand uit $_FILES (bijv. $_FILES['foto'])
 * @param int    $productId   ID van het product
 * @param int    $volgorde    1–4
 * @return string|false
 */
function uploadProductAfbeelding($bestand, $productId, $volgorde) {
    $uploadMap = __DIR__ . '/../uploads/producten/';

    // Controleer of er daadwerkelijk een bestand is
    if (empty($bestand['tmp_name']) || $bestand['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // Controleer bestandstype via MIME (niet alleen extensie)
    // mime_content_type() vereist de fileinfo extensie; getimagesize() werkt altijd voor afbeeldingen
    $toegestaneMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (function_exists('mime_content_type')) {
        $mimeType = mime_content_type($bestand['tmp_name']);
    } else {
        $imageInfo = getimagesize($bestand['tmp_name']);
        $mimeType  = $imageInfo ? $imageInfo['mime'] : '';
    }

    if (!in_array($mimeType, $toegestaneMimeTypes)) {
        return false;
    }

    // Maximale bestandsgrootte: 5 MB
    if ($bestand['size'] > 5 * 1024 * 1024) {
        return false;
    }

    // Extensie bepalen uit MIME-type
    $extensies = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    $extensie = $extensies[$mimeType];

    // Unieke bestandsnaam aanmaken
    $bestandsnaam = 'product_' . $productId . '_' . $volgorde . '_' . time() . '.' . $extensie;
    $doelpad      = $uploadMap . $bestandsnaam;

    if (!move_uploaded_file($bestand['tmp_name'], $doelpad)) {
        error_log('Afbeelding verplaatsen mislukt: ' . $doelpad);
        return false;
    }

    return $bestandsnaam;
}

/**
 * Verwijdert een afbeelding uit de DB en van schijf.
 */
function verwijderProductAfbeelding($afbeeldingId) {
    try {
        $db   = getDB();
        $stmt = $db->prepare("SELECT bestandsnaam FROM product_images WHERE id = ?");
        $stmt->execute([intval($afbeeldingId)]);
        $rij  = $stmt->fetch();

        if ($rij) {
            $pad = __DIR__ . '/../uploads/producten/' . $rij['bestandsnaam'];
            if (file_exists($pad)) {
                unlink($pad);
            }
            $db->prepare("DELETE FROM product_images WHERE id = ?")->execute([intval($afbeeldingId)]);
        }
        return true;
    } catch (PDOException $e) {
        error_log('Afbeelding verwijderen mislukt: ' . $e->getMessage());
        return false;
    }
}
