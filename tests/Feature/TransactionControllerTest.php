<?php

use App\Enums\TransferType;
use App\Exceptions\Transaction\InsufficientFundsException;
use App\Models\User;
use App\Services\Contracts\TransferServiceInterface;
use App\Services\Factories\TransferServiceFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;

uses(RefreshDatabase::class);

it('shows transaction history and stats for authenticated user', function () {
    /** @var User $user */
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('transactions.index'))
        ->assertInertia(
            fn (Assert $page) => $page->component('transaction/Index')
                ->has('history.data')
                ->has('stats')
                ->where('stats.incoming', fn ($v) => is_float($v) || is_int($v))
                ->where('stats.outgoing', fn ($v) => is_float($v) || is_int($v))
                ->where('stats.incoming_count', fn ($v) => is_int($v))
                ->where('stats.outgoing_count', fn ($v) => is_int($v))
        );
});

it('stores a transaction successfully', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $mockService = Mockery::mock(TransferServiceInterface::class);
    $mockService->shouldReceive('getType')->andReturn(TransferType::INTERNAL->value);
    $mockService->shouldReceive('execute')->once();

    $factory = new TransferServiceFactory([$mockService]);
    $this->app->instance(TransferServiceFactory::class, $factory);

    actingAs($sender)
        ->post(route('transactions.store'), [
            'receiver_id' => $receiver->id,
            'amount' => 100,
        ])
        ->assertSessionHas('success', 'Transaction sent successfully!');
});

it('returns error when funds are insufficient', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $mockService = Mockery::mock(TransferServiceInterface::class);
    $mockService->shouldReceive('getType')->andReturn(TransferType::INTERNAL->value);
    $mockService->shouldReceive('execute')
        ->once()
        ->andThrow(new InsufficientFundsException('Not enough funds.'));

    $factory = new TransferServiceFactory([$mockService]);
    $this->app->instance(TransferServiceFactory::class, $factory);

    actingAs($sender)
        ->post(route('transactions.store'), [
            'receiver_id' => $receiver->id,
            'amount' => 1000,
        ])
        ->assertSessionHasErrors(['amount' => 'Not enough funds.']);
});

it('returns error when unexpected exception occurs', function () {
    $sender = User::factory()->create();
    $receiver = User::factory()->create();

    $mockService = Mockery::mock(TransferServiceInterface::class);
    $mockService->shouldReceive('getType')->andReturn(TransferType::INTERNAL->value);
    $mockService->shouldReceive('execute')
        ->once()
        ->andThrow(new Exception('Unexpected!'));

    $factory = new TransferServiceFactory([$mockService]);
    $this->app->instance(TransferServiceFactory::class, $factory);

    Log::spy();

    actingAs($sender)
        ->post(route('transactions.store'), [
            'receiver_id' => $receiver->id,
            'amount' => 500,
        ])
        ->assertSessionHasErrors(['amount' => 'An unexpected error occurred. Please try again later.']);

    Log::shouldHaveReceived('error')->once();
});
