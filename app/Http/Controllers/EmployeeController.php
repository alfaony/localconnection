<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Requests\EmployeeRequest;

use App\Models\Employee;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $employee = Employee::where('name','like', '%' . $request->get('employee') . '%')
        ->OrderBy('created_at','asc')->paginate(10);

        $totalEmployee = count(Employee::get());

        return view('employee.index',compact('employee','totalEmployee'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmployeeRequest $request)
    {
        $employee = new Employee();
        $employee->user_id = Auth::user()->id;
        $employee->name = $request->post('name');
        $employee->phone = $request->post('phone');
        $employee->salary_monthly = $request->post('salary_monthly');
        $employee->salary_daily = $request->post('salary_daily');
        $employee->save();

        return redirect()->back()->with('store',true);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit($slug)
    {
        $totalEmployee = count(Employee::get());
        $employeeEdit = Employee::where('slug', $slug)->firstOrFail();
        $employee = Employee::OrderBy('created_at','asc')->paginate(10);
    
        // Rest of your code for editing the project...

        return view('employee.index', compact('employeeEdit','employee','totalEmployee'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(EmployeeRequest $request, Employee $employee)
    {
        $employee->user_id = Auth::user()->id;
        $employee->name = $request->post('name');
        $employee->phone = $request->post('phone');
        $employee->salary_monthly = $request->post('salary_monthly');
        $employee->salary_daily = $request->post('salary_daily');
        $employee->save();

        return redirect()->to(route('employee.index'))->with('update',true);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->back()->with('delete',true);
    }
}
