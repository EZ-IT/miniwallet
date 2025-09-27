<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Override;

class TransactionCollection extends ResourceCollection
{
    public function __construct($resource, /**
     * The current balance of the authenticated user.
     */
        private readonly string $balance)
    {
        parent::__construct($resource);
    }

    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'balance' => $this->balance,
            ],
        ];
    }
}
