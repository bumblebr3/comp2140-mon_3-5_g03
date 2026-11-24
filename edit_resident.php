<!DOCTYPE html>
<html>
    <head>
        <title>Edit Resident Information</title>
</head>
<body>

<header>

    <img src="" alt = "Az Preston Hall's logo">

</header>

<h1> Edit Resident Information</h1>

<div>

    <h3> Page 1 of 3</h3>
    <form action ="" method ="POST">
        <label>Enter the ID number of the resident you want to update:</label><br>
        <input type = "text" name ="resident_id"><br><br>
        <button type ="button"> search</button><br><br><hr>

        <label> ID number:</label><br>
        <input type = "text" name = "edit_id"><br><br>
        <label>First Name:</label><br>
        <input type = "text" name = "edit_first_name"><br><br>
        <label> Last Name:</label><br>
        <input type = "text" name = "edit_last_name"><br><br>
        <label> Room Number:</label><br>
        <input type ="text" name ="edit_room_number"><br><br>

        <p class = "error-message">[Error Message Placeholder]</p>

        <button type = "button">Next</button>
    </form>
</div>

<div>
    <h3> Page 2 of 3 </h3>
    <form action = "" method = "POST">
        <label> Contact Number:</label><br>
        <input type = "text" name ="edit_contact_number"><br><br>
        <label> Email Address:</label><br>
        <input type="text" name ="edit_email_address"><br><br>
        <p class = "error-message">[Error Message Placeholder]</p>
        <button type = "button">Back</button>
        <button type = "button">Next</button>
    </form>
</div>

<div>
    <h3> Page 3 of 3</h3>
    <form action = "" method = "POST" enctype = "multipart/form-data">
            <label> Upload Resident's Photo:</label><br>
            <input type = "file" name = "edit_photo" accept="image/jpeg,image/png"><br><br>
            <p class = "error-message">[Error Message Placeholder]</p>
            <button type = "button">Back</button>
            <button type = "submit">Submit</button>
        </form>
    </div>
</body>
</html>


