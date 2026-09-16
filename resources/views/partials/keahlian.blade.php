{{-- Satu klaim keahlian (dimuat dengan scope denganStatus). Centang biru kalau terverifikasi. --}}
<span class="chip" @if ($k->terverifikasi) title="Terverifikasi" @endif>
    {{ $k->keahlian->nama }}
    @if ($k->level_klaim)<span class="lvl">{{ \App\Support\Format::level($k->level_klaim) }}</span>@endif
    @if ($k->terverifikasi)<i class="bi bi-patch-check-fill" aria-label="terverifikasi"></i>@endif
</span>
