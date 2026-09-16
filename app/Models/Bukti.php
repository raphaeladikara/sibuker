<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/** Sertifikat PDF. Berkasnya ada di storage, tabel ini hanya menyimpan path di url_berkas. */
class Bukti extends Model
{
    public const CREATED_AT = 'diunggah_pada';
    public const UPDATED_AT = null;

    protected $table = 'bukti';

    protected $fillable = ['pengguna_id', 'jenis_bukti_id', 'judul', 'penerbit', 'url_berkas', 'tanggal_terbit', 'diunggah_pada'];

    protected function casts(): array
    {
        return [
            'tanggal_terbit' => 'date',
            'diunggah_pada' => 'datetime',
        ];
    }

    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'pengguna_id');
    }

    public function jenisBukti(): BelongsTo
    {
        return $this->belongsTo(JenisBukti::class, 'jenis_bukti_id');
    }

    public function verifikasi(): HasMany
    {
        return $this->hasMany(Verifikasi::class, 'bukti_id')->orderByDesc('id');
    }

    /**
     * Menambah kolom status_terakhir = keputusan verifikasi terbaru.
     * Bernilai NULL kalau belum pernah diperiksa, atau kalau berkas diganti setelah pemeriksaan terakhir.
     */
    public function scopeDenganStatus(Builder $query): Builder
    {
        if (is_null($query->getQuery()->columns)) {
            $query->select('bukti.*');
        }

        return $query->addSelect(['status_terakhir' => DB::table('verifikasi')
            ->selectRaw('CASE WHEN verifikasi.dibuat_pada < bukti.diunggah_pada THEN NULL ELSE verifikasi.keputusan END')
            ->whereColumn('verifikasi.bukti_id', 'bukti.id')
            ->orderByDesc('verifikasi.id')
            ->limit(1),
        ]);
    }

    /**
     * Sertifikat yang masih perlu keputusan: belum pernah diperiksa, keputusan terakhirnya ditunda,
     * atau berkasnya diganti setelah pemeriksaan terakhir.
     */
    public function scopeDalamAntrean(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNotExists(fn ($s) => $s->from('verifikasi')->whereColumn('verifikasi.bukti_id', 'bukti.id'))
                ->orWhereExists(fn ($s) => $s->from('verifikasi as vt')
                    ->whereColumn('vt.bukti_id', 'bukti.id')
                    ->whereRaw('vt.id = (SELECT MAX(v2.id) FROM verifikasi v2 WHERE v2.bukti_id = bukti.id)')
                    ->where(fn ($w) => $w->where('vt.keputusan', 'menunggu')->orWhereColumn('vt.dibuat_pada', '<', 'bukti.diunggah_pada')));
        });
    }

    public function perluDiperiksa(): bool
    {
        return in_array($this->status_terakhir, [null, 'menunggu'], true);
    }
}
