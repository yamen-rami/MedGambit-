<?php

namespace App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OptionsController extends Controller
{
    public function create(int $id)
    {
        return view('options.create', compact('id'));
    }

    public function store(Request $request, int $id)
    {
        $data = $request->validate([
            'name' => ['required', 'string'],
            'content' => ['required', 'string', 'min:3'],
            'explanation' => ['required', 'string', 'min:3'],
            'image' => ['required'],
            'correct_answer' => ['nullable'],
        ]);
        $data['questions_id'] = $id;
        DB::transaction(function () use ($data, $request) {
            if ($request->hasFile('image')) {
                $data['image'] = $request->file('image')->store('questions', 'public');
            }
            Option::create($data);
        });
        flash()->success('Option Has Created succefully');

        return redirect()->route('questions.show', $id);
    }

    public function edit(Option $option)
    {
        return view('options.edit', compact('option'));
    }

    public function update(Request $request, Option $option)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string'],
            'explanation' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'image' => ['nullable'],
            'correct_answer' => ['nullable', 'boolean'],
        ]);
        if ($request->hasFile('image')) {
            if ($option->image) {
                Storage::disk('public')->delete($option->image);
            }
            $data['image'] = $data['image']->store('questions', 'public');
        } else {
            $data['image'] = $option->image;
        }
        $option->update($data);
        flash()->info('Option has Updated');

        return redirect()->route('questions.index');
    }

    public function destroy(int $optionId, int $question_id)
    {
        $option = Option::findOrFail($optionId);
        $option->delete();
        if ($option->image) {
            Storage::disk('public')->delete($option->image);
        }

        flash()->success('Option Has Deleted Succefully');

        return redirect()->route('questions.show', $question_id);
    }
}
