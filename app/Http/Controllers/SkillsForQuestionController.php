<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SkillsForQuestion;

class SkillsForQuestionController extends Controller
{
    public function index(Request $request)
    {
        /* 
            1- Search 
            2- good filtering 
            3- skills right answer
            4- eager loading relations 
            ?5- put the important in the index then create it 
        */
        $sort = $request->sort ?? "desc";

        $query = SkillsForQuestion::query();

        if ($request->filled("search")) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }
        $skills = $query->orderBy("id", $sort)->paginate(30);
        $skills->appends($request->all());
        $sort === "desc" ? $sort = "asc" : $sort = "desc";
        return view("skills.index", compact("skills", "sort"));
    }
    public function create()
    {
        return view("skills.create");
    }
    public function store(Request $request)
    {
        $data = $request->validate(['name' => ['required', "string", "min:3", "max:255"]]);
        SkillsForQuestion::create($data);
        flash()->success("Brach has Created");
        return redirect()->route("skills.index");
    }

    public function edit(SkillsForQuestion $skill) {
        return view("skills.edit" , compact("skill"));
    }
    public function update(Request $request, int $id)
    {
        $skills = SkillsForQuestion::findOrFail($id);
        $data = $request->validate(['name' => ['required', "string", "min:3", "max:255"]]);
        $skills->update($data);
        flash()->info("Brach has Updated");
        return redirect()->route("skills.index");
    }
    public function destroy(int $id) {
        $skills = SkillsForQuestion::findOrFail($id);
        $skills->delete();
        flash()->error("skills Has Deleted Succesfully");
        return redirect()->route('skills.index');
    }
    //
}
