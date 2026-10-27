@extends('layouts.app')
@section('title','Konfirmasi Pembayaran QR')
@section('content')
<div class="max-w-md">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

    <div class="text-center mb-6">
        <img src="{{ $qr->user->avatar_url }}" class="w-16 h-16 rounded-full mx-auto mb-3 border-4 border-indigo-100"/>
        <div class="font-bold text-gray-900 text-lg">{{ $qr->user->name }}</div>
        <div class="text-xs font-mono text-indigo-600">{{ $qr->user->wallet->nomor_wallet }}</div>
        @if($qr->catatan)<div class="text-sm text-gray-500 mt-1">{{ $qr->catatan }}</div>@endif
    </div>

    <form action="{{ route('qr.bayar', $qr->kode) }}" method="POST" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">Nominal Pembayaran <span class="text-red-500">*</span></label>
            @if($qr->jumlah)
            {{-- Nominal fixed --}}
            <div class="bg-indigo-50 border-2 border-indigo-200 rounded-xl px-4 py-3 text-center">
                <span class="text-2xl font-bold text-indigo-700">Rp {{ number_format($qr->jumlah, 0, ',', '.') }}</span>
                <div class="text-xs text-indigo-400 mt-1">Nominal sudah ditentukan</div>
            </div>
            <input type="hidden" name="jumlah" value="{{ $qr->jumlah }}"/>
            @else
            {{-- Nominal bebas --}}
            <div class="relative">
                <span class="absolute left-3.5 top-3 text-gray-400 text-sm font-semibold">Rp</span>
                <input type="number" name="jumlah" min="1000" required placeholder="0"
                       class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm font-semibold focus:outline-none focus:border-indigo-500"/>
            </div>
            @endif
        </div>

        <div class="bg-gray-50 rounded-xl p-3 text-xs text-gray-500 flex justify-between">
            <span>Saldo kamu:</span>
            <span class="font-bold text-indigo-600">{{ auth()->user()->wallet->saldo_format }}</span>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">PIN <span class="text-red-500">*</span></label>
            <input type="password" name="pin" maxlength="6" inputmode="numeric" required
                   placeholder="••••••" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-center text-2xl tracking-widest focus:outline-none focus:border-indigo-500"/>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('qr.index') }}" class="text-center bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-3 rounded-xl transition">Batal</a>
            <button type="submit" class="text-white font-bold py-3 rounded-xl transition" style="background:linear-gradient(135deg,#6366F1,#A855F7)">✓ Bayar</button>
        </div>
    </form>
</div>
</div>
@endsection
