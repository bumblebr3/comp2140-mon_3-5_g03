
<?php
/* Displays audit activity. Administrators can filter all users by date, while Security sees only their own activity for the current day. */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('admin', 'security');

// The date filter is available to Administrators; Security is restricted
// to its own current-day entries.
$user = current_user();
$date = trim($_GET['date'] ?? date('Y-m-d'));

if ($user['role'] === 'admin') {
    $stmt = db()->prepare(
        'SELECT username, action, details, created_at
         FROM audit_logs
         WHERE DATE(created_at) = ?
         ORDER BY created_at DESC'
    );

    $stmt->execute([$date]);
} else {
    $stmt = db()->prepare(
        'SELECT username, action, details, created_at
         FROM audit_logs
         WHERE user_id = ?
         AND DATE(created_at) = CURDATE()
         ORDER BY created_at DESC'
    );

    $stmt->execute([$user['id']]);
}

$logs = $stmt->fetchAll();

audit_log('audit_log_viewed', 'Audit log viewed.');

$pageTitle = 'Audit Log';

include __DIR__ . '/includes/layout_top.php';
?>

<h1>Audit Log</h1>

<section class="card">

    <?php if ($user['role'] === 'admin'): ?>

        <form method="get" class="inline-form">
            <div>
                <label for="date">Select Audit Log Date</label>
                <input
                    type="date"
                    id="date"
                    name="date"
                    value="<?= h($date) ?>"
                >
            </div>

            <button class="btn primary" type="submit">Search</button>
        </form>

        <p class="muted">
            Administrator logs include activity by security and administrators,
            including unsuccessful login attempts.
        </p>

    <?php else: ?>

        <p class="muted">
            Your audit log shows your actions for the current day.
        </p>

    <?php endif; ?>

    <?php if (!$logs): ?>

        <p>No audit activity found.</p>

    <?php else: ?>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date and Time</th>
                        <th>Username</th>
                        <th>Action</th>
                        <th>Details</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td>
                                <?= date('d M Y, g:i A', strtotime($log['created_at'])) ?>
                            </td>

                            <td><?= h($log['username']) ?></td>

                            <td>
                                <?php if ($log['action'] === 'login_failed'): ?>
                                    <strong>Failed Login</strong>
                                <?php else: ?>
                                    <?= h($log['action']) ?>
                                <?php endif; ?>
                            </td>

                            <td><?= h($log['details']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>

</section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
