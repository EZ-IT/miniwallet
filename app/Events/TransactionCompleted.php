<?php

namespace App\Events;

use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCompleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Transaction $transaction, public User $userContext) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->userContext->id)];
    }

    public function broadcastAs(): string
    {
        return 'transaction.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'transaction' => new TransactionResource($this->transaction)->resolve(),
            'balance' => $this->userContext->balance,
        ];
    }
}
