# IMPROVEMENTS

_The tracker for feature ideas, found bugs, and optimization plans. Each finding is recorded as an item under Items: copy the template below, fill it in, and place the item at the very bottom of the Items section._

## Item Template

```markdown
### <ID> — <Title>
- **Status:** `recorded` | `verified` | `rejected` | `implemented`
- **Issue:** <#NN> | `—`
- **Recorded:** YYYY-MM-DD HH:MM
- **Implemented:** YYYY-MM-DD HH:MM | `—`
- **Problem:** ...
- **Possible Fix:** ...
- **Actual Fix:** ...
- **Rejection Reason:** ...
- **Actual Implemented:** ...
- **Changes:** ...
```

Item IDs follow the format `<LABEL_CODE>-<NNN>` built from the default GitHub labels, with numbers counted per label code:

| GitHub Label | Code |
|--------------|------|
| `bug` | BUG |
| `documentation` | DOC |
| `enhancement` | ENH |
| `duplicate` | DUP |
| `good first issue` | GFI |
| `help wanted` | HW |
| `invalid` | INV |
| `question` | QST |
| `wontfix` | WFX |

## Items

### BUG-001 — Default admin credentials are documented and shipped in sample data
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** `sql/sample-data.sql` seeds every admin account with the well-known bcrypt hash of `password` (`$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi`), and `.github/copilot-instructions.md` documents the default `super` / `password` login, so any deployed instance keeps a publicly known admin credential.
- **Possible Fix:** Generate per-install password hashes, move default credentials into environment configuration, and remove the plaintext default from the repository documentation.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-001 — Implement ticket submission storage with file uploads
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** `controller/api/tickets.php` does not yet persist tickets with attachments; the upload flow is left un-built (attachment handling is commented out in `controller/config/app.php`).
- **Possible Fix:** Implement ticket creation that stores the record plus uploaded files under `uploads/` per ticket, per the original plan.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-002 — Implement public status check endpoint
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** The public ticket status check (`controller/api/status.php`) is not implemented.
- **Possible Fix:** Build the status endpoint that returns ticket state and activity history from `ticket_code` plus contact.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-003 — Implement admin ticket review flow
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** The admin review actions (validate / reject a ticket from the detail view) are not implemented.
- **Possible Fix:** Add the review endpoints and wire the dashboard action buttons to them.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-004 — Implement template management
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** `model/Template.php` and the template manager (upload `.docx` and map placeholders to fields) are not implemented.
- **Possible Fix:** Implement the template model and the upload / mapping UI on top of `templates` in the schema.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-005 — Implement DOCX generation with placeholder replacement
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** Letter generation from a `.docx` template with placeholder replacement (`controller/helpers/docx-generator.php`) is not implemented.
- **Possible Fix:** Implement placeholder replacement plus numbering based on `last_number_mode` / `zero_padding`, updating the counters after each generation.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-006 — Implement notifications
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** Email (`controller/helpers/mailer.php`) and WhatsApp (`controller/helpers/whatsapp.php`) notification adapters are not implemented.
- **Possible Fix:** Build adapter interfaces with a local stub first, keeping credentials out of the code.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-007 — Add logging and error handling
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** Centralized logging and error handling are not implemented.
- **Possible Fix:** Add a logging layer and consistent error responses across the API.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-008 — Add unit and integration tests for the full flow
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** No automated tests exist; the submit → review → generate → notify flow is untested.
- **Possible Fix:** Add unit tests for numbering and placeholder mapping and integration tests for the API endpoints.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-009 — Security audit
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** No security audit has been run on file upload, authentication, SQL injection, and XSS surfaces.
- **Possible Fix:** Audit those surfaces and harden the vulnerable points.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-010 — Prepare operational documentation
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** How-to-run instructions, environment variable list (SMTP, WhatsApp credentials), and the endpoint list are not documented.
- **Possible Fix:** Write the operational docs covering setup, env vars, and endpoints.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`

### ENH-011 — Optional public QR verification page
- **Status:** `recorded`
- **Issue:** `—`
- **Recorded:** 2026-09-02 09:39
- **Implemented:** `—`
- **Problem:** The optional public page to verify a generated letter number via QR is not implemented.
- **Possible Fix:** Add the verification page once letter generation and QR codes exist.
- **Actual Fix:** `—`
- **Rejection Reason:** `—`
- **Actual Implemented:** `—`
- **Changes:** `—`
