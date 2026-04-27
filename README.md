# PDF Generator System

PDF Generator System is a web-based document generation and delivery system. It lets users create reusable HTML templates, import batch records, generate PDF files, preview or download generated documents, send record-level email messages, and review system activity logs.

This README is a deployment guide and a system reference for the current project version.

## 1. System Overview

The system is designed for scenarios where an organisation needs to produce many structured documents from one template, for example transcripts, certificates, payroll documents, or course feedback documents.

The main idea is:

1. A user creates or imports a template.
2. A user creates a batch and imports multiple records.
3. Each record provides its own data fields, including the recipient email address.
4. The backend replaces template placeholders with record values.
5. TCPDF generates one PDF file per record.
6. PHPMailer can send the generated PDF to the email address stored in that record.
7. Dashboard and Logs pages provide status and traceability.

The current design uses record-level email delivery. The batch stores one shared email subject and one shared email body, but the recipient email is always resolved from each individual record.

## 2. Overall Framework

The project follows a simple frontend/backend separation.

| Layer | Main Responsibility | Main Location |
| --- | --- | --- |
| Frontend | User interface, routing, forms, table views, API calls | `frontend/` |
| Backend API | Authentication, templates, batches, PDF generation, email sending, logs | `backend/api/` |
| Backend Includes | Database connection, config, auth middleware, helpers | `backend/includes/` |
| PDF Library | PDF file creation | `backend/libs/tcpdf/` |
| Email Library | SMTP email delivery | `backend/libs/PHPMailer/` |
| Database | Users, templates, batches, records, email settings, logs | `pdf_generator.sql` |
| Runtime PDF Output | Generated PDF files | `backend/uploads/pdfs/` |

## 3. Technology Architecture

| Area | Technology | Purpose |
| --- | --- | --- |
| Frontend framework | Vue 3 | Single page application |
| Frontend build tool | Vite | Development server and production build |
| Frontend UI library | Element Plus | Tables, forms, dialogs, buttons, messages |
| HTTP client | Axios | API requests with session cookies |
| File parsing | PapaParse, xlsx | CSV and Excel record import support |
| Backend language | PHP 8 | REST-style API implementation |
| Database | MySQL / MariaDB | Persistent storage |
| PDF generation | TCPDF | HTML-to-PDF generation |
| Email sending | PHPMailer | SMTP delivery |
| Authentication | PHP session | Login state and API protection |

Default local development ports:

| Service | URL |
| --- | --- |
| Frontend | `http://localhost:5173` |
| Backend API | `http://localhost:8000/api` |

## 4. Directory Structure And File Guide

```text
Project/
|-- README.md                    Project guide and deployment instructions
|-- pdf_generator.sql             Recommended database initialisation file
|
|-- backend/                      PHP backend
|   |-- run.bat                   Starts the PHP backend development server
|   |
|   |-- api/                      Backend API entry points
|   |   |-- auth.php              Login, register, logout, current user
|   |   |-- templates.php         Template list, create, edit, delete
|   |   |-- batches.php           Batch list, create, edit, delete
|   |   |-- pdf.php               PDF generation, preview, download
|   |   |-- email.php             SMTP settings and email sending
|   |   |-- dashboard.php         Dashboard statistics
|   |   `-- logs.php              Log list, filters, export
|   |
|   |-- includes/                 Backend shared config and helper logic
|   |   |-- config.php            Database, CORS, paths, and app config
|   |   |-- db.php                PDO database connection
|   |   |-- auth_middleware.php   Session authentication guard
|   |   `-- helpers.php           Common responses, validation, logs, permissions
|   |
|   |-- libs/                     Third-party libraries
|   |   |-- PHPMailer/            Email sending library
|   |   `-- tcpdf/                PDF generation library
|   |
|   `-- uploads/
|       `-- pdfs/                 Runtime generated PDF files
|
`-- frontend/                     Vue frontend
    |-- run.bat                   Starts the Vite frontend development server
    |-- package.json              Frontend dependencies and npm scripts
    |-- package-lock.json         Frontend dependency lock file
    |
    `-- src/
        |-- api/                  Frontend API wrappers
        |-- views/                Page-level Vue components
        |-- router/               Frontend route configuration
        |-- layout/               Main authenticated layout
        `-- utils/                Axios request configuration
```

Use the following guide to locate the main implementation areas:

| Area To Inspect | Primary Files |
| --- | --- |
| Login, registration, session | `backend/api/auth.php`, `backend/includes/auth_middleware.php` |
| Template management | `frontend/src/views/Templates.vue`, `frontend/src/views/TemplateEditor.vue`, `backend/api/templates.php` |
| Batch creation and record import | `frontend/src/views/BatchEditor.vue`, `backend/api/batches.php` |
| Batch detail, PDF, email actions | `frontend/src/views/BatchDetail.vue`, `backend/api/pdf.php`, `backend/api/email.php` |
| Dashboard statistics | `frontend/src/views/Dashboard.vue`, `backend/api/dashboard.php` |
| Activity logs | `frontend/src/views/Logs.vue`, `backend/api/logs.php` |
| API base URL and frontend error handling | `frontend/src/utils/request.js` |
| Database schema and demo data | `pdf_generator.sql` |

This project uses the root `pdf_generator.sql` as the recommended database import file. Older SQL files in subfolders are historical or development files and are not the database initialisation file for the current running version.

## 5. URL Structure

### 5.1 Frontend Routes

| Route | Page | Purpose |
| --- | --- | --- |
| `/login` | Login | User login |
| `/register` | Register | New user registration |
| `/dashboard` | Dashboard | Summary counts, recent batches, recent logs |
| `/templates` | Templates | Template list, search, import, delete |
| `/templates/create` | Template Editor | Create a template |
| `/templates/edit/:id` | Template Editor | Edit a template |
| `/batches` | Batches | Batch list and status overview |
| `/batches/create` | Batch Editor | Create a batch and import records |
| `/batches/edit/:id` | Batch Editor | Edit an owned batch |
| `/batches/detail/:id` | Batch Detail | Generate, preview, download, and send records |
| `/logs` | Logs | Activity log search, filter, export |
| `/settings` | Settings | SMTP email configuration |

Unknown frontend routes are redirected to `/dashboard`.

### 5.2 Backend API URL Pattern

The backend API base URL is:

```text
http://localhost:8000/api
```

Most endpoints are PHP files under `backend/api/`, for example:

```text
http://localhost:8000/api/auth.php?action=login
http://localhost:8000/api/batches.php?id=1
http://localhost:8000/api/pdf.php?action=preview&id=1
```

## 6. Functional Module Design

| Module | Files | Main Functions |
| --- | --- | --- |
| Authentication | `auth.php`, `auth_middleware.php`, `Login.vue`, `Register.vue` | Register, login, logout, current user, session protection |
| Dashboard | `dashboard.php`, `Dashboard.vue` | Counts for templates, batches, PDFs, emails, and recent activities |
| Templates | `templates.php`, `Templates.vue`, `TemplateEditor.vue` | Template CRUD, placeholder content, template type, import test template |
| Batches | `batches.php`, `Batches.vue`, `BatchEditor.vue` | Batch CRUD, record import, shared email subject/body |
| Batch Detail | `BatchDetail.vue`, `pdf.php`, `email.php` | Generate PDFs, preview/download PDFs, send emails |
| PDF Generation | `pdf.php`, TCPDF | Replace placeholders and create PDF files |
| Email Sending | `email.php`, PHPMailer, `Settings.vue` | SMTP settings and record-level delivery |
| Logs | `logs.php`, `Logs.vue` | Activity records, search, filter, CSV export |
| Shared API Client | `frontend/src/utils/request.js` | Axios base URL, cookies, response error handling |

## 7. Data Model Summary

| Table | Purpose | Important Fields |
| --- | --- | --- |
| `users` | User accounts | `id`, `username`, `email`, `password`, `created_at` |
| `templates` | Document templates | `user_id`, `name`, `content`, `variables`, `set_type` |
| `batches` | Batch jobs | `user_id`, `template_id`, `name`, `status`, `email_subject`, `email_body` |
| `batch_records` | Individual recipient/data records | `batch_id`, `student_name`, `student_email`, `data`, `pdf_path`, `pdf_generated`, `email_sent` |
| `email_settings` | Per-user SMTP settings | `smtp_host`, `smtp_port`, `smtp_secure`, `smtp_username`, `smtp_password`, `from_email`, `from_name` |
| `logs` | Activity and error records | `user_id`, `batch_id`, `record_id`, `type`, `message`, `recipient_name`, `recipient_email`, `details` |

The `data` field in `batch_records` stores the full imported record as JSON. The `student_name` and `student_email` columns are retained as common display fields and fallback fields, but the current sending design reads recipient information from each record.

## 8. Template And Record Rules

### 8.1 Template Types

Supported template types:

```text
Course Feedback
Certificate
Transcript
Payroll
```

### 8.2 Placeholder Format

Template variables use double curly braces:

```text
{{STUDENT_NAME}}
{{STUDENT_EMAIL}}
{{COURSE_NAME}}
{{ISSUE_DATE}}
```

The backend replaces each placeholder with the matching value from the record data.

### 8.3 Record-Level Email Rule

Supported recipient email fields:

```text
STUDENT_EMAIL
EMPLOYEE_EMAIL
USER_EMAIL
RECIPIENT_EMAIL
EMAIL
```

Supported recipient name fields:

```text
STUDENT_NAME
EMPLOYEE_NAME
USER_FULL_NAME
USER_NAME
RECIPIENT_NAME
NAME
```

Template-type-specific priority:

| Template Type | Preferred Email Field | Preferred Name Field |
| --- | --- | --- |
| `Payroll` | `EMPLOYEE_EMAIL` | `EMPLOYEE_NAME` |
| `Transcript` | `STUDENT_EMAIL` | `STUDENT_NAME` |
| `Certificate` | `USER_EMAIL`, `RECIPIENT_EMAIL` | `USER_FULL_NAME`, `RECIPIENT_NAME` |
| Other/default | Any supported email field | Any supported name field |

## 9. API Response Format

JSON API endpoints use this common response shape:

```json
{
  "success": true,
  "data": {},
  "message": "Operation completed"
}
```

Error responses use:

```json
{
  "success": false,
  "data": [],
  "message": "Error message"
}
```

PDF preview/download endpoints return binary PDF responses instead of JSON.

## 10. Error Code Definitions

| HTTP Code | Meaning | Typical Cause |
| --- | --- | --- |
| `200` | Success | Request completed successfully |
| `400` | Bad request | Missing fields, invalid action, invalid template type, PDF not generated, SMTP not configured |
| `401` | Unauthorized | User is not logged in or login credentials are invalid |
| `403` | Permission denied | Admin or another user attempts a write action on a batch/template they do not own |
| `404` | Not found | Resource does not exist or normal user attempts to access another user's private resource |
| `405` | Method not allowed | Endpoint exists but the HTTP method is not accepted |
| `500` | Server error | PDF generation failure, email sending failure, database/runtime exception |

Frontend error handling is centralised in `frontend/src/utils/request.js`. It displays backend messages through Element Plus and redirects unauthorised non-login requests to `/login`.

## 11. API Interface Documentation

All protected endpoints require an active PHP session cookie. The frontend sends cookies through Axios `withCredentials: true`.

### 11.1 Authentication API

Base file:

```text
/api/auth.php
```

| Method | URL | Auth | Body / Params | Description |
| --- | --- | --- | --- | --- |
| `POST` | `/auth.php?action=register` | No | `username`, `email`, `password` | Create a user account and automatically log in |
| `POST` | `/auth.php?action=login` | No | `username`, `password` | Login by username or email |
| `POST` | `/auth.php?action=logout` | Yes | None | Destroy the current session |
| `GET` | `/auth.php?action=user` | Yes | None | Return the current logged-in user |

Register and login return the user object without the password field.

### 11.2 Templates API

Base file:

```text
/api/templates.php
```

| Method | URL | Auth | Body / Params | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/templates.php` | Yes | `search`, `set_type`, `all=true` optional | List templates |
| `GET` | `/templates.php?id={id}` | Yes | `id` | Get one template |
| `POST` | `/templates.php` | Yes | `name`, `content`, `set_type`, `variables` | Create template |
| `PUT` | `/templates.php?id={id}` | Yes | `name`, `content`, `set_type`, `variables` | Update owned template |
| `DELETE` | `/templates.php?id={id}` | Yes | `id` | Delete owned template if it is not used by batches |

Valid `set_type` values are `Course Feedback`, `Certificate`, `Transcript`, and `Payroll`.

Admin can view all templates. Template update and delete remain owner-only.

### 11.3 Batches API

Base file:

```text
/api/batches.php
```

| Method | URL | Auth | Body / Params | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/batches.php` | Yes | `status` optional | List batches |
| `GET` | `/batches.php?id={id}` | Yes | `id` | Get one batch and its records |
| `POST` | `/batches.php` | Yes | `name`, `template_id`, `records`, `email_subject`, `email_body` | Create a batch |
| `PUT` | `/batches.php?id={id}` | Yes | `name`, `status`, `records`, `email_subject`, `email_body` | Update owned batch |
| `DELETE` | `/batches.php?id={id}` | Yes | `id` | Delete owned batch |

Batch records are sent as an array of record objects. Each record is stored as JSON and inserted into `batch_records`.

Example create body:

```json
{
  "name": "Demo Transcript Batch",
  "template_id": 1,
  "email_subject": "Your transcript",
  "email_body": "Dear {{STUDENT_NAME}}, please find your transcript attached.",
  "records": [
    {
      "STUDENT_ID": "S1001",
      "STUDENT_NAME": "Alice Brown",
      "STUDENT_EMAIL": "alice@example.com",
      "COURSE_NAME": "Computer Science",
      "ISSUE_DATE": "2026-04-01"
    }
  ]
}
```

Admin can view all batches and open batch detail pages. Admin cannot edit, delete, generate, or send another user's batch.

### 11.4 PDF API

Base file:

```text
/api/pdf.php
```

| Method | URL | Auth | Body / Params | Description |
| --- | --- | --- | --- | --- |
| `POST` | `/pdf.php?action=generate_batch&id={batchId}` | Yes | `batchId` | Generate PDFs for all records in an owned batch |
| `POST` | `/pdf.php?action=generate_record&id={recordId}` | Yes | `recordId` | Generate a PDF for one owned record |
| `GET` | `/pdf.php?action=preview&id={recordId}` | Yes | `recordId` | Return PDF inline for browser preview |
| `GET` | `/pdf.php?action=download&id={recordId}` | Yes | `recordId` | Return PDF as a downloadable attachment |

PDF generation writes files into:

```text
backend/uploads/pdfs/
```

Preview and download are read operations. Admin can preview or download PDFs from other users' batches when the PDF exists.

### 11.5 Email API

Base file:

```text
/api/email.php
```

| Method | URL | Auth | Body / Params | Description |
| --- | --- | --- | --- | --- |
| `POST` | `/email.php?action=send_batch&id={batchId}` | Yes | `batchId` | Send emails for generated PDFs in an owned batch |
| `POST` | `/email.php?action=send_record&id={recordId}` | Yes | `recordId` | Send one record email |
| `GET` | `/email.php?action=settings` | Yes | None | Get current user's SMTP settings without password |
| `PUT` | `/email.php?action=settings` | Yes | SMTP settings | Create or update current user's SMTP settings |

SMTP settings body:

```json
{
  "smtp_host": "smtp.example.com",
  "smtp_port": 587,
  "smtp_secure": true,
  "smtp_username": "user@example.com",
  "smtp_password": "smtp-password",
  "from_email": "user@example.com",
  "from_name": "PDF Generator"
}
```

Email sending requires:

1. SMTP settings are configured.
2. The target record has `pdf_generated = 1`.
3. The target record contains a supported recipient email field.

Admin cannot send another user's batch or record.

### 11.6 Dashboard API

Base file:

```text
/api/dashboard.php
```

| Method | URL | Auth | Body / Params | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/dashboard.php` | Yes | None | Return counts and recent activity |

Returned data includes:

```text
templates_count
batches_count
pdfs_generated
emails_sent
recent_batches
recent_logs
is_admin
```

Admin dashboard statistics are calculated across all users. Normal users only see their own data.

### 11.7 Logs API

Base file:

```text
/api/logs.php
```

| Method | URL | Auth | Body / Params | Description |
| --- | --- | --- | --- | --- |
| `GET` | `/logs.php` | Yes | `page`, `page_size`, `type`, `search`, `start_date`, `end_date` | List logs with filters |
| `GET` | `/logs.php?export=csv` | Yes | Same filters | Export filtered logs as CSV |

Supported log types:

```text
generation
email
error
system
```

Admin can view/export all users' logs. Normal users can only view/export their own logs.

## 12. Overall Business Workflow

### 12.1 User Workflow

1. Register or log in.
2. Create or import a template.
3. Create a batch from a selected template.
4. Import or manually enter record data.
5. Set one shared email subject and email body for the batch.
6. Save the batch.
7. Open the batch detail page.
8. Generate PDFs.
9. Preview or download generated PDFs.
10. Configure SMTP settings if email sending is required.
11. Send emails.
12. Review results in Logs and Dashboard.

### 12.2 PDF Generation Workflow

```text
Batch Detail
  -> Generate Batch or Generate Record
  -> Backend loads template and record JSON
  -> replacePlaceholders()
  -> TCPDF writes PDF file
  -> batch_records.pdf_path is updated
  -> batch_records.pdf_generated becomes 1
  -> logs row is inserted
```

### 12.3 Email Sending Workflow

```text
Batch Detail
  -> Send Batch or Send Record
  -> Backend checks ownership
  -> Backend checks PDF generation status
  -> Backend resolves recipient from record data
  -> Backend loads SMTP settings
  -> PHPMailer sends email with PDF attachment
  -> batch_records.email_sent becomes 1
  -> logs row is inserted
```

## 13. Permissions And Security

### 13.1 Authentication

The backend uses PHP sessions. Protected API files call `requireAuth()` before executing business logic.

Unauthenticated API access returns `401 Unauthorized`.

### 13.2 Password Handling

Passwords are hashed with PHP `password_hash()` before being stored. Login uses `password_verify()`.

The API never returns the stored password hash.

### 13.3 Ownership Rules

| Resource / Action | Normal User | Admin |
| --- | --- | --- |
| View own templates | Allowed | Allowed |
| View all templates | Not allowed by ownership, except visible template list usage | Allowed |
| Create template | Allowed | Allowed |
| Edit/delete own template | Allowed | Allowed |
| Edit/delete another user's template | Not allowed | Not allowed |
| View own batches | Allowed | Allowed |
| View all batches | Not allowed | Allowed |
| Open another user's batch detail | Not allowed | Allowed |
| Edit/delete another user's batch | Not allowed | Not allowed |
| Generate/send another user's batch | Not allowed | Not allowed |
| Preview/download another user's generated PDF | Not allowed | Allowed |
| View/export all logs | Not allowed | Allowed |

Admin is mainly a monitoring and review role for other users' batches. Cross-user write actions are blocked.

### 13.4 CORS

CORS is configured in:

```text
backend/includes/config.php
```

Default allowed origin:

```text
http://localhost:5173
```

If the frontend port or domain changes, update `ALLOWED_ORIGIN`.

### 13.5 Database Safety

Database operations use prepared statements through PDO. This reduces SQL injection risk for normal query parameters and request data.

## 14. Error Handling Design

### 14.1 Backend

The backend uses helper functions:

```text
sendSuccess($data, $message)
sendError($message, $code)
```

These functions keep JSON response formatting consistent.

### 14.2 Frontend

The frontend Axios client:

```text
frontend/src/utils/request.js
```

handles:

1. API base URL.
2. Session cookies through `withCredentials`.
3. Backend error messages.
4. Login redirection for unauthorised non-login requests.
5. Element Plus message display.

### 14.3 Logging

Generation, email, and error events are written to the `logs` table. Logs include optional batch id, record id, recipient name, recipient email, and JSON details.

## 15. Deployment Guide

### 15.1 Requirements

- PHP >= 8.0
- MySQL or MariaDB
- Node.js and npm
- Browser
- Composer

### 15.2 Database Setup

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

If the demo login fails after previous local testing, re-import the root `pdf_generator.sql` or reset the local user password hashes. An old local database may contain outdated demo passwords.

### 15.3 Backend Configuration

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

### 15.4 Backend Dependencies

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

### 15.5 Frontend Dependencies

The frontend dependencies are not included through `node_modules/`.

Install them first:

```bash
cd frontend
npm install
```

If PowerShell blocks `npm.ps1`, use:

```bash
npm.cmd install
npm.cmd run dev
npm.cmd run build
```

### 15.6 Running The Project

#### Option 1: Use run.bat

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

#### Option 2: Run Commands Manually

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

### 15.7 Build Frontend

To build the frontend:

```bash
cd frontend
npm run build
```

If PowerShell blocks npm scripts:

```bash
npm.cmd run build
```

The output folder is:

```text
frontend/dist/
```

`dist/` is generated build output. It can be rebuilt from the frontend source and should be regenerated after frontend code changes.

## 16. Demo Workflow

1. Log in with `admin` or `user`.
2. Open Templates and review the demo Transcript template.
3. Open Batches and view the demo batch.
4. Open Batch Detail.
5. Generate PDFs.
6. Preview or download generated PDFs.
7. Configure SMTP in Settings if email sending is required.
8. Send emails after SMTP is configured.
9. Review activity in Logs.

## 17. Email / SMTP Notes

The default SQL does not include real SMTP credentials.

To use email sending, configure SMTP settings in the Settings page after logging in.

For Gmail or similar providers, an app password may be required instead of the normal account password.

## 18. Appendix: Naming Conventions

### 18.1 Variable Names

Use uppercase snake case for imported record fields and template placeholders:

```text
STUDENT_NAME
STUDENT_EMAIL
COURSE_NAME
ISSUE_DATE
EMPLOYEE_NAME
EMPLOYEE_EMAIL
USER_FULL_NAME
RECIPIENT_EMAIL
```

Avoid spaces, mixed casing, and punctuation in variable names.

### 18.2 Placeholder Names

Placeholder names must match record field names exactly:

```text
{{STUDENT_NAME}}
```

matches:

```json
{
  "STUDENT_NAME": "Alice Brown"
}
```

### 18.3 PHP Function Naming

Backend PHP functions use camelCase:

```text
sendSuccess
sendError
replacePlaceholders
getEmailRecipient
isAdminUser
```

### 18.4 JavaScript Function Naming

Frontend API helper functions use camelCase:

```text
getBatches
createBatch
generateBatchPDFs
sendRecordEmail
```

### 18.5 Database Naming

Database tables and columns use lower snake case:

```text
batch_records
email_settings
pdf_generated
email_sent
created_at
```

## 19. Quick Verification Checklist

Use this checklist after deployment:

1. `pdf_generator.sql` has been imported.
2. Backend server is running at `http://localhost:8000`.
3. Frontend server is running at `http://localhost:5173`.
4. `backend/includes/config.php` has the correct database settings.
5. Login works with `admin / Password123` or `user / Password123`.
6. Templates page loads the demo Transcript template.
7. Batches page loads the demo batch.
8. PDF generation works from Batch Detail.
9. PDF preview/download works after generation.
10. SMTP settings are configured before email sending.
11. Logs record generation and email activity.


