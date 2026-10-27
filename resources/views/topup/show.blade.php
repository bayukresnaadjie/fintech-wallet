@extends('layouts.app')
@section('title','Detail Top Up')
@section('subtitle', $topup->nomor_transaksi)
@section('content')
<div class="max-w-lg space-y-4">

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
    <div class="text-center mb-6">
        <div class="text-4xl mb-2">{{ $topup->status==='success'?'✅':($topup->status==='pending'?'⏳':'❌') }}</div>
        <div class="text-xl font-bold text-gray-900">{{ $topup->jumlah_format }}</div>
        <span class="text-xs px-3 py-1 rounded-full {{ $topup->status_badge['class'] }} mt-2 inline-block">{{ $topup->status_badge['label'] }}</span>
    </div>

    <div class="space-y-3 text-sm">
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">No. Transaksi</span><span class="font-mono font-semibold text-indigo-600">{{ $topup->nomor_transaksi }}</span></div>
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Metode</span><span class="font-semibold">{{ $topup->metode_label }}</span></div>
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Jumlah</span><span class="font-semibold">{{ $topup->jumlah_format }}</span></div>
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Biaya Admin</span><span>Rp {{ number_format($topup->biaya_admin, 0, ',', '.') }}</span></div>
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Diterima</span><span class="font-bold text-green-600">Rp {{ number_format($topup->jumlah_diterima, 0, ',', '.') }}</span></div>
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Waktu</span><span>{{ $topup->created_at->format('d M Y H:i') }}</span></div>
        @if($topup->expired_at && $topup->status==='pending')
        <div class="flex justify-between py-2 border-b border-gray-50"><span class="text-gray-400">Expired</span><span class="text-red-500 font-semibold">{{ $topup->expired_at->format('d M Y H:i') }}</span></div>
        @endif
    </div>

    @if($topup->status === 'pending')
    {{-- Instruksi Pembayaran --}}
    <div class="mt-5 bg-amber-50 border border-amber-200 rounded-xl p-4">
        <div class="font-semibold text-amber-800 text-sm mb-3">📋 Instruksi Pembayaran</div>
        @if($topup->metode === 'virtual_account')
        <div class="text-sm text-amber-700 space-y-2">
            <div>Transfer ke <strong>Virtual Account:</strong></div>
            <div class="bg-white rounded-lg px-4 py-3 font-mono text-lg font-bold text-center text-indigo-700 tracking-widest">{{ $topup->nomor_va }}</div>
            <div class="text-xs">Total transfer: <strong>Rp {{ number_format($topup->jumlah, 0, ',', '.') }}</strong></div>
        </div>
        @elseif($topup->metode === 'transfer_bank')
        <div class="text-sm text-amber-700 space-y-1">
            <div>Transfer ke rekening:</div>
            <div class="text-xs"><strong>Bank:</strong> BCA · <strong>No. Rek:</strong> 1234-5678-90 · <strong>A.N:</strong> PayKu Indonesia</div>
            <div class="text-xs">Total transfer: <strong>Rp {{ number_format($topup->jumlah + $topup->biaya_admin, 0, ',', '.') }}</strong></div>
        </div>
        {{-- Upload Bukti --}}
        <form action="{{ route('topup.bukti', $topup) }}" method="POST" enctype="multipart/form-data" class="mt-3">
            @csrf
            <div class="text-xs font-semibold text-amber-800 mb-1">Upload Bukti Transfer:</div>
            <div class="flex gap-2">
                <input type="file" name="bukti" accept="image/*" required class="flex-1 text-xs border border-amber-300 rounded-lg px-2 py-1.5"/>
                <button type="submit" class="bg-amber-600 text-white text-xs font-medium px-3 py-1.5 rounded-lg">Upload</button>
            </div>
        </form>
        @endif
    </div>
    @endif

    @if($topup->bukti_transfer)
    <div class="mt-4">
        <div class="text-xs font-semibold text-gray-500 mb-2">Bukti Transfer:</div>
        <img src="{{ asset('storage/'.$topup->bukti_transfer) }}" class="w-full rounded-xl border border-gray-200"/>
    </div>
    @endif

    {{-- Admin konfirmasi --}}
    @if(auth()->user()->isAdmin() && $topup->status === 'pending')
    <div class="mt-5 pt-4 border-t border-gray-100">
        <div class="text-xs font-semibold text-gray-600 mb-2">⚙️ Konfirmasi Admin:</div>
        <form action="{{ route('topup.konfirmasi', $topup) }}" method="POST" class="flex gap-2">
            @csrf @method('PATCH')
            <button name="aksi" value="approve" class="flex-1 bg-green-600 text-white text-sm font-medium py-2.5 rounded-xl hover:bg-green-700 transition">✓ Approve</button>
            <button name="aksi" value="reject"  class="flex-1 bg-red-100 text-red-600 text-sm font-medium py-2.5 rounded-xl hover:bg-red-200 transition" onclick="return confirm('Tolak top up ini?')">✕ Tolak</button>
        </form>
    </div>
    @endif
</div>

<a href="{{ route('topup.index') }}" class="block text-center text-sm text-gray-500 hover:text-gray-700">← Kembali ke riwayat</a>
</div>
@endsection
