<?php
declare(strict_types=1);
session_start();

$correctPassword = getenv('APP_PASSWORD') ?: 'hotstuff';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    if (hash_equals($correctPassword, $password)) {
        $_SESSION['hotlinkstuff_authenticated'] = true;

        setcookie('hotlinkstuff_pw', $password, [
            'path' => '/',
            'httponly' => false,
            'secure' => false,
            'samesite' => 'Lax',
        ]);

        header('Location: /app/index.html');
        exit;
    } else {
        $error = 'Falsches Passwort.';
    }
}

if (!empty($_SESSION['hotlinkstuff_authenticated'])) {
    header('Location: /app/index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Login · hAI.HotLinkStuff</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Styles wie zuvor -->
</head>
<body>
  <!-- Login-Formular wie zuvor -->
</body>
</html>
