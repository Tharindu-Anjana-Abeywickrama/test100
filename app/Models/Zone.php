<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = ['code', 'long_description', 'short_description'];
    
    public function regions() {
        return $this->hasMany(Region::class);
    }
}
