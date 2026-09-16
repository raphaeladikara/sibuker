<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Master keahlian yang dipakai klaim, syarat lowongan, dan kewenangan verifikator. */
class Keahlian extends Model
{
    protected $table = 'keahlian';

    public $timestamps = false;

    protected $fillable = ['nama', 'kategori', 'deskripsi', 'aktif'];

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function klaim(): HasMany
    {
        return $this->hasMany(KlaimKeahlian::class, 'keahlian_id');
    }

    public function syarat(): HasMany
    {
        return $this->hasMany(SyaratKeahlian::class, 'keahlian_id');
    }

    public function verifikator(): BelongsToMany
    {
        return $this->belongsToMany(Verifikator::class, 'kewenangan_verifikator', 'keahlian_id', 'verifikator_id');
    }
}
