<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UsedLaptopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'is_sold' => $this->sold_status,
            'serial_number' => $this->serial_number,
            'brand' => $this->brand,
            'slug' => $this->slug,
            'name' => $this->name,
            'processor' => $this->processor,
            'ram' => $this->ram,
            'ssd' => $this->ssd,
            'gpu' => $this->gpu,
            'operating_system' => $this->operating_system,
            'notes' => $this->notes,
            'buying_price' => $this->purchase_price,
            'selling_price' => $this->jakarta_price,
            'qc_status' => $this->qc_status,
            'images' => $this->media->sortBy('order')->map(function ($media) {
                return $media->file_path;
            })->toArray(),
            'checklist' => $this->checks->whereNotNull('status')->filter(function ($check) {
                return $check->item;
            })->map(function ($check) {
                if($check->item){
                    return [
                        'item' => $check->item->name,
                        'status' => $check->status == "good" ? "Baik" : "Rusak",
                        'note' => $check->note ?? "-",
                    ];
                }
            })->values()->toArray(),
        ];
    }
}