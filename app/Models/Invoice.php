<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'school_id',
        'subscription_id',
        'plan_id',
        'billing_cycle',
        'amount',
        'currency',
        'billing_period_start',
        'billing_period_end',
        'issued_at',
        'due_at',
        'paid_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'billing_period_start' => 'datetime',
            'billing_period_end' => 'datetime',
            'issued_at' => 'datetime',
            'due_at' => 'datetime',
            'paid_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class)->withTrashed();
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    /**
     * The status shown to users. An unpaid invoice whose due date has
     * passed is displayed as "overdue" without being written to the
     * database, mirroring the read-time approach used for subscriptions.
     */
    public function statusLabel(): string
    {
        if ($this->status === 'unpaid' && $this->due_at !== null && $this->due_at->isPast()) {
            return 'overdue';
        }

        return $this->status;
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['school'] ?? null, function (Builder $query, string $school) {
                $query->whereHas('school', fn ($q) => $q->where('name', 'like', "%{$school}%"));
            })
            ->when($filters['school_id'] ?? null, function (Builder $query, $schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->when($filters['status'] ?? null, function (Builder $query, string $status) {
                if ($status === 'overdue') {
                    $query->where('status', 'unpaid')
                        ->whereNotNull('due_at')
                        ->where('due_at', '<', now());
                } else {
                    $query->where('status', $status);
                }
            })
            ->when($filters['billing_cycle'] ?? null, function (Builder $query, string $cycle) {
                $query->where('billing_cycle', $cycle);
            })
            ->when($filters['date_from'] ?? null, function (Builder $query, string $date) {
                $query->whereDate('issued_at', '>=', $date);
            })
            ->when($filters['date_to'] ?? null, function (Builder $query, string $date) {
                $query->whereDate('issued_at', '<=', $date);
            });
    }
}
