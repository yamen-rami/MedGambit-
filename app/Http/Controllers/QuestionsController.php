<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\{Rule, ValidationException};

use App\Models\{BranchOfMedicine, Questions, Reference, SkillsForQuestion, Specialty};

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
        $sort = $request->sort ?? 'desc';

        $query = Questions::query()->with("reference");

        if ($request->filled('search')) {
            $query->whereFullText(['content', 'topic'], $request->search);
        }
        if ($request->has('length')) {
            if ($request->length === 'short' || $request->length === 'meduim' || $request->length === 'long') {
                $query->where('length', $request->length);
            }
        }
        if ($request->has('difficulty')) {
            if ($request->difficulty === 'easy' || $request->difficulty === 'meduim' || $request->difficulty === 'hard' || $request->difficulty === 'nerd') {
                $query->where('difficulty', $request->difficulty);
            }
        }
        $questions = $query->orderBy('id', $sort)->paginate(30)->withQueryString();

        $questions->appends($request->all());

        $sort === 'desc' ? $sort = 'asc' : $sort = 'desc';

        return view('questions.index', compact('questions', 'sort'));
    }

    // QuestionController@create
    public function create()
    {
        $oldSpecialities = Specialty::whereIn('id', old('speciality', []))->get();
        $oldBranches = BranchOfMedicine::whereIn('id', old('branches', []))->get();
        $oldSkills = SkillsForQuestion::whereIn('id', old('skills', []))->get();
        // dd(old("skills"));
        $oldReferenece = Reference::whereIn('id', old('references', []))->get();
        // Getting value s
        return view('questions.create', compact('oldSpecialities', 'oldBranches', 'oldSkills' , "oldReferenece"));
    }

    public function edit(int $id)
    {
        $question = Questions::with(['branches', 'skills', 'specialties'])->findOrFail($id);
        $oldSpecialityIds = old('speciality', $question->specialties->pluck('id')->toArray());
        $oldBranchesIds = old('branches', $question->branches->pluck('id')->toArray());
        $oldSkillsIds = old('skills', $question->skills->pluck('id')->toArray());

        $oldReferenceId = old("reference", $question->reference->id);        
        $oldSpecialities = Specialty::whereIn('id', $oldSpecialityIds)->get();
        $oldBranches = BranchOfMedicine::whereIn('id', $oldBranchesIds)->get();
        $oldSkills = SkillsForQuestion::whereIn('id', $oldSkillsIds)->get();
        $oldReference = Reference::findOrFail($oldReferenceId);

        return view('questions.edit', compact('question', 'oldSpecialities', 'oldBranches', 'oldSkills', 'oldReference'));
    }

    public function store(Request $request)
    {
        $rules = [
            'options_number' => ['required', 'integer'],
            'content' => ['required', 'string'],
            'speciality' => ['required', 'array'],
            'speciality.*' => ['required', 'exists:specialties,id'],
            'branches' => ['required', 'array'],
            'branches.*' => ['required', 'exists:branch_of_medicines,id'],
            'skills' => ['required', 'array'],
            'skills.*' => ['required', 'exists:skills_for_questions,id'],
            'topic' => ['required', 'string'],
            'main_explanation' => ['required', 'string'],
            'reference' => ['required', 'exists:references,id'],

            'high_yield' => ['required', 'string'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard', 'nerd'])],
            'length' => ['required', Rule::in(['short', 'medium', 'long'])],
            'elo_correct' => ['required'],
            'elo_incorrect' => ['required'],
            'image' => ['nullable', 'image'],
            'options' => ['required', 'array'],
            'options.*.name' => ['required', 'string'],
            'options.*.correct_answer' => ['nullable'],
            'options.*.explanation' => ['required', 'string'],
            'options.*.image' => ['nullable', 'image'],
            'options.*.content' => ['required', 'string'],
            'options.*.topic' => ['required', 'string'],
        ];
        $questionData = $request->validate($rules);
        $countOfCorrectAnswers = 0;
        foreach ($questionData['options'] as $option) {
            if ($option['correct_answer'] == 1) {
                $countOfCorrectAnswers++;
            }
        }
        if ($countOfCorrectAnswers > 1) {
            throw ValidationException::withMessages([
                'correct_answer' => 'Only 1 option is allowed to be the correct answer',
            ]);
        }

        if ($countOfCorrectAnswers < 1) {
            throw ValidationException::withMessages([
                'correct_answer' => 'Exactly 1 option must be the correct answer',
            ]);
        }
        DB::transaction(function () use ($questionData, $request) {
            $path = '';
            if ($request->file("image")) {
                $path  = $request->file("image")->store('questions', 'public');
            }
            $question = Questions::create([
                'content' => $questionData['content'],
                'topic' => $questionData['topic'],
                'main_explanation' => $questionData['main_explanation'],
                'difficulty' => $questionData['difficulty'],
                'high_yield' => $questionData['high_yield'],
                'length' => $questionData['length'],
                'elo_correct' => $questionData['elo_correct'],
                'elo_incorrect' => $questionData['elo_incorrect'],
                'image' => $path,
                "reference_id" => $questionData["reference"],
            ]);
            $question->specialties()->attach($questionData['speciality']);
            $question->skills()->attach($questionData['skills']);
            $question->branches()->attach($questionData['branches']);
            foreach ($questionData['options'] as $option) {
                if (in_array('image', $option, true)) {
                    $option['image'] = $option['image']->store('questions', 'public');
                }
                $question->options()->create($option);
            }
        });
        flash()->success('Question Has Created Succefully');

        return redirect()->route('questions.index');
    }

    public function show(int $id)
    {
        $question = Questions::with(['options', 'branches', 'skills', 'specialties'])->findOrFail($id);
        $correctAnswer = $question->options->where('correct_answer', true)->first();

        return view('questions.show', compact('question', 'correctAnswer'));
    }

    // Update Method
    public function update(Request $request, int $id)
    {
        $questionData = $request->validate(
            [
                'content' => ['nullable', 'string'],
                'main_explanation' => ['nullable', 'string'],
                'high_yield' => ['nullable', 'string'],
                'topic' => ['nullable', 'string'],
                'difficulty' => ['nullable', Rule::in(['easy', 'medium', 'hard', 'nerd'])],
                'length' => ['nullable', Rule::in(['short', 'medium', 'long'])],
                'reference' => ['nullable', 'exists:references,id'],
                'elo_correct' => ['nullable', Rule::in(['4', '8', '12'])],
                'elo_incorrect' => ['nullable', Rule::in(['5', '10', '15'])],
                'image' => ['nullable'],
                'speciality' => ['nullable', 'array'],
                'speciality.*' => ['nullable', 'exists:specialties,id'],
                'branches' => ['nullable', 'array'],
                'branches.*' => ['nullable', 'exists:branch_of_medicines,id'],
                'skills' => ['nullable', 'array'],
                'skills.*' => ['nullable', 'exists:skills_for_questions,id'],
            ]
        );

        DB::transaction(function () use ($request, $questionData, $id) {
            $question = Questions::findOrFail($id);
            $oldImage = $question->image;
            if ($request->has('image')) {
                $questionData['image'] = $questionData['image']->store('questions', 'public');
            }
            $question->update(Arr::except($questionData, ['speciality', 'branches', 'skills']));
            $question->skills()->syncOrFail($questionData['skills'] ?? []);
            $question->branches()->syncOrFail($questionData['branches'] ?? []);
            $question->specialties()->syncOrFail($questionData['speciality'] ?? []);

            if ($request->has('image') && $oldImage) {
                Storage::disk('public')->delete($oldImage);
            }
            /*
                delete here
            */
        });
        flash()->success('Question Updated Succefully');

        return redirect()->route('questions.index');
    }

    public function destroy(int $id)
    {
        if (! $id) {
            return redirect('questions.index');
        }
        $question = Questions::findOrFail($id)->delete();
        flash()->error('You Have Deleted Questions Succefully');

        return redirect()->route('questions.index');
    }
}
