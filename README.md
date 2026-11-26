# AZ Preston Hall Visitor Sign-In System

A website demo for the AZ Preston Hall Visitor Sign-In System using HTML, CSS, vanilla JavaScript, PHP, MySQL and the Brevo email API.

## Requirements

- XAMPP with Apache, MySQL and PHP.
- Using Windows Task Scheduler.
- Internet connection only for Brevo emails.

## Install on XAMPP

1. Extract the `preston-hall` folder into `C:\xampp\htdocs\`.
2. In XAMPP Control Panel, start **Apache** and **MySQL**.
3. Update `config/local.php`.
4. Open `http://localhost/preston-hall/` and sign in.
5. Use **Add User** from the administrator navigation to create security accounts.

## Administrator Sign In
PresAdmin76
Pre$ton64Ad

## Brevo Email Reminders

The sign-in form includes an expected departure date and time. The reminder script checks active visits and sends an email around one hour before the expected departure time. It sends to the guest's email and/or the resident's email when those addresses exist.

1. Create a Brevo API key.
2. Verify the sender email address in Brevo.
3. Add the API key and verified sender address to `config/local.php`:
4. To automate reminders, create a Windows Task Scheduler task to run that command every 5 minutes. The script only processes active visits whose expected departure is within the next 60 minutes. Failed attempts can be retried on a later run.

Brevo requires internet access. Email delivery is subject to the free plan's sending allowance and sender verification.

## Windows Task Scheduler task

1. Create Task
2. Program/script - C:\xampp\php\php.exe
3. Begin task on schedule
4. Daily, repeat every 5 mins.

## Main Pages and Permissions

### Security

- Security dashboard.
- Two-step guest sign-in form.
- Active guest list and guest sign-out.
- Resident name search (does not display resident ID or room number).
- Emergency guest list.
- Own audit activity for the current day.

### Administrator

- Administrator dashboard.
- Add, edit, search and remove residents.
- Add user accounts (Administrator or Security).
- View active guests (read-only).
- Visit history and CSV export.
- Emergency guest list and CSV export.
- Search audit logs by date.

## Notes

- Passwords are stored using PHP password hashing.
- Role checks are performed on the server.
- Sessions end after 30 minutes of inactivity.
- Resident ID number and room number are hidden from Security on the resident search screen and resident picker.
- CSV exports open in Microsoft Excel and Google Sheets. This simple build exports CSV, not native `.xlsx` files.
