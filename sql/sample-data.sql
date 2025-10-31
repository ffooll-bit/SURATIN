-- Sample data untuk tabel tickets dan admins
USE suratin_db;

-- Clear existing data first (optional)
-- DELETE FROM tickets;
-- DELETE FROM admins;

-- Insert sample admin data
INSERT INTO admins (username, password_hash, name, email, role, active, last_login, created_at, updated_at) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin@universitas.ac.id', 'admin', 1, '2024-10-31 08:00:00', '2024-10-01 00:00:00', '2024-10-31 08:00:00'),
('super', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Super Administrator', 'super@universitas.ac.id', 'super', 1, '2024-10-31 07:30:00', '2024-10-01 00:00:00', '2024-10-31 07:30:00'),
('baak_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin BAAK', 'baak@universitas.ac.id', 'admin', 1, NULL, '2024-10-15 10:00:00', '2024-10-15 10:00:00'),
('kemahasiswaan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin Kemahasiswaan', 'kemahasiswaan@universitas.ac.id', 'admin', 1, '2024-10-30 16:45:00', '2024-10-20 09:00:00', '2024-10-30 16:45:00'),
('hrd_admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin HRD', 'hrd@universitas.ac.id', 'admin', 0, NULL, '2024-10-25 14:30:00', '2024-10-25 14:30:00');

-- Insert sample data
INSERT INTO tickets (ticket_code, nama, npm, prodi, jenis_surat, data, attachments, email, wa, status, admin_note, created_at, updated_at) VALUES

-- Generated tickets (sudah selesai)
('TCK-20241029-0001', 'Muhammad Arif Rahman', '2023110001', 'Teknik Informatika', 'Surat Keterangan Aktif Kuliah', 
 '{"semester": "5", "tahun_akademik": "2024/2025", "keperluan": "Persyaratan beasiswa", "alamat": "Jl. Merdeka No. 15, Jakarta"}',
 '[{"name": "KTM.jpg", "path": "uploads/TCK-20241029-0001/KTM.jpg", "size": 245678}]',
 'arif.rahman@student.univ.ac.id', '08123456789', 'generated', 'Surat telah dihasilkan dan dikirim via email', '2024-10-29 08:30:00', '2024-10-29 14:20:00'),

('TCK-20241028-0015', 'Siti Nurhaliza Putri', '2023110025', 'Sistem Informasi', 'Surat Pengantar Magang', 
 '{"nama_perusahaan": "PT. Teknologi Maju", "alamat_perusahaan": "Jl. Sudirman No. 45, Jakarta", "divisi": "IT Development", "durasi": "3 bulan", "periode": "Januari - Maret 2025"}',
 '[{"name": "Proposal_Magang.pdf", "path": "uploads/TCK-20241028-0015/Proposal_Magang.pdf", "size": 1234567}, {"name": "CV.pdf", "path": "uploads/TCK-20241028-0015/CV.pdf", "size": 567890}]',
 'siti.nurhaliza@student.univ.ac.id', '08567891234', 'generated', NULL, '2024-10-28 10:15:00', '2024-10-28 16:45:00'),

('TCK-20241025-0008', 'Budi Santoso Wijaya', '2023110045', 'Teknik Elektro', 'Surat Pengantar Penelitian', 
 '{"judul_penelitian": "Implementasi IoT pada Smart Home", "lokasi_penelitian": "PT. Smart Technology", "dosen_pembimbing": "Dr. Andi Wijaya, M.T.", "durasi": "2 bulan"}',
 '[{"name": "Proposal_Penelitian.pdf", "path": "uploads/TCK-20241025-0008/Proposal_Penelitian.pdf", "size": 2345678}]',
 'budi.santoso@student.univ.ac.id', '08765432109', 'generated', 'Penelitian disetujui', '2024-10-25 09:20:00', '2024-10-25 15:30:00'),

-- Valid tickets (sudah disetujui, menunggu generate)
('TCK-20241027-0012', 'Lisa Permatasari', '2023110067', 'Manajemen', 'Surat Rekomendasi', 
 '{"keperluan": "Pendaftaran S2", "universitas_tujuan": "Universitas Indonesia", "program_studi": "Magister Manajemen", "ipk": "3.75"}',
 '[{"name": "Transkrip_Nilai.pdf", "path": "uploads/TCK-20241027-0012/Transkrip_Nilai.pdf", "size": 890123}, {"name": "Sertifikat_Prestasi.pdf", "path": "uploads/TCK-20241027-0012/Sertifikat_Prestasi.pdf", "size": 456789}]',
 'lisa.permata@student.univ.ac.id', '08234567890', 'valid', 'Data lengkap, siap generate surat', '2024-10-27 11:45:00', '2024-10-29 09:15:00'),

('TCK-20241026-0005', 'Andi Kurniawan', '2023110089', 'Akuntansi', 'Surat Keterangan Aktif Kuliah', 
 '{"semester": "7", "tahun_akademik": "2024/2025", "keperluan": "Persyaratan KKN", "alamat": "Jl. Pahlawan No. 23, Bandung"}',
 '[{"name": "KTM_Andi.jpg", "path": "uploads/TCK-20241026-0005/KTM_Andi.jpg", "size": 198765}]',
 'andi.kurnia@student.univ.ac.id', '08345678901', 'valid', NULL, '2024-10-26 13:10:00', '2024-10-26 16:20:00'),

-- In Review tickets (sedang direview admin)
('TCK-20241029-0018', 'Maya Sari Dewi', '2023110012', 'Psikologi', 'Surat Pengantar Magang', 
 '{"nama_perusahaan": "Rumah Sakit Jiwa Daerah", "alamat_perusahaan": "Jl. Kesehatan No. 12, Surabaya", "divisi": "Psikologi Klinis", "durasi": "4 bulan", "periode": "Februari - Mei 2025"}',
 '[{"name": "Surat_Penerimaan_RS.pdf", "path": "uploads/TCK-20241029-0018/Surat_Penerimaan_RS.pdf", "size": 1456789}]',
 'maya.sari@student.univ.ac.id', '08456789012', 'in_review', NULL, '2024-10-29 14:30:00', '2024-10-29 14:30:00'),

('TCK-20241029-0017', 'Roni Hermawan', '2023110034', 'Teknik Mesin', 'Surat Keterangan Aktif Kuliah', 
 '{"semester": "3", "tahun_akademik": "2024/2025", "keperluan": "Pendaftaran Part Time Job", "alamat": "Jl. Veteran No. 8, Yogyakarta"}',
 '[{"name": "KTM_Roni.png", "path": "uploads/TCK-20241029-0017/KTM_Roni.png", "size": 287654}]',
 'roni.hermawan@student.univ.ac.id', '08567890123', 'in_review', NULL, '2024-10-29 13:15:00', '2024-10-29 13:15:00'),

-- Submitted tickets (baru disubmit, belum direview)
('TCK-20241030-0021', 'Devi Kartika Sari', '2023110056', 'Farmasi', 'Surat Pengantar Penelitian', 
 '{"judul_penelitian": "Analisis Kandungan Obat Herbal Tradisional", "lokasi_penelitian": "BPOM Daerah", "dosen_pembimbing": "Dr. Sari Wijayanti, M.Farm.", "durasi": "3 bulan"}',
 '[{"name": "Proposal_Penelitian_Farmasi.pdf", "path": "uploads/TCK-20241030-0021/Proposal_Penelitian_Farmasi.pdf", "size": 3456789}, {"name": "Surat_Izin_BPOM.pdf", "path": "uploads/TCK-20241030-0021/Surat_Izin_BPOM.pdf", "size": 1234567}]',
 'devi.kartika@student.univ.ac.id', '08678901234', 'submitted', NULL, '2024-10-30 09:45:00', '2024-10-30 09:45:00'),

('TCK-20241030-0020', 'Agus Setiawan', '2023110078', 'Ekonomi', 'Surat Rekomendasi', 
 '{"keperluan": "Beasiswa LPDP", "program": "S2 Ekonomi Pembangunan", "universitas_tujuan": "Universitas Gadjah Mada", "ipk": "3.82"}',
 '[{"name": "Transkrip_IPK.pdf", "path": "uploads/TCK-20241030-0020/Transkrip_IPK.pdf", "size": 765432}, {"name": "Essay_LPDP.pdf", "path": "uploads/TCK-20241030-0020/Essay_LPDP.pdf", "size": 2345678}]',
 'agus.setiawan@student.univ.ac.id', '08789012345', 'submitted', NULL, '2024-10-30 08:20:00', '2024-10-30 08:20:00'),

('TCK-20241030-0019', 'Fitri Rahmawati', '2023110091', 'Hukum', 'Surat Keterangan Aktif Kuliah', 
 '{"semester": "5", "tahun_akademik": "2024/2025", "keperluan": "Persyaratan magang di firma hukum", "alamat": "Jl. Diponegoro No. 33, Semarang"}',
 '[{"name": "KHS_Semester_4.pdf", "path": "uploads/TCK-20241030-0019/KHS_Semester_4.pdf", "size": 432109}]',
 'fitri.rahma@student.univ.ac.id', '08890123456', 'submitted', NULL, '2024-10-30 07:55:00', '2024-10-30 07:55:00'),

-- Rejected tickets (ditolak dengan alasan)
('TCK-20241024-0003', 'Dedi Kurniawan', '2023110123', 'Teknik Sipil', 'Surat Pengantar Magang', 
 '{"nama_perusahaan": "PT. Konstruksi ABC", "alamat_perusahaan": "Jl. Pembangunan No. 17", "divisi": "Engineering", "durasi": "2 bulan"}',
 '[{"name": "CV_Lama.pdf", "path": "uploads/TCK-20241024-0003/CV_Lama.pdf", "size": 234567}]',
 'dedi.kurnia@student.univ.ac.id', '08901234567', 'rejected', 'Dokumen proposal magang tidak lengkap. Mohon sertakan surat penerimaan dari perusahaan dan proposal yang lebih detail.', '2024-10-24 15:20:00', '2024-10-24 17:45:00'),

('TCK-20241023-0007', 'Nina Puspitasari', '2022110045', 'Kedokteran', 'Surat Rekomendasi', 
 '{"keperluan": "Pendaftaran Residency", "program": "Spesialis Anak", "universitas_tujuan": "RSUP Dr. Sardjito", "ipk": "3.2"}',
 '[{"name": "Transkrip_Lama.pdf", "path": "uploads/TCK-20241023-0007/Transkrip_Lama.pdf", "size": 567890}]',
 'nina.puspita@student.univ.ac.id', '08123450987', 'rejected', 'IPK belum memenuhi syarat minimum untuk surat rekomendasi program spesialis (min. 3.5). Silakan tingkatkan prestasi akademik terlebih dahulu.', '2024-10-23 10:30:00', '2024-10-23 14:15:00'),

-- Tickets dengan data yang lebih kompleks
('TCK-20241029-0016', 'Bayu Adi Pratama', '2023110199', 'Teknik Informatika', 'Surat Keterangan Aktif Kuliah', 
 '{"semester": "6", "tahun_akademik": "2024/2025", "keperluan": "Pendaftaran kompetisi programming internasional", "alamat": "Komplek Mahasiswa Blok A No. 15, Depok", "prestasi": ["Juara 1 Hackathon 2023", "Best Innovation Award 2024"]}',
 '[{"name": "KTM_Bayu.jpg", "path": "uploads/TCK-20241029-0016/KTM_Bayu.jpg", "size": 256789}, {"name": "Sertifikat_Juara.pdf", "path": "uploads/TCK-20241029-0016/Sertifikat_Juara.pdf", "size": 1234567}, {"name": "Portfolio_Project.pdf", "path": "uploads/TCK-20241029-0016/Portfolio_Project.pdf", "size": 5678901}]',
 'bayu.adi@student.univ.ac.id', '08234567891', 'in_review', NULL, '2024-10-29 16:00:00', '2024-10-29 16:00:00'),

('TCK-20241028-0014', 'Cindy Maharani', '2023110156', 'Desain Komunikasi Visual', 'Surat Pengantar Magang', 
 '{"nama_perusahaan": "Creative Agency Studio", "alamat_perusahaan": "Jl. Seniman No. 99, Bandung", "divisi": "Graphic Design & Branding", "durasi": "6 bulan", "periode": "Januari - Juni 2025", "project_focus": "UI/UX Design untuk mobile apps"}',
 '[{"name": "Portfolio_Design.pdf", "path": "uploads/TCK-20241028-0014/Portfolio_Design.pdf", "size": 15678901}, {"name": "Surat_Penerimaan_Agency.pdf", "path": "uploads/TCK-20241028-0014/Surat_Penerimaan_Agency.pdf", "size": 987654}]',
 'cindy.maharani@student.univ.ac.id', '08345678902', 'valid', 'Portfolio sangat impressive, disetujui untuk magang', '2024-10-28 11:30:00', '2024-10-28 15:45:00'),

-- Additional sample data for more variety
('TCK-20241031-0025', 'Ahmad Fauzi', '2023110200', 'Teknik Informatika', 'Surat Keterangan Aktif Kuliah', 
 '{"semester": "4", "tahun_akademik": "2024/2025", "keperluan": "Pendaftaran beasiswa unggulan", "alamat": "Jl. Sudirman No. 78, Jakarta"}',
 '[{"name": "KTM_Ahmad.jpg", "path": "uploads/TCK-20241031-0025/KTM_Ahmad.jpg", "size": 234567}]',
 'ahmad.fauzi@student.univ.ac.id', '08123456780', 'submitted', NULL, '2024-10-31 08:15:00', '2024-10-31 08:15:00'),

('TCK-20241031-0024', 'Diana Sari', '2023110201', 'Manajemen', 'Surat Pengantar Penelitian', 
 '{"judul_penelitian": "Analisis Strategi Marketing Digital UMKM", "lokasi_penelitian": "Dinas Koperasi dan UMKM", "dosen_pembimbing": "Dr. Budi Hartono, M.M.", "durasi": "4 bulan"}',
 '[{"name": "Proposal_Marketing.pdf", "path": "uploads/TCK-20241031-0024/Proposal_Marketing.pdf", "size": 2890123}]',
 'diana.sari@student.univ.ac.id', '08234567891', 'in_review', NULL, '2024-10-31 09:30:00', '2024-10-31 09:30:00'),

('TCK-20241031-0023', 'Rizki Pratama', '2023110202', 'Akuntansi', 'Surat Rekomendasi', 
 '{"keperluan": "Pendaftaran Program Chartered Accountant", "program": "CA Indonesia", "universitas_tujuan": "IAI (Ikatan Akuntan Indonesia)", "ipk": "3.68"}',
 '[{"name": "Transkrip_CA.pdf", "path": "uploads/TCK-20241031-0023/Transkrip_CA.pdf", "size": 1234568}]',
 'rizki.pratama@student.univ.ac.id', '08345678902', 'valid', 'IPK memenuhi syarat, dokumen lengkap', '2024-10-31 10:45:00', '2024-10-31 14:20:00'),

('TCK-20241031-0022', 'Sari Indah', '2023110203', 'Psikologi', 'Surat Keterangan Aktif Kuliah', 
 '{"semester": "8", "tahun_akademik": "2024/2025", "keperluan": "Persyaratan wisuda", "alamat": "Jl. Gatot Subroto No. 45, Bandung"}',
 '[{"name": "KRS_Terakhir.pdf", "path": "uploads/TCK-20241031-0022/KRS_Terakhir.pdf", "size": 567891}]',
 'sari.indah@student.univ.ac.id', '08456789013', 'generated', 'Surat untuk wisuda telah diterbitkan', '2024-10-31 11:00:00', '2024-10-31 16:30:00');
