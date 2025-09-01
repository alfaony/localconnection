<?php

namespace App\Listeners;

use App\Events\AbsensiVerified;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AbsensiVerifiedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\AbsensiVerified  $event
     * @return void
     */
    /**
     * Handle the event.
     *
     * @param  \App\Events\AbsensiVerified  $event
     * @return void
     */
    public function handle(AbsensiVerified $event)
    {
        // Arahkan pengguna ke halaman form foto dan lokasi setelah verifikasi selesai
        // Menggunakan redirect untuk mengarahkan ke form foto dan lokasi
        return redirect()->route('attendance.form', ['barcodeId' => $event->barcode->id]);
    }
}
