<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Perusahaan extends Model
{
    protected $table = 'perusahaan';

    public $timestamps = false;

    protected $fillable = ['pengguna_id', 'nama', 'nib', 'deskripsi', 'alamat', 'situs_web', 'logo', 'status_perusahaan'];

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function lowongan(): HasMany
    {
        return $this->hasMany(Lowongan::class, 'perusahaan_id');
    }

    public function urlLogo(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }
}
