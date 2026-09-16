{{-- Strip pencarian lowongan publik. Nilai awal diambil dari query string. --}}
<div class="searchbar">
    <form action="{{ route('lowongan.index') }}" method="GET" role="search">
        <label class="seg">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="sr-only">Kata kunci</span>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Posisi, perusahaan, atau kata kunci">
        </label>
        <label class="seg">
            <i class="bi bi-geo-alt-fill" aria-hidden="true"></i>
            <span class="sr-only">Lokasi</span>
            <input type="text" name="lokasi" value="{{ request('lokasi') }}" placeholder="Lokasi">
        </label>
        <label class="seg">
            <i class="bi bi-briefcase-fill" aria-hidden="true"></i>
            <span class="sr-only">Tipe pekerjaan</span>
            <select name="tipe[]">
                <option value="">Semua tipe</option>
                @foreach (\App\Support\Format::TIPE_PEKERJAAN as $nilai => $teks)
                    <option value="{{ $nilai }}" @selected(in_array($nilai, (array) request('tipe', [])))>{{ $teks }}</option>
                @endforeach
            </select>
        </label>
        <label class="seg seg-opsional">
            <i class="bi bi-patch-check-fill" aria-hidden="true"></i>
            <span class="sr-only">Syarat verifikasi</span>
            <select name="verifikasi">
                <option value="">Semua syarat</option>
                <option value="tidak_wajib" @selected(request('verifikasi') === 'tidak_wajib')>Tanpa centang biru wajib</option>
            </select>
        </label>
        <button type="submit">Cari</button>
    </form>
</div>
