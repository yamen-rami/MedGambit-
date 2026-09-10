<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\{Description, Signature};
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash as FacadesHash;

use App\Models\User;
use Cose\Hash;

#[Signature('admin:create')]
#[Description('Creates Admin User With Role of Super Admin')]
class CreateAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask("name");
        $email = $this->ask("email");
        $pass = $this->secret("password");
        if(User::where("email" , $email)->exists()){
            $this->error('A user with this email already exists.');
            return self::FAILURE;
        }
        $user = User::create([
            'name' => $name,
            'email' => $email,
            "role" => "super_admin",
            "country" => "ps" ,
            "year" => "6" , 
            "know_about_us" => "myself",
            "gender" => "male",
            'password' => FacadesHash::make($pass),
            'email_verified_at' => now(),
        ]);
        $this->info("Admin {$user->email} created successfully.");

        return self::SUCCESS;

        //
    }
}
