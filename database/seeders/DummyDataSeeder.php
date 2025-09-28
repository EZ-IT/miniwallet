<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public const int USERS_TO_INSERT = 500_000;

    public const int TRANSACTIONS_TO_INSERT = 500_000;

    public const int BATCH_SIZE = 5_000;

    public function run(): void
    {
        $this->command->info('Checking max IDs...');
        $maxUserId = DB::table('users')->max('id') ?? 0;
        $startUserId = $maxUserId + 1;
        $now = now();
        $precomputedHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

        // USERS
        $this->command->info('Seeding users...');
        $users = [];
        for ($i = 0; $i < self::USERS_TO_INSERT; $i++) {
            $id = $startUserId + $i;
            $users[] = [
                'name' => "User {$id}",
                'email' => "user{$id}@example.com",
                'email_verified_at' => $now,
                'password' => $precomputedHash,
                'remember_token' => '',
                'balance' => 1000.00,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (count($users) >= self::BATCH_SIZE) {
                DB::table('users')->insert($users);
                $users = [];
                $inserted = $i + 1;
                $this->command->getOutput()->write("\rSeeded {$inserted} / " . self::USERS_TO_INSERT . ' users...');
            }
        }
        if ($users !== []) {
            DB::table('users')->insert($users);
            $inserted = self::USERS_TO_INSERT;
            $this->command->getOutput()->write("\rSeeded {$inserted} / " . self::USERS_TO_INSERT . ' users...');
        }
        $this->command->getOutput()->writeln("\nUsers seeded!");

        // TRANSACTIONS
        $this->command->info('Seeding transactions...');
        $transactions = [];
        // Pre-generate sender/receiver IDs for speed
        $firstUserId = $startUserId;
        $lastUserId = DB::table('users')->max('id');
        $amounts = [10.00, 20.00, 50.00, 100.00, 500.00, 999.99];

        for ($i = 0; $i < self::TRANSACTIONS_TO_INSERT; $i++) {
            $sender = mt_rand($firstUserId, $lastUserId);
            do {
                $receiver = mt_rand($firstUserId, $lastUserId);
            } while ($receiver === $sender);

            $transactions[] = [
                'sender_id' => $sender,
                'receiver_id' => $receiver,
                'amount' => $amounts[array_rand($amounts)],
                'commission_fee' => 0.015,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            if (count($transactions) >= self::BATCH_SIZE) {
                DB::table('transactions')->insert($transactions);
                $transactions = [];
                $inserted = $i + 1;
                if ($inserted % 50000 === 0) {
                    $this->command->getOutput()->write("\rSeeded {$inserted} / " . self::TRANSACTIONS_TO_INSERT . ' transactions...');
                }
            }
        }
        if ($transactions !== []) {
            DB::table('transactions')->insert($transactions);
            $inserted = self::TRANSACTIONS_TO_INSERT;
            $this->command->getOutput()->write("\rSeeded {$inserted} / " . self::TRANSACTIONS_TO_INSERT . ' transactions...');
        }
        $this->command->getOutput()->writeln("\nTransactions seeded!");
    }
}
