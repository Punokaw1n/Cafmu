<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\TenantScope;

class Order extends Model
{
    protected $fillable = [
        'tenant_id',
        'table_id',
        'order_number',
        'total_price',
        'status',
        'payment_status',
        'payment_url',
        'midtrans_transaction_id',
        'customer_name',
        'customer_phone',
        'notes',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
