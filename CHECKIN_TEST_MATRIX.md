# Employee Check-in WebSocket — Testing Matrix

> Arsitektur: **3 Lapis** (WS push → localStorage → WS reconnect+server)  
> Reverb channel: `private employee-checkin.{userId}`  
> localStorage key: `checkin_active_{userId}`

---

## Persiapan Sebelum Testing

```bash
# 1. Pastikan Reverb server berjalan
php artisan reverb:start

# 2. Pastikan queue worker berjalan (untuk broadcast)
php artisan queue:work

# 3. Cek email user yang akan ditest
php artisan checkin:simulate --email=budi@example.com --list
```

**Browser DevTools yang harus dibuka:**
- Console (filter: `[Checkin`) untuk log per lapis
- Application > Local Storage > cari key `checkin_active_{userId}`
- Network tab (filter: `current-active`) untuk cek kapan server hit

---

## TC-01 — Popup Muncul via WebSocket (Lapis 1)

**Tujuan:** Verifikasi WS event langsung membuka popup dan menyimpan localStorage

| Step | Aksi | Expected |
|------|------|----------|
| 1 | Buka browser, login sebagai user target | Halaman loaded, tidak ada popup |
| 2 | Buka DevTools > Console | Lihat `[Checkin L3] WS connected/reconnected` |
| 3 | Jalankan: `php artisan checkin:simulate --email=X --duration=120` | Terminal: `✅ Broadcast dikirim` |
| 4 | Amati browser dalam 2 detik | **Popup muncul**, countdown mulai (120) |
| 5 | DevTools > Console | Log: `[Checkin L1] WS Activated: {ID}` |
| 6 | DevTools > Application > Local Storage | Key `checkin_active_{userId}` **ada**, berisi `local_id`, `expires_at` |
| 7 | Catat `ID` dari terminal output | Dipakai di TC-02, TC-05, TC-08 |

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## TC-02 — Submit Check-in (Happy Path)

**Tujuan:** Verifikasi submit berhasil, localStorage dibersihkan, popup tertutup

*Prasyarat: TC-01 sudah pass, popup sedang muncul*

| Step | Aksi | Expected |
|------|------|----------|
| 1 | Popup sedang muncul dengan countdown | Countdown berkurang tiap detik |
| 2 | Selesaikan reCAPTCHA | Tombol Submit aktif |
| 3 | Klik **Submit Check-in** | Spinner muncul, tombol disabled |
| 4 | Tunggu response | SweetAlert: "Check-in berhasil!" |
| 5 | DevTools > Local Storage | Key `checkin_active_{userId}` **sudah hilang** |
| 6 | DevTools > Network | Request `PUT /employee-checking/{id}` → 200 |
| 7 | Setelah 3 detik | Halaman reload otomatis |

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## TC-03 — Refresh Saat Popup Aktif (Lapis 2 — localStorage)

**Tujuan:** Verifikasi popup muncul kembali dari localStorage tanpa hit server

*Prasyarat: TC-01 sudah pass, popup muncul, JANGAN submit dulu*

| Step | Aksi | Expected |
|------|------|----------|
| 1 | Saat popup muncul, buka DevTools > Network | Perhatikan traffic |
| 2 | Tekan **F5 / Cmd+R** (refresh halaman) | Halaman reload |
| 3 | Setelah halaman load, amati dalam 1 detik | **Popup muncul kembali** otomatis |
| 4 | DevTools > Console | Log: `[Checkin L2] Restore dari localStorage, sisa: XX detik` |
| 5 | DevTools > Console | **TIDAK** ada log `[Checkin L3] localStorage kosong → cek server 1x` |
| 6 | DevTools > Network | **TIDAK** ada request ke `/employee-checking/current-active` |
| 7 | Countdown | Melanjutkan dari sisa waktu (bukan mulai dari 120 lagi) |

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## TC-04 — Refresh Setelah localStorage Expired

**Tujuan:** Verifikasi localStorage expired tidak membuka popup

| Step | Aksi | Expected |
|------|------|----------|
| 1 | Jalankan simulate dengan `--duration=30` (window 30 detik) | Popup muncul, countdown 30 |
| 2 | Tunggu countdown habis (30 detik) tanpa submit | Popup tertutup otomatis |
| 3 | DevTools > Local Storage | Key `checkin_active_{userId}` **sudah hilang** |
| 4 | Refresh halaman | Halaman load normal, **popup TIDAK muncul** |
| 5 | DevTools > Console | Log: `[Checkin L3] localStorage kosong → cek server 1x` |
| 6 | DevTools > Network | Ada 1 request ke `/employee-checking/current-active` → response `{"active": false}` |

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## TC-05 — Deactivate via Command (WS Deactivated Event)

**Tujuan:** Verifikasi popup tertutup saat WS event `EmployeeCheckinDeactivated` diterima

*Prasyarat: TC-01 sudah pass, popup sedang muncul, catat ID-nya*

| Step | Aksi | Expected |
|------|------|----------|
| 1 | Popup sedang muncul | Catat ID dari TC-01 Step 7 |
| 2 | Jalankan: `php artisan checkin:simulate --email=X --deactivate={ID}` | Terminal: `✅ Broadcast Deactivated dikirim` |
| 3 | Amati browser dalam 2 detik | **Popup tertutup** |
| 4 | DevTools > Console | Log: `[Checkin L1] WS Deactivated: {ID}` |
| 5 | DevTools > Local Storage | Key `checkin_active_{userId}` **sudah hilang** |
| 6 | Refresh halaman | Popup **tidak muncul** |

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## TC-06 — Tab Baru di Browser yang Sama (Cross-tab → localStorage)

**Tujuan:** Verifikasi Tab B bisa buka popup dari localStorage Tab A

| Step | Aksi | Expected |
|------|------|----------|
| 1 | Di Tab A: jalankan simulate (TC-01) | Popup muncul di Tab A |
| 2 | Buka **Tab B baru** di browser yang sama | Halaman load |
| 3 | Amati Tab B dalam 1 detik | **Popup muncul di Tab B** (dari localStorage) |
| 4 | DevTools Tab B > Console | Log: `[Checkin L2] Restore dari localStorage` |
| 5 | DevTools Tab B > Console | **TIDAK** ada log `cek server 1x` |
| 6 | DevTools Tab B > Network | **TIDAK** ada request ke `current-active` |

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## TC-07 — Cross-tab Sync: Tab A Submit → Tab B Tutup

**Tujuan:** Verifikasi saat Tab A submit, Tab B ikut menutup popupnya via storage event

*Prasyarat: TC-06 sudah done, popup muncul di Tab A dan Tab B*

| Step | Aksi | Expected |
|------|------|----------|
| 1 | Tab A dan Tab B keduanya menampilkan popup | Kedua popup terbuka |
| 2 | Di Tab A: selesaikan reCAPTCHA dan klik **Submit** | Tab A: spinner, lalu SweetAlert sukses |
| 3 | Amati Tab B SAAT Tab A submit | Tab B: **popup langsung tertutup** |
| 4 | DevTools Tab B > Console | Log: `[Checkin Cross-tab] LS cleared dari tab lain → tutup popup` |
| 5 | DevTools Tab B > Local Storage | Key `checkin_active_{userId}` **hilang** |

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## TC-08 — Cross-tab Sync: Tab A Deactivate → Tab B Tutup

**Tujuan:** Verifikasi WS event deactivated di Tab A menutup Tab B via storage event

*Prasyarat: Popup muncul di Tab A dan Tab B*

| Step | Aksi | Expected |
|------|------|----------|
| 1 | Tab A dan Tab B keduanya menampilkan popup | Kedua popup terbuka |
| 2 | Jalankan: `php artisan checkin:simulate --email=X --deactivate={ID}` | Terminal: sukses |
| 3 | Amati Tab A | Popup tertutup via WS event |
| 4 | Amati Tab B | Popup **ikut tertutup** via storage event |
| 5 | DevTools Tab B > Console | Log: `[Checkin Cross-tab] LS cleared dari tab lain → tutup popup` |

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## TC-09 — Device Baru / Browser Berbeda (Lapis 3 — Server Fallback)

**Tujuan:** Verifikasi device baru yang tidak punya localStorage tetap dapat popup via server request saat WS connect

| Step | Aksi | Expected |
|------|------|----------|
| 1 | Di **Browser A**: jalankan simulate, popup muncul, **JANGAN** submit | localStorage ada di Browser A |
| 2 | Buka **Browser B** (atau incognito, atau device berbeda) | localStorage **kosong** |
| 3 | Login di Browser B dengan akun yang sama | Halaman load |
| 4 | Amati Browser B dalam 2-3 detik | **Popup muncul** |
| 5 | DevTools Browser B > Console | Log: `[Checkin L3] WS connected/reconnected` |
| 6 | DevTools Browser B > Console | Log: `[Checkin L3] localStorage kosong → cek server 1x` |
| 7 | DevTools Browser B > Console | Log: `[Checkin L3] Server: ada checkin aktif {ID}` |
| 8 | DevTools Browser B > Network | Ada 1 request `GET /employee-checking/current-active` → `{"active": true, ...}` |
| 9 | DevTools Browser B > Local Storage | Key `checkin_active_{userId}` **kini ada** (di-populate oleh Lapis 3) |
| 10 | Refresh Browser B | Popup muncul dari **localStorage** (bukan server lagi) |

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## TC-10 — Countdown Habis Tanpa Submit

**Tujuan:** Verifikasi popup tertutup otomatis dan localStorage dibersihkan saat waktu habis

| Step | Aksi | Expected |
|------|------|----------|
| 1 | Jalankan simulate dengan `--duration=15` | Popup muncul, countdown 15 |
| 2 | Tunggu tanpa melakukan apapun | Countdown turun: 14, 13, ... 1, 0 |
| 3 | Saat countdown = 0 | **Popup tertutup otomatis** |
| 4 | DevTools > Local Storage | Key `checkin_active_{userId}` **hilang** |
| 5 | DevTools > Network | Ada request `PUT /employee-checking/{id}/updatestatus` (memberi tahu server `is_active = false`) |
| 6 | Refresh halaman | Popup **tidak muncul** |

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## TC-11 — Staggered Schedule `--count=4`

**Tujuan:** Verifikasi 4 jadwal terbuat dan popup muncul berurutan sesuai waktu

| Step | Aksi | Expected |
|------|------|----------|
| 1 | Jalankan: `php artisan checkin:simulate --email=X --count=4` | Terminal menampilkan tabel: #1–#4 dengan waktu mulai/timeout |
| 2 | Catat 4 waktu mulai dari tabel (misal: 13:42, 13:48, 13:54, 14:00) | — |
| 3 | `php artisan checkin:simulate --email=X --list` | 4 record tampil dengan `Active=✓` |
| 4 | Pastikan scheduler berjalan atau trigger manual tiap jadwal | — |
| 5 | Saat jam 13:42 (jadwal #1 mulai) | **Popup #1 muncul** |
| 6 | Submit atau tunggu popup #1 habis | Popup #1 tertutup |
| 7 | Saat jam 13:48 (jadwal #2 mulai) | **Popup #2 muncul** |
| 8 | Ulangi untuk #3 dan #4 | Popup muncul berurutan |
| 9 | `php artisan checkin:simulate --email=X --list` setelah semua selesai | Status masing-masing record berubah (`Completed` / `Active=–`) |

> Jika scheduler off, gunakan kolom "Trigger manual" dari output command untuk broadcast manual tiap jadwal.

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## TC-12 — List dan Verifikasi DB

**Tujuan:** Verifikasi command `--list` mencerminkan state DB yang akurat

| Step | Aksi | Expected |
|------|------|----------|
| 1 | `php artisan checkin:simulate --email=X --list` | Tampil tabel checkin hari ini |
| 2 | Cek kolom `Active` | Record yang belum lewat window: `✓` |
| 3 | Submit salah satu popup | Record berubah: `Active=–`, `Completed=✓` |
| 4 | Jalankan `--list` lagi | State terupdate sesuai DB |

**Status:** ☐ Pass ☐ Fail  
**Catatan:**

---

## Ringkasan Hasil Testing

| TC | Nama Skenario | Layer | Status |
|----|---------------|-------|--------|
| TC-01 | Popup via WebSocket | Lapis 1 | ☐ |
| TC-02 | Submit Happy Path | Submit | ☐ |
| TC-03 | Refresh saat popup aktif | Lapis 2 | ☐ |
| TC-04 | Refresh setelah expired | Lapis 2 + Lapis 3 | ☐ |
| TC-05 | Deactivate via command | Lapis 1 | ☐ |
| TC-06 | Tab baru di browser sama | Cross-tab (LS) | ☐ |
| TC-07 | Submit Tab A → Tab B tutup | Cross-tab sync | ☐ |
| TC-08 | Deactivate Tab A → Tab B tutup | Cross-tab sync | ☐ |
| TC-09 | Browser baru / device baru | Lapis 3 (server) | ☐ |
| TC-10 | Countdown habis | Auto-close | ☐ |
| TC-11 | Staggered 4 jadwal | --count mode | ☐ |
| TC-12 | Verifikasi DB via --list | Command | ☐ |

---

## Console Log Reference

| Log Pattern | Artinya |
|-------------|---------|
| `[Checkin L1] WS Activated: {ID}` | Event WS masuk, popup dibuka via Lapis 1 |
| `[Checkin L1] WS Deactivated: {ID}` | Event WS deactivated, popup ditutup |
| `[Checkin L2] Restore dari localStorage` | Popup di-restore dari LS tanpa request server |
| `[Checkin L2] localStorage expired, cleared` | LS ada tapi sudah lewat window |
| `[Checkin L3] WS connected/reconnected` | WS berhasil connect |
| `[Checkin L3] localStorage ada, skip server request` | LS ada → tidak perlu server |
| `[Checkin L3] localStorage kosong → cek server 1x` | LS kosong → kirim 1 AJAX |
| `[Checkin L3] Server: ada checkin aktif {ID}` | Server konfirmasi ada checkin aktif |
| `[Checkin Cross-tab] LS cleared dari tab lain` | Tab lain hapus LS → tab ini tutup popup |
| `[Checkin Cross-tab] LS diisi dari tab lain` | Tab lain isi LS → tab ini buka popup |

---

## Troubleshooting

| Gejala | Kemungkinan Penyebab | Cek |
|--------|---------------------|-----|
| Popup tidak muncul sama sekali | Reverb tidak running | `php artisan reverb:start` |
| Popup tidak muncul | WS auth gagal | Network > filter `broadcasting/authorize` → cek 403 |
| Popup tidak muncul | User belum punya `is_checkin=true` | DB: `users.is_checkin` |
| Popup muncul tapi langsung tutup | `timeLeft <= 0` saat event diterima | Cek `scheduled_time` di DB vs waktu sekarang |
| Refresh tidak restore popup | localStorage diblokir browser | Settings > Privacy: izinkan localStorage |
| TC-09 gagal (no server hit) | `currentActive` route tidak terdaftar | `php artisan route:list \| grep current-active` |
| Cross-tab tidak sync | storage event tidak fire | Normal jika Tab A dan Tab B adalah window yang SAMA (bukan tab berbeda) |
