<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PencariKerja;
use App\Models\Perusahaan;
use App\Models\Verifikator;

/** Profil peran milik akun yang sedang login. Middleware peran:* sudah memastikan profilnya ada. */
trait AksesProfil
{
    protected function pencari(): PencariKerja
    {
        return auth()->user()->pencariKerja;
    }

    protected function perusahaan(): Perusahaan
    {
        return auth()->user()->perusahaan;
    }

    protected function verifikator(): Verifikator
    {
        return auth()->user()->verifikator;
    }
}
