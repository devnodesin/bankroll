<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UserAddCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add {username} {email} {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = \App\Models\User::create([
            'name' => $username,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->info("User '{$username}' created successfully with ID: {$user->id}");
        return 0;
    }
}
