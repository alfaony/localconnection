<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\DailyTaskProject;
use App\Models\Project;

class UserApiController extends BaseController
{
    /**
     * @return \Illuminate\Http\Response
     */
    public function indexUsers()
    {
        try {
            $data = User::select('id', 'name', 'email')->get();
            return $this->sendResponse($data->toArray(), 'Daftar pengguna berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar pengguna.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * * @return \Illuminate\Http\Response
     */
    public function indexMainProjects()
    {
        try {
            $user = Auth::user();
            $data = DailyTaskProject::byCompany($user->company_id)->get();

            return $this->sendResponse($data->toArray(), 'Daftar main project berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar main project.', ['error' => $e->getMessage()]);
        }
    }

    /**
     * * @return \Illuminate\Http\Response
     */
    public function indexProjects()
    {
        try {
            $user = Auth::user();
            $data = Project::byCompany($user->company_id)->get();

            return $this->sendResponse($data->toArray(), 'Daftar project berhasil diambil.');
        } catch (\Exception $e) {
            return $this->sendError('Gagal mengambil daftar project.', ['error' => $e->getMessage()]);
        }
    }

}
