<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialEntry extends Model
{
    public const TYPE_RECEIVABLE = 'receivable';
    public const TYPE_PAYABLE = 'payable';

    public const STATUS_PENDING = 'pending';
    public const STATUS_RECEIVED = 'received';
    public const STATUS_PAID = 'paid';
    public const STATUS_OVERDUE = 'overdue';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'user_id',
        'person_id',
        'type',
        'description',
        'amount',
        'issue_date',
        'due_date',
        'status',
        'settlement_date',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'person_id' => 'integer',
            'amount' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
            'settlement_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public static function types(): array
    {
        return [
            self::TYPE_RECEIVABLE,
            self::TYPE_PAYABLE,
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_RECEIVED,
            self::STATUS_PAID,
            self::STATUS_OVERDUE,
            self::STATUS_CANCELLED,
        ];
    }
}