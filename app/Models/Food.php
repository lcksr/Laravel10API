<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    protected $table = 'foods';

    protected $fillable = [
        'name',
        'category_id',
        'description',
        'price',
        'image_url',
        'stock'
    ];

    // Relasi dengan kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi dengan order items (banyak makanan dalam satu pesanan)
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
