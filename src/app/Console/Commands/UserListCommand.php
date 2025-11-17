<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UserListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = \App\Models\User::all(['id', 'name', 'email', 'created_at']);
        
        if ($users->isEmpty()) {
            $this->info('No users found.');
            return 0;
        }
        
        $this->table(
            ['ID', 'Name', 'Email', 'Created At'],
            $users->map(function ($user) {
                return [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->created_at->format('Y-m-d H:i:s'),
                ];
            })
        );
        
        return 0;
    }
}
