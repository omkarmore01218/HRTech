# HRTech — Employee Onboarding & Performance Management System

A PHP + MySQL HR management application designed to run locally with XAMPP.

## Run locally with XAMPP

1. Copy the `hrtech_new` folder into `C:\\xampp\\htdocs\\`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Create a database named `hrtech_db`.
5. Import `database/schema.sql` into `hrtech_db`.
6. If you need sample/demo records, import `database/seed_data.sql` after the schema.
7. Check the database credentials in `config/database.php` (default XAMPP settings: user `root`, blank password).
8. Open `http://localhost/hrtech_new/`.

## Project structure

```text
hrtech_new/
├── index.php                 # Public landing page / portal selection
├── login.php                 # Login
├── register.php              # Registration
├── logout.php                # Logout
├── dashboard.php             # Main dashboard
├── employees.php             # Employee management
├── employee_tasks.php        # Task management
├── my_tasks.php              # Employee task view
├── submit_work.php           # Task submission
├── performance.php           # Performance management
├── my_performance.php        # Employee performance view
├── my_goals.php              # Goals
├── goal_summary.php           # Goal summary
├── appraisals.php             # Appraisals entry point (if implemented)
├── documents.php              # Documents entry point (if implemented)
├── config/
│   └── database.php           # Central database connection
├── database/
│   ├── schema.sql             # Database tables
│   └── seed_data.sql          # Optional sample data
├── modules/                   # Role-specific feature pages
├── api/                       # API endpoint location
├── css/
│   └── style.css              # Main stylesheet
├── js/
│   └── app.js                 # JavaScript
├── uploads/
│   └── employee_documents/    # Uploaded employee documents
└── README.md
```

## Notes

- Existing page URLs and internal links have intentionally been kept in place so the application continues to work without a full routing rewrite.
- `database.php` at the project root is retained as a compatibility wrapper for older PHP files. New code should include `config/database.php`.
- The `modules/` folder contains feature pages that are linked from the dashboard. Root-level PHP files remain as page entry points because the current application uses direct PHP URLs.
- Do not commit real employee documents, passwords, API keys, or other private information to a public GitHub repository. Review the `uploads/` folder before pushing.
