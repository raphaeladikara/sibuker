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

        // Aturan "verifikasi terbaru yang masih sah" sekarang hanya ditulis sekali, di view
        // v_verifikasi_terbaru, supaya aplikasi dan view v_status_keahlian tidak bisa
        // berbeda pendapat soal apakah sebuah sertifikat masih terverifikasi.
        return $query->addSelect(['status_terakhir' => DB::table('v_verifikasi_terbaru')
            ->select('keputusan')
            ->whereColumn('v_verifikasi_terbaru.bukti_id', 'bukti.id')
            ->limit(1),
        ]);
    }

    /**
     * Sertifikat yang masih perlu keputusan: belum pernah diperiksa, keputusan terakhirnya ditunda,
     * atau berkasnya diganti setelah pemeriksaan terakhir.
     */
    public function scopeDalamAntrean(Builder $query): Builder
    {
        // v_verifikasi_terbaru berisi paling banyak satu baris per bukti, dan baris itu
        // hilang sendiri ketika berkas diganti. Jadi ketiga kondisi lama (belum pernah
        // diperiksa, keputusan terakhir ditunda, berkas diganti) cukup ditulis sebagai
        // "tidak ada keputusan akhir yang masih berlaku".
        return $query->whereNotExists(fn ($s) => $s->from('v_verifikasi_terbaru')
            ->whereColumn('v_verifikasi_terbaru.bukti_id', 'bukti.id')
            ->whereIn('v_verifikasi_terbaru.keputusan', ['disetujui', 'ditolak']));
    }

    public function perluDiperiksa(): bool
    {
        return in_array($this->status_terakhir, [null, 'menunggu'], true);
    }
}
