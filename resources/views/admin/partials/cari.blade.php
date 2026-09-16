{{-- Kolom cari di atas tabel admin. $placeholder opsional. --}}
<form method="GET" class="table-cari" role="search">
    <label class="sr-only" for="q">Cari</label>
    <input id="q" type="search" name="q" value="{{ request('q') }}" class="input" placeholder="{{ $placeholder ?? 'Cari' }}">
    <button class="btn btn-line">Cari</button>
    @if (request('q'))<a href="{{ url()->current() }}" class="btn btn-ghost">Reset</a>@endif
</form>
