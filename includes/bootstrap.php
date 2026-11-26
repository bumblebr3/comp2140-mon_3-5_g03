<?php
/* Shared application startup: loads the database helper, configures Jamaica time, starts and checks sessions, and defines authentication, CSRF, flash-message, audit, and upload helpers. */

require_once __DIR__ . '/db.php';
// Use the hall's local time for visit timestamps, reminders, and date displays.
date_default_timezone_set('America/Jamaica');

// Start a session with browser protections enabled before reading user state.
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

// Track authenticated activity and expire sessions that have been idle too long.
if (!empty($_SESSION['user_id'])) {
    $lastActivity = (int)($_SESSION['last_activity'] ?? time());
    if (time() - $lastActivity > 1800) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['flash'] = ['type' => 'error', 'message' => 'Your session ended after 5 minutes of inactivity. Please sign in again.'];
    } else {
        $_SESSION['last_activity'] = time();
    }
}

// Escape dynamic values before placing them into HTML.
function h($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function current_user(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    return [
        'id' => (int)$_SESSION['user_id'],
        'username' => (string)$_SESSION['username'],
        'role' => (string)$_SESSION['role'],
    ];
}

function require_login(): void
{
    if (!current_user()) {
        redirect('login.php');
    }
}

// Enforce both authentication and the list of roles allowed to access a page.
function require_role(string ...$roles): void
{
    require_login();
    if (!in_array($_SESSION['role'], $roles, true)) {
        http_response_code(403);
        exit('Access denied.');
    }
}

// Generate one unpredictable token per session to protect state-changing forms.
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('The form expired. Go back, refresh the page and try again.');
    }
}

// Store a one-time message that the shared layout displays after a redirect.
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash(): void
{
    if (!empty($_SESSION['flash'])) {
        $item = $_SESSION['flash'];
        unset($_SESSION['flash']);
        echo '<p class="notice ' . h($item['type']) . '">' . h($item['message']) . '</p>';
    }
}

// Record the current authenticated user's activity for later review.
function audit_log(string $action, string $details = ''): void
{
    $user = current_user();
    if (!$user) {
        return;
    }
    $stmt = db()->prepare('INSERT INTO audit_logs (user_id, username, action, details) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user['id'], $user['username'], $action, $details]);
}

// Validate image type and size, then save the upload under a generated filename.
function upload_image(array $file, string $folder): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The image could not be uploaded.');
    }
    if (($file['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Images must be 2 MB or smaller.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Only JPG and PNG images are allowed.');
    }
    $relativeFolder = 'uploads/' . $folder;
    $absoluteFolder = __DIR__ . '/../' . $relativeFolder;
    if (!is_dir($absoluteFolder)) {
        mkdir($absoluteFolder, 0775, true);
    }
    $filename = bin2hex(random_bytes(12)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $absoluteFolder . '/' . $filename)) {
        throw new RuntimeException('The image could not be saved.');
    }
    return $relativeFolder . '/' . $filename;
}

function full_name(array $row): string
{
    return trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
}
