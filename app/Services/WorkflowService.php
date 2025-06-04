<?php

namespace App\Services;
use Illuminate\Support\Facades\Storage;

class WorkflowService
{
    public static function generateSteps($itemRequest)
    {
        $usedVendorIds = $itemRequest->purchase->pluck('product_supplier_id')->toArray() ?? [];
        return 
        [
        'Pengajuan Barang' => 
        [
            'icon' => 'fas fa-file-signature',
            'status' => 'completed',
            'description' => 'Permintaan awal pengadaan barang',
            'data' => [
                'requester' => $itemRequest->requester->name ?? 'N/A',
                'notes' => $itemRequest->description
            ],
            'date' => $itemRequest->created_at
        ],
        'Penunjukan PIC' => 
        [
            'icon' => 'fas fa-user-tie',
            'status' =>  $itemRequest->assigned_pic_id ? 'completed' : 'active',
            'description' => 'Penunjukan Penanggung Jawab Procurement',
            'data' => [
                'assigned_pic' => $itemRequest->assignedPic->name ?? 'Belum ditentukan',
                'assignment_method' => 'Beban kerja terkecil',
                'current_workload' => '4 tugas aktif',
                'contact' => $itemRequest->assignedPic->email ?? '-'
            ],
            'date' => $itemRequest->updated_at
        ],
        'Pencarian Toko' => 
        [
            'icon' => 'fas fa-store',
            'status' => $itemRequest->assignedPic ? ($itemRequest->is_open ? 'active' : 'completed') : 'pending',
            'description' => 'Identifikasi dan negoisasi dengan Toko',
            'data' => [
                'vendors' => $itemRequest->potentialVendors->filter(function ($vendor) use ($usedVendorIds) 
                {
                    return $vendor->productSupplier && !in_array($vendor->productSupplier->id, $usedVendorIds);
                })
                ->sortByDesc('responded_at')
                 ->map(function ($vendor) {
                    return [
                        'id' => $vendor->productSupplier->id,
                        'name' => $vendor->productSupplier ? $vendor->productSupplier->store_name : '-',
                        'location' => $vendor->productSupplier ? $vendor->productSupplier->location : '-',
                        'phone_number' => $vendor->productSupplier ? $vendor->productSupplier->phone_number : '-',
                        'foto' => $vendor->productSupplier ? $vendor->productSupplier->store_photo ? Storage::url($vendor->productSupplier->store_photo) : NULL : NULL,
                        'message' => $vendor->response_message ?? '',
                        'response_time' => $vendor->responded_at ?? null,
                        'price_offered' => $vendor->price_offered,
                        'response' => $vendor->responded,
                        'note' => $vendor->note

                    ];
                })->toArray(),
                'broadcast_status' => [
                    'sent' => true,
                    'total_vendors' => $itemRequest->potentialVendors->count(),
                    'responses' => $itemRequest->potentialVendors->count()
                ]
            ],
            'date' => $itemRequest->potentialVendors->first() ? $itemRequest->potentialVendors->first()->updated_at : null
        ],
        'Konfirmasi Pembayaran' => 
        [
            'status' => $itemRequest->purchase->count() > 0 ? ($itemRequest->is_complete_payment ? 'completed' : 'active') : 'pending',
            'title' => 'Konfirmasi Pembayaran',
            'icon' => 'fas fa-coins',
            'description' => 'Verifikasi dan konfirmasi pembayaran',
            'data' => [
                
            ]
            ,
        'date' => $itemRequest->purchase->first() ? $itemRequest->purchase->first()->created_at : null
        ],
        'Upload Resi Pengiriman'=>
        [
            'title' => 'Upload Resi Pengiriman',
            'icon' => 'fas fa-file-invoice',
            'status' => $itemRequest->is_complete_payment ? ($itemRequest->delivery ? 'completed' : 'active') : 'pending',
            'description' => 'Upload bukti pengiriman barang',
            'data' => [
                'tracking_info' => null,
                'shipping_methods' => ['JNE', 'SiCepat', 'DHL']
            ],
            'date' => $itemRequest->delivery ? $itemRequest->delivery->created_at : null
        ],
        'Pengiriman Barang' => 
        [
            'icon' => 'fas fa-truck',
            'status' => $itemRequest->delivery ? ($itemRequest->delivery->delivery_photo ? 'completed' : 'active') : 'pending',
            'description' => 'Proses pengiriman ke Pembeli',
            'data' => [
                'shipping_methods' => ['JNE', 'SiCepat', 'DHL'],
                'tracking_info' => null
            ]
            ,
            'date' => $itemRequest->delivery ? $itemRequest->delivery->updated_at : null
        ]
    ];
    }
}