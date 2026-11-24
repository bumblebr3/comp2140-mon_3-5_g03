<?php
require 'auth_user.php';
require 'sample_data.php';
if ($_SESSION['role'] !== 'admin') { http_response_code(403); exit('Access denied.'); }

$f = [
    'rid'  => trim($_GET['rid'] ?? ''),
    'gn'   => trim($_GET['gn'] ?? ''),
    'rn'   => trim($_GET['rn'] ?? ''),
    'date' => trim($_GET['date'] ?? ''),
];
$searched = isset($_GET['search']) || isset($_GET['export']);
$error = '';
$rows = null;

if ($searched) {
    if (!array_filter($f)) {
        $error = 'Please enter at least one search value.';
    } elseif ($f['rid'] !== '' && !preg_match('/^[A-Za-z0-9-]{1,20}$/', $f['rid'])) {
        $error = 'Resident ID may only contain letters, numbers and dashes.';
    } else {
        $rows = [];
        foreach ($visits as $v) {
            if ($v['out'] === null || !isset($residents[$v['rid']])) continue;   // completed visits only
            $r = $residents[$v['rid']];
            if ($f['rid'] !== '' && $v['rid'] !== $f['rid']) continue;
            if ($f['gn'] !== '' && stripos($v['guest'], $f['gn']) === false) continue;
            if ($f['rn'] !== '' && stripos(fullname($r), $f['rn']) === false) continue;
            if ($f['date'] !== '' && substr($v['in'], 0, 10) !== $f['date']) continue;
            $rows[] = ['guest' => $v['guest'], 'res' => fullname($r), 'rid' => $v['rid'], 'in' => $v['in'], 'out' => $v['out']];
        }

        if (isset($_GET['export']) && $rows) {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="visit_history.csv"');
            $o = fopen('php://output', 'w');
            fputcsv($o, ['Guest Name', 'Resident Name', 'Resident ID', 'Sign In', 'Sign Out']);
            foreach ($rows as $r) fputcsv($o, [$r['guest'], $r['res'], "\t" . $r['rid'], $r['in'], $r['out']]);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Visit History</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="admin_pages.css">
</head>
<body>
<header><img src="" alt="Az Preston Hall's logo"></header>
<?php include 'navbar_admin.php'; ?>

<div class="page">
  <h1>Visit History</h1>
  <section class="card">
    <p>Enter the date, resident ID number, name of guest or name of resident you want to see the visit history of:</p>
    <form method="get" class="search-grid" autocomplete="off">
      <div><label for="rid">Resident ID:</label><input type="text" id="rid" name="rid" value="<?= h($f['rid']) ?>"></div>
      <div><label for="gn">Guest Name:</label><input type="text" id="gn" name="gn" value="<?= h($f['gn']) ?>"></div>
      <div><label for="rn">Resident Name:</label><input type="text" id="rn" name="rn" value="<?= h($f['rn']) ?>"></div>
      <div><label for="date">Date:</label><input type="date" id="date" name="date" value="<?= h($f['date']) ?>"></div>
      <div><button type="submit" name="search" value="1" class="btn btn-primary">Search</button></div>
    </form>
    <?php if ($error): ?><p class="msg msg-error"><?= h($error) ?></p><?php endif; ?>
  </section>

  <?php if ($rows !== null): ?>
  <section class="card">
    <?php if (!$rows): ?>
      <p class="msg msg-error">No records found.</p>
    <?php else: ?>
      <div class="table-head">
        <p class="muted"><?= count($rows) ?> completed visit<?= count($rows) === 1 ? '' : 's' ?> found</p>
        <a class="btn btn-secondary" href="?<?= h(http_build_query($f + ['export' => 1])) ?>">Export to Excel</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Guest Name</th><th>Resident Name</th><th>Resident ID</th><th>Date</th><th>Sign In</th><th>Sign Out</th></tr></thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
            <tr>
              <td><?= h($r['guest']) ?></td><td><?= h($r['res']) ?></td><td><?= h($r['rid']) ?></td>
              <td><?= date('d M Y', strtotime($r['in'])) ?></td>
              <td><?= date('g:i A', strtotime($r['in'])) ?></td>
              <td><?= date('g:i A', strtotime($r['out'])) ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>
  <?php endif; ?>
</div>
</body>
</html>