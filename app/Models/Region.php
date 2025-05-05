<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $fillable = ['zone_id', 'code', 'name'];
    
    public function zone() {
        return $this->belongsTo(Zone::class);
    }
    
    public function territories() {
        return $this->hasMany(Territory::class);
    }
}
