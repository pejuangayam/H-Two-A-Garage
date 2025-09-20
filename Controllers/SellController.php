<?php

namespace App\Http\Controllers;

use App\Models\Sell;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SellController extends Controller
{
    public function index()
    {
        $search = request('search');
        $query = Sell::orderBy('updated_at', 'asc');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('items', 'like', '%' . $search . '%')
                    ->orWhereRaw('DATE(updated_at) = ?', [$search]); // safer date search
            });
        }

        $data = $query->paginate(30);
        return view('sell.index', compact('data'));
    }

    public function create()
    {
        return view('sell.create');
    }

    public function store(Request $request)
    {
        // Keep form values in session (in case of validation fail)
        Session::flash('created_at', $request->created_at);
        Session::flash('items', $request->items);
        Session::flash('quantity', $request->quantity);
        Session::flash('real_price', $request->real_price);
        Session::flash('sales_price', $request->sales_price);

        // Validate input
        $validated = $request->validate([
            'created_at'  => ['required', 'date'],
            'items'       => ['required', 'string', 'max:255'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:100000'],
            'real_price'  => ['required', 'numeric', 'min:0', 'max:1000000'],
            'sales_price' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ]);

        // Calculate values
        $validated['revenue'] = ($validated['sales_price'] - $validated['real_price']) * $validated['quantity'];
        $validated['total']   = $validated['sales_price'] * $validated['quantity'];

        // Ensure updated_at = created_at (your business rule)
        $validated['updated_at'] = $validated['created_at'];

        Sell::create($validated);

        return redirect('sell')->with('success', 'Data Saved');
    }

    public function show(string $id)
    {
        $sell = Sell::findOrFail($id);
        return view('sell.show', compact('sell')); // fixed from index to show
    }

    public function edit(string $id)
    {
        $sell = Sell::findOrFail($id);
        return view('sell.edit', compact('sell'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'updated_at'  => ['required', 'date'],
            'items'       => ['required', 'string', 'max:255'],
            'quantity'    => ['required', 'integer', 'min:1', 'max:100000'],
            'real_price'  => ['required', 'numeric', 'min:0', 'max:1000000'],
            'sales_price' => ['required', 'numeric', 'min:0', 'max:1000000'],
        ]);

        $sell = Sell::findOrFail($id);

        // Manual calculations
        $validated['revenue'] = ($validated['sales_price'] - $validated['real_price']) * $validated['quantity'];
        $validated['total']   = $validated['sales_price'] * $validated['quantity'];

        // Only update what you want (business logic preserved)
        $sell->update($validated);

        return redirect()->route('sell.index')->with('success', 'Item updated successfully');
    }

    public function destroy(string $id)
    {
        Sell::where('id', $id)->delete();
        return redirect('/sell')->with('success', 'Delete Successfully');
    }
}
