<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TahapSeleksi extends Model
{
    public const CREATED_AT = null;
    public const UPDATED_AT = 'diperbarui_pada';

    protected $table = 'tahap_seleksi';

    protected $fillable = ['lamaran_id', 'nama_tahap', 'urutan', 'status', 'jadwal', 'catatan'];

    protected function casts(): array
    {
        return [
            'jadwal' => 'datetime',
            'diperbarui_pada' => 'datetime',
        ];
    }

    public function lamaran(): BelongsTo
    {
        return $this->belongsTo(Lamaran::class, 'lamaran_id');
    }
}
