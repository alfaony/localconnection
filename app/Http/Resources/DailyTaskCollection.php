<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class DailyTaskCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // If the resource is a paginator, the paginator instance is available via $this->resource.
        $isPaginated = method_exists($this->resource, 'total');

        return [
            'data' => $this->collection,
            'pagination' => $isPaginated ? [
                'total'        => $this->resource->total(),
                'count'        => $this->resource->count(),
                'per_page'     => $this->resource->perPage(),
                'current_page' => $this->resource->currentPage(),
                'total_pages'  => $this->resource->lastPage(),
            ] : null,
        ];
    }
}
