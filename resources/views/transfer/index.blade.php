@extends('layouts.app')
@section('title','Riwayat Transfer')
@section('header-action')
<a href="{{ route('transfer.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">💸 Transfer</a>
@endsection
@section('content')
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100"><tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">Transaksi</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">Dari / Ke</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">Kategori</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400">Jumlah</th>
            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-400">Status</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">Waktu</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($transfers as $t)
            @php $isMasuk = $t->penerima_id === auth()->id(); @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <div class="font-mono text-xs text-indigo-600 font-semibold">{{ $t->nomor_transaksi }}</div>
                    <div class="text-xs text-gray-400 mt-0.5">{{ $t->catatan ?? '—' }}</div>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ $isMasuk ? '⬇️':'⬆️' }}</span>
                        <div>
                            <div class="text-xs font-medium text-gray-900">{{ $isMasuk ? $t->pengirim->name : $t->penerima->name }}</div>
                            <div class="text-xs text-gray-400">{{ $isMasuk ? 'Dari':'Ke' }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3"><span class="text-sm">{{ $t->kategori_icon }}</span> <span class="text-xs text-gray-500">{{ ucfirst($t->kategori ?? '—') }}</span></td>
                <td class="px-5 py-3 text-right font-bold {{ $isMasuk?'text-green-600':'text-red-500' }}">
                    {{ $isMasuk?'+':'-' }}{{ $t->jumlah_format }}
                </td>
                <td class="px-5 py-3 text-center"><span class="text-xs px-2 py-0.5 rounded-full {{ $t->status_badge['class'] }}">{{ $t->status_badge['label'] }}</span></td>
                <td class="px-5 py-3 text-xs text-gray-400">{{ $t->created_at->format('d M Y H:i') }}</td>
                <td class="px-5 py-3 text-right"><a href="{{ route('transfer.show', $t) }}" class="text-xs text-indigo-600 bg-indigo-50 hover:bg-indigo-100 px-2.5 py-1 rounded-lg">Detail</a></td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">Belum ada riwayat transfer</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($transfers->hasPages())<div class="px-5 py-3 border-t">{{ $transfers->links() }}</div>@endif
</div>
@endsection
