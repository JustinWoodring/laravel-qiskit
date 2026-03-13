<?php

namespace JustinWoodring\LaravelQiskit\Contracts;

use JustinWoodring\LaravelQiskit\Primitives\PrimitiveResult;
use JustinWoodring\LaravelQiskit\Support\PendingJob;

interface PrimitiveContract
{
    public function toPayload(): array;

    public function dispatch(): PendingJob;

    public function dispatchSync(): PrimitiveResult;
}
