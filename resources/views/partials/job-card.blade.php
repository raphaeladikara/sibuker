{{--
    $l: Lowongan (dengan perusahaan, syarat.keahlian)
    $unggulan: bool, $label: teks tab di atas kartu
    $cocok: hasil Kecocokan::hitung atau null
--}}
@php $unggulan = $unggulan ?? false; @endphp
<article @class(['job-card', 'unggulan' => $unggulan])>
    @if ($unggulan && ($label ?? null))
        <span class="tab-label">{{ $label }}</span>
    @endif
    <div class="top">
        @include('partials.logo', ['perusahaan' => $l->perusahaan])
        <span class="pill {{ ['penuh_waktu' => 't-indigo', 'paruh_waktu' => 't-blue', 'kontrak' => 't-amber', 'magang' => 't-green', 'freelance' => 't-violet'][$l->tipe_pekerjaan] ?? '' }}">{{ \App\Support\Format::tipe($l->tipe_pekerjaan) }}</span>
    </div>
    <h3><a href="{{ route('lowongan.show', $l) }}">{{ $l->posisi }}</a></h3>
    <div class="meta">{{ $l->perusahaan->nama }}</div>
    <div class="meta"><i class="bi bi-geo-alt" aria-hidden="true"></i> {{ $l->lokasi ?: 'Lokasi belum diisi' }}</div>

    <div class="chips">
        @foreach ($l->syarat->take(3) as $s)
            <span class="chip" @if ($s->wajib_terverifikasi) title="Harus terverifikasi" @endif>
                {{ $s->keahlian->nama }}
                @if ($s->wajib_terverifikasi)<i class="bi bi-patch-check-fill" aria-label="harus terverifikasi"></i>@endif
            </span>
        @endforeach
        @if ($l->syarat->count() > 3)
            <span class="chip redup">+{{ $l->syarat->count() - 3 }}</span>
        @endif
    </div>

    @if ($cocok ?? null)
        <div class="skor">Cocok <b class="num">{{ \App\Support\Format::skor($cocok['skor']) }}%</b> &middot; {{ $cocok['memenuhi_wajib'] ? 'syarat wajib terpenuhi' : 'ada syarat wajib yang kurang' }}</div>
    @endif

    <div class="actions">
        <a href="{{ route('lowongan.show', $l) }}" class="btn btn-sm">Lihat detail</a>
    </div>
</article>
