<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;

class UserController extends Controller
{
    public function profile(User $user)
    {
        $user->loadMissing(["attempts.quiz" , "attempts.answers"]);
        return view("user.profile", compact("user"));
    }
    //
}
