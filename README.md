# Travel Now — Travel & Tour Management System (TTMS)

Travel Now is a PHP/MySQL web application for managing travel packages and customer bookings. The current source tree also contains bus-ticketing pages, role-oriented admin/agent pages, payment-method pages, invoices, reviews, and English/Urdu language files. See the code for the exact implemented behavior; some payment flows may be demonstrations rather than live payment processing.

**Project status:** personal/developer project. Features and integrations should be treated as implemented only where the source code confirms them.

## Features represented in the source

- Travel package browsing and package details
- Customer registration, login, logout, and password-reset pages
- Package booking and booking confirmation/invoice pages
- Bus search, seat booking, ticket pages, and bus-management pages
- Admin and agent pages
- Payment-method pages for card, Easypaisa, and JazzCash
- Reviews and contact form pages
- English and Urdu language files
- MySQL database schema files

## Technology

- PHP
- MySQL/MariaDB
- HTML, CSS, and JavaScript
- Bootstrap assets included in the project

## Run locally (XAMPP)

1. Install XAMPP with PHP and MySQL/MariaDB.
2. Copy the project folder into XAMPP's `htdocs` directory.
3. Start Apache and MySQL from the XAMPP Control Panel.
4. Open phpMyAdmin and create a database named `travel_agency` (or update the database name in `db.php`).
5. Review the SQL files in the repository before importing. There are multiple schema files from different project iterations, so do not import all of them blindly. Choose the schema that matches the current PHP code and verify it on a disposable local database first.
6. If needed, update the database host, port, username, password, and database name in `db.php` for your local environment. The current file contains XAMPP-style local defaults; do not use these settings unchanged on a production server.
7. Visit `http://localhost/travel/` (adjust the path to your chosen folder name).

## Important setup and security notes

- This repository has not been security-audited for production use.
- Do not upload real customer records, invoices, passwords, API keys, or production database dumps.
- Before deploying publicly, verify authentication and authorization on every admin/agent page, validate uploads, review SQL queries and output escaping, and replace demo payment behavior with a verified payment-provider integration.
- Use a least-privilege database user in production, and keep credentials outside version control.
- Some SQL files describe different schema versions. Validate the selected schema against the PHP queries before use.
- No license is included yet. Unless a license is added, standard copyright restrictions apply; publishing the repository does not automatically grant permission to reuse the code.

## Repository layout

- `*.php` — application pages and shared PHP functions
- `partials/` — shared navigation and footer
- `payments/` — payment-method pages
- `css/`, `js/` — frontend assets
- `lang/` — English and Urdu language files
- `uploads/` — project image uploads included with the source
- `sql/` and `*.sql` — database schema files; review before importing

## Contributions / contact

Issues and improvement suggestions can be submitted through the repository's GitHub Issues page once enabled. Add a project contact address only if you are comfortable making it public.
