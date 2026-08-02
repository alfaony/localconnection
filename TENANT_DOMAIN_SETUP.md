# Subdomain & Custom Domain Multi-Tenant

## Status Ringkas

| Bagian | Status |
|---|---|
| Subdomain gratis (`oni.internetrt.com`) — halaman registrasi customer | ✅ **Selesai & aman dipasang** |
| Custom domain — data layer (submit, verifikasi TXT record) | ✅ **Selesai** |
| Custom domain — routing (biar domain kamu beneran nampilin halaman) | ⚠️ **Belum**, butuh info tambahan (lihat di bawah) |
| Dashboard admin ISP ikut pindah ke domain sendiri | ⚠️ **Belum disentuh sama sekali** — scope terpisah, lebih besar |

## 1. Subdomain Gratis — Cara Setup

### DNS (WAJIB)
Di DNS provider domain `internetrt.com` kamu, tambahkan **wildcard record**:
```
Type: A (atau CNAME kalau server di belakang load balancer/proxy)
Name: *
Value: <IP server kamu>
```
Ini bikin SEMUA subdomain (`oni.internetrt.com`, `budi.internetrt.com`, dst)
otomatis ngarah ke server yang sama, tanpa perlu nambah DNS record satu-satu
tiap ada company baru.

### SSL Certificate (WAJIB)
Subdomain butuh **wildcard SSL certificate** (`*.internetrt.com`). Let's
Encrypt bisa kasih ini GRATIS, tapi caranya beda dari cert biasa — wajib
pakai **DNS-01 challenge** (bukan HTTP-01), karena certbot perlu
membuktikan kamu punya kontrol atas domain lewat DNS record, bukan lewat
file di web server:

```bash
certbot certonly --manual --preferred-challenges dns \
  -d internetrt.com -d '*.internetrt.com'
```

Certbot akan minta kamu nambahin TXT record sementara ke DNS buat proses
verifikasi. Kalau DNS provider kamu Cloudflare/Route53/dll yang punya API,
certbot punya plugin buat otomatisasi ini (`certbot-dns-cloudflare`, dll)
biar nggak perlu manual tiap renewal.

### .env
```
TENANT_BASE_DOMAIN=internetrt.com
```

### Testing
1. `php artisan route:list | grep tenant` — harus muncul route
   `tenant.registration` dengan domain `{companyId}.internetrt.com`
2. Buka `http://oni.internetrt.com` (ganti `oni` dengan slug company yang
   beneran ada) — harus nampilin form registrasi customer yang sama
   dengan `internetrt.com/internet-customer/registration/oni`

## 2. Custom Domain — Yang Sudah Bisa Dipakai Sekarang

Company bisa masuk ke halaman **Domain Company**
(`route('company.domain-settings')`, ada di menu — cek `AppServiceProvider`
kalau belum nongol), submit domain mereka, dapat instruksi TXT + CNAME
record, dan klik "Verifikasi" buat ngecek DNS-nya.

Setelah verifikasi TXT record berhasil, `companies.custom_domain_verified_at`
keisi. Tapi **domain itu belum beneran nampilin apa-apa** — ini bagian yang
masih PR (lihat bawah).

## 3. Custom Domain — Kenapa Routing-nya Belum Saya Selesaikan

Laravel `Route::domain()` butuh **pattern domain yang diketahui di awal**
(kayak `{slug}.internetrt.com` — jelas polanya). Custom domain itu
**domain arbitrary** (`www.domainsaya.com`, `internet.tokoku.id`, apa
aja) — Laravel nggak bisa bikin 1 route yang "nangkep semua domain yang
belum diketahui sebelumnya".

Solusinya ada, tapi butuh **modifikasi route `/` yang sekarang jadi
dashboard admin** (`HomeController@index`, yang di-protect `auth`
middleware) — supaya route itu ngecek dulu: *"request ini datang dari
custom domain customer yang udah diverifikasi? Kalau iya, tampilkan
halaman registrasi. Kalau bukan, lanjut ke dashboard admin seperti
biasa."*

**Saya sengaja belum ngerjain ini** karena:
1. Saya nggak tau domain utama production kamu yang sebenarnya (bisa
   `internetrt.com`, bisa juga sesuatu yang lain/IP/domain testing) — kalau
   saya modifikasi route inti ini dan tebakan saya salah, **resikonya
   dashboard admin kamu bisa ke-block/nggak bisa diakses**
2. Ini kode yang paling sering dipakai (setiap login admin lewat sini),
   jadi salah sedikit dampaknya besar

**Yang saya butuh dari kamu buat lanjutin ini:**
- Domain utama/production kamu apa? (yang dipakai admin buat login sehari-hari)
- Konfirmasi: boleh saya modifikasi `HomeController@index` dan route `/`?

## 4. Custom Domain — Soal SSL (Tantangan Infra Sesungguhnya)

Ini beda cerita dari subdomain. Custom domain **TIDAK BISA** pakai
wildcard cert yang sama (`*.internetrt.com` cuma cover subdomain
`internetrt.com`, bukan `domainsaya.com`). Tiap custom domain butuh
**SSL certificate sendiri-sendiri**.

Kalau customer nambah domain terus-terusan, generate cert manual satu-satu
via certbot itu nggak scalable. Solusi yang saya sarankan buat kasus ini:

**Ganti reverse proxy ke Caddy** (kalau belum), karena Caddy punya fitur
**"On-Demand TLS"** — otomatis generate SSL certificate BARU begitu ada
request masuk ke domain yang belum dikenal, asal domain itu "diizinkan"
lewat endpoint yang kita kontrol. Ini persis kasus kita.

Contoh Caddyfile:
```
{
    on_demand_tls {
        ask http://localhost/internal/tenant-domain-check
    }
}

:443 {
    tls {
        on_demand
    }
    reverse_proxy localhost:8000
}
```

Laravel perlu expose endpoint `GET /internal/tenant-domain-check?domain=xxx`
yang return `200 OK` kalau domain itu ada di `companies.custom_domain`
dan udah verified, atau `404` kalau bukan (supaya Caddy nolak generate
cert buat domain sembarangan/nggak dikenal). Saya belum bikin endpoint
ini — kabari kalau mau saya buatkan sekalian pas udah settle soal domain
utama di poin 3.

## File yang Dibuat/Diubah

- `database/migrations/2026_08_02_000001_add_custom_domain_to_companies_table.php`
- `app/Models/Company.php` — tambah `resolveByHost()`, `requestCustomDomain()`,
  `verifyCustomDomain()`, `getPublicUrlAttribute()`
- `app/Http/Middleware/IdentifyTenant.php` — resolve tenant dari host, jalan di semua request
- `app/Http/Kernel.php` — daftarin middleware di atas
- `app/Helpers/Helper.php` — helper global `tenant()`
- `app/Http/Livewire/Company/CustomDomainSettings.php` + view — UI submit/verify custom domain
- `config/app.php` — `tenant_base_domain`, `tenant_cname_target`
- `routes/web.php` — route domain-scoped buat subdomain + route settings custom domain

## ⚠️ Penting Sebelum Deploy

`app/Http/Kernel.php` punya baris `// \App\Http\Middleware\TrustHosts::class,`
yang **sengaja di-comment** (sudah dari sebelumnya, bukan saya yang ubah).
**Jangan diaktifkan** tanpa nambahin pattern subdomain + custom domain ke
`TrustHosts`, karena kalau diaktifkan dengan config default, request dari
subdomain/custom domain bisa ke-reject duluan sebelum sempat diproses
`IdentifyTenant`.
