<?php

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();
});

it('returns paginated transactions and balance for the authenticated user', function () {
    $user = User::factory()->create(['balance' => 100]);
    $otherUser = User::factory()->create();
    $transactions = Transaction::factory()->count(3)->create([
        'sender_id' => $user->id,
        'receiver_id' => $otherUser->id,
    ]);
    Sanctum::actingAs($user);

    $response = $this->getJson('/api/transactions');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'sender', 'receiver',
                ],
            ],
            'meta',
            'links',
            'balance',
        ]);
    expect($response->json('balance'))->toEqual($user->balance);
});

it('creates a new transaction with valid data', function () {
    config(['wallet.commission_rate' => '0.015']);

    $sender = User::factory()->create(['balance' => 200]);
    $receiver = User::factory()->create(['balance' => 0]);
    Sanctum::actingAs($sender);

    $payload = [
        'receiver_id' => $receiver->id,
        'amount' => 50,
    ];

    $response = $this->postJson('/api/transactions', $payload);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => [
                'id', 'sender', 'receiver',
            ],
        ]);
    expect((float) $sender->fresh()->balance)->toBe(149.25)
        ->and((float) $receiver->fresh()->balance)->toBe(50.0);
});

it('returns 422 if sender has insufficient funds', function () {
    $sender = User::factory()->create(['balance' => 10]);
    $receiver = User::factory()->create();
    Sanctum::actingAs($sender);

    $payload = [
        'receiver_id' => $receiver->id,
        'amount' => 50,
    ];

    $response = $this->postJson('/api/transactions', $payload);

    $response->assertStatus(422)
        ->assertJson(['message' => 'Insufficient funds to cover the amount and commission fee.']);
});

it('validates the request data', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $payload = [
        'receiver_id' => null,
        'amount' => -10,
    ];

    $response = $this->postJson('/api/transactions', $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['receiver_id', 'amount']);
});
