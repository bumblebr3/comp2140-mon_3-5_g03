<?php
require 'auth_user.php';
require 'sample_data.php';
if ($_SESSION['role'] !== 'admin') { http_response_code(403); exit('Access denied.'); }

$error = $success = '';
$resident = null;
$confirming = false;
$id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'search';
    $id = trim($_POST['resident_id'] ?? '');

    if ($id === '') {
        $error = 'Please enter the ID number of the resident you want to remove.';
    } elseif (!preg_match('/^[A-Za-z0-9-]{1,20}$/', $id)) {
        $error = 'The ID number may only contain letters, numbers and dashes.';
    } elseif (!isset($residents[$id]) || in_array($id, $removed, true)) {
        $error = 'No active resident was found with that ID number.';
    } else {
        $resident = $residents[$id];
        if ($action === 'confirm') {
            $confirming = true;
        } elseif ($action === 'remove') {
            $hasGuest = array_filter($visits, fn($v) => $v['rid'] === $id && $v['out'] === null);
            if ($hasGuest) {
                $error = 'This resident still has a guest signed in. Sign the guest out first.';
            } else {
                $_SESSION['removed'][] = $id;
                $success = 'Resident Successfully Removed!';
                $resident = null;
                $id = '';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Remove Resident</title>
  <link rel="stylesheet" href="styles.css">
  <link rel="stylesheet" href="admin_pages.css">
</head>
<body>
<header><img src="" alt="Az Preston Hall's logo"></header>
<?php include 'navbar_admin.php'; ?>

<div class="page">
  <h1>Remove Resident</h1>
  <section class="card narrow">
    <p>Enter the ID number of the resident you want to remove:</p>
    <form method="post" autocomplete="off">
      <div class="form-row">
        <label for="resident_id">ID Number:</label>
        <input type="text" id="resident_id" name="resident_id" maxlength="20" value="<?= h($id) ?>" <?= $resident ? 'readonly' : '' ?> required>
      </div>
      <div class="form-row"><label>First Name:</label><input type="text" value="<?= h($resident['first'] ?? '') ?>" readonly></div>
      <div class="form-row"><label>Last Name:</label><input type="text" value="<?= h($resident['last'] ?? '') ?>" readonly></div>
      <div class="form-row"><label>Room Number:</label><input type="text" value="<?= h($resident['room'] ?? '') ?>" readonly></div>

      <?php if ($error): ?><p class="msg msg-error"><?= h($error) ?></p><?php endif; ?>
      <?php if ($success): ?><p class="msg msg-success"><?= h($success) ?></p><?php endif; ?>
      <?php if ($resident && !$error): ?><p class="msg msg-success">Resident Found</p><?php endif; ?>

      <div class="actions">
        <?php if ($resident): ?>
          <a class="btn btn-secondary" href="remove_resident.php">Cancel</a>
          <button type="submit" name="action" value="confirm" class="btn btn-danger">Remove Resident</button>
        <?php else: ?>
          <button type="submit" name="action" value="search" class="btn btn-primary">Search</button>
        <?php endif; ?>
      </div>
    </form>
  </section>
</div>

<?php if ($confirming): ?>
<div class="modal">
  <div class="modal-box">
    <h2>Remove this resident?</h2>
    <p><strong><?= h(fullname($resident)) ?></strong> (ID <?= h($id) ?>, Room <?= h($resident['room']) ?>) will no longer appear in the active resident list.</p>
    <form method="post" class="actions">
      <input type="hidden" name="resident_id" value="<?= h($id) ?>">
      <a class="btn btn-secondary" href="remove_resident.php">No, keep resident</a>
      <button type="submit" name="action" value="remove" class="btn btn-danger">Yes, remove</button>
    </form>
  </div>
</div>
<?php endif; ?>
</body>
</html>