@extends('layouts.app')
@section('title','Riwayat Top Up')
@section('header-action')
<a href="{{ route('topup.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">+ Top Up Sekarang</a>
@endsection
@section('content')
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100"><tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">No. Transaksi</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">Metode</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400">Jumlah</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400">Diterima</th>
            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-400">Status</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">Waktu</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($topups as $t)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-mono text-xs text-indigo-600 font-semibold">{{ $t->nomor_transaksi }}</td>
                <td class="px-5 py-3 text-gray-700">{{ $t->metode_label }}</td>
                <td class="px-5 py-3 text-right font-semibold text-gray-900">{{ $t->jumlah_format }}</td>
                <td class="px-5 py-3 text-right font-bold text-green-600">Rp {{ number_format($t->jumlah_diterima, 0, ',', '.') }}</td>
                <td class="px-5 py-3 text-center"><span class="text-xs px-2.5 py-1 rounded-full {{ $t->status_badge['class'] }}">{{ $t->status_badge['label'] }}</span></td>
                <td class="px-5 py-3 text-xs text-gray-400">{{ $t->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-right"><a href="{{ route('topup.show', $t) }}" class="text-xs text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg">Detail</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">Belum ada riwayat top up</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($topups->hasPages())<div class="px-5 py-3 border-t">{{ $topups->links() }}</div>@endif
</div>
@endsection
