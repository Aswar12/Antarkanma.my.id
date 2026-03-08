<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourierReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'courier_id',
        'transaction_id',
        'rating',
        'note',
    ];

    protected $casts = [
        'rating' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
