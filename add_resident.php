<!DOCTYPE html>
<html>
    <head>
        <title> Add Resident</title>
    </head>
    <body>

        <header>
        <img src="" alt = "Az Preston Hall's logo">
    
    </header>
    
    <h1> Add A New Resident</h1>

    <div>
        <h3>Page 1 of 3</h3>
        <form action = "" method = "post">
            <label>Guest ID:</label><br>
            <input type="text" name="guest_id"><br><br>
            <label> First Name:</label><br>
            <input type = "text" name = "first_name"><br><br>
            <label> Last Name:</label><br>
            <input type = "text" name = "last_name"><br><br>
            <label> Room Number:</label><br>
            <input type ="text" name = "room_number"><br><br>
            <p class = "error-message">[Error Message Placeholder]</p>
            <button type = "button">Next</button>
        </form>
    </div>
    <div>
        <h3> page 2 of 3</h3>
        <form action ="" method = "post">
            <label>Contact Number:</label><br>
            <input type= "text" name = "contact_number"><br><br>
            <label> Email Address:</label><br>
            <input type = "text" name = "email"><br><br>
            <p class = "error-message">[Error Message Placeholder]</p>
            <button type = "button">Back</button>
            <button type = "button">Next</button>
        </form>
    </div>
    <div>
        <h3> page 3 of 3</h3>
        <form action = "" method = "POST" enctype = "multipart/form-data">
            <label> Upload Resident's Photo:</label><br>
            <input type = "file" name = "resident_photo" accept="image/jpeg,image/png"><br><br>
            <p class = "error-message">[Error Message Placeholder]</p>
            <button type = "button">Back</button>
            <button type = "submit">Submit</button>
        </form>
    </div>
</body>
</html>
 