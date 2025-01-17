<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'total_price',
        'delivery_address'
    ];

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi dengan OrderItems
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Relasi dengan OrderHistory
    public function orderHistories()
    {
        return $this->hasMany(OrderHistory::class);
    }
}
