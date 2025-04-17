<?php

namespace App\Http\Controllers;

use App\Models\WeeklyReport;
use Illuminate\Http\Request;

class WeeklyReportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('weekly_report.createOrEdit');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WeeklyReport  $weeklyReport
     * @return \Illuminate\Http\Response
     */
    public function show(WeeklyReport $weeklyReport)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WeeklyReport  $weeklyReport
     * @return \Illuminate\Http\Response
     */
    public function edit(WeeklyReport $weeklyReport)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WeeklyReport  $weeklyReport
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WeeklyReport $weeklyReport)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\WeeklyReport  $weeklyReport
     * @return \Illuminate\Http\Response
     */
    public function destroy(WeeklyReport $weeklyReport)
    {
        //
    }
}
