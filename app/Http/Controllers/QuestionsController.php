<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\{Rule, ValidationException};

use App\Models\{BranchOfMedicine, Option, Questions, SkillsForQuestion, Specialty};

class QuestionsController extends Controller
{
    //
    public function index(Request $request)
    {
        /* 
            1- Search 
            2- good filtering 
            3- questions right answer
            4- eager loading relations 
            ?5- put the important in the index then create it 
        */
        $sort = $request->sort ?? "desc";

        $query = Questions::query();

        if ($request->filled("search")) {
            $query->where(function ($q) use ($request) {
                $q->where('content', 'like', "%{$request->search}%")->orWhere("topic", "like", "%{$request->search}%");
            });
        }
        if ($request->has("length")) {
            if ($request->length === "short" || $request->length === "meduim" || $request->length === "long") {
                $query->where("length", $request->length);
            }
        }
        if ($request->has("difficulty")) {
            if ($request->difficulty === "easy" || $request->difficulty === "meduim" || $request->difficulty === "hard" || $request->difficulty === "nerd") {
                $query->where("difficulty", $request->difficulty);
            }
        }
        $questions = $query->orderBy("id", $sort)->paginate(30)->withQueryString();

        $questions->appends($request->all());


        $sort === "desc" ? $sort = "asc" : $sort = "desc";
        return view("questions.index", compact("questions", "sort"));
    }
    public function create()
    {
        // todo Get All elements will heart performance so i should create like a search using fetch() ajax 
        $spicality = Specialty::all();
        $branches = BranchOfMedicine::all();
        $skills = SkillsForQuestion::all();
        return view("questions.create", compact('spicality', "branches", "skills"));
    }
    public function edit(int $id)
    {
        $branches = BranchOfMedicine::all();
        $skills = SkillsForQuestion::all();
        $spicality = Specialty::all();
        $question = Questions::findOrFail($id);
        return view("questions.edit", compact("question", "branches", "skills", "spicality"));
    }
    public function store(Request $request)
    {
        $rules  = [
            "options_number" => ["required", "integer"],
            "content" => ["required", "string"],
            'speciality' => ['required', 'array'],
            'speciality.*' => ['required', 'exists:specialties,id'],
            'branches' => ['required', 'array'],
            'branches.*' => ['required', 'exists:branch_of_medicines,id'],
            'skills' => ['required', 'array'],
            'skills.*' => ['required', "exists:skills_for_questions,id"],
            "topic" => ["required", "string"],
            "main_explanation" => ["required", "string"],
            "reference" => ["required", Rule::in(["MCC Qe", "MRCP", "UW"])],
            "high_yield" => ["required", "string"],
            "difficulty" => ["required", Rule::in(["easy", "medium", "hard", "nerd"])],
            "length" => ["required", Rule::in(["short", "medium", "long"])],
            "elo_correct" => ["required"],
            "elo_incorrect" => ["required"],
            "image" => ["required"],
            "options" => ["required", "array"],
            "options.*.name" => ["required", "string"],
            "options.*.correct_answer" => ["nullable"],
            "options.*.explanation" => ["required", "string"],
            "options.*.image" => ["nullable"],
            "options.*.content" => ["required", "string"],
            "options.*.topic" => ["required", "string"],
        ];
        $questionData = $request->validate($rules);
        $countOfCorrectAnswers = 0;

        foreach ($questionData["options"] as $option) {
            if ($option["correct_answer"] == 1) {
                $countOfCorrectAnswers++;
            }
        }

        if ($countOfCorrectAnswers > 1) {
            throw ValidationException::withMessages([
                "correct_answer" => "Only 1 option is allowed to be the correct answer"
            ]);
        }

        if ($countOfCorrectAnswers < 1) {
            throw ValidationException::withMessages([
                "correct_answer" => "Exactly 1 option must be the correct answer"
            ]);
        }
        DB::transaction(function () use ($request, $questionData) {
            if ($questionData["image"]) {
                $questionData["image"] = $questionData["image"]->store("questions", "public");
            }
            $question = Questions::create([
                "content" => $questionData['content'],
                "topic" => $questionData['topic'],
                "main_explanation" => $questionData['main_explanation'],
                "reference" => $questionData['reference'],
                "difficulty" => $questionData['difficulty'],
                "high_yield" => $questionData['high_yield'],
                "length" => $questionData['length'],
                "elo_correct" => $questionData['elo_correct'],
                "elo_incorrect" => $questionData['elo_incorrect'],
                "image" => $questionData['image'],
            ]);
            foreach ($questionData["speciality"] as $speciality) {
                $question->specialties()->attach($speciality);
            }
            foreach ($questionData["skills"] as $skill) {
                $question->skills()->attach($skill);
            }
            foreach ($questionData["branches"] as $branch) {
                $question->branches()->attach($branch);
            }
            foreach ($questionData["options"] as $option) {
                if (in_array("image", $option, true)) {
                    $option["image"] = $option["image"]->store("questions", "public");
                }
                $question->options()->create($option);
            }
        });
        flash()->success("Question Has Created Succefully");
        return redirect()->route("questions.index");
    }
    public function show(int $id)
    {
        $question = Questions::with(['options', "branches", "skills", "specialties"])->findOrFail($id);
        $branches = BranchOfMedicine::all();
        $skills = SkillsForQuestion::all();
        $speciality = Specialty::all();
        $correctAnswer = $question->options->where("correct_answer", true)->first();
        return view("questions.show", compact("question", "correctAnswer"));
    }
    // Update Method 
    public function update(Request $request, int $id)
    {
        $questionData = $request->validate(
            [
                "content" => ["nullable", "string"],
                "main_explanation" => ["nullable", "string"],
                "high_yield" => ["nullable", "string"],
                "topic" => ["nullable", "string"],
                "difficulty" => ["nullable", Rule::in(['easy', 'medium', "hard", "nerd"])],
                "length" => ["nullable", Rule::in(['short', 'medium', "long"])],
                "reference" => ["nullable", Rule::in(['MRCP', 'UW', "MCC Qe"])],
                "elo_correct" => ["nullable", Rule::in(['4', '8', "12"])],
                "elo_incorrect" => ["nullable", Rule::in(['5', '10', "15"])],
                "image" => ["nullable"],
                'speciality' => ['nullable', 'array'],
                'speciality.*' => ['nullable', 'exists:specialties,id'],
                'branches' => ['nullable', 'array'],
                'branches.*' => ['nullable', 'exists:BranchOfMedicines,id'],
                'skills' => ['nullable', 'array'],
                'skills.*' => ['nullable', "exists:skills_for_questions,id"],
            ]
        );

        DB::transaction(function () use ($request, $questionData, $id) {
            $question = Questions::findOrFail($id);
            $oldImage = $question->image;
            if ($request->has("image")) {
                $questionData["image"] = $questionData["image"]->store("questions", "public");
            }
            $question->update(Arr::except($questionData, ["speciality", "branches", "skills"]));
            $question->skills()->syncOrFail($questionData["skills"] ?? []);
            $question->branches()->syncOrFail($questionData["branches"] ?? []);
            $question->specialties()->syncOrFail($questionData["speciality"] ?? []);

            if ($request->has("image") && $oldImage) {
                Storage::disk("public")->delete($oldImage);
            }
            /* 
                delete here
            */
        });
        flash()->success("Question Updated Succefully");
        return redirect()->route("questions.index");
    }

    public function destroy(int $id)
    {
        if (!$id) {
            return redirect("questions.index");
        }
        $question = Questions::findOrFail($id)->delete();
        flash()->error("You Have Deleted Questions Succefully");
        return redirect()->route("questions.index");
    }
}
