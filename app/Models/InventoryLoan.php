<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLoan extends Model
{
    use HasFactory;

    protected $table = 'inventory_loans';

    protected $fillable = [
        'inventory_id',
        'user_id',
        'start_at',
        'expired_at',
        'quantity',
    ];

    protected $casts = [
        'start_at' => 'date',
        'expired_at' => 'date',
    ];

    public function inventory()
    {
        return $this->belongsTo(Inventory::class, 'inventory_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}