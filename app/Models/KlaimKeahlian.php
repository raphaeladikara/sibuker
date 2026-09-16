<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/** Keahlian yang dicantumkan pencari kerja. Status centang biru dibaca dari view v_status_keahlian. */
class KlaimKeahlian extends Model
{
    public const CREATED_AT = 'tanggal_ditambahkan';
    public const UPDATED_AT = null;

    protected $table = 'klaim_keahlian';

    protected $fillable = ['pencari_kerja_id', 'keahlian_id', 'level_klaim', 'aktif'];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
            'terverifikasi' => 'boolean',
            'tanggal_ditambahkan' => 'datetime',
        ];
    }

    public function pencariKerja(): BelongsTo
    {
        return $this->belongsTo(PencariKerja::class, 'pencari_kerja_id');
    }

    public function keahlian(): BelongsTo
    {
        return $this->belongsTo(Keahlian::class, 'keahlian_id');
    }

    public function verifikasi(): BelongsToMany
    {
        return $this->belongsToMany(Verifikasi::class, 'bukti_keahlian', 'klaim_keahlian_id', 'verifikasi_id');
    }

    /** Menambah kolom terverifikasi. Klaim nonaktif tidak ada di view, jadi hasilnya 0. */
    public function scopeDenganStatus(Builder $query): Builder
    {
        if (is_null($query->getQuery()->columns)) {
            $query->select('klaim_keahlian.*');
        }

        return $query->addSelect(['terverifikasi' => DB::table('v_status_keahlian')
            ->selectRaw('COALESCE(MAX(terverifikasi), 0)')
            ->whereColumn('v_status_keahlian.klaim_keahlian_id', 'klaim_keahlian.id'),
        ]);
    }
}
