<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldImage extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'field_id',
        'image_url',
        'is_primary',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Field, $this>
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
    }
}
