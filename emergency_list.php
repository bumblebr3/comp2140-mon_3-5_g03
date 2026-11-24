<?php
require 'auth_user.php';
require 'sample_data.php';

$isAdmin = $_SESSION['role'] === 'admin';
$types   = ['Earthquake', 'Fire', 'Hurricane', 'Flood', 'Other'];
$type    = trim($_GET['type'] ?? '');
$asAt    = trim($_GET['as_at'] ?? '');
$error   = '';
$guests  = null;

if (isset($_GET['generate']) || isset($_GET['export'])) {
    $moment = time();

    if (!in_array($type, $types, true)) {
        $error = 'Please select an emergency type.';
    } elseif ($asAt !== '') {
        $moment = strtotime($asAt);
        if ($moment === false) {
            $error = 'Please enter a valid date and time.';
        } elseif ($moment > time()) {
            $error = 'The date and time cannot be in the future.';
        }
    }

    if ($error === '') {
        $guests = [];
        foreach ($visits as $v) {
            $in  = strtotime($v['in']);
            $out = $v['out'] ? strtotime($v['out']) : null;
            if ($in <= $moment && ($out === null || $out > $moment)) {
                $r = $residents[$v['rid']];
                $guests[] = [
                    'guest' => $v['guest'],
                    'res'   => fullname($r),
                    'phone' => $v['phone'] ?: $r['phone'],
                    'in'    => $v['in'],
                ];
            }
        }

        if (isset($_GET['export'])) {
            if (!$isAdmin) { http_response_code(403); exit('Only administrators can download the list.'); }
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="emergency_guest_list.csv"');
            $o = fopen('php://output', 'w');
            fputcsv($o, ['Guest Name', 'Accompanying Resident', 'Contact Number', 'Sign In Time']);
            foreach ($guests as $g) {
                fputcsv($o, [$g['guest'], $g['res'], "\t" . $g['phone'], $g['in']]);
            }
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
  <title>Emergency Guest List</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="admin_pages.css">
</head>
<body>
<header><img src="" alt="Az Preston Hall's logo"></header>
<?php include $isAdmin ? 'navbar_admin.php' : 'navbar_security.php'; ?>

<div class="page">
  <h1>Emergency Guest List</h1>
  <section class="card">
    <form method="get" class="inline-form" autocomplete="off">
      <div>
        <label for="type">Emergency Type</label>
        <select id="type" name="type" required>
          <option value="" disabled <?= $type === '' ? 'selected' : '' ?>>Select&hellip;</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= h($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= h($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="as_at">As at (optional)</label>
        <input type="datetime-local" id="as_at" name="as_at" value="<?= h($asAt) ?>">
      </div>
      <button type="submit" name="generate" value="1" class="btn btn-danger">Generate</button>
    </form>
    <?php if ($error): ?><p class="msg msg-error"><?= h($error) ?></p><?php endif; ?>
  </section>

  <?php if ($guests !== null): ?>
  <section class="card">
    <div class="table-head">
      <p><strong><?= count($guests) ?></strong> guest<?= count($guests) === 1 ? '' : 's' ?> recorded in the hall (<?= h($type) ?>)</p>
      <?php if ($isAdmin && $guests): ?>
        <a class="btn btn-secondary" href="?<?= h(http_build_query(['type' => $type, 'as_at' => $asAt, 'export' => 1])) ?>">Download</a>
      <?php endif; ?>
    </div>
    <?php if (!$guests): ?>
      <p class="msg msg-success">No records found. You can select another date or time above.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Guest Name</th><th>Accompanying Resident</th><th>Contact Number</th><th>Sign In Time</th></tr></thead>
          <tbody>
          <?php foreach ($guests as $g): ?>
            <tr>
              <td><?= h($g['guest']) ?></td><td><?= h($g['res']) ?></td><td><?= h($g['phone']) ?></td>
              <td><?= date('d M Y, g:i A', strtotime($g['in'])) ?></td>
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