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
    protected $signature = 'user:add {username} {password} {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a new user (email is optional)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        $password = $this->argument('password');
        $email = $this->option('email');

        // Generate a unique email if not provided
        if (empty($email)) {
            $email = $username . '@bankroll.local';
        }

        $user = \App\Models\User::create([
            'name' => $username,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        $this->info("User '{$username}' created successfully with ID: {$user->id}");
        return 0;
    }
}
