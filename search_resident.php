<?php
/* Searches active resident records. The fields that can be searched and displayed depend on whether the signed-in user is an Administrator or Security. */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('admin', 'security');

$user = current_user();

// Build the search terms once; Administrators may also search by ID and room number.
$search = trim($_GET['search'] ?? '');

$like = '%' . $search . '%';

$sql = 'SELECT id, id_number, first_name, last_name, room_number, contact_number, email FROM residents WHERE is_active=1';

$params = [];

if ($search !== '') {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR CONCAT_WS(' ', first_name, last_name) LIKE ?";
    $params = [$like, $like, $like];

    if ($user['role'] === 'admin') {
        $sql .= ' OR id_number LIKE ? OR room_number LIKE ?';
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= ')';
}


$sql .= ' ORDER BY first_name, last_name';

$stmt=db()->prepare($sql); $stmt->execute($params); $rows=$stmt->fetchAll();

audit_log('resident_search_viewed', $search === '' ? 'Resident search page viewed.' : 'Resident search: ' . $search);

$pageTitle='Search Residents'; include __DIR__ . '/includes/layout_top.php';

?>

<h1>Search Residents</h1>

<section class="card">

  <form method="get" class="inline-form">
    <div>
      <label for="search">Enter resident name<?= $user['role']==='admin'?' or ID number':'' ?>:</label>
      <input id="search" name="search" value="<?= h($search) ?>" placeholder="Search residents">
    </div>
    <button class="btn primary" type="submit">Search</button>
  </form>

  <h2>Resident Information</h2>

  <?php if(!$rows): ?>
    <p class="muted">No residents found.</p>
    <?php else: ?>

  <div class="table-wrap">
    <table><thead><tr><th>First Name</th><th>Last Name</th><?php if($user['role']==='admin'): ?><th>ID Number</th><th>Room Number</th><th>Contact</th><th>Email</th><?php endif; ?></tr></thead><tbody><?php foreach($rows as $r): ?><tr><td><?= h($r['first_name']) ?></td><td><?= h($r['last_name']) ?></td><?php if($user['role']==='admin'): ?><td><?= h($r['id_number']) ?></td><td><?= h($r['room_number']) ?></td><td><?= h($r['contact_number']) ?></td><td><?= h($r['email']) ?></td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
