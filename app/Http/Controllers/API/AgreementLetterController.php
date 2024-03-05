<?php

namespace App\Http\Controllers\API;

use Validator;
use Illuminate\Http\Request;
use App\Http\Requests\AgreementLetterRequest;
use App\Http\Controllers\API\BaseController as BaseController;
use Carbon\Carbon;
use App\Helpers\Access;
use App\Models\AgreementLetter;
use App\Models\Quote;
use App\Models\SettingCompany;

class AgreementLetterController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $paginate = 10;
        if ($request->per_page) {
            $paginate = $request->per_page;
        }
        $order = 'asc';
        if ($request->order == 'desc') 
        {
            $order = 'desc';
        }
        $agreementLetter = AgreementLetter::byCompany(auth()->user()->company_id)
        ->where('number_result','like', '%' . $request->get('agreement_letter') . '%')
        ->orderBy('agreement_letter_number',$order)->paginate($paginate)->toArray();

        return $this->sendResponse($agreementLetter,'success');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $company = SettingCompany::byCompany(auth()->user()->company_id)->get()->pluck('field_value','field_title');
        $agreementTemplate = $company['template_perjanjian'];

        $data['userCreate'] = auth()->user()->name;
        $data['nomorAgreementLetter'] = $this->agreementLetterNumber()['result'];

        return $this->sendResponse($data,"Success");
        // return view('agreement_letter.createOrEdit'.$agreementTemplate,compact('userCreate','nomorAgreementLetter'));
    }

    /**
     * Show
     */
    public function show($id)
    {
        $agreementLetter = AgreementLetter::where('id',$id)->first();
        if(empty($agreementLetter))
        {
            return $this->sendError("Agreement Letter Not Found");
        }
        $data['agreement'] = $agreementLetter;
        $data['link'] = url("/api/agreement-letter/downloadPdf/pdf/".$agreementLetter->slug);

        return $this->sendResponse('Success',$data);

    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   
        $validator = Validator::make($request->all(),
            [
                'date' => 'required|date',
                'quote' => 'required|uuid|exists:quotes,id', // Asumsi bahwa Anda memiliki tabel quotes dengan kolom uuid
                'payment_term' => 'nullable|string',
                'period_term' => 'nullable|string',
                'other_term' => 'nullable|string',
                'rent_address'       => 'nullable|string',
                'rent_start_duration' => 'nullable|date',
                'rent_end_duration'   => 'nullable|date|after_or_equal:rent_start_duration',
                'rent_price'         => 'nullable|numeric',
                'commission_name'    => 'nullable|string',
                'commission_phone'   => 'nullable|string',
                'commission_address' => 'nullable|string',
            ],
            [
                'date.required' => 'Tanggal diperlukan.',
                'quote.required' => 'Kutipan diperlukan.',
                'quote.exists' => 'Kutipan tidak valid.',
                'rent_start_duration.date'     => 'Tanggal awal harus berupa tanggal yang valid.',
                'rent_end_duration.date'       => 'Tanggal akhir harus berupa tanggal yang valid.',
                'rent_end_duration.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
                'rent_price.numeric'           => 'Harga sewa harus berupa angka.',
    
            ]
        );

        if($validator->fails())
        {
            return $this->sendError('Validation Error.', $validator->errors());       
        }

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

        $agreementLetter->user_created_id = auth()->user()->id;
        $agreementLetter->user_updated_id = auth()->user()->id;

        $agreementLetter->save();

        $data = $agreementLetter;
        $data['link'] = url("/api/agreement-letter/downloadPdf/pdf/".$agreementLetter->slug);

        return $this->sendResponse($data, 'Success');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\AgreementLetter  $agreementLetter
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $agreementLetter = AgreementLetter::where('id',$id)->first();
        if(empty($agreementLetter))
        {
            return $this->sendError("Agreement Letter Not Found");
        }

        $company = SettingCompany::byCompany(auth()->user()->company_id)->get()->pluck('field_value','field_title');
        $agreementTemplate = $company['template_perjanjian'];

        $userCreate = $agreementLetter->userCreate ? $agreementLetter->userCreate->name : '';
        $nomorAgreementLetter = $agreementLetter->number_result ?? '';

        // return view('agreement_letter.createOrEdit'.$agreementTemplate,compact('userCreate','nomorAgreementLetter','agreementLetter'));
        $data['agreementLetter'] = $agreementLetter;
        $data['nomorAgreementLetter'] = $nomorAgreementLetter;

        return $this->sendResponse('Success',$data);
    }


    /**
     * Find Id 
     * Dwonload PDF
     */
    function downloadPdf($slug)
    {
        $agreementLetter = AgreementLetter::where('slug',$slug)->first();

        if(empty($agreementLetter))
        {
            return $this->sendError("Agreement Letter Not Found");
        }

        $quote = Quote::byCompany(auth()->user()->company_id)->get();
        $company = SettingCompany::byCompany(auth()->user()->company_id)->get()->pluck('field_value','field_title');
        $agreementTemplate = $company['template_perjanjian'];

        
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
        

        return view('agreement_letter.pdf'.$agreementTemplate.'api',compact('quote','userCreate','nomorAgreementLetter','agreementLetter', 'month', 'year', 'date' ,'company' ,'monthNumber','dateNow', 'yearToRomawi', 'dateNowWithoutDay'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\AgreementLetter  $agreementLetter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $agreementLetter = AgreementLetter::byCompany(auth()->user()->company_id)->where('id', $id)->first();
        if(empty($agreementLetter))
        {
            return $this->sendError("Agreement Not Found");
        }

        $validator = Validator::make($request->all(),
            [
                'date' => 'required|date',
                'quote' => 'required|uuid|exists:quotes,id', // Asumsi bahwa Anda memiliki tabel quotes dengan kolom uuid
                'payment_term' => 'nullable|string',
                'period_term' => 'nullable|string',
                'other_term' => 'nullable|string',
                'rent_address'       => 'nullable|string',
                'rent_start_duration' => 'nullable|date',
                'rent_end_duration'   => 'nullable|date|after_or_equal:rent_start_duration',
                'rent_price'         => 'nullable|numeric',
                'commission_name'    => 'nullable|string',
                'commission_phone'   => 'nullable|string',
                'commission_address' => 'nullable|string',
            ],
            [
                'date.required' => 'Tanggal diperlukan.',
                'quote.required' => 'Kutipan diperlukan.',
                'quote.exists' => 'Kutipan tidak valid.',
                'rent_start_duration.date'     => 'Tanggal awal harus berupa tanggal yang valid.',
                'rent_end_duration.date'       => 'Tanggal akhir harus berupa tanggal yang valid.',
                'rent_end_duration.after_or_equal' => 'Tanggal akhir harus sama atau setelah tanggal awal.',
                'rent_price.numeric'           => 'Harga sewa harus berupa angka.',
    
            ]
        );

        if($validator->fails())
        {
            return $this->sendError('Validation Error.', $validator->errors());       
        }
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
        
        $agreementLetter->user_updated_id = auth()->user()->id;

        $agreementLetter->save(); 

        $data['agreement'] = $agreementLetter;
        $data['link'] = url("/api/agreement-letter/downloadPdf/pdf/".$agreementLetter->slug);

        return $this->sendResponse('Success',$data);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\AgreementLetter  $agreementLetter
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $agreementLetter = AgreementLetter::byCompany(auth()->user()->company_id)->where('id', $id)->first();
        if(empty($agreementLetter))
        {
            return $this->sendError("Agreement Not Found");
        }
        $agreementLetter->delete();
        
        return $this->sendMessage('success');
    }
    
    private function agreementLetterNumber()
    {
        $date = Carbon::now()->format('m/Y');
        $nomor = AgreementLetter::byCompany(auth()->user()->company_id)->withTrashed()->max('agreement_letter_number') + 1;

        return 
        [
            'number' => $nomor ?? 0,
            'result' => $nomor.'/'.$date ?? '' 
        ];
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
