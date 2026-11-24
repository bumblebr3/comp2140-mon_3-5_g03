<?php
require 'auth_user.php';

if($_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Access denied.');
}
$step = isset($_GET['step']) ? $_GET['step'] : '1';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Resident</title>
</head>
<body>
    <header>
        <img src="" alt="Az Preston Hall's logo">
    </header>

    <h1>Add A New Resident</h1>

    <?php if ($step == '1'): ?>
        
        <div>
            <h3>Page 1 of 3</h3>
            <form action="" method="post">
                <label>Student ID:</label><br>
                <input type="text" name="student_id" placeholder="e.g. 620180015"><br><br>
                
                <label>First Name:</label><br>
                <input type="text" name="first_name"><br><br>
                
                <label>Last Name:</label><br>
                <input type="text" name="last_name"><br><br>
                
                <label>Room Number:</label><br>
                <input type="text" name="room_number"><br><br>
                
                <p>[Error Message Placeholder]</p>
                
                <button type="button" onclick="window.location.href='?step=2'">Next</button>
            </form>
        </div>

    <?php elseif ($step == '2'): ?>

        <div>
            <h3>Page 2 of 3</h3>
            <form action="" method="post">
                <label>Contact Number:</label><br>
                <input type="text" name="contact_number" placeholder="e.g. 876-555-1234"><br><br>
                
                <label>Email Address:</label><br>
                <input type="email" name="email" placeholder="e.g. student@email.com"><br><br>
                
                <p>[Error Message Placeholder]</p>
                
                <button type="button" onclick="window.location.href='?step=1'">Back</button>
                <button type="button" onclick="window.location.href='?step=3'">Next</button>
            </form>
        </div>

    <?php elseif ($step == '3'): ?>
    
        <div>
            <h3>Page 3 of 3</h3>
            <form action="" method="POST" enctype="multipart/form-data">
                <label>Upload Resident's Photo:</label><br>
                <input type="file" name="resident_photo"><br><br>
                
                <p>[Error Message Placeholder]</p>
                
                <button type="button" onclick="window.location.href='?step=2'">Back</button>
                <button type="submit">Submit</button>
            </form>
        </div>
    <?php endif; ?>

</body>
</html>