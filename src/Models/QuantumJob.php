<?php

namespace JustinWoodring\LaravelQiskit\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JustinWoodring\LaravelQiskit\Events\QuantumJobCancelled;
use JustinWoodring\LaravelQiskit\Primitives\PrimitiveResult;

/**
 * @property int $id
 * @property string|null $ibm_job_id
 * @property string|null $ibm_session_id
 * @property string $backend
 * @property string $primitive_type
 * @property string $status
 * @property array|null $payload
 * @property array|null $result
 * @property array|null $metadata
 * @property string|null $error_message
 * @property int $poll_count
 * @property int|null $user_id
 * @property int|null $team_id
 * @property Carbon|null $submitted_at
 * @property Carbon|null $completed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class QuantumJob extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_TIMED_OUT = 'timed_out';

    public const TERMINAL_STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_FAILED,
        self::STATUS_CANCELLED,
        self::STATUS_TIMED_OUT,
    ];

    protected $guarded = [];

    protected $casts = [
        'payload' => 'array',
        'result' => 'array',
        'metadata' => 'array',
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'poll_count' => 'integer',
        'user_id' => 'integer',
        'team_id' => 'integer',
    ];

    public function getTable(): string
    {
        return config('qiskit.models.quantum_job_table', 'quantum_jobs');
    }

    // --- Scopes ---

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeForBackend(Builder $query, string $backend): Builder
    {
        return $query->where('backend', $backend);
    }

    // --- Accessors ---

    public function getResultAttribute(mixed $value): ?PrimitiveResult
    {
        if ($value === null) {
            return null;
        }

        $data = is_array($value) ? $value : json_decode($value, true);

        return $data ? PrimitiveResult::fromArray($data) : null;
    }

    public function setResultAttribute(array|PrimitiveResult|null $value): void
    {
        if ($value instanceof PrimitiveResult) {
            $this->attributes['result'] = json_encode($value->getRaw());
        } else {
            $this->attributes['result'] = $value !== null ? json_encode($value) : null;
        }
    }

    // --- State helpers ---

    public function isTerminal(): bool
    {
        return in_array($this->status, self::TERMINAL_STATUSES);
    }

    public function cancel(): bool
    {
        if ($this->isTerminal()) {
            return false;
        }

        $this->update(['status' => self::STATUS_CANCELLED]);

        event(new QuantumJobCancelled($this));

        return true;
    }

    public function refresh(): static
    {
        return parent::refresh();
    }
}
