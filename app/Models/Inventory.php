<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventorys'; // Perhatikan typo "inventorys"

    protected $fillable = [
        'name',
        'category',
        'placement',
        'asset_entry',
        'expired_date',
        'is_can_loan',
        'inv_status',
        'quantity', 
        'available_quantity',
        'img_url',
    ];

    protected $casts = [
        'asset_entry' => 'datetime',
        'expired_date' => 'datetime',
        'is_can_loan' => 'boolean',
        'inv_status' => 'string',
    ];

    public function loans()
    {
        return $this->hasMany(InventoryLoan::class, 'inventory_id');
    }

    public function getAvailableQuantityAttribute()
    {
        $loanedQuantity = $this->loans()->sum('quantity');
        return $this->quantity - $loanedQuantity;
    }
}