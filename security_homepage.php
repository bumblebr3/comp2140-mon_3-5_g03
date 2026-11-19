<?php require 'auth_user.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Security Home</title>
  <link rel="stylesheet" href="style.css">
</head>
<header>
    <img src="" alt = "Az Preston Hall's logo">
</header>

<body>
    <?php include 'navbar_security.php'; ?>
    <!--outer section-->
  <div>
    <!--area containing welcome message and time-->
    <div>
        <h1> Welcome, <?= htmlspecialchars($_SESSION['username']) ?></h1>
    </div>
    <div>
        <h2>Today is <?= date('l, F j, Y') ?> at <?= date('g:i A') ?></h2>
    </div>
  </div>

  <button><a href="">Generate Emergency Guest List</a></button>

  <h3>Active Guests</h3>
  
  <!--holds items from active guests-->
  <div>

  </div>


</body>
</html>

