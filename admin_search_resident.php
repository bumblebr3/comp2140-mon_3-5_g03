<?php
require 'auth_user.php';
require 'sample_data.php';


if($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}

$search = trim($_GET['search'] ?? '');
$results = [];

foreach ($residents as $id => $resident) {
    $residentName = $resident['first'] . ' ' . $resident['last'];

    if (
        $search === '' ||
        stripos($residentName, $search) !== false ||
        stripos($id, $search) !== false
    ){
        $results[$id] = $resident;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Search Residents</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin_pages.css">
</head>

<body>

    <header>
        <img src="" alt="AZ Preston Hall's Logo">
    </header>

    <?php include 'navbar_admin.php'; ?>

    <div class="page">

        <h1>Search Residents</h1>

        <form method="GET" action="">

            <label for="search">Search by resident name or ID number:</label>

            <input
                type="text"
                id="search"
                name="search"
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Enter resident name or ID number"
            >

            <button type="submit">Search</button>

        </form>

        <h2>Resident Information</h2>

        <table>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>ID Number</th>
                <th>Room Number</th>
            </tr>

            <?php if (count($results) > 0): ?>

                <?php foreach ($results as $id => $resident): ?>

                <tr>
                    <td><?= htmlspecialchars($resident['first'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($resident['last'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($resident['room'], ENT_QUOTES, 'UTF-8') ?></td>
                </tr>

                <?php endforeach; ?>

            <?php else: ?>

                <tr>
                    <td colspan="4">No residents found.</td>
                </tr>

            <?php endif; ?>
        </table>

    </div>

</body>
</html>
