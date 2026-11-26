
<?php
/*
 * Displays guests whose visits have not been signed out.
 * Administrators and Security can view the list;
 * only Security can sign a guest out.
 */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('admin', 'security');

$pdo = db();
$user = current_user();
$error = '';

// Handle guest sign-out.
// Only Security is permitted to perform this action.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_role('security');
    verify_csrf();

    $visitId = (int)($_POST['visit_id'] ?? 0);

    $stmt = $pdo->prepare(
        'SELECT v.id, g.full_name
         FROM visits v
         JOIN guests g ON g.id = v.guest_id
         WHERE v.id = ? AND v.sign_out_at IS NULL'
    );

    $stmt->execute([$visitId]);
    $visit = $stmt->fetch();

    if ($visit) {
        $pdo->prepare(
            'UPDATE visits
             SET sign_out_at = NOW(), sign_out_by = ?
             WHERE id = ? AND sign_out_at IS NULL'
        )->execute([$user['id'], $visitId]);

        audit_log(
            'guest_sign_out',
            $visit['full_name'] . ' signed out.'
        );

        flash(
            'success',
            $visit['full_name'] . ' has been signed out.'
        );

        redirect('active_guests.php');
    }

    $error = 'The active visit could not be found.';
}

// Search by guest name or resident name.
$search = trim($_GET['search'] ?? '');

$sql = 'SELECT
            v.id,
            g.full_name AS guest_name,
            g.id_number,
            r.first_name,
            r.last_name,
            v.sign_in_at
        FROM visits v
        JOIN guests g ON g.id = v.guest_id
        JOIN residents r ON r.id = v.resident_id
        WHERE v.sign_out_at IS NULL';

$params = [];

if ($search !== '') {
    $sql .= ' AND (
        g.full_name LIKE ?
        OR r.first_name LIKE ?
        OR r.last_name LIKE ?
        OR CONCAT(r.first_name, " ", r.last_name) LIKE ?
    )';

    $like = '%' . $search . '%';

    $params = [
        $like,
        $like,
        $like,
        $like
    ];
}

// Display the most recently signed-in guests first.
$sql .= ' ORDER BY v.sign_in_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll();

// Record list activity.
audit_log(
    'active_guest_list_viewed',
    $search === ''
        ? 'Active guest list viewed.'
        : 'Active guest list searched: ' . $search
);

$pageTitle = 'Active Guests';

include __DIR__ . '/includes/layout_top.php';
?>

<h1>Active Guests</h1>

<section class="card">

    <!-- Search Form -->
    <form method="get" class="inline-form">

        <div>
            <label for="search">
                Search by guest or resident name:
            </label>

            <input
                id="search"
                name="search"
                value="<?= h($search) ?>"
                placeholder="Enter a guest or resident name"
            >
        </div>

        <button class="btn primary" type="submit">
            Search
        </button>

        <a class="btn secondary" href="active_guests.php">
            Clear
        </a>

    </form>

    <!-- Error Message -->
    <?php if ($error): ?>
        <p class="notice error">
            <?= h($error) ?>
        </p>
    <?php endif; ?>

    <!-- Section Heading -->
    <div class="section-heading">

        <h2>Current Guests</h2>

        <?php if ($user['role'] === 'security'): ?>
            <a href="guest_sign_in.php" class="btn primary">
                Sign In Guest
            </a>
        <?php endif; ?>

    </div>

    <!-- Guest Table -->
    <?php if (!$rows): ?>

        <p class="muted">
            No active guests found.
        </p>

    <?php else: ?>

        <div class="table-wrap">

            <table>

                <thead>
                    <tr>
                        <th>Guest Name</th>
                        <th>Resident Name</th>
                        <th>Sign In Time</th>
                        <th>Date Signed In</th>

                        <?php if ($user['role'] === 'security'): ?>
                            <th>Action</th>
                        <?php endif; ?>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($rows as $row): ?>

                        <tr>

                            <td>
                                <?= h($row['guest_name']) ?>
                            </td>

                            <td>
                                <?= h(
                                    $row['first_name'] . ' ' .
                                    $row['last_name']
                                ) ?>
                            </td>

                            <td>
                                <?= date(
                                    'g:i A',
                                    strtotime($row['sign_in_at'])
                                ) ?>
                            </td>

                            <td>
                                <?= date(
                                    'F j, Y',
                                    strtotime($row['sign_in_at'])
                                ) ?>
                            </td>

                            <?php if ($user['role'] === 'security'): ?>

                                <td>

                                    <form
                                        method="post"
                                        onsubmit="return confirm('Sign out this guest?')"
                                    >

                                        <?= csrf_field() ?>

                                        <input
                                            type="hidden"
                                            name="visit_id"
                                            value="<?= (int)$row['id'] ?>"
                                        >

                                        <button
                                            class="btn small danger"
                                            type="submit"
                                        >
                                            Sign Out
                                        </button>

                                    </form>

                                </td>

                            <?php endif; ?>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
