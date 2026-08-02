<?php
declare(strict_types=1);
session_start();

// Zentrales Passwort aus ENV (Portainer: APP_PASSWORD)
$correctPassword = getenv('APP_PASSWORD') ?: 'hotstuff';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    if (hash_equals($correctPassword, $password)) {
        // Session-Flag setzen
        $_SESSION['hotlinkstuff_authenticated'] = true;

        // Passwort im Cookie speichern (für die Module)
        setcookie('hotlinkstuff_pw', $password, [
            'path'     => '/',
            'httponly' => false,  // JS soll das Cookie sehen können
            'secure'   => false,  // bei HTTPS auf true setzen
            'samesite' => 'Lax',
        ]);

        // Weiterleitung auf die Shell
        header('Location: /app/index.html');
        exit;
    } else {
        $error = 'Falsches Passwort.';
    }
}

// Wenn schon eingeloggt, direkt zur Shell
if (!empty($_SESSION['hotlinkstuff_authenticated'])) {
    header('Location: /app/index.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Login · hAI.HotStuff</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href="favicon.ico" type="image/x-icon" />
  <style>
    :root {
      --bg: #0f172a;
      --surface: #0b1120;
      --accent: #0f766e;
      --accent2: #ea580c;
      --text: #e5e7eb;
      --muted: #9ca3af;
      --border: #1f2937;
      --radius: 18px;
      --shadow: 0 20px 50px rgba(15, 23, 42, 0.65);
    }
    * { box-sizing: border-box; }
    body {
      margin: 0;
      font-family: Inter, system-ui, sans-serif;
      background:
        radial-gradient(circle at top left, rgba(234, 88, 12, 0.35), transparent 52%),
        radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.35), transparent 52%),
        linear-gradient(180deg, #020617 0%, #0b1120 70%, #020617 100%);
      color: var(--text);
      min-height: 100vh;
      display: grid;
      place-items: center;
      padding: 20px;
    }
    .card {
      width: min(420px, 100%);
      background: var(--surface);
      border-radius: 24px;
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
      padding: 28px 26px 24px;
      display: grid;
      gap: 18px;
    }
    .brand {
      display: flex;
      gap: 12px;
      align-items: center;
    }
    .brand-mark {
      width: 52px;
      height: 52px;
      border-radius: 16px;
      display: grid;
      place-items: center;
      font-size: 26px;
      background: linear-gradient(135deg, var(--accent2), var(--accent));
    }
    h1 {
      margin: 0;
      font-size: 1.3rem;
    }
    p {
      margin: 4px 0 0;
      color: var(--muted);
      font-size: 0.95rem;
    }
    label {
      display: grid;
      gap: 8px;
      font-weight: 600;
      font-size: 0.95rem;
    }
    input[type="password"] {
      width: 100%;
      padding: 12px 14px;
      border-radius: 14px;
      border: 1px solid #374151;
      background: #020617;
      color: var(--text);
      font: inherit;
    }
    input[type="password"]::placeholder { color: #6b7280; }
    .btn {
      margin-top: 4px;
      width: 100%;
      padding: 11px 14px;
      border-radius: 999px;
      border: none;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.98rem;
      background: linear-gradient(135deg, #0f766e, #0ea5e9);
      color: #f9fafb;
    }
    .btn:hover { filter: brightness(1.05); }
    .error {
      margin-top: 4px;
      color: #fca5a5;
      font-size: 0.9rem;
    }
    .hint {
      font-size: 0.85rem;
      color: var(--muted);
    }
  </style>
</head>
<body>
  <form class="card" method="post" action="">
    <div class="brand">
      <div class="brand-mark">🔥🧠</div>
      <div>
        <h1>hAI.HotLinkStuff</h1>
        <p>Login für HotStuff & PromptSave</p>
      </div>
    </div>
    <label>
      Passwort
      <input type="password" name="password" placeholder="Passwort" autofocus>
    </label>
    <?php if ($error): ?>
      <div class="error">
        <?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
      </div>
    <?php endif; ?>
    <button class="btn" type="submit">Anmelden</button>
    <div class="hint">
      Nach erfolgreichem Login wirst du automatisch zur Oberfläche weitergeleitet.
    </div>
  </form>
</body>
</html>
