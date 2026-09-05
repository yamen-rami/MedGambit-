<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

use App\Concerns\{PasswordValidationRules, ProfileValidationRules};
use App\Models\User;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'country' => ['required', 'string' , "max:40"],
            'know_about_us' => ['required', 'string', "max:100"],
            'image' => ['nullable', 'image', 'max:2048'],
            'graduated' => ['required', 'in:true,false'],
            'gender' => ['required', 'in:male,female'],
            'year' => ['nullable', 'integer', 'required_if:graduated,false' ,'prohibited_if:graduated,true' ],
        ])->validate();
        $imagePath = null;
        if (isset($input['image'])) {
            $imagePath = $input['image']->store('profiles', 'public');
        }

        return User::create([
            'name' => $input['name'],
            'country' => $input['country'],
            'know_about_us' => $input['know_about_us'],
            'image' => $imagePath,
            'year' => $input['year'],
            'graduated' => $input['graduated'] === 'true' ? true : false,
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
    }
}
