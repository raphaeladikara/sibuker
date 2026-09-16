<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PencariKerja extends Model
{
    protected $table = 'pencari_kerja';

    public $timestamps = false;

    protected $fillable = ['pengguna_id', 'headline', 'ringkasan', 'lokasi', 'tanggal_lahir'];

    protected function casts(): array
    {
        return ['tanggal_lahir' => 'date'];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function klaimKeahlian(): HasMany
    {
        return $this->hasMany(KlaimKeahlian::class, 'pencari_kerja_id');
    }

    public function lamaran(): HasMany
    {
        return $this->hasMany(Lamaran::class, 'pencari_kerja_id');
    }
}
