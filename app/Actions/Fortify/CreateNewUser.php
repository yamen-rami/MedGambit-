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
            'image' => ['nullable', 'image', 'max:2048'], // Validate as image
            'year' => ['nullable', 'integer' , "max:6"],
            'graduated' => ['required'],
        ])->validate();
        $imagePath = null;
        if (isset($input['image'])) {
            $imagePath = $input['image']->store('profiles', 'public');
        }
        return User::create([
            'name' => $input['name'],
            "image" => $imagePath ,
            "year" => $input["year"],
            "graduated" => $input["graduated"] ==="true" ? true : false,
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
    }
}
