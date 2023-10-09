<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use Ramsey\Uuid\Uuid;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $userUuid = User::first();

        for ($i = 1; $i <= 20; $i++) {
            $project = new Project(); // Membuat instance baru dari model Project
            $project->user_id = $userUuid->id;
            $project->title = "Project $i";
            $project->budget = 1000000 * $i; // Ubah anggaran sesuai kebutuhan
            $project->start_date = now();
            $project->end_date = now()->addMonths(1);
            $project->description = "Deskripsi Project $i";
            $project->save(); // Menyimpan proyek ke database
        }
    }
}

