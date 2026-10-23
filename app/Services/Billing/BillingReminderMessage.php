<?php

namespace App\Services\Billing;

use App\Models\UserCustomer;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Menyusun pesan WA reminder tagihan.
 *
 * Satu nomor HP bisa punya beberapa layanan. Agar tidak dikirim berulang, blok detail
 * (ID, paket, jatuh tempo, tagihan) pada template diulang per layanan, lalu ditambah
 * total keseluruhan dan daftar link pembayaran. Satu layanan = hasil sama dengan template asli.
 */
class BillingReminderMessage
{
    private const DETAIL_PLACEHOLDERS = ['{{kode}}', '{{paket}}', '{{jatuh_tempo}}', '{{tagihan}}'];

    /**
     * @param  Collection<int, UserCustomer>  $customers  layanan dengan nomor HP yang sama
     * @param  string  $template  template yang sudah melalui html_to_wa()
     */
    public static function build(Collection $customers, string $template): string
    {
        $rows = $customers->map(fn (UserCustomer $c) => self::customerData($c))->values();

        if ($rows->count() === 1) {
            return strtr($template, self::replacements($rows->first(), $rows->first()['name']));
        }

        $lines = explode("\n", $template);
        $first = $last = null;
        foreach ($lines as $i => $line) {
            if (self::hasDetailPlaceholder($line)) {
                $first ??= $i;
                $last = $i;
            }
        }

        $name = $rows->first()['name'];
        $total = 'Rp. ' . number_format($rows->sum('amount'), 2, ',', '.');
        $links = $rows->map(fn ($r) => "{$r['code']} ({$r['package']}): {$r['url']}")->implode("\n");

        // Template tanpa blok detail: kirim apa adanya dengan data layanan pertama + total.
        if ($first === null) {
            return strtr($template, ['{{url}}' => $links] + self::replacements($rows->first(), $name));
        }

        $blockTemplate = implode("\n", array_slice($lines, $first, $last - $first + 1));
        $blocks = [];
        foreach ($rows as $n => $row) {
            $blocks[] = 'Layanan ' . ($n + 1) . "\n" . strtr($blockTemplate, self::replacements($row, $name));
        }
        $blocks[] = "Total Seluruh Tagihan: {$total}";

        $before = implode("\n", array_slice($lines, 0, $first));
        $after = implode("\n", array_slice($lines, $last + 1));

        $message = $before . "\n" . implode("\n\n", $blocks) . "\n" . $after;

        return strtr($message, ['{{nama}}' => $name, '{{url}}' => $links]);
    }

    public static function customerData(UserCustomer $customer): array
    {
        $internetCustomer = $customer->internetCustomer;
        $priceData = $internetCustomer->internetPackage->getPriceForRegion(
            $internetCustomer->province_id,
            $internetCustomer->city_id,
            $internetCustomer->district_id,
            $internetCustomer->subdistrict_id
        );
        $latestPurchase = $internetCustomer->purchases()->latest()->first();
        $due = Carbon::parse($customer->end_billing_date);

        return [
            'name'    => $customer->name,
            'code'    => $internetCustomer->code,
            'package' => $internetCustomer->internetPackage->name,
            'due'     => $due->format('d') . ' ' . $due->locale('id')->monthName . ' ' . $due->year,
            'amount'  => (float) ($latestPurchase?->amount_paid ?? $priceData['price_nett']),
            'url'     => route('internet-customer.customer.show', $internetCustomer->code),
        ];
    }

    private static function replacements(array $row, string $name): array
    {
        return [
            '{{nama}}'        => $name,
            '{{kode}}'        => $row['code'],
            '{{paket}}'       => $row['package'],
            '{{jatuh_tempo}}' => $row['due'],
            '{{tagihan}}'     => 'Rp. ' . number_format($row['amount'], 2, ',', '.'),
            '{{url}}'         => $row['url'],
        ];
    }

    private static function hasDetailPlaceholder(string $line): bool
    {
        foreach (self::DETAIL_PLACEHOLDERS as $p) {
            if (str_contains($line, $p)) {
                return true;
            }
        }

        return false;
    }
}
