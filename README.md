# 🏥 MedCore — Hospital Appointment & Patient Management System

MedCore is a web-based hospital administration system that centralizes patient records, medical staff, departments, appointment scheduling, prescriptions, and laboratory diagnostics in one dashboard. It was built by **Team Hunter** as a Design Project 2 submission.

## ✨ Features

### Authentication
- Account signup and login with **bcrypt-hashed passwords** (`password_hash` / `password_verify`)
- Session-based access control and secure logout
- Duplicate-username prevention

### Dashboard (Clinical Command Center)
- Live KPI cards: total patient records, on-duty physicians, upcoming appointments
- **Global patient search** by name, ID, phone, or email
- Quick actions and sidebar navigation to every module

### Patient Registry
- Register, update, and delete patient records
- Stores name, date of birth, phone, email, and address
- **Age is calculated automatically** from date of birth

### Medical Staff (Doctors)
- Add, edit, and remove doctors
- Assign each doctor to a department
- Track contact details and duty hours/availability

### Departments
- Create and decommission clinical units
- Live department counter

### Central Scheduling (Appointments)
- Book, reschedule, and cancel appointments
- **Double-booking prevention:** a doctor cannot be booked twice at the same date and time
- Status shown automatically as *Confirmed* (upcoming) or *Completed* (past)

### Pharmacy & Prescriptions
- Issue prescriptions linked to a specific appointment
- Medications, dosage, and clinical notes
- Prescriptions show the patient, prescribing doctor, and issue time

### Laboratory Diagnostics
- Record test name, observed result, and reference range
- **Critical flag** with a visual alert for abnormal results

## 🛠️ Tech Stack

| Layer      | Technology                                                          |
|------------|---------------------------------------------------------------------|
| Frontend   | HTML5, CSS3 (CSS variables, Flexbox, Grid), vanilla JavaScript      |
| Styling    | Custom clinical design system, Tailwind CSS (CDN) on auth pages     |
| Typography | Google Fonts (Inter, JetBrains Mono)                                |
| Backend    | PHP (MySQLi with prepared statements)                               |
| Database   | MySQL / MariaDB                                                     |
| Server     | Apache (XAMPP)                                                      |

## 🗄️ Database Design

Seven relational tables connected by foreign keys:

```
users
departments ──< doctors ──< appointments >── patients ──< lab_reports
                                 │
                                 └──< prescriptions
```

- Deleting a department sets its doctors' department to `NULL`
- Deleting a patient or doctor cascades to their appointments
- Deleting an appointment cascades to its prescriptions

## 📁 Project Structure

```
├── db.php             # Database connection and session start
├── schema.sql         # Database creation script
├── index.php          # Dashboard, KPIs, patient search
├── login.php          # User login
├── signup.php         # User registration
├── logout.php         # Ends the session
├── patients.php       # Patient registry (CRUD)
├── doctors.php        # Medical staff (CRUD)
├── departments.php    # Departments
├── appointments.php   # Scheduling with double-booking check
├── prescriptions.php  # Prescriptions linked to appointments
├── lab_reports.php    # Laboratory reports
├── style.css          # Shared styles
└── app.js             # Client-side helpers
```

## 🚀 Getting Started

### Requirements
- XAMPP (or any PHP 8+ and MySQL/MariaDB stack)

### Installation
1. Copy the project folder into `xampp/htdocs/`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open **phpMyAdmin** and import `schema.sql`. This creates the `hospital_db` database and all tables.
4. If your MySQL credentials differ from the defaults, edit them in `db.php`:
   ```php
   $DB_HOST = 'localhost';
   $DB_USER = 'root';
   $DB_PASS = '';
   $DB_NAME = 'hospital_db';
   ```
5. Visit `http://localhost/<project-folder>/signup.php` to create an account.
6. Log in at `login.php`. You will be taken to the dashboard.

## 🔒 Security Notes

**Implemented**
- Password hashing with bcrypt
- Prepared statements for all insert/update operations
- Output escaping with `htmlspecialchars()` on most pages
- Session check on the dashboard

**Known limitations (planned improvements)**
- Add the login check to every module page, not only the dashboard
- Escape all output in the dashboard search results
- Replace GET-based delete links with POST requests and CSRF tokens
- Add role-based access (admin, doctor, receptionist)
- Move database credentials to environment variables

> This is an educational project and is not production-ready.

## 🔮 Future Improvements

- Role-based access control (admin / doctor / receptionist)
- Live "Recent Appointments" feed and pharmacy inventory alerts on the dashboard
- Printable prescriptions and lab reports (PDF)
- Appointment status workflow (Scheduled → Checked-in → Completed → Cancelled)
- Pagination and filtering on large tables
- Billing and invoice module

## 👥 Team

**Team Hunter** — Design Project 2, Summer 2026

## 📄 License

Created for academic purposes.