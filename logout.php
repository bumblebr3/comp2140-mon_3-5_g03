<?php
/* Records the sign-out event, clears the session and session cookie, and redirects the user to the login page. */

require_once __DIR__ . '/includes/bootstrap.php';
if (current_user()) {
    audit_log('logout', 'User signed out.');
}
// Clear session data and expire the browser's session cookie before destroying the session.
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
redirect('login.php');
