<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProblemReport extends Model
{
    use SoftDeletes;

    public const CATEGORY_BREAKDOWN = 'breakdown';
    public const CATEGORY_ACCIDENT = 'accident';
    public const CATEGORY_ROUTING = 'routing';
    public const CATEGORY_SAFETY = 'safety';
    public const CATEGORY_DRIVER = 'driver';
    public const CATEGORY_OTHER = 'other';

    public const SEVERITY_LOW = 'low';
    public const SEVERITY_MEDIUM = 'medium';
    public const SEVERITY_HIGH = 'high';

    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';

    protected $fillable = [
        'school_id',
        'reporter_user_id',
        'trip_id',
        'bus_id',
        'category',
        'severity',
        'status',
        'description',
        'latitude',
        'longitude',
        'resolved_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'resolved_at' => 'datetime',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    public static function categories(): array
    {
        return [
            self::CATEGORY_BREAKDOWN => 'Breakdown',
            self::CATEGORY_ACCIDENT => 'Accident',
            self::CATEGORY_ROUTING => 'Routing',
            self::CATEGORY_SAFETY => 'Safety',
            self::CATEGORY_DRIVER => 'Driver',
            self::CATEGORY_OTHER => 'Other',
        ];
    }

    public static function severities(): array
    {
        return [
            self::SEVERITY_LOW => 'Low',
            self::SEVERITY_MEDIUM => 'Medium',
            self::SEVERITY_HIGH => 'High',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_RESOLVED => 'Resolved',
        ];
    }

    public static function categoryValues(): array
    {
        return array_keys(self::categories());
    }

    public static function severityValues(): array
    {
        return array_keys(self::severities());
    }

    public static function statusValues(): array
    {
        return array_keys(self::statuses());
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function getCategoryLabelAttribute(): string
    {
        return self::categories()[$this->category] ?? $this->category;
    }

    public function getSeverityLabelAttribute(): string
    {
        return self::severities()[$this->severity] ?? $this->severity;
    }

    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }
}
