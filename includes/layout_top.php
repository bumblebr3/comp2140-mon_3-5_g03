<?php
/* Shared page header, document metadata, navigation, signed-in user details, and flash-message output used by application pages. */

// Allow each page to provide its own title while retaining a site-wide default.
$pageTitle = $pageTitle ?? "AZ Preston Hall's Visitor Sign-In System";
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> | AZ Preston Hall</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topbar">
    <div class="brand"><span class="brand-mark"> <img src="assets/logo.png"/></span><span>Preston Hall<br><small>Visitor Sign-In System</small></span></div>
    <?php if ($user): ?>
        <div class="user-badge"><?= h($user['username']) ?> · <?= h(ucfirst($user['role'])) ?></div>
    <?php endif; ?>
</header>
<?php if ($user): ?>
<nav class="navigation">
    <a href="<?= $user['role'] === 'admin' ? 'admin_home.php' : 'security_homepage.php' ?>">Home</a>
    <?php if ($user['role'] === 'security'): ?>
        <a href="guest_sign_in.php">Add A New Guest</a>
    <?php endif; ?>
    <a href="active_guests.php">Active Guests</a>
    <a href="search_resident.php">Search Residents</a>
    <?php if ($user['role'] === 'admin'): ?>
        <a href="add_resident.php">Add Resident</a>
        <a href="edit_resident.php">Edit Resident</a>
        <a href="remove_resident.php">Remove Resident</a>
        <a href="adduser.php">Add User</a>
        <a href="visit_history.php">Visit History</a>
    <?php endif; ?>
    <a href="emergency_list.php">Emergency Guest List</a>
    <a href="audit_log.php">Audit Log</a>
    <a href="logout.php" class="logout-link">Logout</a>
</nav>
<?php endif; ?>
<main class="page">
<?php show_flash(); ?>
