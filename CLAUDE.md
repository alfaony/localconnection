# Keloola BOS — Internet (ISP management SaaS)

Laravel 9 + Livewire 2 + AdminLTE. Multi-tenant: satu aplikasi melayani banyak ISP (`Company`).
Platform menagih tiap ISP Rp1000 per customer aktif per bulan (lihat "Platform billing").

Codebase ini diturunkan dari produk Keloola BOS (manajemen gedung/HR). Sisa istilah lama
(task, attendance, project) boleh dihapus kalau ketemu; yang aktif hanya domain internet.

## Menjalankan

- Dev lokal: MAMP (`/Applications/MAMP/htdocs/internet-customer-only`), MySQL 3306, `.env` tidak di-commit.
- Queue: `QUEUE_CONNECTION=database` (`php artisan queue:work`). Scheduler: `php artisan schedule:work`.
- Docker: `docker-compose.yml` (app, nginx, mysql, redis, queue, scheduler). Variabel
  `DOCKER_DB_*` WAJIB diisi di `.env` (lihat `env.example`).

## Test

```bash
./vendor/bin/phpunit
```

- phpunit.xml mengarahkan DB ke `internet_customer_only_test` (DB dan Radius DB). JANGAN ganti ke DB dev/staging.
- Buat sekali: `CREATE DATABASE internet_customer_only_test`, lalu
  `DB_DATABASE=internet_customer_only_test php artisan migrate --env=testing --force`.
- Test membuat datanya sendiri. Jangan pakai `Model::firstOrFail()` pada data yang hanya ada di staging.
- Tidak ada router/OLT sungguhan di test: `RouterOSService` di-mock.

## Arsitektur

**Tenant.** `Company` (uuid) = satu ISP. Hampir semua tabel punya `company_id`. Isolasi data saat ini
lewat scope manual `byCompany(...)` per model (BELUM global scope, jadi wajib dipanggil di setiap query).
- `IdentifyTenant` (middleware global) resolve tenant dari host: `{slug}.internetrt.com` atau custom domain
  terverifikasi. Hasilnya di helper `tenant()`. Detail: `TENANT_DOMAIN_SETUP.md`.
- Custom domain: data layer jadi, routing belum.

**Akses & izin.** Group route admin memakai `auth`, `role.permission`, `ip.restriction`, `billing.active`.
`RolePermission` mengotorisasi dari segmen terakhir NAMA ROUTE (`internet-customer.monitor.status` -> `status`).
Route baru harus dinamai dengan benar dan permission-nya di-seed
(`database/seeders/PermissionForMenuInternetCustomerSeeder.php`).

**Tipe layanan customer** (`internet_customers.access_type`):
| access_type | Alur |
|---|---|
| `pppoe` | PPP secret di MikroTik; auth via RADIUS bila `RADIUS_ENABLED`, fallback Direct API |
| `ipoe` (Static) | IP manual + Simple Queue; isolir via address-list `ISOLIR_STATIC`. Lihat `docs/STATIC_IPOE_SETUP.md` |
| `hotspot` | HotspotServer + user/voucher; Radius bisa dipakai. Model `HotspotVoucher` BELUM ada |

**Provisioning.** `ProvisionCustomerJob` memilih alur dari `access_type` dan status
(installed / suspended / reactivated). `RouterOSService` = Direct API ke MikroTik
(`evilfreelancer/routeros-api-php`); `RadiusService` menulis ke DB FreeRADIUS (koneksi `radius`,
model di `app/Models/Radius`). `PppoeReconcileService` / `StaticReconcileService` mencocokkan DB dengan router.

**Monitoring.**
- Customer: `CustomerMonitorController` (PPPoE status/traffic/latency/restart via RouterOS), route
  `internet-customer/{id}/monitor`.
- Jaringan: `RouterHealthCheckJob`, `NetworkIncidentAlertService` (alert WhatsApp ke NOC).
- OLT: `Services/Olt` (SNMP, `GenericSnmpDriver` digerakkan `Olt::$oid_map`), `PollOltJob`, `olts:poll`.

**Billing.**
- Customer ISP -> ISP: Midtrans/Xendit (`MidtransService`, `XenditService`, `SubscriptionService`).
- Platform -> ISP: `Services/PlatformBilling`, command `platform-billing:generate --type=snapshot|invoice|suspend`.
  Snapshot harian customer aktif, invoice bulanan memakai rata-rata harian, ISP menunggak disuspend
  (`EnsureCompanyNotSuspended`). Tarif: `services.platform_billing.rate_per_customer` (default 1000).

**Notifikasi.** WhatsApp lewat WAHA (`Services/WhatsApp`, `WAHA_SETUP.md`); `Services/Weblas` (Wablas, ada `WablasWebhookController`) masih ada; belum dicek mana yang dipakai produksi.

## Konvensi

- Komentar dan UI berbahasa Indonesia; nama kode berbahasa Inggris.
- Livewire component di `app/Http/Livewire/<Domain>/`, view di `resources/views/livewire/...`.
- Route terdaftar di `routes/web.php` per bagian (INTERNET INFRASTRUCTURE, CUSTOMERS, dst).
- Commit per sprint: test lulus dulu, baru commit. Jangan commit `.DS_Store`.

## Utang teknis yang diketahui

- Tidak ada global scope tenant (risiko kebocoran data antar ISP).
- Username Radius tanpa namespace tenant; password Radius cleartext dengan fallback `admin123`.
- `ProvisionCustomerJob` menelan semua exception (tidak retry).
- Model `Country` mengizinkan `iso_code` tetapi tabelnya tidak punya kolomnya.
- Driver OLT Hioso/HSGQ masih kosong; belum ada alert ambang RX power.
- `Kernel::scheduleRouterSyncJobs()` mereferensikan `SyncActiveSessionsJob` yang tidak ada (kode mati).
