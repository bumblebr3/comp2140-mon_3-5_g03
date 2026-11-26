
<?php
/*
 * Command-line scheduled task that finds active visits approaching
 * their expected departure time and attempts to email the guest and/or resident.
 */

// Run this file from Windows Task Scheduler every 5 minutes.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit("Run this script from the command line.\n");
}

// Load only the database and email helpers needed by this background task.
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/brevo.php';

date_default_timezone_set('America/Jamaica');

$pdo = db();

$sql = "
    SELECT
        v.id,
        v.expected_departure_at,
        g.full_name,
        g.email AS guest_email,
        r.first_name,
        r.last_name,
        r.email AS resident_email
    FROM visits v
    JOIN guests g ON g.id = v.guest_id
    JOIN residents r ON r.id = v.resident_id
    WHERE v.sign_out_at IS NULL
        AND v.reminder_sent_at IS NULL
        AND v.expected_departure_at BETWEEN NOW()
            AND DATE_ADD(NOW(), INTERVAL 60 MINUTE)
        AND (
            v.last_reminder_attempt_at IS NULL
            OR v.last_reminder_attempt_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        )
";

// Select visits that are still active, have not been reminded,
// and are due within the next hour.
$visits = $pdo->query($sql)->fetchAll();

// Attempt one reminder per matching visit and save the delivery outcome.
foreach ($visits as $visit) {
    $recipients = [];

    if (!empty($visit['guest_email'])) {
        $recipients[$visit['guest_email']] = $visit['full_name'];
    }

    if (!empty($visit['resident_email'])) {
        $recipients[$visit['resident_email']] = trim(
            $visit['first_name'] . ' ' . $visit['last_name']
        );
    }

    $pdo->prepare(
        'UPDATE visits SET last_reminder_attempt_at = NOW() WHERE id = ?'
    )->execute([$visit['id']]);

    $html = '<p>This is a reminder that '
        . htmlspecialchars($visit['full_name'], ENT_QUOTES, 'UTF-8')
        . ' is approaching the expected departure time at AZ Preston Hall.</p>';

    $html .= '<p>Expected departure: '
        . date('d M Y, g:i A', strtotime($visit['expected_departure_at']))
        . '</p>';

    $result = brevo_send_email(
        $recipients,
        'Preston Hall visitor departure reminder',
        $html
    );

    if ($result['success']) {
        $pdo->prepare("
            UPDATE visits
            SET
                reminder_status = 'sent',
                reminder_sent_at = NOW(),
                reminder_error = NULL
            WHERE id = ?
        ")->execute([$visit['id']]);

        echo 'Reminder sent for visit #' . $visit['id'] . PHP_EOL;
    } else {
        $status = empty($recipients) ? 'no_email' : 'failed';

        $pdo->prepare(
            'UPDATE visits SET reminder_status = ?, reminder_error = ? WHERE id = ?'
        )->execute([
            $status,
            $result['message'],
            $visit['id']
        ]);

        echo 'Reminder not sent for visit #'
            . $visit['id']
            . ': '
            . $result['message']
            . PHP_EOL;
    }
}

if (!$visits) {
    echo "No reminders due.\n";
}
