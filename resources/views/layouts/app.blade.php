<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>PayKu — @yield('title','Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .gradient-card { background: linear-gradient(135deg, #6366F1 0%, #8B5CF6 50%, #A855F7 100%); }
        .gradient-green { background: linear-gradient(135deg, #059669 0%, #10B981 100%); }
        .gradient-blue  { background: linear-gradient(135deg, #2563EB 0%, #6366F1 100%); }
    </style>
</head>
<body class="bg-gray-50 font-sans antialiased">
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-64 bg-white border-r border-gray-100 flex flex-col fixed inset-y-0 left-0 z-30 shadow-sm">
        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100">
            <div class="w-9 h-9 gradient-card rounded-xl flex items-center justify-center flex-shrink-0 text-lg">💳</div>
            <div>
                <div class="font-bold text-gray-900 text-sm">PayKu</div>
                <div class="text-xs text-gray-400">Fintech Wallet</div>
            </div>
        </div>

        {{-- Saldo Card --}}
        @auth
        <div class="mx-3 my-3 gradient-card rounded-2xl p-4 text-white">
            <div class="text-xs text-indigo-200 mb-1">Saldo Dompet</div>
            <div class="text-2xl font-bold">{{ auth()->user()->wallet?->saldo_format ?? 'Rp 0' }}</div>
            <div class="text-xs text-indigo-200 mt-2">{{ auth()->user()->wallet?->nomor_wallet }}</div>
        </div>
        @endauth

        <nav class="flex-1 px-3 py-2 overflow-y-auto space-y-0.5">
            @php
            $menu = [
                ['route'=>'dashboard',       'label'=>'Dashboard',    'icon'=>'📊'],
                ['divider'=>'TRANSAKSI'],
                ['route'=>'topup.index',     'label'=>'Top Up',       'icon'=>'💰'],
                ['route'=>'transfer.index',  'label'=>'Transfer',     'icon'=>'💸'],
                ['route'=>'withdrawal.index','label'=>'Tarik Tunai',  'icon'=>'🏧'],
                ['route'=>'qr.index',        'label'=>'QR Payment',   'icon'=>'📱'],
                ['divider'=>'FITUR'],
                ['route'=>'splitbill.index', 'label'=>'Split Bill',   'icon'=>'🤝'],
                ['route'=>'laporan.index',   'label'=>'Laporan',      'icon'=>'📈'],
                ['route'=>'notifikasi.index','label'=>'Notifikasi',   'icon'=>'🔔'],
            ];
            if(auth()->check() && auth()->user()->isAdmin()) {
                $menu[] = ['divider'=>'ADMIN'];
                $menu[] = ['route'=>'admin.dashboard','label'=>'Admin Panel','icon'=>'⚙️'];
            }
            @endphp

            @foreach($menu as $item)
                @if(isset($item['divider']))
                    <div class="pt-3 pb-1 px-3"><span class="text-xs font-semibold text-gray-400 tracking-wider">{{ $item['divider'] }}</span></div>
                @else
                    @php $active = request()->routeIs(explode('.', $item['route'])[0] . '.*') || request()->routeIs($item['route']); @endphp
                    <a href="{{ route($item['route']) }}" class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition {{ $active ? 'bg-indigo-50 text-indigo-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <span>{{ $item['icon'] }}</span>
                        {{ $item['label'] }}
                        @if($item['route']==='notifikasi.index')
                        @php $nc = auth()->user()?->notifikasi()->where('dibaca',false)->count() ?? 0; @endphp
                        @if($nc > 0)<span class="ml-auto bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full" style="font-size:9px">{{ $nc > 9 ? '9+' : $nc }}</span>@endif
                        @endif
                    </a>
                @endif
            @endforeach
        </nav>

        {{-- User --}}
        @auth
        <div class="px-4 py-3 border-t border-gray-100">
            <div class="flex items-center gap-2 px-2 mb-2">
                <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0"/>
                <div class="flex-1 min-w-0">
                    <div class="text-xs font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</div>
                    <div class="flex items-center gap-1">
                        <span class="text-xs {{ auth()->user()->kyc_badge['class'] }} px-1.5 py-0.5 rounded-full" style="font-size:9px">{{ auth()->user()->kyc_badge['icon'] }} {{ auth()->user()->kyc_badge['label'] }}</span>
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="w-full flex items-center gap-2 px-3 py-1.5 text-xs text-gray-500 hover:text-red-500 hover:bg-red-50 rounded-xl transition">🚪 Keluar</button>
            </form>
        </div>
        @endauth
    </aside>

    {{-- Main --}}
    <main class="flex-1 ml-64">
        <header class="bg-white border-b border-gray-100 px-8 py-4 sticky top-0 z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-base font-semibold text-gray-900">@yield('title','Dashboard')</h1>
                    @hasSection('subtitle')<p class="text-xs text-gray-400">@yield('subtitle')</p>@endif
                </div>
                <div class="flex items-center gap-3">@yield('header-action')</div>
            </div>
        </header>

        <div class="px-8 pt-4">
            @foreach(['success'=>'green','error'=>'red','info'=>'blue'] as $type => $color)
            @if(session($type))
            <div class="bg-{{ $color }}-50 border border-{{ $color }}-200 text-{{ $color }}-700 px-4 py-3 rounded-xl text-sm mb-4 flex items-center gap-2">
                <span>{{ $type==='success'?'✓':($type==='error'?'✕':'ℹ') }}</span> {{ session($type) }}
            </div>
            @endif
            @endforeach
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
                <ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif
        </div>

        <div class="px-8 py-5 pb-10">@yield('content')</div>
    </main>
</div>
</body>
</html>
