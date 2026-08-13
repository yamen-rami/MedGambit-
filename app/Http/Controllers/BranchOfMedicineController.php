<?php

namespace App\Http\Controllers;

use App\Models\BranchOfMedicine;
use Illuminate\Http\Request;

class BranchOfMedicineController extends Controller
{
    //
    public function index(Request $request)
    {
        /*
            1- Search
            2- good filtering
            3- branch right answer
            4- eager loading relations
            ?5- put the important in the index then create it
        */
        $sort = $request->sort ?? 'desc';

        $query = BranchOfMedicine::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }
        $branches = $query->orderBy('id', $sort)->paginate(30);
        $branches->appends($request->all());
        $sort === 'desc' ? $sort = 'asc' : $sort = 'desc';

        return view('branch.index', compact('branches', 'sort'));
    }

    public function create()
    {
        return view('branch.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'min:3', 'max:255']]);
        BranchOfMedicine::create($data);
        flash()->success('Brach has Created');

        return redirect()->route('branch.index');
    }

    public function edit(BranchOfMedicine $branch)
    {
        return view('branch.edit', compact('branch'));
    }

    public function update(Request $request, int $id)
    {
        $branch = BranchOfMedicine::findOrFail($id);
        $data = $request->validate(['name' => ['required', 'string', 'min:3', 'max:255']]);
        $branch->update($data);
        flash()->info('Brach has Updated');

        return redirect()->route('branch.index');
    }

    public function destroy(int $id)
    {
        $branch = BranchOfMedicine::findOrFail($id);
        $branch->delete();
        flash()->error('branch Has Deleted Succesfully');

        return redirect()->route('branch.index');
    }
}
