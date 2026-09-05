<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\{BranchOfMedicine, Reference, SkillsForQuestion, Specialty};

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function branches(Request $request)
    {
        $search = $request->input('search');
        $branches = BranchOfMedicine::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            })
            ->limit(20)
            ->get();

        return response()->json($branches);
        //
    }

    public function s(Request $request)
    {
        $s = Specialty::query()->when($request->search, function ($q) use ($request) {
            $q->where('name', 'LIKE', "%$request->search%");
        })->limit(20)->get();

        return response()->json($s);
    }

    public function skills(Request $request)
    {
        $search = $request->input('search');
        $skills = SkillsForQuestion::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            })
            ->limit(20)
            ->get();

        return response()->json($skills);
        //
    }
     public function references(Request $request)
    {
        $search = $request->input('search');
        $references = Reference::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            })
            ->limit(20)
            ->get();

        return response()->json($references);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
