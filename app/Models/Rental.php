<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rental extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $primaryKey = 'uuid'; // pakai uuid sebagai primary key
    public $incrementing = false;   // jangan auto increment
    protected $keyType = 'string';  // tipe string

    /**
     * Get the user that owns the Rental
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'rental_id', 'uuid');
    }
}
