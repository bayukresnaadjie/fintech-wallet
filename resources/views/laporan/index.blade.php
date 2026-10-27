@extends('layouts.app')
@section('title','Laporan Keuangan')
@section('content')
<form method="GET" class="flex gap-3 mb-5 items-center">
    <label class="text-xs font-semibold text-gray-600">Bulan:</label>
    <input type="month" name="bulan" value="{{ $bulan }}" class="border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
    <button type="submit" class="bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl">Tampilkan</button>
</form>

{{-- Ringkasan --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-5">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="text-xs text-gray-400 mb-1">Total Pemasukan</div>
        <div class="text-xl font-bold text-green-600">Rp {{ number_format($ringkasan['total_masuk'],0,',','.') }}</div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="text-xs text-gray-400 mb-1">Total Pengeluaran</div>
        <div class="text-xl font-bold text-red-500">Rp {{ number_format($ringkasan['total_keluar'],0,',','.') }}</div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="text-xs text-gray-400 mb-1">Selisih</div>
        @php $selisih = $ringkasan['total_masuk'] - $ringkasan['total_keluar']; @endphp
        <div class="text-xl font-bold {{ $selisih >= 0 ? 'text-indigo-600':'text-red-600' }}">Rp {{ number_format(abs($selisih),0,',','.') }}</div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
        <div class="text-xs text-gray-400 mb-1">Total Transfer</div>
        <div class="text-xl font-bold text-gray-900">{{ $ringkasan['total_transfer'] }}×</div>
    </div>
</div>

<div class="grid grid-cols-3 gap-4 mb-4">
    {{-- Grafik harian --}}
    <div class="col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">📊 Cashflow Harian</h3>
        @php $maxVal = max($grafikHarian->max('masuk'), $grafikHarian->max('keluar'), 1); @endphp
        <div class="flex items-end gap-0.5 h-24 overflow-x-auto">
            @foreach($grafikHarian as $g)
            <div class="flex-shrink-0 flex flex-col items-center gap-0.5" style="min-width:12px">
                <div class="w-full flex gap-0.5 items-end" style="height:88px">
                    <div class="flex-1 bg-green-400 rounded-t" style="height:{{ max(1,($g['masuk']/$maxVal)*80) }}px"></div>
                    <div class="flex-1 bg-red-300 rounded-t"   style="height:{{ max(1,($g['keluar']/$maxVal)*80) }}px"></div>
                </div>
                <span class="text-gray-400" style="font-size:7px">{{ $g['tgl'] }}</span>
            </div>
            @endforeach
        </div>
        <div class="flex gap-4 mt-2 text-xs">
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-400 rounded"></span> Masuk</span>
            <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-300 rounded"></span> Keluar</span>
        </div>
    </div>

    {{-- Per Kategori --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-900 mb-3">🏷️ Per Kategori</h3>
        @if($perKategori->count() > 0)
        @php $maxKat = $perKategori->max('total'); @endphp
        <div class="space-y-2.5">
            @foreach($perKategori as $k)
            <div>
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-gray-600">{{ ucfirst($k->kategori) }}</span>
                    <span class="font-semibold">Rp {{ number_format($k->total/1000,0,',','.')}}rb</span>
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

{{-- Riwayat Ledger --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50"><h3 class="text-sm font-semibold text-gray-900">📋 Riwayat Mutasi</h3></div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50"><tr>
            <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-400">Waktu</th>
            <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-400">Keterangan</th>
            <th class="text-left px-5 py-2.5 text-xs font-semibold text-gray-400">Referensi</th>
            <th class="text-right px-5 py-2.5 text-xs font-semibold text-gray-400">Jumlah</th>
            <th class="text-right px-5 py-2.5 text-xs font-semibold text-gray-400">Saldo</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($ledger as $l)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-2.5 text-xs text-gray-400 whitespace-nowrap">{{ $l->created_at->format('d M H:i') }}</td>
                <td class="px-5 py-2.5 text-gray-700 text-xs">{{ $l->keterangan }}</td>
                <td class="px-5 py-2.5 font-mono text-xs text-indigo-600">{{ $l->referensi }}</td>
                <td class="px-5 py-2.5 text-right font-bold text-xs {{ $l->tipe==='credit'?'text-green-600':'text-red-500' }}">
                    {{ $l->tipe_label['sign'] }}{{ $l->jumlah_format }}
                </td>
                <td class="px-5 py-2.5 text-right text-xs text-gray-500">Rp {{ number_format($l->saldo_sesudah,0,',','.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">Tidak ada mutasi di bulan ini</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($ledger->hasPages())<div class="px-5 py-3 border-t">{{ $ledger->appends(request()->query())->links() }}</div>@endif
</div>
@endsection
