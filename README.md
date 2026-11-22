Hospital Appointment & Patient Management System
------------------------------------------------
Tech: PHP, MySQL, HTML, CSS, JS (vanilla)

Structure:
- db.php                 : MySQL connection file
- sql/schema.sql         : SQL to create database and tables + sample data
- index.php              : Home / navigation
- patients.php           : List/add patients
- doctors.php            : List/add doctors
- departments.php        : List/add departments
- appointments.php       : List/add appointments (checks doctor availability)
- prescriptions.php      : List/add prescriptions
- assets/css/style.css   : Basic styles
- assets/js/app.js       : Small client-side helpers

How to run:
1. Create a MySQL database (name: hospital_db) or change credentials in db.php
2. Import sql/schema.sql into MySQL to create tables and sample data:
   mysql -u root -p < sql/schema.sql
3. Place these files in a PHP-enabled webserver folder (eg. XAMPP htdocs)
4. Open index.php in your browser.

Notes:
- This is a simple educational system, not production-ready.
- No authentication implemented for simplicity.
