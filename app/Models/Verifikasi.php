<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** Satu kali pemeriksaan sertifikat. Riwayat tidak pernah ditimpa; yang dipakai selalu baris terbaru. */
class Verifikasi extends Model
{
    public const CREATED_AT = 'dibuat_pada';
    public const UPDATED_AT = null;

    protected $table = 'verifikasi';

    protected $fillable = ['bukti_id', 'verifikator_id', 'keputusan', 'catatan', 'diverifikasi_pada', 'berlaku_sampai'];

    protected function casts(): array
    {
        return [
            'diverifikasi_pada' => 'datetime',
            'berlaku_sampai' => 'date',
            'dibuat_pada' => 'datetime',
        ];
    }

    public function bukti(): BelongsTo
    {
        return $this->belongsTo(Bukti::class, 'bukti_id');
    }

    public function verifikator(): BelongsTo
    {
        return $this->belongsTo(Verifikator::class, 'verifikator_id');
    }

    /** Klaim keahlian yang dinyatakan terbukti (tabel bukti_keahlian). */
    public function klaimKeahlian(): BelongsToMany
    {
        return $this->belongsToMany(KlaimKeahlian::class, 'bukti_keahlian', 'verifikasi_id', 'klaim_keahlian_id');
    }
}
