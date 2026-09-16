<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisBukti extends Model
{
    protected $table = 'jenis_bukti';

    public $timestamps = false;

    protected $fillable = ['nama'];

    public function bukti(): HasMany
    {
        return $this->hasMany(Bukti::class, 'jenis_bukti_id');
    }
}
