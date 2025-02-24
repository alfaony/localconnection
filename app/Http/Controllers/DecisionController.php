<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Decision;    
use App\Models\User;    

class DecisionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->query('search');
        $users = User::byCompany(Auth::user()->company_id)->get();

        $decisions = Decision::byCompany(Auth::user()->company_id)
                            // ->where('question','LIKE',"%{$search}%")
                            // ->orWhere('answer','LIKE',"%{$search}%")
                            ->paginate(10);
        return view('decision.index', compact('decisions','users'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'responsible' => 'required|uuid|exists:users,id',
            'accountable' => 'required|uuid|exists:users,id',
            'consult' => 'nullable|uuid|exists:users,id',
            'question' => 'required|string',
            'analysisResult' => 'required|string',
            'trustScoreResult' => 'required|integer|between:0,100',
            'executionScoreResult' => 'nullable|integer|between:0,100',
        ], [
            'responsible.required' => 'User yang responsible harus diisi',
            'responsible.uuid' => 'User yang responsible harus berupa UUID',
            'responsible.exists' => 'User yang responsible tidak ditemukan',
            'accountable.required' => 'User yang accountable harus diisi',
            'accountable.uuid' => 'User yang accountable harus berupa UUID',
            'accountable.exists' => 'User yang accountable tidak ditemukan',
            'consult.uuid' => 'User yang consult harus berupa UUID',
            'consult.exists' => 'User yang consult tidak ditemukan',
            'question.required' => 'Pertanyaan harus diisi',
            'analysisResult.required' => 'Hasil analisa harus diisi',
            'trustScoreResult.required' => 'Nilai trust score harus diisi',
            'trustScoreResult.between' => 'Nilai trust score harus di antara 0-100',
            'executionScoreResult.between' => 'Nilai execution score harus di antara 0-100',
        ]);

        try {
            //code...
            $dicision = Decision::create([
                'user_responsible_id' => $request->responsible ?? null,
                'user_accountable_id' => $request->accountable ?? null,
                'user_consult_id' => $request->consult ?? null,
                'question' => $request->question,
                'answer' => $request->analysisResult,
                'trust_score' => $request->trustScoreResult ?? null,
                'execution_score' => $request->executionScoreResult ?? null,
                'user_create_id' => auth()->id()
            ]);

            return redirect()->route('decision.index')->with('store', true);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error($th->getMessage());
            return redirect()->route('decision.index')->with('store', false);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {   
        $decision = Decision::byCompany(Auth::user()->company_id)->find($id);
        if (!$decision) 
        {
            return redirect()->route('decision.index')->with('error', 'Decision not found.');
        }
        return view('decision.show', compact('decision'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $decision = Decision::byCompany(Auth::user()->company_id)->find($id);
        if (!$decision) 
        {
            return redirect()->route('decision.index')->with('error', 'Decision not found.');
        }
        $decision->user_sharing = json_encode($request->users);
        $decision->save();

        return redirect()->route('decision.index')->with('update', true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $decision = Decision::byCompany(Auth::user()->company_id)->find($id);
        if (!$decision) 
        {
            return redirect()->route('decision.index')->with('error', 'Decision not found.');
        }
        $decision->delete();
        return redirect()->route('decision.index')->with('delete', true);
    }

    /**
     * Approve the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function approvement($id)
    {
        $decision = Decision::findOrFail($id);
        $decision->is_approve = true;
        $decision->save();

        return redirect()->route('decision.show', $id )->with('approve', true);
    }
}
