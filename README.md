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
/project-root
├─ frontend/                   # Frontend statis (HTML/CSS/JS) atau SPA
│  ├─ public/                  # assets publik
│  ├─ src/
│  │  ├─ pages/                # ticket-form.html, check-status.html, admin-login.html (mock)
│  │  └─ components/
│  └─ build/                   # hasil build jika pakai bundler
├─ backend/                    # PHP API & admin pages (server-side)
│  ├─ api/                     # api/tickets.php, api/auth.php, api/templates.php, ...
│  ├─ admin/                   # admin panel (protected pages) — bisa simple PHP + AJAX
│  ├─ lib/                     # helper (db.php, mailer.php, docx_generator.php)
│  └─ public/                  # index.php (gateway) atau gunakan virtual host
├─ sql/
│  └─ schema.sql
└─ README.md
```

> Catatan: front-end dapat di-develop terpisah (mis. di `frontend/`) lalu Copilot dapat generate komponen HTML/CSS/JS tanpa perlu PHP. Frontend akan memanggil endpoint di `backend/api/*`.

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

Semua `api/*` mengembalikan JSON.

### Publik (no auth)

* `POST /api/tickets` — submit ticket (payload form / multipart for files) → returns `{ticket_code, status}`
* `GET /api/tickets/status?ticket_code=...&email=...` — cek status

### Admin (session auth or token)

* `POST /api/auth/login` — login admin → sets session
* `GET /api/tickets` — list tickets (params filter)
* `GET /api/tickets/{id}` — detail ticket
* `POST /api/tickets/{id}/validate` — mark valid (body: template_id, optional edits)
* `POST /api/tickets/{id}/reject` — mark reject (body: reason)
* `POST /api/tickets/{id}/generate` — force generate surat (backend will produce file, update `letters`)
* `GET /api/templates` — list templates
* `POST /api/templates` — upload template

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

1. [x] Buat halaman `ticket-form.html` (static) — form lengkap + client validation.
2. [x] Buat `success.html` dengan menampilkan ticket_code (mock).
3. [ ] Buat `check-status.html` + mock status responses.
4. [ ] Buat admin login page (form).
5. [ ] Buat admin dashboard static (tabel sample tickets).
6. [ ] Komponen modal, toast, file upload.
7. [ ] Responsiveness & accessibility check.

### Sprint API & DB (setelah UI siap)

8. [ ] Buat DB schema (`sql/schema.sql`) dan helper `backend/lib/db.php`.
9. [ ] Implement `POST /api/tickets` (simpan: tickets + files).
10. [ ] Implement `GET /api/tickets` (list, filters).
11. [ ] Implement admin auth & session.
12. [ ] Implement ticket review endpoints (validate/reject).
13. [ ] Integrasi template manager & upload.
14. [ ] Implement generate surat (phpword + numbering update).
15. [ ] Implement notifikasi worker (email + WA adapter stub).
16. [ ] Add logging (file + DB event logs).

### Sprint finishing

17. [ ] Tests: unit + integration for full flow.
18. [ ] Security audit: file upload, auth, SQL injection, XSS.
19. [ ] Prepare docs: how-to-run, env vars (SMTP, WA creds), endpoints list.
20. [ ] Optional: add QR verification public page (verifikasi nomor surat).

## Petunjuk implementasi cepat (snippet & tips)

* **Ticket code**: `TCK-YYYYMMDD-<4digit>` generated di server.
* **Replace placeholders** (contoh phpword pseudo):
  ```php
  $templateProcessor->setValue('{nama_mahasiswa}', $data['nama']);
  ```
* **Generate nomor**: salin logic `last_number_mode` dari README (skip vs offset).
* **Notifikasi email**: gunakan PHPMailer, jangan hardcode creds; gunakan ENV.
* **WA integration**: buat adapter interface `WaSenderInterface` dan implementasi stub lokal dulu (log → true). Nanti ganti ke provider.

## Catatan penting / rekomendasi

* Karena kamu ingin **no login untuk user**, pastikan ticket_code + email cukup untuk verifikasi status. Jangan mengekspos data sensitif.
* Untuk WhatsApp: API resmi memerlukan business verification; selama dev gunakan email + fallback SMS (jika perlu) atau UI in-app notifications (dashboard).
* Simpan template mapping di DB agar admin bisa ubah tanpa deploy kode.
* Gunakan job queue (even simple cron polling DB) untuk proses berat: konversi DOCX, pengiriman WA.