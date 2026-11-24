<?php
require 'auth_user.php';
require 'sample_data.php';
if ($_SESSION['role'] !== 'admin') { http_response_code(403); exit('Access denied.'); }

$guestCount = count(array_filter($visits, fn($v) => $v['out'] === null));
$logs = [
    ['09:15 AM', 'SecurityTest', 'signed in guest Tom Reid'],
    ['09:02 AM', 'AdminTest', 'viewed visit history'],
    ['08:30 AM', 'SecurityTest', 'signed out guest Kyle Brown'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Home</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="admin_pages.css">
</head>
<body>
<header><img src="" alt="Az Preston Hall's logo"></header>
<?php include 'navbar_admin.php'; ?>

<div class="page">
  <section class="welcome">
    <div>
      <h1>Welcome, <?= h($_SESSION['username']) ?>!</h1>
      <h2 class="muted">Today is <span id="clock"><?= date('l, F j, Y \a\t g:i A') ?></span></h2>
    </div>
    <a class="btn btn-danger" href="emergency_list.php">Generate Emergency Guest List</a>
  </section>

  <section class="card">
    <p class="muted">Guests currently in the hall</p>
    <span class="stat-number"><?= $guestCount ?></span>
    <a href="active_guests.php">View active guest list &rarr;</a>
  </section>

  <section class="card">
    <h3>Audit Log &ndash; <?= date('F j, Y') ?></h3>
    <ul class="log-list">
      <?php foreach ($logs as $l): ?>
        <li><span class="log-time"><?= h($l[0]) ?></span><strong><?= h($l[1]) ?></strong> <?= h($l[2]) ?></li>
      <?php endforeach; ?>
    </ul>
    <p><a href="audit_log.php">View logs for another date &rarr;</a></p>
  </section>
</div>

<script>
  setInterval(function () {
    var d = new Date();
    document.getElementById('clock').textContent =
      d.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' }) +
      ' at ' + d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
  }, 1000);
</script>
</body>
</html>