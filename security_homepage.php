
<?php
/* Shows the Security dashboard and the guests who are currently signed in. */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('security');

audit_log('security_dashboard_viewed', 'Security dashboard viewed.');

// Retrieve the current visits for the live guest summary.
$active = db()->query(
    'SELECT
        g.full_name,
        r.first_name,
        r.last_name,
        v.sign_in_at
    FROM visits v
    JOIN guests g ON g.id = v.guest_id
    JOIN residents r ON r.id = v.resident_id
    WHERE v.sign_out_at IS NULL
    ORDER BY v.sign_in_at DESC'
)->fetchAll();

$pageTitle = 'Security Dashboard';

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

<section class="card">
    <div class="section-heading">
        <h2>Active Guests</h2>

        <a class="btn primary" href="guest_sign_in.php">
            Sign In Guest
        </a>
    </div>

    <p class="stat-inline">
        <?= count($active) ?> guest(s) currently in the hall
    </p>

    <?php if (!$active): ?>
        <p class="muted">There are no active guests.</p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Guest Name</th>
                        <th>Resident Name</th>
                        <th>Sign In Time</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($active as $row): ?>
                        <tr>
                            <td>
                                <?= h($row['full_name']) ?>
                            </td>

                            <td>
                                <?= h($row['first_name'] . ' ' . $row['last_name']) ?>
                            </td>

                            <td>
                                <?= date('g:i A', strtotime($row['sign_in_at'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
