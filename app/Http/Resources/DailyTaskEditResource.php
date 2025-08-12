<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DailyTaskEditResource extends JsonResource
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
            'objective' => $this->objective_id,
            'key_result' => $this->keyResults->pluck('id'), // asumsi relasi keyResults
            'daily_task_project' => $this->daily_task_project_id,
            'project_id' => $this->project_id,
            'user_id' => $this->user_id,
            'assignment_user_id' => $this->assignment_user_id,
            'custom_field_values' => $this->custom_field_values, // bisa array
            'start_date' => $this->start_date ? \Carbon\Carbon::parse($this->start_date)->toDateString() : null,
            'end_date' => $this->end_date ? \Carbon\Carbon::parse($this->end_date)->toDateString() : null,
            'assignment_user_id' => $this->assignment_user_id,
            'category_id' => $this->daily_task_category_id,
            'type_id' => $this->daily_task_type_id,
            'recurring' => $this->recurring ?? [],
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
