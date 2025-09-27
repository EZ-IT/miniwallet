<?php

namespace App\Services\Contracts;

use App\Models\Transaction;
use App\Models\User;

interface TransferServiceInterface
{
    public function execute(User $sender, User $receiver, string $amount): Transaction;

    /**
     * Returns the unique identifier for this transfer service type.
     * This is used by the factory to map and retrieve the service.
     */
    public function getType(): string;
}
