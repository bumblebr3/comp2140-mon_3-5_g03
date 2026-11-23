<?php require 'auth_user.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <header>
        <img src="" alt = "Az Preston Hall's logo">
        <h2>Add A New User</h2>
        <?php include 'navbar_admin.php'; ?>
    </header>
    <div>
        <form>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username">
            <br>
            <label for="password">Password:</label>
            <input type="text" id="username" name="username">
            <br>
            <label for="role">Role:</label>
            <select id="role" name="role">
                <option value="">Role</option>
                <option value="admin">Admin</option>
                <option value="security">Security Guard</option>
            </select>
            <!--Error message-->
            <div>
                <p class="error"></p>
            </div>
            <br>
            <button type="button">Create User</button>
        </form>
    </div>
</body>
</html>