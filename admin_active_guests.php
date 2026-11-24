<?php
require 'auth_user.php';
require 'sample_data.php';

if($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

$search = trim($_GET['search'] ?? '');
$activeGuests = [];

foreach ($visits as $visit) {

    if ($visit['out'] === null) {

        $resident = $residents[$visit['rid']] ?? null;

        if ($resident === null) {
            continue;
        }

        $residentName = $resident['first'] . ' ' . $resident['last'];

        if (
            $search === '' ||
            stripos($visit['guest'], $search) !== false ||
            stripos($residentName, $search) !== false
        ) {
            $activeGuests[] = [
                'guest' => $visit['guest'],
                'resident' => $residentName,
                'in' => $visit['in']
            ];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Active Guests</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin_pages.css">
</head>

<body>

    <header>
        <img src="" alt=" AZ Preston Hall's Logo">
    </header>

    <?php include 'navbar_admin.php'; ?>

    <div class="page">

        <h1>Active Guests</h1>

        <form method="GET" action="">

            <label for="search">Search by guest or resident name:</label>

            <input
                type="text"
                id="search"
                name="search"
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Enter guest or resident name"
            >

            <button type="submit">Search</button>

        </form>

        <h2>Current Guests</h2>

        <table>
            <tr>
                <th>Guest Name</th>
                <th>Resident Name</th>
                <th>Sign In Time</th>
                <th>Date Signed In</th>
            </tr>

            <?php if (count($activeGuests) > 0): ?>

                <?php foreach ($activeGuests as $guest): ?>

                    <tr>
                        <td><?= htmlspecialchars($guest['guest'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($guest['resident']) ?></td>
                        <td><?= date('g:i A', strtotime($guest['in'])) ?></td>
                        <td><?= date('F j, Y', strtotime($guest['in'])) ?></td>
                    </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td colspan="4">No active guests found.</td>
                </tr>

            <?php endif; ?>

        </table>
    
    </div>

</body>
</html>
