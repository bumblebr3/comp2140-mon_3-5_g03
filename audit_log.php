<?php
require 'auth_user.php';

if($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

$search_date = '';
$has_searched = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_date = trim($_POST['audit_log_date'] ?? '');
    if ($search_date !== '') {
        $has_searched = true;
    }
}
?>

<header>
    <img src="" alt="Az Preston Hall's logo">
</header>
<?php include 'navbar_admin.php'; ?>

<div class="page">
    <h1>Audit Logs</h1>
    
    <section class="card narrow">
        <p>Enter the date to view audit logs:</p>
        <form method="post" autocomplete="off">
            <div class="form-row">
                <label for="audit_log_date">Date:</label>
                <input type="date" id="audit_log_date" name="audit_log_date" value="<?= h($search_date) ?>" required>
            </div>

            <?php if ($has_searched): ?>
                <p class="msg msg-success">Showing logs for: <?= h($search_date) ?></p>
            <?php endif; ?>
            
            <div class="actions">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
    </section>

    <?php if ($has_searched): ?>
    <section class="card">
        <h2>Audit Logs for <?= h($search_date) ?></h2>
        
        <div class="log-entry">
            <p>[Security Username] signed in [Guest Name] at [Timestamp]</p>
            <p>[Guest Name] with accompanying resident, [resident name] signed in at [Timestamp]</p>
            <p>[Security Username] signed out [Guest Name] at [Timestamp]</p>
            <p>[Guest Name] with accompanying resident, [resident name] signed out at [Timestamp]</p>
            
            <p>[Admin Username] edited resident information for [resident name] at [Timestamp]</p>

            <p class="msg msg-error">[Security Username] signed out [Guest Name] at [Timestamp]</p>
            <p class="msg msg-error">[Guest Name] with accompanying resident, [resident name] signed out at [Timestamp]</p>

            <p>[Admin Username] removed resident [resident name] at [Timestamp]</p>
        </div>
    </section>
    <?php endif; ?>
</div>

</body>

</html>

