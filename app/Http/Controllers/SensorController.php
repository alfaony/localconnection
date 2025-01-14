<?php

namespace App\Http\Controllers;

use App\Models\Sensor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SensorController extends Controller
{
    public function index()
    {
        $sensors = Sensor::byCompany(Auth::user()->company_id)->with('user')->paginate(10);
        $sensorType = config('custom.sensorType');

        return view('sensor.index', compact('sensors', 'sensorType'));
    }

    public function create()
    {
        return view('sensor.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255'
        ]);

        Sensor::create([
            'name' => $request->name,
            'type' => $request->type,
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('sensor.index')->with('store', true);
    }

    public function edit(Sensor $sensor)
    {
        $sensors = Sensor::byCompany(Auth::user()->company_id)->with('user')->paginate(10);
        $sensorType = config('custom.sensorType');

        return view('sensor.index', compact('sensor', 'sensors', 'sensorType'));
    }

    public function update(Request $request, Sensor $sensor)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
        ]);

        $sensor->update($request->all());

        return redirect()->route('sensor.index')->with('update', true);
    }

    public function destroy(Sensor $sensor)
    {
        $sensor->delete();
        return redirect()->route('sensor.index')->with('delete', true);
    }
}