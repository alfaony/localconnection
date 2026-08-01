# Setup Network Incident Alert (Deteksi Gangguan Real-time)

## Cara Kerja

Setiap kali `SyncInstalledCustomersJob` jalan (dijadwalkan berkala) dan
nemuin customer yang harusnya online (status INSTALLED/REACTIVATED) tapi
nggak ketemu di router/RADIUS/ARP, itu **langsung** dianggap insiden nyata
dan **langsung** (di run yang sama, tanpa delay) kirim WA ke:

1. **NOC/teknisi** — selalu, ke nomor yang di-set di `noc_wa_number`
   - Kalau di router yang sama ada **lebih dari 3 customer** putus
     bareng → **1 pesan rangkuman** ("Gangguan Massal", daftar nama)
   - Kalau ≤3 → pesan per-customer
2. **Customer** — selalu, per-customer, pesan lebih halus ("mohon maaf,
   sedang kami cek"), cuma kalau nomor HP-nya ada di data

## WAJIB: Setup Nomor NOC

Belum ada UI form khusus buat setting ini di project kamu (saya cek, nggak
ketemu halaman "Settings" khusus company). Tambahkan manual dulu, lewat
tinker atau langsung ke `SettingCompany`:

```php
\App\Models\SettingCompany::create([
    'user_id' => $adminUserId, // pemilik company
    'field_title' => 'noc_wa_number',
    'field_value' => '628123456789,628987654321', // bisa lebih dari 1, pisah koma
]);
```

Kalau kamu punya halaman settings company yang saya belum temukan, kasih
tau nanti saya sesuaikan biar bisa diisi lewat UI.

## Migration

```bash
php artisan migrate
```

## ⚠️ Gap yang Saya Temukan (Belum Diselesaikan, di Luar Scope Awal)

`getCustomers()` di `SyncInstalledCustomersJob` cuma nge-query customer
dengan status **INSTALLED/REACTIVATED**. Begitu customer di-set jadi
DISCONNECTED (karena putus), dia **keluar dari query ini selamanya** —
job ini nggak akan pernah cek dia lagi buat tau "eh dia udah balik online
belum?", karena query awalnya udah nge-exclude status DISCONNECTED.

Artinya: **fitur recovery detection (nutup insiden otomatis + notif "udah
pulih") belum jalan** untuk customer yang statusnya udah kadung
DISCONNECTED. Saya udah siapin method `reportRecovered()` di
`NetworkIncidentAlertService`, tapi belum ada yang manggil dia, karena
saya belum nemu di kode kamu: gimana caranya customer yang DISCONNECTED
itu balik lagi jadi INSTALLED/REACTIVATED (apakah ada job/webhook lain,
atau manual oleh admin)?

**Kalau kamu tau alur itu**, kasih tau saya prosesnya di mana, saya
sambungin `reportRecovered()` ke situ. Kalau belum ada mekanismenya sama
sekali, ini PR terpisah yang lebih besar (perlu bikin job yang juga
ngecek customer DISCONNECTED secara berkala, bukan cuma INSTALLED/REACTIVATED).

## Testing

1. Pastikan `noc_wa_number` udah di-set
2. Cabut/matiin salah satu customer test dari router (disconnect PPP
   session-nya manual, atau matiin device static-nya)
3. Jalankan `php artisan customers:check-active` (atau job terjadwal yang
   biasa jalan)
4. Cek WA masuk ke nomor NOC dan ke nomor customer (kalau ada)
5. Cek tabel `network_incidents` — harus ada record baru dengan
   `detected_at` terisi
