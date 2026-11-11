<?php

namespace App\Models;

use App\States\SalesOrder\SalesOrderState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\ModelStates\HasStates;

class SalesOrder extends Model
{
    use HasStates;

    protected $with = ['items'];
    protected $casts = [
        'payment_payload' => 'array',
        'payment_paid_at' => 'datetime',
        'status' => SalesOrderState::class
    ];

    public function items() : HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }
}
