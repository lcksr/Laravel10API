<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodSearch extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'search_query'
    ];

    // Relasi dengan User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
