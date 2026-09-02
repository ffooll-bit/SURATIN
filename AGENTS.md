# AGENTS.md

Operating notes for AI agents working on SURATIN (Sistem Urus Surat Terintegrasi) — a PHP/MySQL ticket system for student letter processing. Detailed architecture lives in `.github/copilot-instructions.md`; this file covers what that file gets wrong and what is only learnable from the code.

## Stack and runtime

- Plain PHP 8 + MySQL (PDO), Bootstrap 5.3.8 and bootstrap-icons 1.13.1 vendored under `view/assets/`. No Composer, no npm, no build step, no unit test suite. CI (`/.github/workflows/ci.yml`) only fails on CRLF or BOM in `.md` files.
- Reference environment is XAMPP. DB: `suratin_db`, user `root`, empty password (see `controller/config/database.php`).
- Setup from project root with MySQL running: `php run-schema.php`, then `php run-sample-data.php`. App runs at `http://localhost/SURATIN/`. Seeded admin: `super` / `password` (tracked as BUG-001 in `docs/IMPROVEMENTS.md`).
- No migration system — `sql/create-schema.sql` is the source of truth for tables `admins`, `tickets`, `ticket_logs`.

## Architecture (current, verified in code)

- `index.php` is a tiny GET router: `?page=home|ticket|status|success|admin` (`action=login|logout`).
- `controller/api/*.php` — JSON REST endpoints: `tickets.php`, `dashboard.php`, `auth.php`, `export-tickets.php`, `server-time.php`. Admin endpoints guard `$_SESSION['admin_logged_in']` at the top and return `401` JSON otherwise.
- `controller/config/app.php` defines constants (APP_ENV, SESSION_LIFETIME, APP_TIMEZONE `Asia/Makassar`, DB UTC offset `+08:00`); always `require_once` it first. MySQL connection init sets `SET time_zone='+08:00'`; timestamps come from `NOW()`.
- DB access: use the `getDbConnection()` helper — never instantiate `Database` directly. All models are PDO-based (`model/Ticket.php`, `model/TicketLog.php`).
- **Ticket model**: code `TCK-YYYYMMDD-XXXX`; status enum `submitted|in_review|valid|rejected|generated`; every status change must write a `ticket_logs` row via `TicketLog::create()` (initial `submitted` has `admin_id = NULL`).
- **Admin dashboard is a lazy-loaded SPA-lite**: `view/pages/admin-dashboard.php` defines a `SectionManager` that fetches one HTML section from `view/components/*.html` (e.g. `tickets-content.html`) plus its JS file, then calls a `window.initializeX` init function. JS files (`view/assets/js/dashboard.js`, `tickets.js`, `templates.js`) are IIFE modules with module-scoped state; each registers its functions/intervals for cleanup via `window.registerGlobalFunction` / `registerInterval` / `registerTimeout` so nothing leaks when the section is switched.

## Gotchas an agent will otherwise trip on

- JS `fetch` calls use **relative paths** (`controller/api/tickets.php`, `./controller/api/dashboard.php`) — no `/api/` prefix, not absolute. The older `.github/copilot-instructions.md` claims JS uses absolute `/SURATIN/...` paths and top-level globals; both are outdated since the IIFE refactor (commit `b187847`).
- The **templates feature is a frontend-only prototype**: `templates.js` + `templates-content.html` exist but there is no `templates.php` controller, no model, and no schema table — all state is client-side. Do not assume a server API for templates exists.
- The dashboard references a **settings section** (`settings-content.html`, `settings.js`) that does **not exist** — selecting Settings in the sidebar renders an error box. Don't treat that as a regression.
- Admin auth: session flag `admin_logged_in` only; no CSRF tokens, single-factor. Keep that in mind for any new endpoint (add the session guard yourself).
- Formatting helpers in `app.php` are dead code (commented out); `getTimeAgo()` is the only live one.

## Project conventions

- Docs, commits, issues: **International English**. UI text: Indonesian. Chat with USER: Indonesian. (Project policy #304.)
- LF line endings and UTF-8 without BOM everywhere, enforced by `.editorconfig`, `.gitattributes`, and CI. Markdown is NOT hard-wrapped (except LICENSE).
- Git workflow is governed by the GAIN-CODING playbook: issue-driven, one branch per issue off `develop` (currently checked out), Conventional Commits, PR into `develop` (which merges into `main` at release), no direct push, CI must pass before merge. Manual checks: `git status`/`git diff` before any commit.
- Improvement ideas go through `docs/IMPROVEMENTS.md` (ID format `<LABEL_CODE>-<NNN>`) before becoming GitHub issues.
- Tool scripts live in `temp/` (gitignored). `temp/check-hardwrap.ps1` checks for hard-wrapped Markdown.

## Verification

- No test harness exists. Verify changes by running the app on a local XAMPP instance (schema + sample data above) and exercising the affected flow, since JS is loaded dynamically per dashboard section.