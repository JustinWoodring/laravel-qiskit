<?php

namespace JustinWoodring\LaravelQiskit\Contracts;

interface CircuitContract
{
    public function toQasm(): string;

    public function __toString(): string;
}
