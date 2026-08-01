<?php

namespace App\Services\WhatsApp;

/**
 * Abstraksi pengiriman WhatsApp — biar kode yang manggil (misal
 * NetworkIncidentAlertService) tidak perlu tau provider-nya WAHA,
 * Wablas, atau apapun. Ganti provider = ganti implementasi ini,
 * tidak perlu ubah kode yang manggil.
 */
interface WhatsAppSender
{
    /**
     * Kirim pesan teks WhatsApp.
     *
     * @param string $phone Nomor tujuan, format bebas (062xxx, +62xxx,
     *   62xxx) — implementasi yang bertanggung jawab normalisasi ke
     *   format yang dibutuhkan provider masing-masing.
     * @param string $message Isi pesan.
     * @return array Response mentah dari provider (buat logging/debug).
     * @throws \App\Services\WhatsApp\WhatsAppSendException Kalau gagal kirim.
     */
    public function sendText(string $phone, string $message): array;
}
