<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    protected $fillable = [
        'user_id',
        'recipient_name',
        'phone',
        'province',
        'city',
        'district',
        'postal_code',
        'full_address',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function formatted(): string
    {
        $parts = array_filter([
            $this->recipient_name.' ('.$this->phone.')',
            $this->full_address,
            $this->district,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }
}
