<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\LetterSubmissionRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Schemas\ParamSchema;

use App\Models\LetterSubmission;
use App\Models\LetterType;
use App\Models\Position;
use App\Models\UserPosition;
use App\Models\SettingCompany;
use App\Helpers\InboxHelper;
use App\Models\User;

use Carbon\Carbon;
class LetterSubmissionController extends Controller
{
    /**
     * Display a listing of the letter submissions.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $letterSubmissions = LetterSubmission::byRole()->orderBy('created_at','desc')->paginate(10);
        return view('letter_submission.index', compact('letterSubmissions'));
    }
    /**
     * Show the form for creating a new letter submission.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Mengambil semua jenis surat yang ada
        $letterTypes = LetterType::filterByUserStatus()->get();
        $positions = Position::byCompany(Auth::user()->company_id)->get();
        $lastPositon = Auth::user()->last_position ? Position::where('id',Auth::user()->last_position->position_id)->get() : null;

        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');

        // Melempar data jenis surat ke view create
        return view('letter_submission.create', compact('letterTypes','positions','company','lastPositon'));
    }

    /**
     * Store a newly created letter submission in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LetterSubmissionRequest $request)
    {
        DB::beginTransaction();
        try 
        {
            $fieldData = $request->except([
                '_token', 'user_id', 'name', 'address','id_card','npwp_number','id_card_image'
            ]);
            
            $signatureImage = $request->input('signature_image');
            if ($signatureImage) 
            {
                // Decode the base64 image
                $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureImage));

                // Simpan gambar ke storage, buat nama file unik
                $fileName = uniqid() . '.png';
                $filePath = 'public/signatures/' . $fileName;

                // Simpan ke storage
                Storage::put($filePath, $imageData);

                // Update signature image path di tabel users
                $fieldData['signature_image'] = $filePath;
            }

            $letterSubmission = new LetterSubmission();
            $letterSubmission->letter_type_id = $request->letter_type_id;
            $letterSubmission->user_id = Auth::id();
            $letterSubmission->field = json_encode($fieldData);
            
            $letterType = LetterType::findOrFail($request->letter_type_id);
            
            if($letterType->auto_approve == ParamSchema::TRUE)
            {
                $letterSubmission->is_approved = ParamSchema::TRUE;
            }
            $letterSubmission->save();

            $this->updateProfile($request, $letterSubmission->user);

            DB::commit();
            return redirect()->route('letter-submission.index')->with('success', 'Pengajuan surat berhasil dibuat.');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());
            // dd($th);
            return redirect()->route('letter-submission.index')->with('error', 'Gagal membuat pengajuan surat.');
        }
    }
    public function edit($id)
    {
        $letterSubmission = LetterSubmission::findOrFail($id);
        $letterTypes = LetterType::where('id', $letterSubmission->letter_type_id)->get();

        $user = $letterSubmission->user;
        
        if($user->last_position || isset($letterSubmission->convert_field['position_new_id']))
        {
            $position_id = isset($letterSubmission->convert_field['position_new_id']) ? $this->findPosition($letterSubmission->convert_field['position_new_id']) : $user->last_position->position_id;

            $positions = Position::where('id', $position_id)->get();

        }else
        {
            $positions = Position::byCompany($user->company_id)->get();
        }

        $lastPositon = $user->last_position ? Position::where('id',$user->last_position->position_id)->get() : null;

        $company = SettingCompany::byCompany($user->company_id)->get()->pluck('field_value','field_title');

        return view('letter_submission.edit', compact('letterSubmission', 'letterTypes','positions', 'company', 'user','lastPositon'));
    }

    public function update(LetterSubmissionRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            // Temukan letter submission berdasarkan ID
            $letterSubmission = LetterSubmission::findOrFail($id);

            // Update kolom sederhana
            $letterSubmission->letter_type_id = $request->letter_type_id;
            $letterSubmission->is_approved = NULL;

            // Data yang akan disimpan sebagai JSON di kolom 'field'
            $fieldData = $request->except([
                '_token', 'letter_type_id', 'user_id', 'name', 'address','id_card'
            ]);

            $this->updateProfile($request, $letterSubmission->user);
            
            // Simpan data field dalam format JSON
            $existingFieldData = json_decode($letterSubmission->field, true);

            // Lakukan merge antara data lama dengan data baru
            $mergedFieldData = array_merge($existingFieldData, $fieldData);

            // Simpan data profile jika diperlukan
            $this->updateProfile($request, $letterSubmission->user);

            // Simpan data field yang sudah di-merge sebagai JSON
            $letterSubmission->field = json_encode($mergedFieldData);

            // Simpan perubahan
            $letterSubmission->save();

            DB::commit();
            return redirect()->route('letter-submission.index')->with('success', 'Pengajuan surat berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui pengajuan surat.');
        }
    }


    public function show($id)
    {
        $letterSubmission = LetterSubmission::findOrFail($id);
        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $date = Carbon::parse($letterSubmission->created_at)->locale('id')->translatedFormat('d F Y');

        try {
            return view('letter_submission.template.'.$letterSubmission->letterType->template, compact('letterSubmission','company', 'date'));
        } catch (\Throwable $th) {
            return redirect()->to(route('letter-submission.index'))->with('error', 'Template surat tidak ditemukan.');
        }
    }

    public function destroy($id)
    {
        try {
            $letterSubmission = LetterSubmission::findOrFail($id);
            $letterSubmission->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pengajuan berhasil dihapus.'
            ], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus pengajuan.'
            ], 500);
        }
    }


    public function approvement(Request $request)
    {
        $this->validate($request, [
            'action' => 'required|in:approve,decline',
            'selected_ids' => 'required|array',
            'selected_ids.*' => 'required|uuid|exists:letter_submissions,id'
        ]);

        DB::beginTransaction();
        try {
            $action = $request->input('action');
            $selectedIds = $request->input('selected_ids', []);
    
            if (empty($selectedIds)) {
                return redirect()->back()->with('error', 'Tidak ada surat yang dipilih.');
            }
    
            $status = null;
    
            if ($action === 'approve') {
                $status = true;
            } elseif ($action === 'decline') {
                $status = false;
            }
    
            if ($status !== null) 
            {
                foreach ($selectedIds as $selected ) 
                {
                    # code...
                    $letterSubmission = LetterSubmission::findOrFail($selected);
                    $letterSubmission->is_approved = $status;
                    $letterSubmission->save();
                    if(($status == ParamSchema::APPROVE) && ($letterSubmission->letterType->name == ParamSchema::PERJANJIANKERJA))
                    {
                        $this->updateStatus(ParamSchema::STAFF,$letterSubmission->user_id);
                    }
                    
                    if($status == ParamSchema::APPROVE && isset($letterSubmission->convert_field['position_new_id']))
                    {
                        $this->updatePosition($letterSubmission->convert_field['position_new_id'],$letterSubmission);
                    }
                }
                
                DB::commit();
                return redirect()->route('letter-submission.index')->with('success', 'Pengajuan surat berhasil diupdate.');
            }
    
            return redirect()->back()->with('error', 'Aksi tidak valid.');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            Log::error($th->getMessage());
            return redirect()->back()->with('error', 'Aksi tidak valid.');
        }
    }


    protected function findPosition($name)
    {
        $positions = Position::byCompany(Auth::user()->company_id)->where('name',$name)->first();
        if ($positions) {
            return $positions->id;
        }else
        {
            $position = new Position();
            $position->name = $name;
            $position->company_id = Auth::user()->company_id;
            $position->save();
            return $position->id;
        }

    }

    protected function updateProfile($request, $user)
    {
        $letterType = LetterType::findOrFail($request->letter_type_id);
        
        if($letterType->is_ending)
        {
            $this->updateStatus(ParamSchema::NONSTAFF,$user->id);
            $lastPosition = $user->last_position;
            if($lastPosition)
            {
                $lastPosition->end_date = Carbon::now();
                $lastPosition->save();
            }
        }

        if($request->name || $request->address || $request->id_card)
        {
            $user->name = $request->name;
            $user->address = $request->address;
            $user->id_card = $request->id_card;
        }

        if($request->npwp_number)
        {
            $user->npwp_number = $request->npwp_number;
        }

        if ($request->hasFile('id_card_image')) 
        {
            $file = $request->file('id_card_image');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $filePath = 'public/id_cards/' . $fileName;

            // Simpan file ke storage
            Storage::put($filePath, file_get_contents($file));

            // Update kolom id_card_image di tabel users
            $user = Auth::user();
            $user->id_card_image = $filePath;
            $user->save();
        }

        $user->save();
    }

    protected function updateStatus($status,$user_id)
    {
        $user = User::findOrFail($user_id);
        $user->status_position = $status;
        $user->save();
    }

    protected function updatePosition($position_id, $letterSubmission)
    {
        $lastPositon = $letterSubmission->user->last_position;
        if($lastPositon)
        {
            $lastPositon->end_date = Carbon::now();
            $lastPositon->save();
        }

        if($position_id)
        {
            $position_id = $this->findPosition($position_id);

            $userPosition = new UserPosition();
            $userPosition->user_id = $letterSubmission->user->id;
            $userPosition->position_id = $position_id;
            $userPosition->start_date = $letterSubmission->convert_field['start_date'] ?? Carbon::now();
            $userPosition->end_date = $letterSubmission->convert_field['end_date'] ?? null;
            $userPosition->save();
        }
    }

    public function sentInbox($to,$message,$directUrl)
    {
        $inboxHelper = new InboxHelper();
        $inboxHelper->sent(
            $to, 
            Auth::user()->id, 
            $message, 
            $directUrl
        );

        return;
    }
}
