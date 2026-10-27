@extends('layouts.app')
@section('title', $splitBill->judul)
@section('subtitle', $splitBill->nomor_tagihan . ' · ' . $splitBill->status_badge['label'])
@section('content')
<div class="max-w-lg space-y-4">

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
    <div class="flex items-start justify-between mb-4">
        <div>
            <h2 class="font-bold text-gray-900 text-xl">{{ $splitBill->judul }}</h2>
            @if($splitBill->deskripsi)<p class="text-sm text-gray-400 mt-1">{{ $splitBill->deskripsi }}</p>@endif
        </div>
        <span class="text-xs px-2.5 py-1 rounded-full {{ $splitBill->status_badge['class'] }}">{{ $splitBill->status_badge['label'] }}</span>
    </div>

    <div class="grid grid-cols-3 gap-3 mb-4">
        <div class="bg-indigo-50 rounded-xl p-3 text-center">
            <div class="text-xs text-indigo-400 mb-1">Total</div>
            <div class="font-bold text-indigo-700">{{ $splitBill->total_format }}</div>
        </div>
        <div class="bg-gray-50 rounded-xl p-3 text-center">
            <div class="text-xs text-gray-400 mb-1">Per Orang</div>
            <div class="font-bold text-gray-900">{{ $splitBill->per_orang_format }}</div>
        </div>
        <div class="bg-gray-50 rounded-xl p-3 text-center">
            <div class="text-xs text-gray-400 mb-1">Progress</div>
            <div class="font-bold text-gray-900">{{ $splitBill->progress }}%</div>
        </div>
    </div>

    {{-- Progress bar --}}
    <div class="w-full bg-gray-100 rounded-full h-2.5 mb-2">
        <div class="h-2.5 rounded-full bg-indigo-500 transition-all" style="width:{{ $splitBill->progress }}%"></div>
    </div>
    <div class="text-xs text-gray-400 text-right">{{ $splitBill->members->where('sudah_bayar',true)->count() }}/{{ $splitBill->members->count() }} sudah bayar</div>
</div>

{{-- Daftar Anggota --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-50"><h3 class="text-sm font-semibold text-gray-900">👥 Anggota Tagihan</h3></div>
    <div class="divide-y divide-gray-50">
        @foreach($splitBill->members as $m)
        <div class="px-5 py-3.5 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm {{ $m->sudah_bayar?'bg-green-500':'bg-gray-300' }}">
                {{ substr($m->user->name,0,1) }}
            </div>
            <div class="flex-1">
                <div class="text-sm font-medium text-gray-900">{{ $m->user->name }}
                    @if($m->user_id === $splitBill->dibuat_oleh)<span class="text-xs text-indigo-500 ml-1">· Pembuat</span>@endif
                    @if($m->user_id === auth()->id())<span class="text-xs text-gray-400 ml-1">· Kamu</span>@endif
                </div>
                <div class="text-xs text-gray-400">
                    {{ $m->jumlah_format }}
                    @if($m->sudah_bayar) · ✓ Bayar {{ $m->dibayar_at->format('d M Y') }}
                    @else · ⏳ Belum bayar @endif
                </div>
            </div>
            @if($m->sudah_bayar)
            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded-full">✓ Lunas</span>
            @else
            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded-full">Belum</span>
            @endif
        </div>
        @endforeach
    </div>
</div>

{{-- Tombol Bayar --}}
@if($myMember && !$myMember->sudah_bayar && $splitBill->status === 'aktif')
<div class="bg-white rounded-2xl border border-indigo-200 shadow-sm p-5">
    <h3 class="text-sm font-semibold text-gray-900 mb-3">💳 Bayar Tagihan Kamu</h3>
    <div class="bg-indigo-50 rounded-xl p-3 text-center mb-4">
        <div class="text-xs text-indigo-400 mb-1">Tagihan kamu</div>
        <div class="text-2xl font-bold text-indigo-700">{{ $myMember->jumlah_format }}</div>
    </div>
    <form action="{{ route('splitbill.bayar', $splitBill) }}" method="POST" class="space-y-3">
        @csrf
        <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1">PIN</label>
            <input type="password" name="pin" maxlength="6" inputmode="numeric" required
                   placeholder="••••••" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-center text-2xl tracking-widest focus:outline-none focus:border-indigo-500"/>
        </div>
        <div class="text-xs text-gray-400 flex justify-between">
            <span>Saldo kamu:</span>
            <span class="font-bold text-indigo-600">{{ auth()->user()->wallet->saldo_format }}</span>
        </div>
        <button type="submit" class="w-full text-white font-bold py-3 rounded-xl transition" style="background:linear-gradient(135deg,#6366F1,#A855F7)">
            💸 Bayar {{ $myMember->jumlah_format }}
        </button>
    </form>
</div>
@endif

<a href="{{ route('splitbill.index') }}" class="block text-center text-sm text-gray-500 hover:text-gray-700">← Kembali</a>
</div>
@endsection
