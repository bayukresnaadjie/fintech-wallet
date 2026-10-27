@extends('layouts.app')
@section('title','Notifikasi')
@section('header-action')
<form action="{{ route('notifikasi.hapus-semua') }}" method="POST">
    @csrf @method('DELETE')
    <button class="text-sm text-red-500 bg-red-50 hover:bg-red-100 px-4 py-2 rounded-xl transition">🗑️ Hapus Semua</button>
</form>
@endsection
@section('content')
<div class="max-w-2xl">
    @if($notifikasi->count() > 0)
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50">
        @foreach($notifikasi as $n)
        <div class="px-5 py-4 flex items-start gap-3 {{ !$n->dibaca?'bg-indigo-50/40':'' }}">
            <div class="w-10 h-10 rounded-full flex items-center justify-center text-xl flex-shrink-0
                {{ $n->tipe==='topup'?'bg-green-100':($n->tipe==='transfer'?'bg-blue-100':($n->tipe==='withdraw'?'bg-orange-100':($n->tipe==='split'?'bg-purple-100':'bg-gray-100'))) }}">
                {{ $n->tipe_icon }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-0.5">
                    <span class="text-sm font-semibold text-gray-900">{{ $n->judul }}</span>
                    @if(!$n->dibaca)<span class="w-2 h-2 bg-indigo-500 rounded-full flex-shrink-0"></span>@endif
                </div>
                <p class="text-sm text-gray-600">{{ $n->pesan }}</p>
                @if($n->jumlah)
                <div class="text-sm font-bold text-indigo-600 mt-1">Rp {{ number_format($n->jumlah,0,',','.') }}</div>
                @endif
                <div class="text-xs text-gray-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
            </div>
        </div>
        @endforeach
    </div>
    @if($notifikasi->hasPages())<div class="mt-4">{{ $notifikasi->links() }}</div>@endif
    @else
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-12 text-center">
        <div class="text-5xl mb-3">🔔</div>
        <div class="text-gray-500 text-sm">Tidak ada notifikasi</div>
    </div>
    @endif
</div>
@endsection
