<?php

use App\Models\BranchOfMedicine;
use App\Models\Questions;
use App\Models\Quiz;
use App\Models\SkillsForQuestion;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function adminUser(): User
{
    return User::factory()->create(['role' => 'admin']);
}

function adminQuiz(array $attributes = []): Quiz
{
    return Quiz::create(array_merge([
        'name' => 'Admin quiz',
        'topic' => 'General medicine',
        'difficulty' => 'easy',
        'length' => 'short',
        'type' => 'admin',
        'questions_number' => 3,
    ], $attributes));
}

test('an admin can open quiz creation and editing pages', function () {
    $admin = adminUser();
    $quiz = adminQuiz();

    $this->actingAs($admin)
        ->get(route('quizez.create'))
        ->assertOk()
        ->assertSee('Create quiz')
        ->assertSee('select2 form-select')
        ->assertSee('Choose questions');

    $this->actingAs($admin)
        ->get(route('quizez.edit', $quiz))
        ->assertOk()
        ->assertSee('Edit quiz')
        ->assertSee('select2 form-select')
        ->assertSee('Question bank');
});

test('an admin can create a quiz', function () {
    $admin = adminUser();

    $this->actingAs($admin)
        ->post(route('quizez.store'), [
            'name' => 'Created quiz',
            'topic' => 'Cardiology',
            'difficulty' => 'easy',
            'length' => 'short',
            'questions_number' => 3,
            'type' => 'admin',
        ])
        ->assertRedirect(route('quizez.index'));

    $this->assertDatabaseHas('quizzes', ['name' => 'Created quiz']);
});

test('an admin can edit a quiz', function () {
    $admin = adminUser();
    $quiz = adminQuiz();

    $this->actingAs($admin)
        ->put(route('quizez.update', $quiz), [
            'name' => 'Updated quiz',
            'topic' => 'Neurology',
            'difficulty' => 'hard',
            'length' => 'long',
            'questions_number' => 4,
            'type' => 'admin',
        ])
        ->assertRedirect(route('quizez.index'));

    $this->assertDatabaseHas('quizzes', [
        'id' => $quiz->id,
        'name' => 'Updated quiz',
        'difficulty' => 'hard',
    ]);
});

test('an admin can delete a quiz', function () {
    $admin = adminUser();
    $quiz = adminQuiz();

    $this->actingAs($admin)
        ->delete(route('quizez.destroy', $quiz))
        ->assertRedirect(route('quizez.index'));

    $this->assertDatabaseMissing('quizzes', ['id' => $quiz->id]);
});

test('quiz filter endpoints and livewire filters return matching records', function () {
    $admin = adminUser();
    $branch = BranchOfMedicine::factory()->create(['name' => 'Cardiology branch']);
    $specialty = Specialty::factory()->create(['name' => 'Cardiology specialty']);
    $skill = SkillsForQuestion::factory()->create(['name' => 'ECG interpretation']);
    $hard = Questions::factory()->create(['content' => 'Hard filtered question', 'difficulty' => 'hard']);
    $easy = Questions::factory()->create(['content' => 'Easy hidden question', 'difficulty' => 'easy']);
    $hard->branches()->attach($branch);
    $hard->specialties()->attach($specialty);
    $hard->skills()->attach($skill);

    $this->get(route('getBranches', ['search' => 'Cardiology']))->assertOk()->assertJsonFragment(['id' => $branch->id, 'name' => $branch->name]);
    $this->get(route('getSpeciality', ['search' => 'Cardiology']))->assertOk()->assertJsonFragment(['id' => $specialty->id, 'name' => $specialty->name]);
    $this->get(route('getSkills', ['search' => 'ECG']))->assertOk()->assertJsonFragment(['id' => $skill->id, 'name' => $skill->name]);

    Livewire::actingAs($admin)->test('create-quiz')
        ->set('difficulty', 'hard')
        ->set('branches', [$branch->id])
        ->set('specialties', [$specialty->id])
        ->set('skills', [$skill->id])
        ->assertSee('Hard filtered question')
        ->assertDontSee('Easy hidden question');
});
