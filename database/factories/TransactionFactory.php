<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    public function definition(): array
    {
        $sender = User::factory()->create();
        do {
            $receiver = User::factory()->create();
        } while ($receiver->id === $sender->id);

        return [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'amount' => $this->faker->randomFloat(Transaction::PRECISION, 1, 1000),
            'commission_fee' => $this->faker->randomFloat(Transaction::PRECISION, 0, 10),
        ];
    }
}
