# Setup WAHA (WhatsApp HTTP API) — Self-Hosted

## 1. Jalankan WAHA Server

```bash
docker run -d --name waha \
  -p 3000:3000 \
  -e WHATSAPP_API_KEY=<bikin_key_rahasia_sendiri> \
  -v waha_sessions:/app/.sessions \
  devlikeapro/waha
```

(Sesuaikan port/volume sesuai kebutuhan. Untuk production, pakai
docker-compose + reverse proxy HTTPS, jangan expose port 3000 langsung
ke internet.)

## 2. Mulai Session & Scan QR

```bash
curl -X POST http://localhost:3000/api/sessions/start \
  -H "Content-Type: application/json" \
  -H "X-Api-Key: <key_yang_di_set_tadi>" \
  -d '{"name": "default"}'
```

Buka `http://localhost:3000/api/screenshot?session=default` atau lewat
dashboard WAHA (`http://localhost:3000`) buat scan QR pakai nomor WA yang
mau dipakai buat kirim notifikasi.

**PENTING**: nomor yang di-scan ini akan dipakai buat KIRIM pesan, jadi
sebaiknya nomor khusus (bukan nomor pribadi admin), dan device HP-nya
harus tetap online/terhubung internet (WhatsApp Web butuh HP asal tetap
nyala & konek, kecuali pakai versi WAHA yang multi-device tanpa
ketergantungan HP — cek dokumentasi WAHA versi terbaru).

## 3. Setup di Aplikasi Laravel

Sama seperti `noc_wa_number`, tambahkan setting ini ke `SettingCompany`
(belum ada UI-nya, insert manual dulu):

```php
\App\Models\SettingCompany::create(['user_id' => $adminUserId, 'field_title' => 'waha_base_url', 'field_value' => 'http://localhost:3000']);
\App\Models\SettingCompany::create(['user_id' => $adminUserId, 'field_title' => 'waha_session', 'field_value' => 'default']);
\App\Models\SettingCompany::create(['user_id' => $adminUserId, 'field_title' => 'waha_api_key', 'field_value' => '<key_yang_di_set_tadi>']);
```

## 4. Testing

```php
// php artisan tinker
$sender = new \App\Services\WhatsApp\WahaClient('http://localhost:3000', 'default', '<api_key>');
$sender->sendText('08123456789', 'Test dari WAHA');
```

Cek status session dulu kalau gagal:
```php
$sender->sessionStatus();
// Harus 'status' => 'WORKING'. Kalau 'SCAN_QR_CODE' berarti belum di-scan / QR expired.
```

## Migration dari Wablas

- `NetworkIncidentAlertService` (fitur baru) **sudah pakai WAHA**, bukan
  Wablas lagi
- File Wablas (`app/Services/Weblas/*`) **TIDAK saya hapus/ubah** — masih
  dipakai di banyak tempat lain (`GenerateIsolirJob`,
  `SendBillingReminderJob`, `SendPaymentSuccessWaJob`, dan beberapa
  Livewire component). Migrasi semua itu ke WAHA itu kerjaan terpisah
  yang lebih besar dan berisiko kalau dikerjain buru-buru tanpa testing
  bertahap
- Kalau kamu mau migrasi bagian lain juga ke WAHA, kabari saya file mana
  dulu yang mau dipindah — saya bisa bantu satu-satu, pakai abstraksi
  `WhatsAppSender` yang sama (biar kalau nanti ganti provider lagi,
  tinggal ganti implementasi, gak perlu ubah kode pemanggilnya)

## File yang Dibuat

- `app/Services/WhatsApp/WhatsAppSender.php` — interface/kontrak
- `app/Services/WhatsApp/WhatsAppSendException.php` — exception khusus
- `app/Services/WhatsApp/WahaClient.php` — implementasi WAHA
- `app/Services/NetworkIncidentAlertService.php` — diupdate, pakai
  `WhatsAppSender` (WAHA) bukan Wablas lagi
