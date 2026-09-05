<?php

namespace App\Http\Controllers;

use App\Models\Reference;
use Illuminate\Http\Request;

class ReferenceController extends Controller
{
    //
    public function index(Request $request)
    {

        return view('reference.index');
    }

    public function create()
    {
        return view('reference.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
        Reference::create($validated);
        flash()->success('Reference Has Created');

        return redirect()->route('references.index');
    }

    public function show(Reference $reference)
    {
        $reference->loadMissing('questions');

        return view('reference.show', compact('reference'));
    }

    public function edit(Reference $reference)
    {
        return view('reference.edit', compact('reference'));
    }

    public function update(Reference $reference, Request $request)
    {
        $data = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
            ]
        );
        $reference->update($data);
        flash()->info('Reference Has Updated Succefully');

        return redirect()->route('references.index');
    }

    public function destroy(Reference $reference)
    {
        $reference->delete();
        flash()->error('Reference Has Deleted');

        return redirect()->route('references.destroy');
    }
}
