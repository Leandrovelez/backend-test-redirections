<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Redirect extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'status',
        'last_redirect_at',
    ];

    public function getCodeAtribute(){
        return Hashids::encode($this->id);
    }
}
