<?php

namespace App\Imports;

use App\Models\DailyTask;
use App\Models\TaskStatus;
use App\Models\DailyTaskCategory;
use App\Models\DailyTaskType;
use App\Models\DailyTaskCustomFieldValue;
use App\Models\DailyTaskMessage;
use App\Models\DailyTaskStatusRecord;

use App\Schemas\ParamSchema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Carbon\Carbon;

class DailyTaskImport implements WithMultipleSheets
{
    protected $objectiveIds;
    protected $keyResultIds;
    protected $projectId;
    protected $dataProjectId;

    public function __construct($request)
    {
        $this->objectiveIds = $request['objective_id'];
        $this->keyResultIds = $request['key_result_0'];
        $this->projectId = $request['project_id'];
        $this->dataProjectId = $request['data_project_id'][0];
        $this->custom_field_values = $request['custom_field_values'] ?? NULL;
    }

    public function sheets(): array
    {
        return [
            'Daily Tasks' => new DailyTaskSheetImport($this->objectiveIds, $this->keyResultIds, $this->projectId, $this->dataProjectId, $this->custom_field_values),
        ];
    }
}

class DailyTaskSheetImport implements ToCollection, WithCalculatedFormulas, WithStartRow
{
    protected $objectiveIds;
    protected $keyResultIds;
    protected $projectId;
    protected $dataProjectId;
    protected $custom_field_values;

    public function __construct($objectiveIds, $keyResultIds, $projectId, $dataProjectId, $custom_field_values = NULL)
    {
        $this->objectiveIds = $objectiveIds;
        $this->keyResultIds = $keyResultIds;
        $this->projectId = $projectId;
        $this->dataProjectId = $dataProjectId;
        $this->custom_field_values = $custom_field_values;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        $errors = [];

        foreach ($rows as $rowIndex => $row) 
        {
            if ($row->filter()->isEmpty()) {
                continue; // Skip the empty row
            }
            try {
                $this->validateRow($row, $rowIndex);
                
                
                $start_date = $this->transformDate(trim($row[0]));
                $end_date = $this->transformDate(trim($row[1]));
                $category = trim($row[2]);
                $type = trim($row[3]);
                $user_email = trim($row[4]);
                $submit_date = $this->transformDate(trim($row[5]));
                $task_name = trim($row[6]);
                $description = trim($row[7]);
                $report = trim($row[8]);


                $check = $this->dailyTaskSubmit($end_date,$submit_date);
                $categoryId = $this->getCategoryId($category);

                $dailyTask = new DailyTask();
                $dailyTask->user_id = Auth::user()->id;
                $dailyTask->task_status_id = $check['status'];
                $dailyTask->start_date = Carbon::parse($start_date);
                $dailyTask->end_date = Carbon::parse($end_date);
                $dailyTask->assignment_user_id = $this->getUserIdByEmail($user_email);
                $dailyTask->daily_task_category_id = $categoryId;
                $dailyTask->daily_task_type_id = $this->getTypeId($type);
                $dailyTask->project_id = $this->dataProjectId;
                $dailyTask->daily_task_project_id = $this->projectId;
                $dailyTask->name = $task_name;
                $dailyTask->description = $description ?? NULL;
                $dailyTask->point = NULL;
                $dailyTask->report_note = $submit_date ? $report : NULL ;
                $dailyTask->submit = $submit_date ? Carbon::parse($submit_date) : NULL;
                $dailyTask->objective_id = $this->objectiveIds;

                $dailyTask->save();


                // Duplicate Custom Field
            if (isset($this->custom_field_values)) {
                foreach ($this->custom_field_values as $customFieldId => $customFieldValueId) {
                    if(is_array($customFieldValueId))
                    {
                        // 
                        foreach($customFieldValueId as $valueId)
                        {
                            DailyTaskCustomFieldValue::create([
                                'daily_task_id' => $dailyTask->id,
                                'custom_field_id' => $customFieldId,
                                'custom_field_value_id' => $valueId,
                            ]);
                        }
                    }else{
                        DailyTaskCustomFieldValue::create([
                            'daily_task_id' => $dailyTask->id,
                            'custom_field_id' => $customFieldId,
                            'custom_field_value_id' => $customFieldValueId,
                        ]);
                    }
                }
            }

                $this->message($dailyTask->id,'create',' Import Tugas '.$dailyTask->name);
                $this->statusrecord($dailyTask, $check['status']);

            } catch (ValidationException $e) {
                $errorMessages = $this->formatValidationErrors($e->errors());
                $errors[] = "Kesalahan pada baris " . ($rowIndex + 2) . ": " . $errorMessages;
                Log::error("Kesalahan pada baris " . ($rowIndex + 2) . ": " . $errorMessages);
            } catch (\Exception $e) {
                // dd($e);
                Log::error("Kesalahan pada baris " . ($rowIndex + 2) . ": " . $e->getMessage());
            }
        }

        if (!empty($errors)) {
            throw new \Exception(implode("\n", $errors));
        }
    }

    private function getUserIdByEmail($email)
    {
        $user = \App\Models\User::where('email', $email)->first();
        return $user ? $user->id : null;
    }

    private function getCategoryId($category)
    {
        return DailyTaskCategory::byCompany(Auth::user()->company_id)->orWhere('name', 'like', '%' . $category . '%')->first()->id ?? NULL;
    }

    private function getTypeId($type)
    {
        return DailyTaskType::Where('name', 'like', '%' . $type . '%')->first()->id ?? NULL;
    }

    private function transformDate($value)
    {
        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('Y-m-d');
        }

        return $value;
    }

    private function validateRow($row, $rowIndex)
    {
        $validator = \Validator::make(
            [
                'start_date' => $this->transformDate(trim($row[0])),
                'end_date' => $this->transformDate(trim($row[1])),
                'category' => trim($row[2]),
                'type' => trim($row[3]),
                'user_email' => trim($row[4]),
                'submit_date' => $this->transformDate(trim($row[5])),
                'task_name' => trim($row[6]),
            ],
            [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'category' => 'required|exists:daily_task_categories,name',
                'type' => 'required|exists:daily_task_types,name',
                'user_email' => 'required|email|exists:users,email',
                'submit_date' => 'nullable|date',
                'task_name' => 'required|string|max:255'
            ],
            [
                'start_date.required' => 'Tanggal mulai harus diisi.',
                'start_date.date_format' => 'Format tanggal mulai tidak valid. Gunakan format Y-m-d.',
                'start_date.date' => 'Tanggal mulai harus berupa tanggal yang valid.',
                'end_date.required' => 'Tanggal berakhir harus diisi.',
                'end_date.date_format' => 'Format tanggal berakhir tidak valid. Gunakan format Y-m-d.',
                'end_date.date' => 'Tanggal berakhir harus berupa tanggal yang valid.',
                'end_date.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal mulai.',
                'category.required' => 'Kategori harus diisi.',
                'category.exists' => 'Kategori yang dipilih tidak valid.',
                'type.required' => 'Tipe harus diisi.',
                'type.exists' => 'Tipe yang dipilih tidak valid.',
                'user_email.required' => 'Email pengguna harus diisi.',
                'user_email.email' => 'Format email tidak valid.',
                'user_email.exists' => 'email tidak valid.',
                'submit_date.date' => 'Tanggal submit harus berupa tanggal yang valid.',
                'task_name.required' => 'Nama tugas harus diisi.',
                'task_name.string' => 'Nama tugas harus berupa string.',
                'task_name.max' => 'Nama tugas tidak boleh lebih dari 255 karakter.',
            ]
        );
        

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function formatValidationErrors($errors)
    {
        $errorMessages = [];
        foreach ($errors as $field => $messages) {
            $errorMessages[] = implode(", ", $messages);
        }
        return implode("; ", $errorMessages);
    }

    private function dailyTaskSubmit($end_date = null,$submit = null)
    {
        $statusReport = null;

        if($end_date && $submit)
        {
            $status = TaskStatus::where('name', ParamSchema::INREVIEW)->firstOrFail()->id;
    
            $endDate = Carbon::parse($end_date)->endOfDay();
            $submitDate = Carbon::parse($submit)->startOfDay();
            $statusReport = ($submitDate->lessThanOrEqualTo($endDate)) ? ParamSchema::ONTIME : ParamSchema::LATE;
        }else
        {
            $status = TaskStatus::where('name', ParamSchema::TODO)->firstOrFail()->id;
        }

        return 
        [
            'status' => $status,
            'statusReport' => $statusReport
        ];
        
    }

    protected function message($dailyTaskId, $template, $message, $filePath = null)
    {
        switch ($template) 
        {
            case 'create':
                $message = 
                '
                <div class="alert alert-primary d-flex align-items-center" role="alert" style="background-color: #cce5ff; border-color: #004085; color: #004085;">
                    <i class="fa fa-plus-circle mr-2" style="color: #004085;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
            case 'edit':
                $message = 
                '
                <div class="alert alert-warning d-flex align-items-center" role="alert" style="background-color: #fff3cd; border-color: #856404; color: #856404;">
                    <i class="fa fa-edit mr-2" style="color: #856404;"></i>
                    <div>
                        '.$message.' 
                    </div>
                </div>
                ';
                break;
            case 'report':
                $message = 
                '
                <div class="alert alert-primary d-flex align-items-center" role="alert" style="background-color: #cce5ff; border-color: #004085; color: #004085;">
                    <i class="fa fa-plus-circle mr-2" style="color: #004085;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'approvement':
                $message = 
                '
                <div class="alert alert-success d-flex align-items-center" role="alert" style="background-color: #d4edda; border-color: #155724; color: #155724;">
                    <i class="fa fa-thumbs-up mr-2" style="color: #155724;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            case 'extend':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #383d41; color: #383d41;">
                    <i class="fa fa-clock mr-2" style="color: #383d41;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;

            case 'reject':
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #ae2121; color: #ae2121;">
                    <i class="fa fa-times-circle mr-2" style="color: #ae2121;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
            default:
                $message = 
                '
                <div class="alert alert-secondary d-flex align-items-center" role="alert" style="background-color: #e2e3e5; border-color: #383d41; color: #383d41;">
                    <i class="fa fa-comment mr-2" style="color: #383d41;"></i>
                    <div>
                        '.$message.'
                    </div>
                </div>
                ';
                break;
        }

        $dailyTaskMessage = new DailyTaskMessage();
        $dailyTaskMessage->user_id = Auth::user()->id;
        $dailyTaskMessage->daily_task_id = $dailyTaskId;
        $dailyTaskMessage->message = $message;
        $dailyTaskMessage->file_path = $filePath ?? NULL;
        $dailyTaskMessage->save();

        return true;
    }

    protected function statusrecord($dailyTask, $status)
    {
        DailyTaskStatusRecord::create([
            'daily_task_id' => $dailyTask->id,
            'task_status_id' => $status,
            'date' => now(),
        ]);

        return true;
    }
}