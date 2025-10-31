# SURATIN
SURATIN (Sistem Urus Surat Terintegrasi) adalah aplikasi berbasis web yang membantu pengurusan surat secara efisien

## Phase A — Persiapan umum (sebelum coding)

1. Buat repository dan branch awal
   * `main` / `develop` / `ui` (mulai di `ui` untuk tampilan)
   * README web project (tujuan, stack: PHP + MySQL, Bootstrap/Tailwind, XAMPP)
2. Tentukan tooling Copilot workflow
   * Aturan commit kecil (1 fitur UI per PR/commit), deskripsi commit, dan file scope.
3. Siapkan environment lokal XAMPP
   * Database kosong, virtual host (mis. `surat.local`), folder project di `htdocs/`

## Phase B — Desain UI (prioritas utama)

Tujuan: hasilkan komponen HTML/CSS/JS yang bisa diuji tanpa backend (mock data). Fokus pada kemudahan reviewer/admin.

### 1. Halaman publik (Ticketing / Form input)

* Komponen/form:
  * Form pengisian ticket (field dinamis sesuai jenis surat): Nama, NPM, Prodi, Jenis Surat (dropdown), Lampiran (upload), Email, No WA.
  * Tombol Submit + summary modal.
  * Validasi sisi-klien (JS): required, format email, ukuran file.
* Halaman success + nomor ticket (ID ticket ditampilkan) + CTA (cek status).
* Komponen responsif (mobile + desktop).
* Microcopy & helper text (contoh format NPM, file size).

### 2. Halaman cek status ticket (publik)

* Input: Nomor ticket / Email + Captcha sederhana (opsional).
* Status bar: Submitted → In Review → Valid / Rejected → Surat Dihasilkan (link/download).
* Riwayat aktivitas (log brief).

### 3. Halaman admin (akses via login)

* Dashboard ringkas: jumlah ticket aktif, ticket baru (recent), notifikasi (bell).
* Ticket list (tabel) dengan kolom: ID, Nama, Jenis Surat, Tanggal, Status, Aksi (Review, Detail).
  * Filter (status, jenis surat), search (nama/NPM/ID).
* Ticket detail view:
  * Tampilkan semua data input + preview dokumen (jika template data sudah lengkap show preview placeholder) + lampiran.
  * Tombol: Mark as Valid → Generate Surat, Mark as Invalid → Kirim Rejection (template alasan).
  * Tombol untuk edit minor data sebelum generate.
* Template management (Admin level higher):
  * Upload template .docx, set mapping placeholder ke field.
* Pengaturan penomoran: set zero padding, last_number, mode (skip/offset).

### 4. Modals & UI patterns

* Modal konfirmasi (generate surat), modal alasan reject.
* Toast / Alerts untuk feedback.
* Komponen file upload (drag & drop).

### 5. Style system

* Pilih: **Bootstrap** (faster for Copilot) atau **Tailwind** (lebih fleksibel).
  * Saya rekomendasi: **Bootstrap 5** jika ingin cepat dan siap pakai; **Tailwind** kalau mau custom UI modern.
* Buat design tokens: warna brand (success, warning, danger), spacings, typografi.

## Phase C — Struktur Front-End / Back-End (separasi)

Tujuan: front-end berkomunikasi ke back-end lewat API JSON; admin login via session.

### Struktur file (saran)

```
/SURATIN
├─ index.php                   # Landing page utama / gateway
├─ view/                       # Frontend statis (HTML/CSS/JS)
│  ├─ assets/                  # CSS, JS, images, fonts
│  │  ├─ css/
│  │  ├─ js/
│  │  └─ img/
│  ├─ ticket-form.html         # Form pengajuan ticket
│  ├─ check-status.html        # Cek status ticket
│  ├─ success.html             # Halaman sukses submit
│  ├─ admin/                   # Halaman admin (protected)
│  │  ├─ login.html            # Login admin
│  │  ├─ dashboard.html        # Dashboard admin
│  │  ├─ ticket-detail.html    # Detail ticket
│  │  └─ template-manager.html # Kelola template
│  └─ components/              # Komponen reusable (modals, etc)
├─ controller/                 # PHP logika & API endpoints
│  ├─ api/                     # REST API endpoints
│  │  ├─ tickets.php           # CRUD tickets
│  │  ├─ auth.php              # Login/logout admin
│  │  ├─ templates.php         # Kelola template
│  │  └─ status.php            # Cek status public
│  ├─ admin/                   # Admin panel controllers
│  │  ├─ dashboard.php         # Logic dashboard
│  │  ├─ ticket-review.php     # Review & approve ticket
│  │  └─ template-upload.php   # Upload & manage templates
│  ├─ helpers/                 # Helper functions
│  │  ├─ mailer.php            # Email sender
│  │  ├─ docx-generator.php    # Generate dokumen
│  │  ├─ whatsapp.php          # WA integration
│  │  └─ utils.php             # General utilities
│  └─ config/                  # Konfigurasi
│     ├─ database.php          # DB connection
│     └─ app.php               # App settings
├─ model/                      # PHP untuk database & data layer
│  ├─ Ticket.php               # Model ticket operations
│  ├─ Admin.php                # Model admin/user
│  ├─ Template.php             # Model template dokumen
│  ├─ Letter.php               # Model surat yang dihasilkan
│  └─ Database.php             # Base database class
├─ uploads/                    # File uploads (lampiran)
│  └─ tickets/                 # Organized by ticket_code
├─ storage/                    # Generated files
│  ├─ letters/                 # Surat yang dihasilkan (.docx/.pdf)
│  ├─ templates/               # Template .docx
│  └─ qrcodes/                 # QR code images
├─ sql/
│  └─ schema.sql               # Database schema
└─ README.md
```

> Catatan: `index.php` sebagai entry point yang bisa routing ke halaman yang tepat. File di `view/` adalah HTML statis yang berkomunikasi dengan `controller/api/` melalui AJAX/fetch.

## Phase D — Skema Database (MySQL)

Skema minimal untuk ticketing + admin + templates + numbering:

```sql
-- tickets
CREATE TABLE tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_code VARCHAR(32) UNIQUE, -- human readable e.g. TCK-20251029-0001
  nama VARCHAR(255),
  npm VARCHAR(50),
  prodi VARCHAR(100),
  jenis_surat VARCHAR(100),
  data JSON,           -- fleksibel: menyimpan field tambahan
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

## Phase E — API Contract (endpoints penting)

Semua endpoint di `controller/api/*` mengembalikan JSON.

### Publik (no auth)

* `POST /controller/api/tickets.php` — submit ticket (payload form / multipart for files) → returns `{ticket_code, status}`
* `GET /controller/api/status.php?ticket_code=...&email=...` — cek status

### Admin (session auth or token)

* `POST /controller/api/auth.php` — login admin → sets session
* `GET /controller/api/tickets.php` — list tickets (params filter)
* `GET /controller/api/tickets.php?id={id}` — detail ticket
* `POST /controller/api/tickets.php` dengan action=validate — mark valid (body: template_id, optional edits)
* `POST /controller/api/tickets.php` dengan action=reject — mark reject (body: reason)
* `POST /controller/api/tickets.php` dengan action=generate — force generate surat
* `GET /controller/api/templates.php` — list templates
* `POST /controller/api/templates.php` — upload template

## Phase F — Fungsional Back-End (logika)

1. **Upload & store attachments**
   * Simpan di `uploads/{ticket_code}/` dan catat path di DB.
2. **Ticket creation**
   * generate `ticket_code` (unik), simpan `data` JSON.
   * kirim notifikasi ke admin (email/WA) bahwa ada ticket baru.
3. **Admin review flow**
   * Admin membuka detail → edit/approve/reject.
   * Saat approve, backend memvalidasi data (required fields) → jika ok lanjut generate surat.
4. **Generate surat otomatis**
   * Ambil template .docx → replace placeholders dengan data.
   * Penomoran: gunakan `templates.last_number` & `zero_padding` & `last_number_mode` (mirror README logic). Update last_number setelah generate.
   * Konversi DOCX → PDF (opsional) — tools: LibreOffice headless (commandline) atau PHP library (pandoc, unoconv).
   * Simpan file, insert row di `letters`.
   * Generate QR code (isi: link verifikasi / nomor surat) — simpan image.
5. **Notifikasi**
   * Email: SMTP library (PHPMailer) atau system SMTP (Gmail/SendGrid).
   * WA: dua pilihan — (a) WhatsApp Business API (resmi, butuh approval), (b) gateway pihak ketiga (tergantung kebijakan). Kirim isi template (valid/rejected/generate link).
   * Pastikan retry & queue mechanism (simple table `notifications` atau job queue).
6. **Security**
   * Admin auth: password hash (bcrypt), session cookie secure, CSRF token di admin forms.
   * File upload: batasi extension, scan mime-type, simpan outside webroot jika perlu.
   * Rate-limit submit form, captchas jika spam.

## Phase G — Integrasi dokumen (.docx) dan penomoran

1. Pilih library PHP untuk memanipulasi DOCX:
   * `phpoffice/phpword` untuk replace placeholders.
2. Penomoran: implementasikan fungsi `generate_nomor(template_id)` yang memperhitungkan `last_number_mode` (copy logic dari README).
3. Convert DOCX → PDF:
   * Prefer LibreOffice headless di server lokal (`libreoffice --headless --convert-to pdf ...`) atau gunakan layanan konversi.
4. QR Code: gunakan `endroid/qr-code` atau PHP GD.

## Phase H — Notifikasi & Queue

1. Buat tabel `notifications` (id, target, channel, payload, status, retries, last_attempt).
2. Worker (cron) untuk mengirim notifikasi, retry, mark failed.
3. Channels: `email`, `whatsapp` (WA integration: send phone number + template).

## Phase I — Testing & Deployment (lokal → production)

1. Unit tests untuk fungsi penomoran, placeholder mapping, API endpoints (bisa memakai PHPUnit).
2. UAT: jalankan seluruh alur — submit ticket → admin approve → generate → notifikasi.
3. Deployment: XAMPP (dev). Untuk production: Apache/Nginx + PHP-FPM, backup DB, SSL.

## Checklist implementasi (urutan pekerjaan Copilot-friendly)

> Fokus agar Copilot bisa buat komponen UI dulu; tiap task adalah unit kecil.

### Sprint UI (tahap awal)

1. [x] Buat `index.php` sebagai landing page dengan routing sederhana.
2. [x] Buat halaman `view/ticket-form.html` (static) — form lengkap + client validation.
3. [x] Buat `view/success.html` dengan menampilkan ticket_code (mock).
4. [x] Buat `view/check-status.html` + mock status responses.
5. [x] Buat `view/admin/login.html` (form login).
6. [x] Buat `view/admin/dashboard.html` static (tabel sample tickets).
7. [x] Komponen modal, toast, file upload di `view/components/`.
8. [x] Responsiveness & accessibility check.

### Sprint API & DB (setelah UI siap)

9. [ ] Buat DB schema (`sql/schema.sql`) dan `model/Database.php`.
10. [ ] Implement `model/Ticket.php` untuk CRUD operations.
11. [ ] Implement `controller/api/tickets.php` (simpan: tickets + files).
12. [ ] Implement `controller/api/status.php` untuk cek status publik.
13. [ ] Implement `model/Admin.php` dan `controller/api/auth.php`.
14. [ ] Implement ticket review di `controller/admin/ticket-review.php`.
15. [ ] Implement `model/Template.php` dan template manager.
16. [ ] Implement generate surat di `controller/helpers/docx-generator.php`.
17. [ ] Implement notifikasi di `controller/helpers/mailer.php` dan `whatsapp.php`.
18. [ ] Add logging dan error handling.

### Sprint finishing

19. [ ] Tests: unit + integration for full flow.
20. [ ] Security audit: file upload, auth, SQL injection, XSS.
21. [ ] Prepare docs: how-to-run, env vars (SMTP, WA creds), endpoints list.
22. [ ] Optional: add QR verification public page (verifikasi nomor surat).

## Petunjuk implementasi cepat (snippet & tips)

* **Ticket code**: `TCK-YYYYMMDD-<4digit>` generated di `model/Ticket.php`.
* **Replace placeholders** (contoh phpword di `controller/helpers/docx-generator.php`):
  ```php
  $templateProcessor->setValue('{nama_mahasiswa}', $data['nama']);
  ```
* **Generate nomor**: salin logic `last_number_mode` dari README (skip vs offset) di `model/Template.php`.
* **Notifikasi email**: gunakan PHPMailer di `controller/helpers/mailer.php`, jangan hardcode creds; gunakan ENV.
* **WA integration**: buat adapter interface di `controller/helpers/whatsapp.php` dan implementasi stub lokal dulu.

## Catatan penting / rekomendasi

* Karena kamu ingin **no login untuk user**, pastikan ticket_code + email cukup untuk verifikasi status. Jangan mengekspos data sensitif.
* Untuk WhatsApp: API resmi memerlukan business verification; selama dev gunakan email + fallback SMS (jika perlu) atau UI in-app notifications (dashboard).
* Simpan template mapping di DB agar admin bisa ubah tanpa deploy kode.
* Gunakan job queue (even simple cron polling DB) untuk proses berat: konversi DOCX, pengiriman WA.