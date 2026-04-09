<?php
// uitloggen.php
require_once __DIR__ . '/includes/config.php';
global $SITE_URL;

// Sessie netjes opruimen
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();
redirect($SITE_URL . '/inloggen.php');
