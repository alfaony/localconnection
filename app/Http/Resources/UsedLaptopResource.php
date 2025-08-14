<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

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
            'selling_price' => $this->suggested_selling_price,
            'images' => $this->media->map(function ($media) {
                return env('APP_URL') . Storage::url($media->file_path);
            })->toArray(),
        ];
    }
}
