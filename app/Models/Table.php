<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Table extends Model
{
    protected $fillable = [
        'tenant_id',
        'table_number',
        'qr_code_string',
        'is_active',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Status labels untuk tampilan
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'available' => 'Tersedia',
            'occupied'  => 'Ditempati',
            'dirty'     => 'Perlu Dibersihkan',
            default     => 'Tidak Diketahui',
        };
    }

    // Status color untuk badge Tailwind
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'available' => 'green',
            'occupied'  => 'red',
            'dirty'     => 'yellow',
            default     => 'gray',
        };
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
