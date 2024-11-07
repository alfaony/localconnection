<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NationalHoliday;

class NationalHolidayController extends Controller
{
    // Display a listing of the national holidays
    public function index()
    {
        $holidays = NationalHoliday::orderBy('date')->paginate(10);
        return view('national_holiday.index', compact('holidays'));
    }

    // Store a newly created national holiday in storage
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:national_holidays,date'
        ]);

        NationalHoliday::create($request->only('name', 'date'));

        return redirect()->route('national-holiday.index')->with('success', 'National holiday created successfully.');
    }

    // Update the specified national holiday in storage
    public function update(Request $request, NationalHoliday $nationalHoliday)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'date' => 'required|date|unique:national_holidays,date,' . $nationalHoliday->id
        ]);

        $nationalHoliday->update($request->only('name', 'date'));

        return redirect()->route('national-holiday.index')->with('success', 'National holiday updated successfully.');
    }

    // Remove the specified national holiday from storage
    public function destroy(NationalHoliday $nationalHoliday)
    {
        $nationalHoliday->delete();
        return redirect()->route('national-holiday.index')->with('success', 'National holiday deleted successfully.');
    }
}
