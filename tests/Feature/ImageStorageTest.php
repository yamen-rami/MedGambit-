<?php

use App\Models\BranchOfMedicine;
use App\Models\Option;
use App\Models\Questions;
use App\Models\Reference;
use App\Models\SkillsForQuestion;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('public image paths resolve through the storage URL', function () {
    config(['filesystems.disks.public.url' => 'https://medgambit.test/storage']);

    expect((new Questions(['image' => 'questions/question.jpg']))->image_url)
        ->toBe('https://medgambit.test/storage/questions/question.jpg')
        ->and((new Option(['image' => 'questions/option.jpg']))->image_url)
        ->toBe('https://medgambit.test/storage/questions/option.jpg')
        ->and((new User(['image' => 'profiles/avatar.jpg']))->image_url)
        ->toBe('https://medgambit.test/storage/profiles/avatar.jpg');
});

test('absolute and bundled image URLs remain unchanged', function () {
    expect((new Questions(['image' => 'https://images.example.test/question.jpg']))->image_url)
        ->toBe('https://images.example.test/question.jpg')
        ->and((new User(['image' => 'assets/img/avatars/1.png']))->image_url)
        ->toBe(asset('assets/img/avatars/1.png'));
});

test('question and option images are stored on the public disk', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => 'admin']);
    $specialty = Specialty::factory()->create();
    $branch = BranchOfMedicine::factory()->create();
    $skill = SkillsForQuestion::factory()->create();
    $reference = Reference::factory()->create();

    $this->actingAs($admin)
        ->post(route('questions.store'), [
            'options_number' => 2,
            'name' => 'Image question',
            'content' => 'Question content',
            'speciality' => [$specialty->id],
            'branches' => [$branch->id],
            'skills' => [$skill->id],
            'topic' => 'Imaging',
            'main_explanation' => 'Main explanation',
            'reference' => $reference->id,
            'high_yield' => '1',
            'difficulty' => 'easy',
            'length' => 'short',
            'elo_correct' => '4',
            'elo_incorrect' => '5',
            'image' => UploadedFile::fake()->image('question.jpg'),
            'options' => [
                [
                    'name' => 'A',
                    'content' => 'Correct answer',
                    'correct_answer' => 1,
                    'explanation' => 'Correct explanation',
                    'image' => UploadedFile::fake()->image('correct.jpg'),
                    'topic' => 'Imaging',
                ],
                [
                    'name' => 'B',
                    'content' => 'Incorrect answer',
                    'correct_answer' => 0,
                    'explanation' => 'Incorrect explanation',
                    'image' => UploadedFile::fake()->image('incorrect.jpg'),
                    'topic' => 'Imaging',
                ],
            ],
        ])
        ->assertRedirect(route('questions.index'));

    $question = Questions::query()->where('name', 'Image question')->firstOrFail();

    Storage::disk('public')->assertExists($question->image);

    $question->options->each(
        fn (Option $option) => Storage::disk('public')->assertExists($option->image)
    );
});
