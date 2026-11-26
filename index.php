<?php
/* Entry point that sends signed-in users to the dashboard matching their role and sends unauthenticated visitors to the login page. */

require_once __DIR__ . '/includes/bootstrap.php';
// Route users to login first, then send authenticated users to their role-specific dashboard.
if (!current_user()) {
    redirect('login.php');
}
redirect($_SESSION['role'] === 'admin' ? 'admin_home.php' : 'security_homepage.php');
