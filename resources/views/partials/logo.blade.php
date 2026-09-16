{{-- $perusahaan: Perusahaan. $ukuran: '' atau 'lg'. --}}
@if ($perusahaan->urlLogo())
    <img src="{{ $perusahaan->urlLogo() }}" alt="Logo {{ $perusahaan->nama }}" class="logo {{ $ukuran ?? '' }}" style="background:#fff">
@else
    <span class="logo {{ $ukuran ?? '' }} rona-{{ \App\Support\Format::rona('p' . $perusahaan->id) }}" aria-hidden="true">{{ \App\Support\Format::inisial($perusahaan->nama) }}</span>
@endif
