<?php

namespace JustinWoodring\LaravelQiskit\Contracts;

use JustinWoodring\LaravelQiskit\Backends\Backend;
use JustinWoodring\LaravelQiskit\Backends\BackendFilter;
use JustinWoodring\LaravelQiskit\Client\QiskitResponse;

interface BackendRepositoryContract
{
    /** @return Backend[] */
    public function all(): array;

    /** @return Backend[] */
    public function available(): array;

    public function filter(): BackendFilter;

    public function find(string $id): Backend;

    public function status(string $id): QiskitResponse;

    public function properties(string $id): QiskitResponse;

    public function configuration(string $id): QiskitResponse;
}
