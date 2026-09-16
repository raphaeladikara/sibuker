<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lowongan extends Model
{
    public const CREATED_AT = 'dibuat_pada';
    public const UPDATED_AT = 'diperbarui_pada';

    protected $table = 'lowongan';

    protected $fillable = ['perusahaan_id', 'kode', 'posisi', 'deskripsi', 'lokasi', 'tipe_pekerjaan', 'status', 'dipublikasikan_pada', 'ditutup_pada'];

    protected function casts(): array
    {
        return [
            'dipublikasikan_pada' => 'datetime',
            'ditutup_pada' => 'datetime',
        ];
    }

    public function perusahaan(): BelongsTo
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

    public function syarat(): HasMany
    {
        return $this->hasMany(SyaratKeahlian::class, 'lowongan_id')->orderByDesc('bobot')->orderBy('id');
    }

    public function lamaran(): HasMany
    {
        return $this->hasMany(Lamaran::class, 'lowongan_id');
    }

    public function scopeDipublikasikan(Builder $query): Builder
    {
        return $query->where('lowongan.status', 'dipublikasikan');
    }

    public function butuhCentangBiru(): bool
    {
        return $this->syarat->contains('wajib_terverifikasi', true);
    }
}
