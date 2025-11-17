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
    protected $signature = 'user:remove {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }
        
        $username = $user->name;
        $user->delete();
        
        $this->info("User '{$username}' ({$email}) deleted successfully.");
        return 0;
    }
}
