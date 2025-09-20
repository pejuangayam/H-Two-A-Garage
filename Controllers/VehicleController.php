<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $search = request('search');

        $query = Vehicle::with('service')->orderBy('created_at', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_id', 'like', '%' . $search . '%')
                    ->orWhere('model', 'like', '%' . $search . '%');
            });
        }
        //each 30 page
        $vehicles = $query->paginate(30);

        return view('vehicles.index', compact('vehicles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('/vehicles/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Session::flash('created_at', $request->created_at);
        Session::flash('vehicle_id', $request->vehicle_id);
        Session::flash('model', $request->model);
        Session::flash('name', $request->name);
        Session::flash('kilometer', $request->kilometer);
        Session::flash('noPhone', $request->noPhone);

        $data = $request->validate([
            'created_at' => ['required', 'date'],
            'vehicle_id' => ['required', 'string', 'unique:vehicles,vehicle_id'],
            'model'      => ['required', 'string'],
            'name'       => ['required', 'string'],
            'noPhone'       => ['required', 'string'],
            'kilometer'  => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'] // Fixed validation
        ]);

        Vehicle::create($data);

        return redirect()->route('vehicles.index')->with('success', 'Vehicle added successfully!');
    }
    /**
     * Display the specified resource.
     */
    public function show(Vehicle $vehicle)
    {
        $vehicle->load('service');
        return response()->json($vehicle);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $request->validate([
            'created_at' => ['required' , 'date'],
            'vehicle_id' => ['required', 'string', 'unique:vehicles,vehicle_id,' . $vehicle->id],
            'model'      => ['required', 'string'],
            'name'       => ['required', 'string'],
            'noPhone'       => ['required', 'string'],
            'kilometer'  => ['required', 'numeric', 'min:0', 'decimal:0,1'] // Consistent with store method
        ]);

        $vehicle->update($data);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehicle deleted successfully!');
    }
}
