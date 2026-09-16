@if (session('success'))
    <div class="flash sukses" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i><div>{{ session('success') }}</div></div>
@endif
@if (session('error'))
    <div class="flash gagal" role="alert"><i class="bi bi-exclamation-circle" aria-hidden="true"></i><div>{{ session('error') }}</div></div>
@endif
@if ($errors->any() && ! ($sembunyikanError ?? false))
    <div class="flash gagal" role="alert">
        <i class="bi bi-exclamation-circle" aria-hidden="true"></i>
        <div>
            @if ($errors->count() === 1)
                {{ $errors->first() }}
            @else
                Ada {{ $errors->count() }} isian yang perlu diperbaiki:
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            @endif
        </div>
    </div>
@endif
