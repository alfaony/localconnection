# Setup Static / IPoE (access_type = `ipoe`)

Customer Static TIDAK memakai PPP session dan TIDAK memakai RADIUS. Internet berjalan lewat IP yang
diisi manual di `internet_customers.ip_address`. Kecepatan dibatasi MikroTik Simple Queue berdasarkan IP itu.

Sumber kebenaran: `ProvisionCustomerJob::handleIpoe()` dan `RouterOSService` (`upsertSimpleQueue`,
`addToIsolirAddressList`, `removeFromIsolirAddressList`).

## Yang dilakukan aplikasi otomatis

| Status customer | Aksi di MikroTik |
|---|---|
| installed / reactivated | Hapus IP dari address-list `ISOLIR_STATIC`, buat/aktifkan Simple Queue |
| suspended | Tambah IP ke address-list `ISOLIR_STATIC`, nonaktifkan Simple Queue |

Customer tanpa `ip_address` di-skip (hanya dicatat di log sebagai warning).

## Yang HARUS disiapkan manual di router (sekali per router)

Aplikasi hanya mengisi address-list. Walled garden harus dibuat di firewall oleh admin ISP.
Contoh dasar (sesuaikan dengan topologi dan IP halaman pembayaran Anda):

```routeros
# 1. Izinkan customer terisolir mengakses DNS dan halaman tagihan saja
/ip firewall filter
add chain=forward src-address-list=ISOLIR_STATIC dst-address=<IP_HALAMAN_TAGIHAN> action=accept \
    place-before=0 comment="isolir: izinkan halaman tagihan"
add chain=forward src-address-list=ISOLIR_STATIC protocol=udp dst-port=53 action=accept \
    place-before=0 comment="isolir: izinkan DNS"

# 2. Blokir sisanya
add chain=forward src-address-list=ISOLIR_STATIC action=drop \
    comment="isolir: blokir sisanya"
```

Opsional: redirect HTTP customer terisolir ke halaman tagihan dengan NAT `dst-nat` dari
`src-address-list=ISOLIR_STATIC`, `dst-port=80`.

## Verifikasi

1. Customer aktif: IP tidak ada di `/ip firewall address-list print where list=ISOLIR_STATIC`,
   dan ada Simple Queue dengan target IP customer.
2. Suspend customer dari aplikasi: IP muncul di address-list, queue disabled, dan customer
   hanya bisa membuka halaman tagihan.
3. Reactivate: kembali ke kondisi 1.

Halaman rekonsiliasi: `static-customer/reconcile` (membandingkan data aplikasi dengan router).

## Batasan saat ini

- Isolir hanya berfungsi bila firewall di atas sudah dipasang. Tanpa itu, suspend hanya mematikan
  Simple Queue, dan customer masih bisa lewat jika router tidak membatasi default-nya.
- IP customer harus tetap (static/DHCP lease statis). Perubahan IP tidak otomatis dipindah di router.
