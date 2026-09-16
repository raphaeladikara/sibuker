<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Verifikator extends Model
{
    protected $table = 'verifikator';

    public $timestamps = false;

    protected $fillable = ['pengguna_id', 'instansi', 'jabatan', 'status_verifikator'];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    /** Bidang keahlian yang boleh diperiksa (tabel kewenangan_verifikator). */
    public function kewenangan(): BelongsToMany
    {
        return $this->belongsToMany(Keahlian::class, 'kewenangan_verifikator', 'verifikator_id', 'keahlian_id')
            ->withPivot('diberikan_pada');
    }

    public function verifikasi(): HasMany
    {
        return $this->hasMany(Verifikasi::class, 'verifikator_id');
    }
}
