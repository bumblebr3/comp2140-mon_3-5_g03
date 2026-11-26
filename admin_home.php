
<?php
/* Builds the administrator dashboard using current guest and resident
 * totals plus the latest audit-log entries.
 */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('admin');

// Gather the summary values displayed in the administrator dashboard.
$pdo = db();

$activeCount = (int)$pdo->query(
    'SELECT COUNT(*)
     FROM visits
     WHERE sign_out_at IS NULL'
)->fetchColumn();

$residentCount = (int)$pdo->query(
    'SELECT COUNT(*)
     FROM residents
     WHERE is_active = 1'
)->fetchColumn();

audit_log(
    'admin_dashboard_viewed',
    'Administrator dashboard viewed.'
);

// Show a short, recent sample of the audit history on the dashboard.
$logs = $pdo->query(
    'SELECT username, action, details, created_at
     FROM audit_logs
     ORDER BY created_at DESC
     LIMIT 8'
)->fetchAll();

$pageTitle = 'Administrator Dashboard';

include __DIR__ . '/includes/layout_top.php';
?>

<section class="welcome-row">
    <div>
        <h1>Welcome, <?= h($_SESSION['username']) ?>!</h1>

        <p class="muted">
            Today is <?= date('l, F j, Y') ?> at <?= date('g:i A') ?>
        </p>
    </div>

    <a class="btn danger" href="emergency_list.php">
        Generate Emergency Guest List
    </a>
</section>

<div class="stats-grid">

    <section class="card stat">
        <span class="muted">Guests currently in the hall</span>

        <strong><?= $activeCount ?></strong>

        <a href="active_guests.php">View active guests →</a>
    </section>

    <section class="card stat">
        <span class="muted">Active residents</span>

        <strong><?= $residentCount ?></strong>

        <a href="search_resident.php">Search residents →</a>
    </section>

    <section class="card stat">
        <span class="muted">System users</span>

        <strong>
            <?= (int)$pdo->query(
                'SELECT COUNT(*)
                 FROM users
                 WHERE is_active = 1'
            )->fetchColumn() ?>
        </strong>

        <a href="adduser.php">Manage users →</a>
    </section>

</div>

<section class="card">

    <div class="section-heading">
        <h2>Recent Audit Activity</h2>

        <a href="audit_log.php">View all logs →</a>
    </div>

    <?php if (!$logs): ?>
        <p class="muted">No activity has been recorded yet.</p>

    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>User</th>
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

                            <td><?= h($log['action']) ?></td>

                            <td><?= h($log['details']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
