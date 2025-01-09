<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AgreementLetterRequest;

use Carbon\Carbon;
use App\Helpers\Access;
use App\Models\AgreementLetter;
use App\Models\Quote;
use App\Models\SettingCompany;
use App\Models\TemplateAgreement;

// commnet
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
        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $agreementTemplate = $company['template_perjanjian'];
        $selectTemplate = TemplateAgreement::where('is_active',true)->where('template_name',$agreementTemplate)->get();
        $userCreate = Auth::user()->name;
        $nomorAgreementLetter = $this->agreementLetterNumber()['result'];
        return view('agreement_letter.createOrEdit'.$agreementTemplate,compact('userCreate','nomorAgreementLetter','selectTemplate'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AgreementLetterRequest $request)
    {
        $customFieldData = [
            'custom_' => $request->custom_br_bp,
            'custom_nik' => $request->custom_nik,
        ];

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

        $agreementLetter->rent_address = $request->input('rent_address');
        $agreementLetter->rent_start_duration = $request->input('rent_start_duration');
        $agreementLetter->rent_end_duration = $request->input('rent_end_duration');

        $agreementLetter->commission_name = $request->input('commission_name');
        $agreementLetter->commission_phone = $request->input('commission_phone');
        $agreementLetter->commission_address = $request->input('commission_address');

        $agreementLetter->user_created_id = Auth::user()->id;
        $agreementLetter->user_updated_id = Auth::user()->id;
        $agreementLetter->template_agreement_id = $request->input('template_agreement_id');
        
        $agreementLetter->custom_fields = $customFieldData;

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
        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $agreementTemplate = $company['template_perjanjian'];
        $selectTemplate = TemplateAgreement::where('is_active',true)->where('template_name',$agreementTemplate)->get();

        $agreementLetter = AgreementLetter::where('slug',$slug)->first();
        $userCreate = $agreementLetter->userCreate ? $agreementLetter->userCreate->name : '';
        $nomorAgreementLetter = $agreementLetter->number_result ?? '';

        return view('agreement_letter.createOrEdit'.$agreementTemplate,compact('userCreate','nomorAgreementLetter','agreementLetter','selectTemplate'));
    }


    /**
     * Find Id 
     * Dwonload PDF
     */
    function downloadPdf($slug)
    {
        $quote = Quote::byCompany(Auth::user()->company_id)->get();
        $company = SettingCompany::byCompany(Auth::user()->company_id)->get()->pluck('field_value','field_title');
        $agreementTemplate = $company['template_perjanjian'];

        $agreementLetter = AgreementLetter::where('slug',$slug)->first();
        
        $userCreate = $agreementLetter->userCreate ? $agreementLetter->userCreate->name : '';
        $nomorAgreementLetter = $agreementLetter->number_result ?? '';
        $bulan_indonesia = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];

        $now = Carbon::now()->locale('id');
        $now->settings(['formatFunction' => 'translatedFormat']);
        $dateNow = $now->format('l, j F Y');
        $dateNowWithoutDay = $now->format('j F Y');

        $monthNumber = Carbon::now()->format('m');
        $year = Carbon::now()->format('Y');
        $yearGenerate = Carbon::now()->format('y');
        $date = Carbon::now()->format('d');
        $month = $bulan_indonesia[$monthNumber];
        $yearToRomawi = $this->toRomawi($yearGenerate);
        
        if($agreementLetter->templateAgreement)
        {
            $agreementTemplate = $agreementLetter->templateAgreement->template_agreement;
        }else
        {
            $agreementTemplate = TemplateAgreement::where('is_default',true)->where('is_active',true)->where('template_name',$agreementTemplate)->first()->template_agreement;
        }
        
        return view('agreement_letter.pdf'.$agreementTemplate,compact('quote','userCreate','nomorAgreementLetter','agreementLetter', 'month', 'year', 'date' ,'company' ,'monthNumber','dateNow', 'yearToRomawi', 'dateNowWithoutDay'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AgreementLetter  $agreementLetter
     * @return \Illuminate\Http\Response
     */
    public function update(AgreementLetterRequest $request, $slug)
    {
        $customFieldData = [
            'custom_br_bp' => $request->custom_br_bp,
            'custom_nik' => $request->custom_nik,
        ];

        $agreementLetter = AgreementLetter::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $agreementLetter->date = $request->input('date');
        $agreementLetter->quote_id = $request->input('quote');
        $agreementLetter->payment_term = $request->input('payment_term');
        $agreementLetter->period_term = $request->input('period_term');
        $agreementLetter->other_term = $request->input('other_term');
        $agreementLetter->payment_term_english = $request->input('payment_term_english');
        $agreementLetter->period_term_english = $request->input('period_term_english');
        $agreementLetter->other_term_english = $request->input('other_term_english');

        $agreementLetter->rent_address = $request->input('rent_address');
        $agreementLetter->rent_start_duration = $request->input('rent_start_duration');
        $agreementLetter->rent_end_duration = $request->input('rent_end_duration');

        $agreementLetter->commission_name = $request->input('commission_name');
        $agreementLetter->commission_phone = $request->input('commission_phone');
        $agreementLetter->commission_address = $request->input('commission_address');
        $agreementLetter->template_agreement_id = $request->input('template_agreement_id');

        $agreementLetter->custom_fields = $customFieldData;
        
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
    public function destroy($slug)
    {
        $agreementLetter = AgreementLetter::byCompany(Auth::user()->company_id)->where('slug', $slug)->firstOrFail();
        $agreementLetter->delete();
        return redirect()->back()->with('delete',true);
    }
    
    private function agreementLetterNumber()
    {
        $date = Carbon::now()->format('m/Y');
        $nomor = AgreementLetter::byCompany(Auth::user()->company_id)->withTrashed()->max('agreement_letter_number') + 1;

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
        $query->byCompany(Auth::user()->company_id)->orderBy('agreement_letter_number', 'desc');;

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
            
            
            
        ];

        if(Access::can('downloadPdf','agreement_letters'))
        {
            $pdf = 
            [
                'name' => 'Pdf',
                'route' => 'agreement-letter.download.pdf',
                'id' => true,
            ];

            array_push($actionButtons,$pdf);
        }

        if(Access::can('edit','agreement_letters'))
        {
            $edit = 
            [
                'name' => 'Edit',
                'route' => 'agreement-letter.edit',
                'id' => true,
            ];

            array_push($actionButtons,$edit);
        }

        if(Access::can('destroy','agreement_letters'))
        {
            $destroy = 
            [
                'name' => 'Delete',
                'route' => 'agreement-letter.destroy',
                'id' => true,
            ];

            array_push($actionButtons,$destroy);
        }

        return datatablesFormater($query, $columnNames, $actionButtons, $searchable, $bootstrap);
    }

    private function toRomawi($number)
    {
        $map = [
            'M'  => 1000,
            'CM' => 900,
            'D'  => 500,
            'CD' => 400,
            'C'  => 100,
            'XC' => 90,
            'L'  => 50,
            'XL' => 40,
            'X'  => 10,
            'IX' => 9,
            'V'  => 5,
            'IV' => 4,
            'I'  => 1,
        ];

        $result = '';

        foreach ($map as $roman => $value) {
            $matches = intval($number / $value);
            $result .= str_repeat($roman, $matches);
            $number %= $value;
        }

        return $result;
    }
}
