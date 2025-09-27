<?php

namespace App\Services\Factories;

use App\Enums\TransferType;
use App\Services\Contracts\TransferServiceInterface;
use InvalidArgumentException;

/**
 * TransferServiceFactory is responsible for selecting the appropriate transfer service implementation
 * based on the requested transfer type (e.g., internal, external, etc.).
 *
 * By injecting all services tagged as 'transfer.services', this factory decouples the controller/business logic
 * from the concrete implementations of transfer services. This makes it easy to add new transfer types
 * without modifying existing code; simply register and tag the new service.
 */
final class TransferServiceFactory
{
    private array $services = [];

    /**
     * @param iterable<TransferServiceInterface> $taggedServices
     */
    public function __construct(iterable $taggedServices)
    {
        foreach ($taggedServices as $service) {
            $this->services[$service->getType()] = $service;
        }
    }

    /**
     * Resolve a transfer service by type.
     *
     * @throws InvalidArgumentException if type is not supported
     */
    public function make(TransferType $type): TransferServiceInterface
    {
        return $this->services[$type->value]
            ?? throw new InvalidArgumentException("Unsupported transfer type [{$type->value}].");
    }
}
