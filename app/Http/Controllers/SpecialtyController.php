<?php

namespace App\Http\Controllers;

use App\Models\Specialty;
use Illuminate\Http\Request;

class SpecialtyController extends Controller
{
    //
    public function index(Request $request)
    {
        /*
            1- Search
            2- good filtering
            3- speciality right answer
            4- eager loading relations
            ?5- put the important in the index then create it
        */
        $sort = $request->sort ?? 'desc';

        $query = Specialty::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }
        $specialities = $query->orderBy('id', $sort)->paginate(30);
        $specialities->appends($request->all());
        $sort === 'desc' ? $sort = 'asc' : $sort = 'desc';

        return view('speciality.index', compact('specialities', 'sort'));
    }

    public function create()
    {
        return view('speciality.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'min:3', 'max:255']]);
        Specialty::create($data);
        flash()->success('Speciality has Created');

        return redirect()->route('speciality.index');
    }

    public function show(Specialty $speciality) {}

    public function edit(Specialty $speciality)
    {
        return view('speciality.edit', compact('speciality'));
    }

    public function update(Request $request, int $id)
    {
        $specialty = Specialty::findOrFail($id);
        $data = $request->validate(['name' => ['required', 'string', 'min:3', 'max:255']]);
        $specialty->update($data);
        flash()->info('Speciality has Updated');

        return redirect()->route('speciality.index');
    }

    public function destroy(int $id)
    {
        $specialty = Specialty::findOrFail($id);
        $specialty->delete();
        flash()->error('speciality Has Deleted Succesfully');

        return redirect()->route('speciality.index');
    }
}
