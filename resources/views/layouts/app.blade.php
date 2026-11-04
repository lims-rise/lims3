<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'LIMS3.0')</title>
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="{{ asset('vendor/bootstrap-5.3.3-dist/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"> -->
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> -->
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"> -->

<style>
        body { background: #f4f6fb; }

        .sidebar { width: 280px; background: #14213d; color: #fff; position: fixed; top: 0; bottom: 0; left: 0;
                   overflow-y: auto; overscroll-behavior: contain;
                   scrollbar-width: thin; scrollbar-color: rgba(255,255,255,.25) transparent; }
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,.25); border-radius: 3px; }

        .sidebar a.brand { padding: 1.25rem 1rem; text-align: center; font-size: 1.6rem; font-weight: 300; color: #fff;
                           border-bottom: 1px solid rgba(255,255,255,.1); }
        .sidebar a.brand strong { font-weight: 700; }
        .sidebar a.brand:hover { background: transparent; }

        .sidebar a { color: #cdd6ea; text-decoration: none; display: block; padding: .6rem 1.5rem; }
        .sidebar a:hover { background: rgba(255,255,255,.08); color: #fff; }
        .sidebar a.active { background: rgba(255,255,255,.14); color: #fff; }

        /* submenu: tighter indent, smaller font, wrapped lines stay aligned with the text */
        .sidebar .sub a { display: flex; align-items: flex-start; gap: .55rem;
                          padding: .5rem 1rem .5rem 2.1rem; font-size: .88rem; line-height: 1.3; }
        .sidebar .sub a i { margin-top: .12rem; }

        .content { margin-left: 280px; padding: 1.5rem; }
        .topbar { background: #fff; border-radius: 12px; padding: .75rem 1.25rem; margin-bottom: 1.25rem; box-shadow: 0 1px 4px rgba(0,0,0,.06); }
    </style>
    @stack('styles')
</head>
<body>
    <aside class="sidebar">
        <a href="{{ route('dashboard') }}" class="brand">RISE|<strong>LIMS3.0</strong></a>
        <form method="POST" action="{{ route('lab.switch') }}" class="px-3 py-3">
            @csrf
            <select name="lab" class="form-select" onchange="this.form.submit()" aria-label="Laboratory country">
                @foreach (\App\Support\Lab::LABS as $code => $name)
                    <option value="{{ $code }}" @selected(\App\Support\Lab::current() === (string) $code)>{{ $name }}</option>
                @endforeach
            </select>
        </form>    
        @foreach ($parentMenus ?? [] as $parent)
            @php
                $kids = $childMenus[$parent->id_menu];
                $open = $kids->contains(fn ($k) => request()->is('m/'.$k->url));
            @endphp
            <a data-bs-toggle="collapse" href="#menu-{{ $parent->id_menu }}" role="button"
            aria-expanded="{{ $open ? 'true' : 'false' }}">
                <!-- <i class="bi bi-flask me-2"></i> {{ $parent->title }} -->
                <i class="{{ $parent->icon }} me-2"></i> {{ $parent->title }}
                <i class="fa fa-chevron-down float-end small mt-1"></i>
            </a>
            <div class="collapse sub {{ $open ? 'show' : '' }}" id="menu-{{ $parent->id_menu }}">
                @foreach ($kids as $child)
                    @if ($child->url === '#')
                        <hr class="my-1 mx-4 border-secondary opacity-50">
                    @else
                        <a href="{{ url('m/'.$child->url) }}"
                        class="{{ request()->is('m/'.$child->url) ? 'active' : '' }}">
                            <i class="{{ $child->icon }} fa-fw"></i>
                            <span>{!! str_replace('&lt;br&gt;', '<br>', e($child->title)) !!}</span>
                        </a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </aside>

    <main class="content">
        <div class="topbar d-flex justify-content-between align-items-center">
            <h5 class="mb-0">@yield('title', 'LIMS')</h5>
            <form method="POST" action="{{ route('logout') }}" class="d-flex align-items-center gap-3">
                @csrf
                <span class="text-muted small">{{ auth()->user()->full_name }}</span>
                <button class="btn btn-sm btn-outline-secondary">Sign out</button>
            </form>
        </div>
        @yield('content')
    </main>

    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script> -->
<!-- Ganti CDN JS Bootstrap dengan yang lokal -->
<script src="{{ asset('vendor/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js') }}"></script>    
    @stack('scripts')

    <script>
        (function () {
            const side = document.querySelector('.sidebar');
            const active = side && side.querySelector('a.active');
            if (active) { side.scrollTop = active.offsetTop - side.clientHeight / 2; }
        })();
    </script>    
</body>
</html>