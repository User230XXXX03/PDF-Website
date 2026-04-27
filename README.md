# PDF Generator System

PDF Generator System is a web-based document generation system built with Vue 3, PHP, MySQL, TCPDF, and PHPMailer. It supports reusable HTML templates, batch records, PDF generation, PDF preview/download, email delivery, and activity logs.

## Requirements

- PHP >= 8.0
- MySQL or MariaDB
- Node.js and npm
- Browser
- Composer

## Database Setup

Import the root SQL file:

```text
pdf_generator.sql
```

The SQL file creates and uses the database:

```text
pdf_generator
```

Using MySQL command line:

```bash
mysql -u root < pdf_generator.sql
```

If your MySQL root user has a password:

```bash
mysql -u root -p < pdf_generator.sql
```

Using phpMyAdmin:

1. Open phpMyAdmin.
2. Import the root `pdf_generator.sql`.
3. Confirm the `pdf_generator` database is created.

Default demo accounts:

```text
Admin user:
Username: admin
Password: Password123

Normal user:
Username: user
Password: Password123
```

## Backend Configuration

Backend database settings are in:

```text
backend/includes/config.php
```

Default local settings:

```text
DB_HOST = localhost
DB_USER = root
DB_PASS = ''
DB_NAME = pdf_generator
```

If your MySQL password or database settings are different, update this file before running the backend.

## Backend Dependencies

The backend uses TCPDF and PHPMailer.

If this project includes:

```text
backend/libs/tcpdf/
backend/libs/PHPMailer/
```

then Composer installation is usually not required.

If `backend/libs/` is missing, run:

```bash
cd backend
composer install
```

## Frontend Dependencies

The frontend dependencies are not included through `node_modules/`.

Install them first:

```bash
cd frontend
npm install
```

## Running The Project

### Option 1: Use run.bat

Backend:

```text
backend/run.bat
```

This runs:

```bash
php -S localhost:8000
```

Frontend:

```text
frontend/run.bat
```

This runs:

```bash
npm run dev
```

### Option 2: Run Commands Manually

Start backend:

```bash
cd backend
php -S localhost:8000
```

Start frontend in another terminal:

```bash
cd frontend
npm run dev
```

Open the frontend:

```text
http://localhost:5173
```

Backend API base URL:

```text
http://localhost:8000/api
```

## Demo Workflow

1. Log in with `admin` or `user`.
2. Open Templates and review the demo Transcript template.
3. Open Batches and view the demo batch.
4. Open Batch Detail.
5. Generate PDFs.
6. Preview or download generated PDFs.
7. Configure SMTP in Settings if email sending is required.
8. Send emails after SMTP is configured.
9. Review activity in Logs.

## Email / SMTP Notes

The submitted SQL does not include real SMTP credentials.

To use email sending, configure SMTP settings in the Settings page after logging in.