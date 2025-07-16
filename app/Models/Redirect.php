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

    public function getCodeAttribute(){
        return Hashids::encode($this->id);
    }

    public function logs(){
        return $this->hasMany(RedirectLog::class);
    }
}
