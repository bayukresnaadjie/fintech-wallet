@extends('layouts.app')
@section('title','Dashboard')
@section('subtitle', 'Halo ' . auth()->user()->name . ', ' . now()->translatedFormat('l d F Y'))
@section('header-action')
<div class="flex gap-2">
    <a href="{{ route('topup.create') }}"    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">+ Top Up</a>
    <a href="{{ route('transfer.create') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl transition">💸 Transfer</a>
</div>
@endsection
@section('content')

{{-- Stat Cards --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    <div class="gradient-card rounded-2xl p-5 text-white">
        <div class="text-xs text-indigo-200 mb-2">Saldo Dompet</div>
        <div class="text-2xl font-bold">{{ $wallet->saldo_format }}</div>
        <div class="text-xs text-indigo-200 mt-2">{{ $wallet->nomor_wallet }}</div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-2"><span class="text-xs text-gray-400">Pemasukan Bulan Ini</span><span class="text-green-500">▲</span></div>
        <div class="text-xl font-bold text-green-600">Rp {{ number_format($bulanIni['pemasukan'], 0, ',', '.') }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ now()->translatedFormat('F Y') }}</div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-2"><span class="text-xs text-gray-400">Pengeluaran Bulan Ini</span><span class="text-red-400">▼</span></div>
        <div class="text-xl font-bold text-red-500">Rp {{ number_format($bulanIni['pengeluaran'], 0, ',', '.') }}</div>
        <div class="text-xs text-gray-400 mt-1">{{ now()->translatedFormat('F Y') }}</div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-2"><span class="text-xs text-gray-400">Transaksi Bulan Ini</span><span class="text-indigo-400">💳</span></div>
        <div class="text-2xl font-bold text-gray-900">{{ $bulanIni['transaksi'] }}</div>
        <div class="text-xs text-gray-400 mt-1">kali transaksi</div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-5">
    <h3 class="text-sm font-semibold text-gray-700 mb-4">Aksi Cepat</h3>
    <div class="grid grid-cols-5 gap-3">
        @php
        $actions = [
            ['route'=>'topup.create',     'label'=>'Top Up',    'icon'=>'💰', 'color'=>'indigo'],
            ['route'=>'transfer.create',  'label'=>'Transfer',  'icon'=>'💸', 'color'=>'purple'],
            ['route'=>'withdrawal.create','label'=>'Tarik',     'icon'=>'🏧', 'color'=>'blue'],
            ['route'=>'splitbill.create', 'label'=>'Split Bill','icon'=>'🤝', 'color'=>'pink'],
            ['route'=>'qr.index',         'label'=>'QR Pay',    'icon'=>'📱', 'color'=>'violet'],
        ];
        @endphp
        @foreach($actions as $a)
        <a href="{{ route($a['route']) }}" class="flex flex-col items-center gap-2 p-3 rounded-2xl bg-{{ $a['color'] }}-50 hover:bg-{{ $a['color'] }}-100 transition group">
            <div class="text-2xl">{{ $a['icon'] }}</div>
            <span class="text-xs font-semibold text-{{ $a['color'] }}-700">{{ $a['label'] }}</span>
        </a>
        @endforeach
    </div>
</div>

<div class="grid grid-cols-3 gap-4 mb-4">
    {{-- Grafik Cashflow --}}
    <div class="col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">📈 Cashflow 7 Hari Terakhir</h3>
        @php $maxVal = max($grafikCashflow->max('masuk'), $grafikCashflow->max('keluar'), 1); @endphp
        <div class="flex items-end gap-3 h-28">
            @foreach($grafikCashflow as $g)
            <div class="flex-1 flex flex-col items-center gap-0.5">
                <div class="w-full flex gap-0.5 items-end" style="height:100px">
                    <div class="flex-1 bg-green-400 rounded-t" style="height:{{ max(2,($g['masuk']/$maxVal)*90) }}px" title="Masuk"></div>
                    <div class="flex-1 bg-red-300 rounded-t"   style="height:{{ max(2,($g['keluar']/$maxVal)*90) }}px" title="Keluar"></div>
                </div>
                <span class="text-gray-400" style="font-size:8px">{{ $g['tanggal'] }}</span>
            </div>
            @endforeach
        </div>
        <div class="flex gap-4 mt-3 text-xs">
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-400 rounded"></span> Masuk</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-300 rounded"></span> Keluar</span>
        </div>
    </div>

    {{-- Pengeluaran per Kategori --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">🏷️ Per Kategori</h3>
        @if($perKategori->count() > 0)
        @php $maxKat = $perKategori->max('total'); @endphp
        <div class="space-y-2.5">
            @foreach($perKategori->take(6) as $k)
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-600">{{ \App\Models\Transfer::make(['kategori'=>$k->kategori])->kategori_icon }} {{ ucfirst($k->kategori) }}</span>
                    <span class="font-semibold text-gray-900">Rp {{ number_format($k->total/1000,0,',','.')}}rb</span>
                </div>
                <div class="w-full bg-gray-100 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full bg-indigo-400" style="width:{{ ($k->total/$maxKat)*100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="text-center text-gray-300 py-6 text-sm">Belum ada data</div>
        @endif
    </div>
</div>

{{-- Transaksi Terbaru --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">🕐 Transaksi Terbaru</h3>
        <a href="{{ route('laporan.index') }}" class="text-xs text-indigo-600 hover:underline">Lihat semua</a>
    </div>
    <div class="divide-y divide-gray-50">
        @forelse($transaksiTerbaru as $item)
        <div class="px-5 py-3.5 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full flex items-center justify-center flex-shrink-0 {{ $item['tipe']==='masuk'?'bg-green-100':'bg-red-50' }}">
                <span class="text-lg">{{ $item['tipe']==='topup'?'💰':($item['tipe']==='masuk'?'⬇️':'⬆️') }}</span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium text-gray-900">
                    @if($item['tipe']==='topup')
                        Top Up via {{ $item['data']->metode_label }}
                    @elseif($item['tipe']==='masuk')
                        Dari {{ $item['data']->pengirim->name }}
                    @else
                        Ke {{ $item['data']->penerima->name }}
                    @endif
                </div>
                <div class="text-xs text-gray-400">{{ $item['waktu']->diffForHumans() }}</div>
            </div>
            <div class="text-right">
                <div class="text-sm font-bold {{ $item['tipe']==='keluar'?'text-red-500':'text-green-600' }}">
                    {{ $item['tipe']==='keluar'?'-':'+' }}{{ $item['tipe']==='topup' ? $item['data']->jumlah_format : $item['data']->jumlah_format }}
                </div>
                @php $badge = $item['data']->status_badge; @endphp
                <span class="text-xs {{ $badge['class'] }} px-1.5 py-0.5 rounded-full">{{ $badge['label'] }}</span>
            </div>
        </div>
        @empty
        <div class="px-5 py-10 text-center text-sm text-gray-400">Belum ada transaksi</div>
        @endforelse
    </div>
</div>
@endsection
