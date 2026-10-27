@extends('layouts.app')
@section('title','Tarik Tunai')
@section('header-action')
<a href="{{ route('withdrawal.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">+ Tarik Tunai</a>
@endsection
@section('content')
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100"><tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">No. Transaksi</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">Rekening Tujuan</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400">Jumlah</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400">Diterima</th>
            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-400">Status</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">Waktu</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($withdrawals as $w)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3 font-mono text-xs text-indigo-600 font-semibold">{{ $w->nomor_transaksi }}</td>
                <td class="px-5 py-3">
                    <div class="font-medium text-gray-900 text-sm">{{ $w->bankAccount->nama_bank }}</div>
                    <div class="text-xs text-gray-400 font-mono">{{ $w->bankAccount->nomor_rekening }} · {{ $w->bankAccount->nama_pemilik }}</div>
                </td>
                <td class="px-5 py-3 text-right font-semibold text-red-500">{{ $w->jumlah_format }}</td>
                <td class="px-5 py-3 text-right font-bold text-gray-900">Rp {{ number_format($w->jumlah_diterima,0,',','.') }}</td>
                <td class="px-5 py-3 text-center"><span class="text-xs px-2 py-0.5 rounded-full {{ $w->status_badge['class'] }}">{{ $w->status_badge['label'] }}</span></td>
                <td class="px-5 py-3 text-xs text-gray-400">{{ $w->created_at->format('d M Y H:i') }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-400">Belum ada riwayat tarik tunai</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($withdrawals->hasPages())<div class="px-5 py-3 border-t">{{ $withdrawals->links() }}</div>@endif
</div>
@endsection
