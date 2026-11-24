<!DOCTYPE html>
<html>
    <head>
        <title>Audit Logs</title>

    </head>
    <body>
        
    <header>

        <img src="" alt = "Az Preston Hall's logo">
        <h2>Audit Logs</h2>
    
    </header>
    <div>
        <form action="" method = "POST">
            <label> Enter the date of the audit log </label>
            <input type="text" name = "audit_log_date"><br><br>
            <button type = "button"> Search</button>
        </form>
        <p class = "error-message">[Error Message Placeholder]</p>

        <br> 

        <h3> Audit Logs for the date of [Date Placeholder] </h3>

        <div>

        <p>[Security Username] signed in [Guest Name] at [Timestamp]</p>

        <p> [Guest Name] with accompanying resident,  [resident name] signed in at [Timestamp] </p>

        <p> [Security Username] signed out [ Guest Name] at [Timestamp] </p>
        <p> [Guest Name] with accompanying resident,  [resident name] signed out at [Timestamp] </p>
        <p> [Admin Username] edited resident information for [resident name] at [Timestamp] </p>

        <p class =" error-message"> [Security Username] signed out [Guest Name] at [Timestamp] </p>

        <p class = "error-message">[Guest Name] with accompanying resident,  [resident name] signed out at [Timestamp] </p>

        <p> [Admin Username] removed resident [resident name] at [Timestamp] </p>
    </div>

</div>

</body>


</html>

