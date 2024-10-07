<?php

namespace App\Exports;

use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class ProjectsExport implements FromCollection, WithHeadings, WithMapping
{
    private $rowNumber = 0;
    public function collection()
    {
        return Project::byCompany(Auth::user()->company_id)->orderBy('created_at', 'desc')->get();
    }

    // Menentukan judul kolom untuk file export
    public function headings(): array
    {
        return [
            'No',
            'Client', 
            'Project Name', 
            'PM (Project Manager)', 
            'Status Project', 
            'Start Date', 
            'End Date', 
            'Timeline', 
            'Progress'
        ];
    }

    // Mapping data ke kolom yang sesuai
    public function map($project): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,  // Kolom nomor urut
            $project->workOrder->quote->customer->name,                     // Field Client
            $project->title,                            // Field Project Name
            $project->user->name,             // Field PM (Project Manager)
            Ucfirst($project->status_project),                           // Field Status Project
            \Carbon\Carbon::parse($project->start_date)->format('d-m-Y'), // Field Start Date
            \Carbon\Carbon::parse($project->end_date)->format('d-m-Y'),   // Field End Date
            $project->progress_percentage,                   // Field Progress (misalnya dalam persen)
            $project->progress_task ?? "0%"                  // Field Progress (misalnya dalam persen)
        ];
    }
}
