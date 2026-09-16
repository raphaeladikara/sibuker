<?php

namespace App\Models;

use App\Support\Format;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;

/** Tabel pengguna: akun login. Peran ditentukan dari profil yang dimiliki dan kolom is_admin. */
class Pengguna extends Authenticatable
{
    public const CREATED_AT = 'dibuat_pada';
    public const UPDATED_AT = 'diperbarui_pada';

    public const PERAN = [
        'pencari' => 'Pencari Kerja',
        'perusahaan' => 'Perusahaan',
        'verifikator' => 'Verifikator',
        'admin' => 'Administrator',
    ];

    protected $table = 'pengguna';

    // Skema tidak punya kolom remember_token, jadi fitur "ingat saya" dimatikan.
    protected $rememberTokenName = '';

    protected $fillable = ['nama', 'email', 'password', 'nomor_telepon', 'foto_profil', 'is_admin', 'status_akun'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'dibuat_pada' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function pencariKerja(): HasOne
    {
        return $this->hasOne(PencariKerja::class, 'pengguna_id');
    }

    public function verifikator(): HasOne
    {
        return $this->hasOne(Verifikator::class, 'pengguna_id');
    }

    public function perusahaan(): HasOne
    {
        return $this->hasOne(Perusahaan::class, 'pengguna_id');
    }

    public function bukti(): HasMany
    {
        return $this->hasMany(Bukti::class, 'pengguna_id');
    }

    /** Kunci peran yang boleh dipakai akun ini, urut sesuai PERAN. Verifikator nonaktif tidak dihitung. */
    public function daftarPeran(): array
    {
        $peran = [];
        if ($this->pencariKerja) {
            $peran[] = 'pencari';
        }
        if ($this->perusahaan) {
            $peran[] = 'perusahaan';
        }
        if ($this->verifikator && $this->verifikator->status_verifikator === 'aktif') {
            $peran[] = 'verifikator';
        }
        if ($this->is_admin) {
            $peran[] = 'admin';
        }

        return $peran;
    }

    public function punyaPeran(string $peran): bool
    {
        return in_array($peran, $this->daftarPeran(), true);
    }

    /** Nama route dashboard untuk peran tertentu, atau peran pertama yang dimiliki. */
    public function ruteDashboard(?string $peran = null): string
    {
        $peran ??= $this->daftarPeran()[0] ?? null;

        return $peran ? $peran . '.dashboard' : 'beranda';
    }

    public function urlFoto(): ?string
    {
        return $this->foto_profil ? Storage::disk('public')->url($this->foto_profil) : null;
    }

    public function inisial(): string
    {
        return Format::inisial($this->nama);
    }
}
