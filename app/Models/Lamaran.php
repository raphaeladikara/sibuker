<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lamaran extends Model
{
    public const CREATED_AT = 'dilamar_pada';
    public const UPDATED_AT = 'diperbarui_pada';

    /** Status yang sudah final: lamaran tidak bisa ditarik lagi. */
    public const STATUS_FINAL = ['diterima', 'ditolak', 'ditarik'];

    protected $table = 'lamaran';

    protected $fillable = ['lowongan_id', 'pencari_kerja_id', 'status', 'skor_kecocokan'];

    protected function casts(): array
    {
        return [
            'skor_kecocokan' => 'float',
            'dilamar_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function lowongan(): BelongsTo
    {
        return $this->belongsTo(Lowongan::class, 'lowongan_id');
    }

    public function pencariKerja(): BelongsTo
    {
        return $this->belongsTo(PencariKerja::class, 'pencari_kerja_id');
    }

    public function tahapSeleksi(): HasMany
    {
        return $this->hasMany(TahapSeleksi::class, 'lamaran_id')->orderBy('urutan');
    }

    public function sudahFinal(): bool
    {
        return in_array($this->status, self::STATUS_FINAL, true);
    }
}
