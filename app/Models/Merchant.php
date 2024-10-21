<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    /** @use HasFactory<\Database\Factories\MerchantFactory> */
    use HasFactory;
    protected $fillable = [
        'owner_id',
        'name',
        'address',
        'phone_number',
        'status',
        'description',
        'logo'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
