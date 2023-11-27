<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AgreementLetterRequest;

use Carbon\Carbon;

use App\Models\AgreementLetter;
use App\Models\Quote;
use App\Models\SettingCompany;

class AgreementLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('agreement_letter.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // $quote = Quote::orderBy('created_at','desc')->get();
        $userCreate = Auth::user()->name;
        $nomorAgreementLetter = $this->agreementLetterNumber()['result'];
        return view('agreement_letter.createOrEdit',compact('userCreate','nomorAgreementLetter'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AgreementLetterRequest $request)
    {
        $agreementLetter = new AgreementLetter();
        $nomorAgreementLetter = $this->agreementLetterNumber();

        $agreementLetter->date = $request->input('date');
        $agreementLetter->agreement_letter_number = $nomorAgreementLetter['number'];
        $agreementLetter->number_result = $nomorAgreementLetter['result'];
        $agreementLetter->quote_id = $request->input('quote');
        $agreementLetter->payment_term = $request->input('payment_term');
        $agreementLetter->period_term = $request->input('period_term');
        $agreementLetter->other_term = $request->input('other_term');
        
        $agreementLetter->payment_term_english = $request->input('payment_term_english');
        $agreementLetter->period_term_english = $request->input('period_term_english');
        $agreementLetter->other_term_english = $request->input('other_term_english');

        $agreementLetter->user_created_id = Auth::user()->id;
        $agreementLetter->user_updated_id = Auth::user()->id;

        $agreementLetter->save();

        return redirect()->to(route('agreement-letter.download.pdf',$agreementLetter->slug))->with('store', true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AgreementLetter  $agreementLetter
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        // $quote = Quote::orderBy('created_at','desc')->get();

        $agreementLetter = AgreementLetter::where('slug',$slug)->first();
        $userCreate = $agreementLetter->userCreate ? $agreementLetter->userCreate->name : '';
        $nomorAgreementLetter = $agreementLetter->number_result ?? '';

        return view('agreement_letter.createOrEdit',compact('userCreate','nomorAgreementLetter','agreementLetter'));
    }


    /**
     * Find Id 
     * Dwonload PDF
     */
    function downloadPdf($slug)
    {
        $quote = Quote::all();
        $company = SettingCompany::get()->pluck('field_value','field_title');

        $agreementLetter = AgreementLetter::where('slug',$slug)->first();
        
        $userCreate = $agreementLetter->userCreate ? $agreementLetter->userCreate->name : '';
        $nomorAgreementLetter = $agreementLetter->number_result ?? '';
        $bulan_indonesia = [
            '1' => 'Januari',
            '2' => 'Februari',
            '3' => 'Maret',
            '4' => 'April',
            '5' => 'Mei',
            '6' => 'Juni',
            '7' => 'Juli',
            '8' => 'Agustus',
            '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('Y');
        $date = Carbon::now()->format('d');
        $month = $bulan_indonesia[$month];


        return view('agreement_letter.pdf',compact('quote','userCreate','nomorAgreementLetter','agreementLetter', 'month', 'year', 'date' ,'company'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AgreementLetter  $agreementLetter
     * @return \Illuminate\Http\Response
     */
    public function update(AgreementLetterRequest $request, AgreementLetter $agreementLetter)
    {
        $agreementLetter->date = $request->input('date');
        $agreementLetter->quote_id = $request->input('quote');
        $agreementLetter->payment_term = $request->input('payment_term');
        $agreementLetter->period_term = $request->input('period_term');
        $agreementLetter->other_term = $request->input('other_term');
        $agreementLetter->payment_term_english = $request->input('payment_term_english');
        $agreementLetter->period_term_english = $request->input('period_term_english');
        $agreementLetter->other_term_english = $request->input('other_term_english');

        $agreementLetter->user_updated_id = Auth::user()->id;

        $agreementLetter->save();

        return redirect()->to(route('agreement-letter.download.pdf',$agreementLetter->slug))->with('store', true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AgreementLetter  $agreementLetter
     * @return \Illuminate\Http\Response
     */
    public function destroy(AgreementLetter $agreementLetter)
    {
        $agreementLetter->delete();
        return redirect()->back()->with('delete',true);
    }
    
    private function agreementLetterNumber()
    {
        $date = Carbon::now()->format('m/Y');
        $nomor = AgreementLetter::withTrashed()->max('agreement_letter_number') + 1;

        return 
        [
            'number' => $nomor ?? 0,
            'result' => $nomor.'/'.$date ?? '' 
        ];
    }

    /**
     * Data table for load AgreementLetter
     */
    public function dataTableJson()
    {
        // Fetch data for the DataTable
        $query = AgreementLetter::query();

        // Map column indexes to column names (this may vary based on your table structure)
        $columnNames = ['number_result','date', 'slug'];

        // Define searchable columns
        $searchable = [
            0 => 'number_result',
            1 => 'date',
        ];

        // define your bootstrap version (4 or 5)
        $bootstrap = 4;

        // Add action buttons to each row
        $actionButtons = [
            [
                'name' => 'Pdf',
                'route' => 'agreement-letter.download.pdf',
                'id' => true,
            ],
            [
                'name' => 'Edit',
                'route' => 'agreement-letter.edit',
                'id' => true,
            ],
            [
                'name' => 'Delete',
                'route' => 'agreement-letter.destroy',
                'id' => true,
            ],
        ];

        return datatablesFormater($query, $columnNames, $actionButtons, $searchable, $bootstrap);
    }
}
