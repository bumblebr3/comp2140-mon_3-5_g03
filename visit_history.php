
<?php
/*
 * Administrator-only search and CSV export for completed visits,
 * with filters for resident ID, guest name, resident name, and sign-in date.
 */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('admin');

$f = [
    'resident_id'  => trim($_GET['resident_id'] ?? ''),
    'guest_name'   => trim($_GET['guest_name'] ?? ''),
    'resident_name' => trim($_GET['resident_name'] ?? ''),
    'date'         => trim($_GET['date'] ?? '')
];

// Only run the history query after the user submits a search or requests an export.
$searched = isset($_GET['search']) || isset($_GET['export']);

$rows = [];
$error = '';

if ($searched) {
    if (!array_filter($f)) {
        $error = 'Enter at least one search value.';
    } else {
        $sql = '
            SELECT
                v.id,
                g.full_name AS guest_name,
                g.id_number AS guest_id,
                g.id_type,
                r.first_name,
                r.last_name,
                r.id_number AS resident_id,
                v.sign_in_at,
                v.sign_out_at
            FROM visits v
            JOIN guests g ON g.id = v.guest_id
            JOIN residents r ON r.id = v.resident_id
            WHERE v.sign_out_at IS NOT NULL
        ';

        $params = [];

        if ($f['resident_id'] !== '') {
            $sql .= ' AND r.id_number LIKE ?';
            $params[] = '%' . $f['resident_id'] . '%';
        }

        if ($f['guest_name'] !== '') {
            $sql .= ' AND g.full_name LIKE ?';
            $params[] = '%' . $f['guest_name'] . '%';
        }

        if ($f['resident_name'] !== '') {
            $sql .= ' AND CONCAT(r.first_name, " ", r.last_name) LIKE ?';
            $params[] = '%' . $f['resident_name'] . '%';
        }

        if ($f['date'] !== '') {
            $sql .= ' AND DATE(v.sign_in_at) = ?';
            $params[] = $f['date'];
        }

        $sql .= ' ORDER BY v.sign_in_at DESC';

        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        $rows = $stmt->fetchAll();

        audit_log('visit_history_viewed', 'Visit history searched.');

        // Stream the filtered results as CSV instead of rendering the HTML page.
        if (isset($_GET['export'])) {
            header('Content-Type: text/csv; charset=UTF-8');
            header('Content-Disposition: attachment; filename="visit_history.csv"');

            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Visit ID',
                'Guest Name',
                'Guest ID',
                'ID Type',
                'Resident Name',
                'Resident ID',
                'Sign In',
                'Sign Out'
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['id'],
                    $r['guest_name'],
                    $r['guest_id'],
                    $r['id_type'],
                    $r['first_name'] . ' ' . $r['last_name'],
                    $r['resident_id'],
                    $r['sign_in_at'],
                    $r['sign_out_at']
                ]);
            }

            fclose($out);
            exit;
        }
    }
}

$pageTitle = 'Visit History';

include __DIR__ . '/includes/layout_top.php';
?>

<h1>Visit History</h1>

<section class="card">
    <p class="muted">
        Enter a date, resident ID number, guest name or resident name.
    </p>

    <form method="get" class="search-grid">
        <div>
            <label>Resident ID</label>
            <input
                name="resident_id"
                value="<?= h($f['resident_id']) ?>"
            >
        </div>

        <div>
            <label>Guest Name</label>
            <input
                name="guest_name"
                value="<?= h($f['guest_name']) ?>"
            >
        </div>

        <div>
            <label>Resident Name</label>
            <input
                name="resident_name"
                value="<?= h($f['resident_name']) ?>"
            >
        </div>

        <div>
            <label>Date</label>
            <input
                type="date"
                name="date"
                value="<?= h($f['date']) ?>"
            >
        </div>

        <button class="btn primary" name="search" value="1">
            Search
        </button>
    </form>

    <?php if ($error): ?>
        <p class="notice error">
            <?= h($error) ?>
        </p>
    <?php endif; ?>
</section>

<?php if ($searched && !$error): ?>
    <section class="card">
        <div class="section-heading">
            <h2>
                <?= count($rows) ?> completed visit(s)
            </h2>

            <?php if ($rows): ?>
                <a
                    class="btn secondary"
                    href="?<?= h(http_build_query($f + ['export' => 1])) ?>"
                >
                    Export CSV for Excel
                </a>
            <?php endif; ?>
        </div>

        <?php if (!$rows): ?>
            <p class="muted">No records found.</p>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Guest</th>
                            <th>Guest ID</th>
                            <th>Resident</th>
                            <th>Resident ID</th>
                            <th>Date</th>
                            <th>Sign In</th>
                            <th>Sign Out</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <?= h($r['guest_name']) ?>
                                </td>

                                <td>
                                    <?= h($r['guest_id']) ?>
                                </td>

                                <td>
                                    <?= h($r['first_name'] . ' ' . $r['last_name']) ?>
                                </td>

                                <td>
                                    <?= h($r['resident_id']) ?>
                                </td>

                                <td>
                                    <?= date('d M Y', strtotime($r['sign_in_at'])) ?>
                                </td>

                                <td>
                                    <?= date('g:i A', strtotime($r['sign_in_at'])) ?>
                                </td>

                                <td>
                                    <?= date('g:i A', strtotime($r['sign_out_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/layout_bottom.php'; ?>
