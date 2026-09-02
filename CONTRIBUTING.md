# Contributing to SURATIN

Thank you for considering a contribution. This project works issue-first: every change starts as a GitHub issue and lands through a reviewed pull request.

## Reporting Issues

Open an issue with the bug report or feature request template. Describe the problem with reproduction steps, expected behavior, and your environment.

## Development Setup

Requires PHP and MySQL (XAMPP is the reference environment). From the project root with MySQL running:

```bash
php run-schema.php       # create the database and schema
php run-sample-data.php  # insert sample data
php -l <modified-file>   # PHP syntax check
```

## Pull Request Process

1. Create a working branch from `develop` named `feature/`, `fix/`, `chore/`, `docs/`, or `refactor/`.
2. Make atomic commits with Conventional Commit messages (`feat`, `fix`, `docs`, `refactor`, `test`, `chore`).
3. Run `php -l` on modified files and check the app in the browser before pushing.
4. Open a pull request into `develop` that references the issue with `Fixes #N`.
5. Wait for green CI and review approval before merging.

## Style

Follow the conventions already used in the codebase: `require_once` with `__DIR__` for relative paths, the `getDbConnection()` PDO helper for all database access, admin API endpoints guarded by the session check and returning a consistent JSON shape (`success`, `message`, `data`), and a `TicketLog::create()` entry on every ticket status change.
