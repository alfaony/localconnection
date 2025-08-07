<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyTaskResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'point' => $this->point,
            'status' => $this->taskStatus->name,
            'status_submit' => $this->status_submit,
            'approved' => $this->approved,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'assign' => $this->assign ? [
                'id' => $this->assign->id,
                'name' => $this->assign->name,
                'email' => $this->assign->email,
            ] : null,
            'category' => $this->category ? $this->category->name : null,
            'type' => $this->type->name,
            'project' => $this->project ? $this->project->name : null,
            'media' => $this->media->map(function($media) {
                return [
                    'id' => $media->id,
                    'file_path' => asset('storage/'.$media->file_path),
                    'file_type' => $media->file_type
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
