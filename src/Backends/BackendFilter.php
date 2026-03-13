<?php

namespace JustinWoodring\LaravelQiskit\Backends;

class BackendFilter
{
    private ?bool $online = null;

    private ?int $minQubits = null;

    private ?int $maxQueueDepth = null;

    private ?bool $simulator = null;

    /** @var Backend[] */
    private array $backends;

    public function __construct(array $backends)
    {
        $this->backends = $backends;
    }

    public function online(): self
    {
        $this->online = true;

        return $this;
    }

    public function withMinQubits(int $n): self
    {
        $this->minQubits = $n;

        return $this;
    }

    public function withMaxQueueDepth(int $n): self
    {
        $this->maxQueueDepth = $n;

        return $this;
    }

    public function simulator(bool $value = true): self
    {
        $this->simulator = $value;

        return $this;
    }

    /** @return Backend[] */
    public function get(): array
    {
        return array_values(array_filter($this->backends, function (Backend $backend) {
            if ($this->online !== null && $backend->isOnline() !== $this->online) {
                return false;
            }

            if ($this->minQubits !== null && $backend->qubits < $this->minQubits) {
                return false;
            }

            if ($this->maxQueueDepth !== null && $backend->queueDepth > $this->maxQueueDepth) {
                return false;
            }

            if ($this->simulator !== null && $backend->isSimulator !== $this->simulator) {
                return false;
            }

            return true;
        }));
    }
}
