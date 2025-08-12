<?php


namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Schemas\RoleSchema;

use App\Http\Requests\ObjectiveStoreRequest;

use App\Models\Objective;
use App\Models\Division;
use App\Models\DailyTask;
use App\Models\User;
use App\Models\ObjectiveKeyResult;
use App\Models\TaskStatus;
use App\Models\Mission;

class ObjectiveController extends BaseController
{
    // Custom
    public function getresult(Request $request, $slug)
    {
        try {
            $objective = Objective::byCompany(Auth::user()->company_id)->where('id', $slug)->firstOrFail();
            // Ambil semua key results milik objective
            if(!$objective) {
                return response()->json([
                    'success' => false,
                    'message' => 'Objective tidak ditemukan',
                ], 404);
            }
            $keyResults = $objective->keyResults()
                ->select('id', 'result')
                ->orderBy('result')
                ->get();

            // Parameter opsional
            $index = (int) ($request->input('index', 0));
            $dailyTaskId = $request->input('dailyTaskId');

            // Default
            $selectedKeyResultIds = collect();
            $hasHead = false;

            // Jika dailyTaskId diberikan, tandai mana yang sudah terpilih dan cek hasHead
            if (!empty($dailyTaskId)) {
                $dailyTask = DailyTask::with(['keyResults:id', 'head'])->find($dailyTaskId);

                if ($dailyTask) {
                    $selectedKeyResultIds = $dailyTask->keyResults->pluck('id');
                    $hasHead = !is_null($dailyTask->head);
                }
            }

            // Susun payload key results
            $payload = $keyResults->map(function ($kr) use ($selectedKeyResultIds) {
                return [
                    'id' => $kr->id,
                    'result' => $kr->result,
                    'selected' => $selectedKeyResultIds->contains($kr->id),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'objective_id' => $objective->id,
                    'key_results' => $payload,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('ObjectiveController@getresult error: ' . $e->getMessage());

            // dd($e);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch key results.',
            ], 500);
        }
    }
}

