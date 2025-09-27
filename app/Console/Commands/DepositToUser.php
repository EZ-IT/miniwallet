<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class DepositToUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:deposit {user_id} {amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Deposit an amount to a user's balance";

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $userId = $this->argument('user_id');
        $amount = $this->argument('amount');

        $user = User::query()->find($userId);

        if (!$user) {
            $this->error('User not found.');

            return 1;
        }

        $user->increment('balance', (float) $amount);

        $this->info("Deposited {$amount} to user ID {$user->id} ({$user->name}, {$user->email}). New balance: {$user->balance}");

        return 0;
    }
}
