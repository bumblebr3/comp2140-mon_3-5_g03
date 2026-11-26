
<?php
/* Administrator-only page that removes a resident from the active list
 * by marking the record inactive; residents with active visits cannot be removed.
 */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('admin');

$pdo = db();
$error = '';
$resident = null;

// Look up the requested resident while excluding records that are already inactive.
$id = trim($_REQUEST['resident_id'] ?? '');

if ($id !== '') {
    $stmt = $pdo->prepare(
        'SELECT *
         FROM residents
         WHERE id_number = ? AND is_active = 1'
    );

    $stmt->execute([$id]);
    $resident = $stmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (!$resident) {
        $error = 'No active resident was found with that ID number.';

    } elseif (($_POST['action'] ?? '') === 'remove') {

        // Do not deactivate a resident while one of their guests is still signed in.
        $check = $pdo->prepare(
            'SELECT COUNT(*)
             FROM visits
             WHERE resident_id = ? AND sign_out_at IS NULL'
        );

        $check->execute([$resident['id']]);

        if ((int)$check->fetchColumn() > 0) {
            $error = 'This resident still has a guest signed in. Sign the guest out first.';

        } else {
            $pdo->prepare(
                'UPDATE residents
                 SET is_active = 0
                 WHERE id = ?'
            )->execute([$resident['id']]);

            audit_log(
                'resident_removed',
                full_name($resident) . ' (ID ' . $id . ') removed.'
            );

            flash(
                'success',
                'Resident has been removed from the active resident list.'
            );

            redirect('remove_resident.php');
        }
    }
}

$pageTitle = 'Remove Resident';

include __DIR__ . '/includes/layout_top.php';
?>

<h1>Remove Resident</h1>

<section class="card form-card">

    <form method="get" class="inline-form">
        <div>
            <label for="resident_id">
                Enter the ID number of the resident
            </label>

            <input
                id="resident_id"
                name="resident_id"
                value="<?= h($id) ?>"
                required
            >
        </div>

        <button class="btn primary" type="submit">
            Search
        </button>
    </form>

    <?php if ($error): ?>
        <p class="notice error"><?= h($error) ?></p>
    <?php endif; ?>

    <?php if ($id !== '' && !$resident && !$error): ?>
        <p class="notice error">
            No active resident was found with that ID number.
        </p>
    <?php endif; ?>

    <?php if ($resident): ?>

        <div class="record-summary">

            <p>
                <strong>ID Number:</strong>
                <?= h($resident['id_number']) ?>
            </p>

            <p>
                <strong>First Name:</strong>
                <?= h($resident['first_name']) ?>
            </p>

            <p>
                <strong>Last Name:</strong>
                <?= h($resident['last_name']) ?>
            </p>

            <p>
                <strong>Room Number:</strong>
                <?= h($resident['room_number']) ?>
            </p>

        </div>

        <form
            method="post"
            onsubmit="return confirm('Remove this resident?')"
        >
            <?= csrf_field() ?>

            <input
                type="hidden"
                name="resident_id"
                value="<?= h($id) ?>"
            >

            <input
                type="hidden"
                name="action"
                value="remove"
            >

            <button class="btn danger" type="submit">
                Remove Resident
            </button>
        </form>

    <?php endif; ?>

</section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
