{{-- $orang: Pengguna. $ukuran: '' atau 'lg'. --}}
@if ($orang->urlFoto())
    <img src="{{ $orang->urlFoto() }}" alt="" class="avatar {{ $ukuran ?? '' }}">
@else
    <span class="avatar {{ $ukuran ?? '' }} rona-{{ \App\Support\Format::rona($orang->id) }}" aria-hidden="true">{{ $orang->inisial() }}</span>
@endif
