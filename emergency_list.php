
<?php
/* Creates an emergency guest roster for a selected date and exports the roster as CSV. Access is limited to Administrator and Security accounts. */

require_once __DIR__ . '/includes/bootstrap.php';

require_role('admin', 'security');

$user = current_user();

// Keep the emergency categories consistent between validation and the form.
$types = ['Earthquake', 'Fire', 'Hurricane', 'Flood', 'Other'];

$type = trim($_GET['type'] ?? '');
$asAt = trim($_GET['as_at'] ?? '');

$error = '';
$rows = null;

if (isset($_GET['generate']) || isset($_GET['export'])) {

    // Validate emergency type
    if (!in_array($type, $types, true)) {
        $error = 'Please select an emergency type.';
    }

    // Date is required
    if ($error === '' && $asAt === '') {
        $error = 'Please select a date.';
    }

    // Validate selected date
    if ($error === '') {

        $parsed = DateTime::createFromFormat('!Y-m-d', $asAt);

        if (!$parsed || $parsed->format('Y-m-d') !== $asAt) {
            $error = 'Please enter a valid date.';
        } elseif ($asAt > date('Y-m-d')) {
            $error = 'The date cannot be in the future.';
        }
    }

    // Generate emergency guest list
    if ($error === '') {

        $startDate = $asAt . ' 00:00:00';

        $endDate = date(
            'Y-m-d H:i:s',
            strtotime($asAt . ' +1 day')
        );

        // A visit is included when it overlaps any part of the selected calendar date.
        $stmt = db()->prepare(
            'SELECT
                g.full_name AS guest_name,
                g.contact_number AS guest_phone,
                r.first_name,
                r.last_name,
                r.contact_number AS resident_phone,
                v.sign_in_at

            FROM visits v

            JOIN guests g ON g.id = v.guest_id
            JOIN residents r ON r.id = v.resident_id

            WHERE v.sign_in_at < ?
            AND (
                v.sign_out_at IS NULL
                OR v.sign_out_at >= ?
            )

            ORDER BY v.sign_in_at'
        );

        $stmt->execute([$endDate, $startDate]);

        $rows = $stmt->fetchAll();

        // Record activity
        audit_log(
            'emergency_list_generated',
            'Emergency list generated for ' . $type .
            ' on ' . $asAt . '.'
        );
    }

    // Export emergency list
    if (isset($_GET['export']) && $error === '') {

        // Only Admin and Security can export
        if (!in_array($user['role'], ['admin', 'security'], true)) {
            http_response_code(403);
            exit('You are not authorized to export the emergency list.');
        }

        header('Content-Type: text/csv; charset=UTF-8');

        header(
            'Content-Disposition: attachment; filename="emergency_guest_list.csv"'
        );

        $out = fopen('php://output', 'w');

        // Excel UTF-8 compatibility
        fwrite($out, "\xEF\xBB\xBF");

        // Report information
        fputcsv($out, ['EMERGENCY GUEST LIST']);

        fputcsv($out, ['Emergency Type', $type]);

        fputcsv($out, [
            'Date',
            date('d M Y', strtotime($asAt))
        ]);

        fputcsv($out, []);

        // Table headings
        fputcsv($out, [
            'Guest Name',
            'Accompanying Resident',
            'Contact Number',
            'Sign In Time'
        ]);

        // Guest records
        foreach ($rows as $r) {

            fputcsv($out, [
                $r['guest_name'],
                $r['first_name'] . ' ' . $r['last_name'],
                $r['guest_phone'] ?: $r['resident_phone'],
                date(
                    'd M Y, g:i A',
                    strtotime($r['sign_in_at'])
                )
            ]);
        }

        fclose($out);
        exit;
    }
}

$pageTitle = 'Emergency Guest List';

include __DIR__ . '/includes/layout_top.php';
?>

<h1>Emergency Guest List</h1>

<section class="card">

    <form method="get" class="inline-form">

        <div>
            <label for="type">Emergency Type</label>

            <select id="type" name="type" required>

                <option value="">Select emergency type</option>

                <?php foreach ($types as $t): ?>

                    <option
                        value="<?= h($t) ?>"
                        <?= $type === $t ? 'selected' : '' ?>
                    >
                        <?= h($t) ?>
                    </option>

                <?php endforeach; ?>

            </select>
        </div>

        <div>
            <label for="as_at">Date</label>

            <input
                type="date"
                id="as_at"
                name="as_at"
                value="<?= h($asAt) ?>"
                max="<?= date('Y-m-d') ?>"
                required
            >
        </div>

        <button
            class="btn danger"
            type="submit"
            name="generate"
            value="1"
        >
            Generate List
        </button>

    </form>

    <?php if ($error): ?>

        <p class="notice error">
            <?= h($error) ?>
        </p>

    <?php endif; ?>

</section>

<?php if ($rows !== null): ?>

<section class="card">

    <div class="section-heading">

        <div>

            <h2>
                <?= count($rows) ?> Guest(s) in the Hall
            </h2>

            <p>
                <strong>Emergency Type:</strong>
                <?= h($type) ?>
            </p>

            <p>
                <strong>Date:</strong>
                <?= h(date('d M Y', strtotime($asAt))) ?>
            </p>

        </div>

        <!-- Admin and Security Download Button -->
        <?php if (in_array($user['role'], ['admin', 'security'], true)): ?>

            <a
                class="btn secondary"
                href="?<?= h(http_build_query([
                    'type' => $type,
                    'as_at' => $asAt,
                    'export' => 1
                ])) ?>"
            >
                Download Excel Sheet
            </a>

        <?php endif; ?>

    </div>

    <?php if (!$rows): ?>

        <p class="notice success">
            No guests were recorded in the hall on this date.
        </p>

    <?php else: ?>

        <div class="table-wrap">

            <table>

                <thead>

                    <tr>
                        <th>Guest Name</th>
                        <th>Accompanying Resident</th>
                        <th>Contact Number</th>
                        <th>Sign In Time</th>
                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($rows as $r): ?>

                        <tr>

                            <td>
                                <?= h($r['guest_name']) ?>
                            </td>

                            <td>
                                <?= h(
                                    $r['first_name'] . ' ' .
                                    $r['last_name']
                                ) ?>
                            </td>

                            <td>
                                <?= h(
                                    $r['guest_phone'] ?:
                                    $r['resident_phone']
                                ) ?>
                            </td>

                            <td>
                                <?= h(date(
                                    'd M Y, g:i A',
                                    strtotime($r['sign_in_at'])
                                )) ?>
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
