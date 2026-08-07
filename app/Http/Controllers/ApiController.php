<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\{BranchOfMedicine, Specialty};

class ApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function branches(string $search)
    {
        $branches = BranchOfMedicine::query()->when($search, function ($q) use ($search) {
            $q->where("name", "LIKE", "%$search%");
        })->limit(20)->get();
        return response()->json($branches) ;
        //
    }
    public function s(string $search)
    {
        $branches = Specialty::query()->when($search, function ($q) use ($search) {
            $q->where("name", "LIKE", "%$search%");
        })->limit(20)->get();
        return response()->json($branches) ;
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
