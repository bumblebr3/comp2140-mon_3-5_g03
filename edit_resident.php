
<?php
/* Administrator-only page for finding an active resident
 * and updating their details. The resident ID number is kept unchanged.
 */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('admin');

$pdo = db();
$error = '';
$resident = null;

// Find the active resident by ID number.
$idNumber = trim($_GET['id'] ?? $_POST['id_number'] ?? '');

if ($idNumber !== '') {
    $stmt = $pdo->prepare(
        'SELECT *
         FROM residents
         WHERE id_number = ? AND is_active = 1'
    );

    $stmt->execute([$idNumber]);
    $resident = $stmt->fetch() ?: null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    if (!$resident) {
        $error = 'No active resident was found with that ID number.';

    } else {
        try {
            $first = trim($_POST['first_name'] ?? '');
            $last = trim($_POST['last_name'] ?? '');
            $room = trim($_POST['room_number'] ?? '');

            $phone = trim($_POST['contact_number'] ?? '');
            $email = trim($_POST['email'] ?? '');

            if ($first === '' || $last === '' || $room === '') {
                throw new RuntimeException(
                    'First name, last name and room number are required.'
                );
            }

            if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Enter a valid email address.');
            }

            $sql = 'UPDATE residents
                    SET first_name = ?,
                        last_name = ?,
                        room_number = ?,
                        contact_number = ?,
                        email = ?
                    WHERE id = ?';

            $values = [
                $first,
                $last,
                $room,
                $phone ?: null,
                $email ?: null,
                $resident['id']
            ];

            $pdo->prepare($sql)->execute($values);

            audit_log(
                'resident_edited',
                $first . ' ' . $last . ' (ID ' . $idNumber . ') edited.'
            );

            flash('success', 'Resident information has been updated.');

            redirect('edit_resident.php?id=' . urlencode($idNumber));

        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

$pageTitle = 'Edit Resident';

include __DIR__ . '/includes/layout_top.php';
?>

<h1>Edit Resident Information</h1>

<section class="card form-card">

    <form method="get" class="inline-form">
        <div>
            <label for="id">Enter resident ID number</label>
            <input
                id="id"
                name="id"
                value="<?= h($idNumber) ?>"
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

    <?php if ($idNumber !== '' && !$resident && !$error): ?>
        <p class="notice error">No active resident was found.</p>
    <?php endif; ?>

    <?php if ($resident): ?>
        <form method="post">
            <?= csrf_field() ?>

            <input
                type="hidden"
                name="id_number"
                value="<?= h($resident['id_number']) ?>"
            >

            <label>ID Number (cannot be changed)</label>
            <input
                value="<?= h($resident['id_number']) ?>"
                readonly
            >

            <div class="two-col">
                <div>
                    <label>First Name</label>
                    <input
                        name="first_name"
                        value="<?= h($resident['first_name']) ?>"
                        required
                    >
                </div>

                <div>
                    <label>Last Name</label>
                    <input
                        name="last_name"
                        value="<?= h($resident['last_name']) ?>"
                        required
                    >
                </div>
            </div>

            <label>Room Number</label>
            <input
                name="room_number"
                value="<?= h($resident['room_number']) ?>"
                required
            >

            <div class="two-col">
                <div>
                    <label>Contact Number</label>
                    <input
                        name="contact_number"
                        value="<?= h($resident['contact_number']) ?>"
                    >
                </div>

                <div>
                    <label>Email Address</label>
                    <input
                        type="email"
                        name="email"
                        value="<?= h($resident['email']) ?>"
                    >
                </div>
            </div>

            <button class="btn primary" type="submit">
                Save Changes
            </button>
        </form>
    <?php endif; ?>

</section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
