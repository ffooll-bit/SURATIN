# SURATIN Development Plan

_The original development plan captured before the repository was aligned to the workflow standard (workflow adoption). The content below is the full English version of the historical plan; the original text is preserved in the repository history._

# SURATIN (Integrated Letter Management System)

SURATIN (Sistem Urus Surat Terintegrasi) is a web application that supports efficient letter management.

## Phase A — General preparation (before coding)

1. Create repository and initial branches
   * `main` / `develop` / `ui` (start on `ui` for the UI work)
   * Web project README (purpose, stack: PHP + MySQL, Bootstrap/Tailwind, XAMPP)
2. Define the Copilot workflow tooling
   * Small commit rule (1 UI feature per PR/commit), commit description, and file scope.
3. Set up the local XAMPP environment
   * Empty database, virtual host (e.g. `surat.local`), project folder under `htdocs/`

## Phase B — UI design (main priority)

Goal: produce HTML/CSS/JS components that can be tested without a backend (mock data). Focus on ease for reviewers/admins.

### 1. Public pages (Ticketing / input form)

* Components/form:
  * Ticket input form (dynamic fields according to letter type): Name, NPM, Study Program (Prodi), Letter Type (dropdown), Attachment (upload), Email, WA number.
  * Submit button + summary modal.
  * Client-side validation (JS): required, email format, file size.
* Success page + ticket number (ticket ID displayed) + CTA (check status).
* Responsive components (mobile + desktop).
* Microcopy & helper text (example NPM format, file size).

### 2. Public ticket status check page

* Input: Ticket number / Email + simple captcha (optional).
* Status bar: Submitted → In Review → Valid / Rejected → Letter generated (link/download).
* Activity history (brief log).

### 3. Admin page (access via login)

* Compact dashboard: active ticket count, recent tickets, notifications (bell).
* Ticket list (table) with columns: ID, Name, Letter Type, Date, Status, Actions (Review, Detail).
  * Filters (status, letter type), search (name/NPM/ID).
* Ticket detail view:
  * Show all input data + document preview (if the template data is complete, show a preview placeholder) + attachment.
  * Buttons: Mark as Valid → Generate Letter, Mark as Invalid → Send Rejection (reason template).
  * Button to edit minor data before generating.
* Template management (higher-level Admin):
  * Upload a `.docx` template, set placeholder-to-field mapping.
* Numbering settings: set zero padding, last_number, mode (skip/offset).

### 4. Modals & UI patterns

* Confirmation modal (generate letter), reject reason modal.
* Toasts / alerts for feedback.
* File upload component (drag & drop).

### 5. Style system

* Choose: **Bootstrap** (faster for Copilot) or **Tailwind** (more flexible).
  * Recommendation: **Bootstrap 5** when speed and out-of-the-box components matter; **Tailwind** for a modern custom UI.
* Create design tokens: brand colors (success, warning, danger), spacings, typography.

## Phase C — Front-End / Back-End structure (separation)

Goal: the front-end talks to the back-end through a JSON API; admin login via session.

### Application configuration

File `controller/config/app.php` contains:
* **Timezone**: Asia/Makassar (UTC+8)
* **Date format**: Indonesian format
* **File upload**: max 5MB, allowed types
* **Session**: 8 hour lifetime
* **Debug mode**: based on environment

### File structure (suggestion)

```
/SURATIN
├─ index.php                   # Main landing page / gateway
├─ view/                       # Static front-end (HTML/CSS/JS)
│  ├─ assets/                  # CSS, JS, images, fonts
│  │  ├─ css/
│  │  ├─ js/
│  │  └─ img/
│  ├─ pages/                   # Main pages
│  │  ├─ ticket-form.php       # Ticket request form (uses app.php configuration)
│  │  ├─ check-status.php      # Ticket status check (uses app.php configuration)
│  │  ├─ success.php           # Successful submit page (uses app.php configuration)
│  │  ├─ admin-login.php       # Admin login (uses app.php configuration)
│  │  └─ admin-dashboard.php   # Admin dashboard (uses app.php configuration)
│  └─ components/              # Reusable components (modals, etc.)
├─ controller/                 # PHP logic & API endpoints
│  ├─ api/                     # REST API endpoints
│  │  ├─ tickets.php           # CRUD tickets
│  │  ├─ auth.php              # Admin login/logout
│  │  ├─ dashboard.php         # Dashboard statistics
│  │  ├─ templates.php         # Template management
│  │  └─ status.php            # Public status check
│  ├─ config/                  # Configuration
│  │  ├─ app.php               # App settings & timezone
│  │  └─ database.php          # DB connection
│  └─ helpers/                 # Helper functions
├─ model/                      # PHP for database & data layer
├─ uploads/                    # File uploads (attachments)
├─ storage/                    # Generated files
└─ sql/                        # Database scripts
```

> Note: `index.php` is the entry point that can route to the right page. Files in `view/pages/` use the configuration from `controller/config/app.php` for application-data consistency.

## Application Configuration

### Central Configuration (`controller/config/app.php`)

SURATIN uses a centralized configuration system that enables consistent management across the whole application:

```php
// Application Settings
define('APP_NAME', 'SURATIN');
define('APP_DESCRIPTION', 'Sistem Urus Surat Terintegrasi');
define('APP_VERSION', '1.0.0');
define('APP_DEV', 'FFOOLL-BIT');
define('APP_ENV', 'development'); // development, staging, production

// Timezone Settings
define('APP_TIMEZONE', 'Asia/Makassar');
define('DATE_FORMAT', 'Y-m-d H:i:s');
define('DISPLAY_DATE_FORMAT', 'd/m/Y H:i');

// Session Settings
define('SESSION_LIFETIME', 3600 * 8); // 8 hours in seconds

// Debug Mode (automatic based on environment)
define('DEBUG_MODE', APP_ENV === 'development');
```

### Integration with Pages

All files in `view/pages/` have been converted to PHP to use this configuration:

- **Dynamic Title**: `<?= APP_NAME; ?>` — consistent application name
- **Meta Description**: uses `APP_DESCRIPTION` for SEO
- **Footer Information**: displays the developer (`APP_DEV`) and the automatic year
- **Debug Mode**: visual indicator while in development mode
- **Version Display**: shows the application version in the admin area
- **JavaScript Config**: configuration is available to the front-end through `APP_CONFIG`

### Environment-based Features

- **Development Mode**:
  - Debug indicator in the top-right corner
  - More verbose console logging
  - Fuller mock data
- **Production Mode**:
  - Debug features disabled
  - Reduced error reporting
  - Performance optimizations

## Phase D — Database Schema (MySQL)

Minimal schema for ticketing + admin + templates + numbering:

```sql
-- tickets
CREATE TABLE tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_code VARCHAR(32) UNIQUE, -- human readable e.g. TCK-20251029-0001
  nama VARCHAR(255),
  npm VARCHAR(50),
  prodi VARCHAR(100),
  jenis_surat VARCHAR(100),
  data JSON,           -- flexible: stores additional fields
  attachments JSON,    -- array {name,path}
  email VARCHAR(255),
  wa VARCHAR(30),
  status ENUM('submitted','in_review','valid','rejected','generated') DEFAULT 'submitted',
  admin_note TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
);

-- users/admin
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) UNIQUE,
  password_hash VARCHAR(255),
  name VARCHAR(255),
  email VARCHAR(255),
  role ENUM('admin','super') DEFAULT 'admin',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- templates
CREATE TABLE templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255),
  filename VARCHAR(255),
  placeholder_map JSON, -- {"{nama_mahasiswa}":"nama", ...}
  zero_padding INT DEFAULT 3,
  last_number INT DEFAULT 0,
  last_number_mode ENUM('skip','offset') DEFAULT 'skip',
  active TINYINT(1) DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- generated letters
CREATE TABLE letters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT,
  template_id INT,
  nomor_surat VARCHAR(100),
  output_file VARCHAR(255),
  qr_code VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## Phase E — API Contract (important endpoints)

All endpoints under `controller/api/*` return JSON.

### Public (no auth)

* `POST /controller/api/tickets.php` — submit ticket (form payload / multipart for files) → returns `{ticket_code, status}`
* `GET /controller/api/status.php?ticket_code=...&email=...` — status check

### Admin (session auth or token)

* `POST /controller/api/auth.php` — admin login → sets session
* `GET /controller/api/tickets.php` — list tickets (filter params)
* `GET /controller/api/tickets.php?id={id}` — ticket detail
* `POST /controller/api/tickets.php` with action=validate — mark valid (body: template_id, optional edits)
* `POST /controller/api/tickets.php` with action=reject — mark rejected (body: reason)
* `POST /controller/api/tickets.php` with action=generate — force letter generation
* `GET /controller/api/templates.php` — list templates
* `POST /controller/api/templates.php` — upload template

## Phase F — Back-End Functionality (logic)

1. **Upload & store attachments**
   * Save to `uploads/{ticket_code}/` and record the path in the DB.
2. **Ticket creation**
   * Generate a unique `ticket_code`, store `data` as JSON.
   * Notify admins (email/WA) that a new ticket has arrived.
3. **Admin review flow**
   * Admin opens the detail → edit/approve/reject.
   * On approval, the backend validates the data (required fields) → if valid, generate the letter.
4. **Automatic letter generation**
   * Take the `.docx` template → replace placeholders with data.
   * Numbering: use `templates.last_number` & `zero_padding` & `last_number_mode` (mirroring the README logic). Update `last_number` after generation.
   * Convert DOCX → PDF (optional) — tools: LibreOffice headless (command line) or a PHP library (pandoc, unoconv).
   * Save the file, insert a row in `letters`.
   * Generate a QR code (content: verification link / letter number) — save the image.
5. **Notifications**
   * Email: SMTP library (PHPMailer) or system SMTP (Gmail/SendGrid).
   * WA: two options — (a) WhatsApp Business API (official, requires approval), (b) third-party gateway (depends on policy). Send the content template (valid/rejected/generate link).
   * Provide a retry & queue mechanism (a simple `notifications` table or job queue).
6. **Security**
   * Admin auth: password hash (bcrypt), secure session cookie, CSRF token in admin forms.
   * File upload: restrict extension, scan MIME type, store outside the web root when needed.
   * Rate-limit the submit form, captchas if there is spam.

## Phase G — Document (.docx) integration and numbering

1. Choose a PHP library to manipulate DOCX:
   * `phpoffice/phpword` to replace placeholders.
2. Numbering: implement a `generate_nomor(template_id)` function that accounts for `last_number_mode` (copy the logic from the README: skip vs offset) in `model/Template.php`.
3. Convert DOCX → PDF:
   * Prefer LibreOffice headless on the local server (`libreoffice --headless --convert-to pdf ...`) or use a conversion service.
4. QR Code: use `endroid/qr-code` or PHP GD.

## Phase H — Notifications & Queue

1. Create a `notifications` table (id, target, channel, payload, status, retries, last_attempt).
2. Worker (cron) to send notifications, retry, and mark failed.
3. Channels: `email`, `whatsapp` (WA integration: send phone number + template).

## Phase I — Testing & Deployment (local → production)

1. Unit tests for the numbering function, placeholder mapping, and API endpoints (PHPUnit can be used).
2. UAT: run the entire flow — submit ticket → admin approve → generate → notify.
3. Deployment: XAMPP (dev). For production: Apache/Nginx + PHP-FPM, DB backup, SSL.

## Implementation checklist (Copilot-friendly order)

> Focus is for Copilot to build the UI components first; each task is a small unit.

### UI sprint (early stage)

1. [x] Create `index.php` as the landing page with simple routing.
2. [x] Create the `view/pages/ticket-form.php` page (using the app.php configuration).
3. [x] Create `view/pages/success.php` (using the app.php configuration).
4. [x] Create `view/pages/check-status.php` (using the app.php configuration).
5. [x] Create `view/pages/admin-login.php` (using the app.php configuration).
6. [x] Create `view/pages/admin-dashboard.php` (using the app.php configuration).
7. [x] `view/components/dashboard-content.html` component with real data and an activity modal.
8. [x] Responsiveness & accessibility check.

### API & DB sprint (after the UI is ready)

9. [x] Create the DB schema (`sql/create-schema.sql`) and `controller/config/database.php`.
10. [x] Implement `model/Ticket.php` for CRUD operations.
11. [x] Implement `controller/api/dashboard.php` for statistics and real-time activity.
12. [x] Set up `controller/config/app.php` for timezone and application configuration.
13. [ ] Implement `controller/api/tickets.php` (saving: tickets + files).
14. [ ] Implement `controller/api/status.php` for the public status check.
15. [x] Implement `model/Admin.php` and `controller/api/auth.php`.
16. [ ] Implement ticket review in `controller/admin/ticket-review.php`.
17. [ ] Implement `model/Template.php` and the template manager.
18. [ ] Implement letter generation in `controller/helpers/docx-generator.php`.
19. [ ] Implement notifications in `controller/helpers/mailer.php` and `whatsapp.php`.
20. [ ] Add logging and error handling.

### Finishing sprint

19. [ ] Tests: unit + integration for the full flow.
20. [ ] Security audit: file upload, auth, SQL injection, XSS.
21. [ ] Prepare docs: how-to-run, env vars (SMTP, WA credentials), endpoint list.
22. [ ] Optional: add a public QR verification page (letter number verification).

## Quick implementation tips (snippets)

* **Ticket code**: `TCK-YYYYMMDD-<4digit>` generated in `model/Ticket.php`.
* **Replace placeholders** (phpword example in `controller/helpers/docx-generator.php`):
  ```php
  $templateProcessor->setValue('{nama_mahasiswa}', $data['nama']);
  ```
* **Generate number**: copy the `last_number_mode` logic from the README (skip vs offset) in `model/Template.php`.
* **Email notifications**: use PHPMailer in `controller/helpers/mailer.php`, never hardcode credentials; use ENV.
* **WA integration**: create an adapter interface in `controller/helpers/whatsapp.php` and implement a local stub first.

## Important notes / recommendations

* Because there is **no login for users**, make sure ticket_code + email are enough to verify status. Do not expose sensitive data.
* For WhatsApp: the official API requires business verification; during development use email + an SMS fallback (if needed) or in-app UI notifications (dashboard).
* Store template mapping in the DB so admins can change it without deploying code.
* Use a job queue (even a simple cron that polls the DB) for heavy processes: DOCX conversion, WA delivery.
