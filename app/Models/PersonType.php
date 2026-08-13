<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonType extends Model
{
    public const CUSTOMER = 'customer';

    public const SUPPLIER = 'supplier';

    protected $fillable = [
        'name',
        'slug',
    ];

    public static function defaults(): array
    {
        return [
            ['name' => 'Cliente', 'slug' => self::CUSTOMER],
            ['name' => 'Fornecedor', 'slug' => self::SUPPLIER],
        ];
    }

    public static function options(): array
    {
        return self::query()
            ->orderBy('id')
            ->get(['id', 'name', 'slug'])
            ->map(fn (self $type) => [
                'value' => $type->id,
                'label' => $type->name,
                'slug' => $type->slug,
            ])
            ->all();
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
    }
}
