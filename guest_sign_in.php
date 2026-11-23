<?php require 'auth_user.php'; ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Guest Sign In</title>
        <script src="guest_sign_in.js" defer></script>
    </head>
    <body>
        <header>
            <img src="" alt = "Az Preston Hall's logo">
            <h2>Sign In Guest</h2>
             <?php include 'navbar_security.php'; ?>
        </header>
        <form id="guestForm">
            <div class="pg1 form-section"> 
                <p>Page 1 of 3</p>
                <br>
                <label for="resident">Accompanying Resident:</label>
                <input type="text" id="resident" name="resident">
                <br>
                <label for="guestID">Guest ID:</label>
                <input type="text" id="guestID" name="guestID">
                <br>
                <label for="idType">ID Type:</label>
                <select id="idType" name="idType">
                    <option value="">Select</option>
                    <option value="schoolID">School ID</option>
                    <option value="trn">TRN</option>
                    <option value="license">Driver's License</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="pg2 form-section">
                <p>Page 2 of 3</p>
                <br>
                <label for="contactNum">Contact Number:</label>
                <input type="text" id="contactNum">
                <br>
                <label for="email">Email Address:</label>
                <input type="email" name="email" id="email">
            </div>
            <div class="pg3 form-section">
                <p>Page 3 of 3</p>
                <br>
                <img id="photoPreview" alt="Guest Photo Preview">
                <!--Section to upload photo-->
                <div>

                </div>
                <button type="button">Take photo</button>
            </div>
            <!--Error message-->
            <p class="error"></p>

            <!--Form buttons that should reappear and disappear-->
            <div id="formBtns">
                <button id="backBtn" type="button">Back</button>
                <button id="nxtBtn" type="button">Next</button>
                <button id="submitBtn" type="button">Submit</button>
            </div>
            <!--Confirmation page-->
            <div class="confirmation">
                <div class="message">

                </div>
                <button id="newGuest" type="button">Add a New Guest</button>
                <button id="dashboardBtn" type="button">Return to Dashboard</button>
            </div>
        </form>
    </body>
</html>