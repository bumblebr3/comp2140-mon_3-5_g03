
<?php
/* Security-only visitor registration form.
 * It validates the visit, reuses an existing guest record when possible,
 * and creates a new visit inside a database transaction.
 */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('security');

$pdo = db();

$residents = $pdo->query(
    'SELECT id, first_name, last_name
     FROM residents
     WHERE is_active = 1
     ORDER BY first_name, last_name'
)->fetchAll();

$error = '';

// Process submitted visitor details.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $residentId = (int)($_POST['resident_id'] ?? 0);
    $guestName = trim($_POST['guest_name'] ?? '');
    $guestId = trim($_POST['guest_id'] ?? '');
    $idType = trim($_POST['id_type'] ?? '');
    $phone = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $expected = trim($_POST['expected_departure_at'] ?? '');

    $validTypes = [
        'School ID',
        'Voters ID',
        'Drivers licence',
        'Other'
    ];

    try {
        if (
            $residentId < 1 ||
            $guestName === '' ||
            $guestId === '' ||
            !in_array($idType, $validTypes, true)
        ) {
            throw new RuntimeException(
                'Complete the resident, guest name, guest ID and ID type fields.'
            );
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Enter a valid email address.');
        }

        if ($expected === '') {
            throw new RuntimeException(
                'Enter the expected departure date and time.'
            );
        }

        $timestamp = strtotime($expected);

        if ($timestamp === false) {
            throw new RuntimeException(
                'Enter a valid expected departure date and time.'
            );
        }

        $expectedSql = date('Y-m-d H:i:s', $timestamp);

        if ($timestamp <= time()) {
            throw new RuntimeException(
                'Expected departure must be in the future.'
            );
        }

        // Confirm that the selected resident is still active.
        $residentCheck = $pdo->prepare(
            'SELECT id
             FROM residents
             WHERE id = ? AND is_active = 1'
        );

        $residentCheck->execute([$residentId]);

        if (!$residentCheck->fetch()) {
            throw new RuntimeException(
                'The selected resident could not be found.'
            );
        }

        // Keep guest lookup/creation and visit creation together.
        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'SELECT id
             FROM guests
             WHERE id_number = ?
             FOR UPDATE'
        );

        $stmt->execute([$guestId]);
        $guest = $stmt->fetch();

        if ($guest) {
            $guestPk = (int)$guest['id'];

            $activeCheck = $pdo->prepare(
                'SELECT id
                 FROM visits
                 WHERE guest_id = ? AND sign_out_at IS NULL'
            );

            $activeCheck->execute([$guestPk]);

            if ($activeCheck->fetch()) {
                throw new RuntimeException(
                    'This guest already has an active visit.'
                );
            }

            // Update existing guest information.
            $update = $pdo->prepare(
                'UPDATE guests
                 SET full_name = ?,
                     id_type = ?,
                     contact_number = ?,
                     email = ?
                 WHERE id = ?'
            );

            $update->execute([
                $guestName,
                $idType,
                $phone ?: null,
                $email ?: null,
                $guestPk
            ]);

        } else {
            // Create a new guest record.
            $insert = $pdo->prepare(
                'INSERT INTO guests (
                    id_number,
                    full_name,
                    id_type,
                    contact_number,
                    email
                ) VALUES (?, ?, ?, ?, ?)'
            );

            $insert->execute([
                $guestId,
                $guestName,
                $idType,
                $phone ?: null,
                $email ?: null
            ]);

            $guestPk = (int)$pdo->lastInsertId();
        }

        // Create the visit record.
        $visit = $pdo->prepare(
            'INSERT INTO visits (
                guest_id,
                resident_id,
                sign_in_by,
                expected_departure_at
            ) VALUES (?, ?, ?, ?)'
        );

        $visit->execute([
            $guestPk,
            $residentId,
            $_SESSION['user_id'],
            $expectedSql
        ]);

        $pdo->commit();

        audit_log(
            'guest_sign_in',
            $guestName . ' signed in to visit resident ID ' . $residentId . '.'
        );

        flash('success', $guestName . ' has been signed in successfully.');

        redirect('security_homepage.php');

    } catch (Throwable $e) {
        // Undo database changes if the transaction fails.
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        $error = $e->getMessage();
    }
}

$pageTitle = 'Sign In Guest';

include __DIR__ . '/includes/layout_top.php';
?>

<section class="card form-card">

    <h1>Sign In Guest</h1>

    <p class="muted">
        Complete the two steps to register the guest.
    </p>

    <?php if ($error): ?>
        <p class="notice error"><?= h($error) ?></p>
    <?php endif; ?>

    <?php if (!$residents): ?>
        <p class="notice error">
            There are no active residents. Ask an administrator to add residents first.
        </p>

    <?php else: ?>

        <form
            id="guestForm"
            method="post"
            novalidate
        >

            <?= csrf_field() ?>

            <section class="form-section">

                <p class="step-label">
                    Page 1 of 2 · Guest and resident
                </p>

                <label for="resident_id">Accompanying Resident</label>

                <select id="resident_id" name="resident_id" required>
                    <option value="">Select resident</option>

                    <?php foreach ($residents as $resident): ?>
                        <option
                            value="<?= (int)$resident['id'] ?>"
                            <?= (int)($_POST['resident_id'] ?? 0) === (int)$resident['id'] ? 'selected' : '' ?>
                        >
                            <?= h($resident['first_name'] . ' ' . $resident['last_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="guest_name">Guest Full Name</label>
                <input
                    id="guest_name"
                    name="guest_name"
                    value="<?= h($_POST['guest_name'] ?? '') ?>"
                    required
                >

                <label for="guest_id">Guest ID Number</label>
                <input
                    id="guest_id"
                    name="guest_id"
                    value="<?= h($_POST['guest_id'] ?? '') ?>"
                    required
                >

                <label for="id_type">ID Type</label>

                <select id="id_type" name="id_type" required>
                    <option value="">Select ID type</option>

                    <?php foreach (['School ID', 'Voters ID', 'Drivers licence', 'Other'] as $t): ?>
                        <option
                            value="<?= h($t) ?>"
                            <?= ($_POST['id_type'] ?? '') === $t ? 'selected' : '' ?>
                        >
                            <?= h($t) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

            </section>

            <section class="form-section">

                <p class="step-label">
                    Page 2 of 2 · Contact and departure
                </p>

                <label for="contact_number">Contact Number</label>
                <input
                    id="contact_number"
                    name="contact_number"
                    required
                    value="<?= h($_POST['contact_number'] ?? '') ?>"
                >

                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    value="<?= h($_POST['email'] ?? '') ?>"
                >

                <label for="expected_departure_at">
                    Expected Departure Date and Time
                </label>

                <input
                    type="datetime-local"
                    id="expected_departure_at"
                    name="expected_departure_at"
                    value="<?= h($_POST['expected_departure_at'] ?? '') ?>"
                    required
                >

                <p class="small muted">
                    Reminder emails are attempted approximately one hour before
                    this time when an email address is available.
                </p>

            </section>

            <p id="formError" class="notice error" hidden></p>

            <div class="form-actions">

                <button
                    class="btn secondary"
                    type="button"
                    id="backBtn"
                >
                    Back
                </button>

                <button
                    class="btn primary"
                    type="button"
                    id="nextBtn"
                >
                    Next
                </button>

                <button
                    class="btn primary"
                    type="submit"
                    id="submitBtn"
                >
                    Sign In Guest
                </button>

            </div>

        </form>

    <?php endif; ?>

</section>

<script src="assets/js/guest-form.js"></script>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
