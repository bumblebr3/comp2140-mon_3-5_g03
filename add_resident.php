
<?php
/* Administrator-only form for creating a resident record
 * and validating contact details.
 */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('admin');

$error = '';

// Process and validate the new resident form.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $idNumber = trim($_POST['id_number'] ?? '');
    $first = trim($_POST['first_name'] ?? '');
    $last = trim($_POST['last_name'] ?? '');
    $room = trim($_POST['room_number'] ?? '');
    $phone = trim($_POST['contact_number'] ?? '');
    $email = trim($_POST['email'] ?? '');

    try {
        if (
            $idNumber === '' ||
            $first === '' ||
            $last === '' ||
            $room === ''
        ) {
            throw new RuntimeException(
                'ID number, first name, last name and room number are required.'
            );
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Enter a valid email address.');
        }

        $stmt = db()->prepare(
            'INSERT INTO residents (
                id_number,
                first_name,
                last_name,
                room_number,
                contact_number,
                email
            ) VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stmt->execute([
            $idNumber,
            $first,
            $last,
            $room,
            $phone ?: null,
            $email ?: null
        ]);

        audit_log(
            'resident_added',
            full_name([
                'first_name' => $first,
                'last_name' => $last
            ]) . ' (ID ' . $idNumber . ') added.'
        );

        flash('success', 'Resident has been added successfully.');

        redirect('add_resident.php');

    } catch (Throwable $e) {
        $error = $e instanceof PDOException
            ? 'A resident with that ID number already exists.'
            : $e->getMessage();
    }
}

$pageTitle = 'Add Resident';

include __DIR__ . '/includes/layout_top.php';
?>

<h1>Add A New Resident</h1>

<section class="card form-card">

    <?php if ($error): ?>
        <p class="notice error"><?= h($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <?= csrf_field() ?>

        <label for="id_number">ID Number</label>
        <input
            id="id_number"
            name="id_number"
            required
            maxlength="30"
        >

        <div class="two-col">
            <div>
                <label for="first_name">First Name</label>
                <input
                    id="first_name"
                    name="first_name"
                    required
                >
            </div>

            <div>
                <label for="last_name">Last Name</label>
                <input
                    id="last_name"
                    name="last_name"
                    required
                >
            </div>
        </div>

        <label for="room_number">Room Number</label>
        <input
            id="room_number"
            name="room_number"
            required
        >

        <div class="two-col">
            <div>
                <label for="contact_number">Contact Number</label>
                <input
                    id="contact_number"
                    name="contact_number"
                >
            </div>

            <div>
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                >
            </div>
        </div>

        <button class="btn primary" type="submit">
            Add Resident
        </button>
    </form>

</section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
