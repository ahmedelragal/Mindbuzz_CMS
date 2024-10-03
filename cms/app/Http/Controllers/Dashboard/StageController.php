<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use Illuminate\Http\Request;

class StageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stages = Stage::simplePaginate(10);

        return view('dashboard.stage.index', compact('stages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.stage.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:stages|max:255',
        ]);
        $stage = Stage::create([
            'name' => $request->name,
        ]);
        return redirect()->route('stages.create')->with('success', 'Stage created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $stages = Stage::findOrFail($id);
        return view("dashboard.stage.edit", compact("stages"));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|unique:stages|max:255',
        ]);

        $stage = Stage::findOrFail($id);
        $stage->update([
            'name' => $request->name,
        ]);

        return redirect()->route('stages.edit', $id)->with('success', 'Stage updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $stage = Stage::findOrFail($id);
        $stage->delete();
        return redirect()->route('stages.index')->with('success', 'Stage deleted successfully!');
    }
}
