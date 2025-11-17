<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UserRemoveCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:remove {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a user by username';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');
        
        $user = \App\Models\User::where('name', $username)->first();
        
        if (!$user) {
            $this->error("User with username '{$username}' not found.");
            return 1;
        }
        
        $email = $user->email;
        $user->delete();
        
        $this->info("User '{$username}' ({$email}) deleted successfully.");
        return 0;
    }
}
