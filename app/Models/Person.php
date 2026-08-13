<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Person extends Model
{
    protected $fillable = [
        'user_id',
        'person_type_id',
        'name',
        'document',
        'email',
        'phone',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'person_type_id' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function personType(): BelongsTo
    {
        return $this->belongsTo(PersonType::class);
    }
}
