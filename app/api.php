<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$inputRaw = file_get_contents('php://input');
$input = json_decode($inputRaw, true);
if (!is_array($input)) {
    $input = $_POST ?: [];
}

$resource = $input['resource'] ?? '';
$action = $input['action'] ?? '';
$password = $input['password'] ?? '';
$correctPassword = getenv('APP_PASSWORD') ?: 'hotstuff';
$requestId = bin2hex(random_bytes(4));

error_log(sprintf(
    '[api] request=%s resource=%s action=%s path=%s',
    $requestId,
    (string)$resource,
    (string)$action,
    $_SERVER['REQUEST_URI'] ?? 'unknown'
));

// The login page already establishes this same-origin session. Use it as the
// primary browser auth path and keep the password field for API clients.
$isAuthenticated = !empty($_SESSION['hotlinkstuff_authenticated']);
if (!$isAuthenticated && !hash_equals($correctPassword, (string)$password)) {
    echo json_encode(['success' => false, 'message' => 'Falsches Passwort']);
    exit;
}

function jsonResponse(array $data, int $status = 200): void {
    http_response_code($status);
    $json = json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
    );
    if ($json === false) {
        http_response_code(500);
        echo '{"success":false,"message":"API konnte die Daten nicht als JSON kodieren"}';
        exit;
    }
    echo $json;
    exit;
}

function ensureColumn(PDO $db, string $table, string $column, string $definition): void {
    $columns = $db->query("PRAGMA table_info(" . $table . ")")->fetchAll(PDO::FETCH_COLUMN, 1);
    if (!in_array($column, $columns, true)) {
        $db->exec("ALTER TABLE " . $table . " ADD COLUMN " . $column . " " . $definition);
    }
}

function getHotstuffDb(): PDO {
    $dbFile = '/data/heisser-scheiss.db';
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        category TEXT NOT NULL,
        content TEXT,
        link TEXT,
        image TEXT,
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL
    )");
    ensureColumn($db, 'items', 'title', 'TEXT');
    ensureColumn($db, 'items', 'category', 'TEXT');
    ensureColumn($db, 'items', 'content', 'TEXT');
    ensureColumn($db, 'items', 'link', 'TEXT');
    ensureColumn($db, 'items', 'image', 'TEXT');
    ensureColumn($db, 'items', 'created_at', 'TEXT');
    ensureColumn($db, 'items', 'updated_at', 'TEXT');
    return $db;
}

function getPromptsDb(): PDO {
    $dbFile = '/data/prompts.db';
    $db = new PDO('sqlite:' . $dbFile);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec("CREATE TABLE IF NOT EXISTS prompts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        uuid TEXT UNIQUE NOT NULL,
        title TEXT NOT NULL,
        content TEXT NOT NULL,
        category TEXT NOT NULL CHECK(category IN ('gemini', 'chatgpt', 'general')),
        created_at TEXT NOT NULL,
        updated_at TEXT NOT NULL,
        created_timestamp INTEGER NOT NULL,
        updated_timestamp INTEGER NOT NULL
    )");
    ensureColumn($db, 'prompts', 'title', 'TEXT');
    ensureColumn($db, 'prompts', 'content', 'TEXT');
    ensureColumn($db, 'prompts', 'category', 'TEXT');
    ensureColumn($db, 'prompts', 'created_at', 'TEXT');
    ensureColumn($db, 'prompts', 'updated_at', 'TEXT');
    ensureColumn($db, 'prompts', 'created_timestamp', 'INTEGER');
    ensureColumn($db, 'prompts', 'updated_timestamp', 'INTEGER');
    return $db;
}

function generateUuid(): string {
    return bin2hex(random_bytes(16));
}

function handleItems(string $action, array $input, PDO $db): void {
    global $requestId;
    switch ($action) {
        case 'test':
            jsonResponse(['success' => true, 'message' => 'Items-Verbindung OK']);
        case 'getAll':
            $stmt = $db->query("SELECT * FROM items ORDER BY created_at DESC");
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("[api] request={$requestId} items rows=" . count($items));
            jsonResponse([
                'success' => true,
                'items' => $items,
                'count' => count($items),
                'database' => 'heisser-scheiss.db'
            ]);
        case 'create':
            $item = $input['item'] ?? [];
            $now = date('Y-m-d H:i:s');
            $stmt = $db->prepare("INSERT INTO items (title, category, content, link, image, created_at, updated_at)
                VALUES (:title, :category, :content, :link, :image, :created_at, :updated_at)");
            $stmt->execute([
                ':title' => $item['title'] ?? '',
                ':category' => $item['category'] ?? '',
                ':content' => $item['content'] ?? '',
                ':link' => $item['link'] ?? '',
                ':image' => $item['image'] ?? '',
                ':created_at' => $now,
                ':updated_at' => $now
            ]);
            jsonResponse(['success' => true, 'message' => 'Eintrag erstellt', 'id' => $db->lastInsertId()]);
        case 'update':
            $item = $input['item'] ?? [];
            $now = date('Y-m-d H:i:s');
            $stmt = $db->prepare("UPDATE items SET title = :title, category = :category, content = :content, link = :link, image = :image, updated_at = :updated_at WHERE id = :id");
            $stmt->execute([
                ':title' => $item['title'] ?? '',
                ':category' => $item['category'] ?? '',
                ':content' => $item['content'] ?? '',
                ':link' => $item['link'] ?? '',
                ':image' => $item['image'] ?? '',
                ':updated_at' => $now,
                ':id' => $item['id'] ?? 0
            ]);
            jsonResponse(['success' => true, 'message' => 'Eintrag aktualisiert']);
        case 'delete':
            $id = (int)($input['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM items WHERE id = :id");
            $stmt->execute([':id' => $id]);
            jsonResponse(['success' => true, 'message' => 'Eintrag gelöscht']);
        default:
            jsonResponse(['success' => false, 'message' => 'Unbekannte Aktion für items'], 400);
    }
}

function handlePrompts(string $action, array $input, PDO $db): void {
    global $requestId;
    switch ($action) {
        case 'test':
            jsonResponse(['success' => true, 'message' => 'Prompts-Verbindung OK']);
        case 'getAll':
            $stmt = $db->query("SELECT * FROM prompts ORDER BY created_timestamp DESC");
            $prompts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("[api] request={$requestId} prompts rows=" . count($prompts));
            jsonResponse([
                'success' => true,
                'prompts' => $prompts,
                'count' => count($prompts),
                'database' => 'prompts.db'
            ]);
        case 'create':
            $prompt = $input['prompt'] ?? [];
            $now = date('Y-m-d H:i:s');
            $ts = time();
            $stmt = $db->prepare("INSERT INTO prompts (uuid, title, content, category, created_at, updated_at, created_timestamp, updated_timestamp)
                VALUES (:uuid, :title, :content, :category, :created_at, :updated_at, :created_timestamp, :updated_timestamp)");
            $stmt->execute([
                ':uuid' => $prompt['uuid'] ?? generateUuid(),
                ':title' => $prompt['title'] ?? '',
                ':content' => $prompt['content'] ?? '',
                ':category' => $prompt['category'] ?? 'general',
                ':created_at' => $now,
                ':updated_at' => $now,
                ':created_timestamp' => $ts,
                ':updated_timestamp' => $ts
            ]);
            jsonResponse(['success' => true, 'message' => 'Prompt erstellt', 'id' => $db->lastInsertId()]);
        case 'update':
            $prompt = $input['prompt'] ?? [];
            $now = date('Y-m-d H:i:s');
            $ts = time();
            $stmt = $db->prepare("UPDATE prompts SET title = :title, content = :content, category = :category, updated_at = :updated_at, updated_timestamp = :updated_timestamp WHERE id = :id");
            $stmt->execute([
                ':title' => $prompt['title'] ?? '',
                ':content' => $prompt['content'] ?? '',
                ':category' => $prompt['category'] ?? 'general',
                ':updated_at' => $now,
                ':updated_timestamp' => $ts,
                ':id' => $prompt['id'] ?? 0
            ]);
            jsonResponse(['success' => true, 'message' => 'Prompt aktualisiert']);
        case 'delete':
            $id = (int)($input['id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM prompts WHERE id = :id");
            $stmt->execute([':id' => $id]);
            jsonResponse(['success' => true, 'message' => 'Prompt gelöscht']);
        case 'exportCsv':
            $stmt = $db->query("SELECT title, content, category FROM prompts ORDER BY created_timestamp DESC");
            jsonResponse(['success' => true, 'rows' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
        default:
            jsonResponse(['success' => false, 'message' => 'Unbekannte Aktion für prompts'], 400);
    }
}

try {
    switch ($resource) {
        case 'items':
            handleItems($action, $input, getHotstuffDb());
        case 'prompts':
            handlePrompts($action, $input, getPromptsDb());
        default:
            jsonResponse(['success' => false, 'message' => 'Unbekannte Resource'], 400);
    }
} catch (Throwable $e) {
    jsonResponse(['success' => false, 'message' => 'Fehler: ' . $e->getMessage()], 500);
}
