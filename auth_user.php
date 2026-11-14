<?php
session_start();

//temporary login information which defaults to security
if (!($_SESSION['role'])){
    $_SESSION['username'] = 'Test';
    $_SESSION['role'] = 'security';
}

//switches the role by adding ?role=admin or ?role=security at the end of your page's url
if (isset($_GET['role']) && in_array($_GET['role'], ['admin', 'security'])) {
    $_SESSION['role'] = $_GET['role'];
    $_SESSION['username'] = ucfirst($_GET['role']) . 'Test';
}
?>