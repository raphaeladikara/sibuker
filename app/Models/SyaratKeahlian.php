<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SyaratKeahlian extends Model
{
    protected $table = 'syarat_keahlian';

    public $timestamps = false;

    protected $fillable = ['lowongan_id', 'keahlian_id', 'level_minimum', 'sifat', 'bobot', 'wajib_terverifikasi'];

    protected function casts(): array
    {
        return [
            'bobot' => 'float',
            'wajib_terverifikasi' => 'boolean',
        ];
    }

    public function lowongan(): BelongsTo
    {
        return $this->belongsTo(Lowongan::class, 'lowongan_id');
    }

    public function keahlian(): BelongsTo
    {
        return $this->belongsTo(Keahlian::class, 'keahlian_id');
    }
}
