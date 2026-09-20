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

    /**
     * Panjang token terpendek yang diindeks InnoDB (innodb_ft_min_token_size, bawaan 3).
     * Kata yang lebih pendek tidak masuk indeks FULLTEXT sama sekali.
     */
    private const PANJANG_TOKEN_MINIMUM = 3;

    /**
     * Pencarian kata kunci pada posisi, deskripsi, dan nama perusahaan.
     *
     * Posisi dan deskripsi dicari lewat indeks FULLTEXT ft_lowongan_pencarian. Pola lama
     * LIKE '%kata%' tidak pernah bisa memakai indeks karena diawali wildcard, sehingga
     * selalu memindai seluruh tabel.
     *
     * Kata yang lebih pendek dari batas token InnoDB tidak ada di indeks FULLTEXT, jadi
     * untuk kata seperti "UI" pencarian dikembalikan ke LIKE agar hasilnya tidak kosong.
     * Nama perusahaan tetap memakai LIKE karena berada di tabel lain.
     */
    public function scopeCari(Builder $query, ?string $kata): Builder
    {
        $kata = trim((string) $kata);

        if ($kata === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($kata) {
            if ($ekspresi = $this->ekspresiFullText($kata)) {
                $q->whereRaw('MATCH (lowongan.posisi, lowongan.deskripsi) AGAINST (? IN BOOLEAN MODE)', [$ekspresi]);
            } else {
                $q->where('lowongan.posisi', 'like', "%{$kata}%")
                    ->orWhere('lowongan.deskripsi', 'like', "%{$kata}%");
            }

            $q->orWhereHas('perusahaan', fn ($p) => $p->where('nama', 'like', "%{$kata}%"));
        });
    }

    /**
     * Ubah kata kunci menjadi ekspresi BOOLEAN MODE, misalnya "data analyst" -> "+data* +analyst*".
     * Mengembalikan null bila ada token yang terlalu pendek untuk indeks FULLTEXT.
     */
    private function ekspresiFullText(string $kata): ?string
    {
        // Buang karakter operator BOOLEAN MODE (+ - > < ( ) ~ * " @) supaya masukan pengguna
        // tidak mengubah arti query.
        $token = preg_split('/[^\p{L}\p{N}]+/u', $kata, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($token === []) {
            return null;
        }

        foreach ($token as $t) {
            if (mb_strlen($t) < self::PANJANG_TOKEN_MINIMUM) {
                return null;
            }
        }

        return implode(' ', array_map(fn ($t) => '+' . $t . '*', $token));
    }

    public function butuhCentangBiru(): bool
    {
        return $this->syarat->contains('wajib_terverifikasi', true);
    }
}
