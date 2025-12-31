<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingDestination extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'post_code',
        'address',
        'building_name',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}