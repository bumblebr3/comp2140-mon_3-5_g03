<?php
require 'auth_user.php';

if($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}
$search_id = isset($_GET['search_id']) ? trim($_GET['search_id']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Resident Information</title>
</head>
<body>
    <header>
        <img src="" alt="AZ Preston Hall's Logo">
        <h2>Edit Resident Information</h2>
    </header>

    <div>
        
        <form method="GET" action="">
            <label>Enter the ID number of the resident you want to update:</label><br>
            <input type="text" name="search_id" value="<?= htmlspecialchars($search_id) ?>" required>
            <button type="submit">Search</button>
        </form>

        <hr>
        
        
        <?php if ($search_id !== ''): ?>
            <h3>Page 1 of 3</h3>
            <form method="POST" action="">
                
                <label>ID Number:</label><br>
                <!-- This is readonly so they can't change the ID -->
                <input type="text" value="<?= htmlspecialchars($search_id) ?>" readonly><br><br>
                
                <label>First Name:</label><br>
                <input type="text" name="first_name"><br><br>
                
                <label>Last Name:</label><br>
                <input type="text" name="last_name"><br><br>
                
                <label>Room Number:</label><br>
                <input type="text" name="room_number"><br><br>
                
                <p>[Error Message Placeholder]</p>
                
                <button type="button">Back</button>
                <button type="button">Next</button>
            </form>
        <?php endif; ?>
    </div>

</body>
</html>