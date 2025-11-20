# SURATIN - AI Coding Agent Instructions

## Project Overview

SURATIN (Sistem Urus Surat Terintegrasi) is a PHP-based ticket management system for student letter processing, running on XAMPP (Apache/MySQL). It uses a hybrid MVC pattern with REST API endpoints, Bootstrap 5.3.8 UI, and vanilla JavaScript.

## Architecture

### Directory Structure Pattern
- `index.php` - Simple GET-based router (`?page=ticket&action=login`)
- `controller/api/*.php` - JSON REST endpoints (require admin session)
- `controller/config/*.php` - App constants and database singleton
- `model/*.php` - Database operations with PDO
- `view/pages/*.php` - Server-rendered HTML pages
- `view/assets/js/*.js` - Frontend logic (no framework)
- `sql/*.sql` - Schema and sample data files

### Session & Authentication
All admin API endpoints check `$_SESSION['admin_logged_in']` at the top:
```php
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}
```
Public endpoints (ticket submission, status check) do NOT require session checks.

### Database Access Pattern
Always use `getDbConnection()` helper from `controller/config/database.php`:
```php
require_once __DIR__ . '/../controller/config/database.php';
$pdo = getDbConnection(); // Returns singleton PDO instance
```
**Never** instantiate `Database` class directly - use the helper function.

### API Response Format
All APIs return consistent JSON structure:
```php
echo json_encode([
    'success' => true,
    'message' => 'Operation completed',
    'data' => $result,
    'pagination' => ['current_page' => 1, 'per_page' => 50, 'total' => 100]
]);
```

### Configuration Constants
Located in `controller/config/app.php`:
- `APP_TIMEZONE` = 'Asia/Makassar' (UTC+8)
- `APP_ENV` = 'development' (affects `DEBUG_MODE`)
- `SESSION_LIFETIME` = 28800 (8 hours)
- All pages include this file first: `require_once __DIR__ . '/../../controller/config/app.php';`

### Ticket Code Generation
Format: `TCK-YYYYMMDD-XXXX` (e.g., `TCK-20251120-0001`)
- Increments daily counter with 4-digit zero padding
- Logic in `model/Ticket.php::generateTicketCode()`

### Database Schema
Tables: `tickets`, `admins`, `ticket_logs`
- `tickets.data` and `tickets.attachments` are JSON columns
- `tickets.status` enum: `submitted`, `in_review`, `valid`, `rejected`, `generated`
- Every status change creates a `ticket_logs` entry via `TicketLog::create($ticketId, $adminId, $status)`

## Development Workflows

### Database Setup
```powershell
# Run from project root in XAMPP (Apache & MySQL must be running)
php run-schema.php      # Creates database & schema
php run-sample-data.php # Inserts test data
```

### Local Testing
1. Place project in `d:\XAMPP\htdocs\SURATIN`
2. Access via `http://localhost/SURATIN/`
3. Default super admin: username `super`, password `password`

### Frontend-Backend Communication
JavaScript calls APIs with full URL paths:
```javascript
fetch('/SURATIN/controller/api/tickets.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify(data)
})
```
Note: No `/api/` prefix in URLs - direct path to controller files.

## Code Conventions

### PHP Coding Style
- **File requires**: Use `require_once` with `__DIR__` for relative paths
- **Error handling**: Wrap API logic in try-catch, return JSON errors
- **Headers**: Set `Content-Type: application/json` for all API endpoints
- **CORS**: Already configured in API files (`Access-Control-Allow-Origin: *`)

### JavaScript Patterns
- **No frameworks**: Vanilla JS with Bootstrap 5 components
- **Global state**: Top-level vars like `ticketsData`, `currentPage` (see `tickets.js`)
- **Event cleanup**: Functions register listeners for removal on navigation
- **Modals**: Use `bootstrap.Modal` API, check existence before creating

### Model Methods
Standard CRUD pattern in all model classes:
- `create($data)` - Returns `['success' => bool, 'data' => ..., 'error' => ...]`
- `getAll($filters, $page, $limit)` - Returns paginated results
- `getById($id)` - Single record fetch
- `update($id, $data)` - Updates record
- `delete($id)` - Soft or hard delete

### Timezone Handling
Database stores UTC timestamps. PHP uses `Asia/Makassar` via `date_default_timezone_set()`. All `created_at`/`updated_at` use MySQL `NOW()` with `SET time_zone = '+08:00'` connection init.

## Critical Files Reference

- `controller/config/app.php` - Application constants (timezone, debug mode)
- `controller/config/database.php` - Singleton pattern, `getDbConnection()` helper
- `model/Ticket.php` - Ticket CRUD, automatic logging via `TicketLog`
- `view/assets/js/tickets.js` - Admin ticket management UI logic
- `sql/create-schema.sql` - Source of truth for database structure

## Common Pitfalls

1. **Don't use `Database::getInstance()`** - use `getDbConnection()` instead
2. **Admin session required** - Add session check to new API endpoints
3. **JSON columns** - Decode with `json_decode($ticket['data'], true)` in PHP
4. **Absolute paths** - JavaScript uses `/SURATIN/controller/api/...` not relative paths
5. **Status changes** - Always call `TicketLog::create()` after status updates

## Development Notes

- Project follows phased development (see README.md Phase A-D)
- Bootstrap 5.3.8 is vendored in `view/assets/bootstrap-5.3.8/`
- No build tools (no npm, webpack, etc.) - plain PHP/JS/CSS
- File uploads not yet implemented (commented out in `app.php`)
- Templates feature planned but not built yet
