<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vinkla\Hashids\Facades\Hashids;

class Redirect extends Model
{
    use HasFactory;
    //use SoftDeletes;

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

    public function resolveRouteBinding($value, $field = null)
    {
        $ids = Hashids::decode($value);

        if (count($ids) === 0) {
            return null;
        }
        
        return $this->where('id', $ids[0])->first();
    }
}
