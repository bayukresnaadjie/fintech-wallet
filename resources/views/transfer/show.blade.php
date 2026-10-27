@extends('layouts.app')
@section('title','Detail Transfer')
@section('subtitle', $transfer->nomor_transaksi)
@section('content')
<div class="max-w-lg">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
    @php $isMasuk = $transfer->penerima_id === auth()->id(); @endphp
    <div class="text-center mb-6">
        <div class="text-5xl mb-3">{{ $transfer->status==='success'?($isMasuk?'💰':'💸'):'❌' }}</div>
        <div class="text-2xl font-bold {{ $isMasuk?'text-green-600':'text-red-500' }}">
            {{ $isMasuk?'+':'-' }}{{ $transfer->jumlah_format }}
        </div>
        <span class="text-xs px-3 py-1 rounded-full {{ $transfer->status_badge['class'] }} mt-2 inline-block">{{ $transfer->status_badge['label'] }}</span>
    </div>

    <div class="space-y-3 text-sm">
        <div class="flex justify-between py-2.5 border-b border-gray-50"><span class="text-gray-400">No. Transaksi</span><span class="font-mono text-xs font-semibold text-indigo-600">{{ $transfer->nomor_transaksi }}</span></div>
        <div class="flex justify-between py-2.5 border-b border-gray-50"><span class="text-gray-400">Pengirim</span>
            <div class="text-right"><div class="font-semibold text-gray-900">{{ $transfer->pengirim->name }}</div><div class="text-xs text-gray-400">{{ $transfer->walletPengirim->nomor_wallet }}</div></div>
        </div>
        <div class="flex justify-between py-2.5 border-b border-gray-50"><span class="text-gray-400">Penerima</span>
            <div class="text-right"><div class="font-semibold text-gray-900">{{ $transfer->penerima->name }}</div><div class="text-xs text-gray-400">{{ $transfer->walletPenerima->nomor_wallet }}</div></div>
        </div>
        <div class="flex justify-between py-2.5 border-b border-gray-50"><span class="text-gray-400">Jumlah</span><span class="font-semibold">{{ $transfer->jumlah_format }}</span></div>
        <div class="flex justify-between py-2.5 border-b border-gray-50"><span class="text-gray-400">Biaya Admin</span><span>{{ $transfer->biaya_admin > 0 ? 'Rp '.number_format($transfer->biaya_admin,0,',','.') : 'Gratis' }}</span></div>
        <div class="flex justify-between py-2.5 border-b border-gray-50"><span class="text-gray-400">Diterima</span><span class="font-bold text-green-600">Rp {{ number_format($transfer->jumlah_diterima,0,',','.') }}</span></div>
        @if($transfer->catatan)<div class="flex justify-between py-2.5 border-b border-gray-50"><span class="text-gray-400">Catatan</span><span class="text-gray-700">{{ $transfer->catatan }}</span></div>@endif
        @if($transfer->kategori)<div class="flex justify-between py-2.5 border-b border-gray-50"><span class="text-gray-400">Kategori</span><span>{{ $transfer->kategori_icon }} {{ ucfirst($transfer->kategori) }}</span></div>@endif
        <div class="flex justify-between py-2.5"><span class="text-gray-400">Waktu</span><span class="text-gray-700">{{ $transfer->created_at->format('d M Y H:i:s') }}</span></div>
    </div>
</div>
<a href="{{ route('transfer.index') }}" class="block text-center mt-4 text-sm text-gray-500 hover:text-gray-700">← Kembali ke riwayat</a>
</div>
@endsection
