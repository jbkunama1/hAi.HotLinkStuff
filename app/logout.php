<?php
declare(strict_types=1);
session_start();

// Session leeren
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

// Passwort-Cookie löschen
setcookie('hotlinkstuff_pw', '', time() - 3600, '/', '', false, false);

// Zur Login-Seite umleiten
header('Location: /');
exit;
