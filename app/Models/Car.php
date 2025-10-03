<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    protected $guarded = ['id'];


    /**
     * Get all of the comments for the Car
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rental(): HasMany
    {
        return $this->hasMany(Rental::class);
    }

    /**
     * Get all of the comments for the Car
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function maintenance(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }
}
