@extends('layouts.app')
@section('title','Split Bill')
@section('header-action')
<a href="{{ route('splitbill.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-xl transition">🤝 Buat Tagihan</a>
@endsection
@section('content')
<div class="grid grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($tagihan as $t)
    <a href="{{ route('splitbill.show', $t) }}" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition block">
        <div class="flex items-start justify-between mb-3">
            <div>
                <div class="font-semibold text-gray-900">{{ $t->judul }}</div>
                <div class="text-xs text-gray-400 mt-0.5">{{ $t->pembuatTagihan->name }}</div>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full {{ $t->status_badge['class'] }}">{{ $t->status_badge['label'] }}</span>
        </div>

        <div class="text-2xl font-bold text-indigo-700 mb-1">{{ $t->total_format }}</div>
        <div class="text-xs text-gray-400 mb-3">{{ $t->per_orang_format }} × {{ $t->jumlah_orang }} orang</div>

        {{-- Progress --}}
        <div class="flex items-center justify-between text-xs mb-1.5">
            <span class="text-gray-400">{{ $t->members->where('sudah_bayar',true)->count() }}/{{ $t->members->count() }} sudah bayar</span>
            <span class="font-bold text-indigo-600">{{ $t->progress }}%</span>
        </div>
        <div class="w-full bg-gray-100 rounded-full h-2">
            <div class="h-2 rounded-full bg-indigo-500 transition-all" style="width:{{ $t->progress }}%"></div>
        </div>

        {{-- Member avatars --}}
        <div class="flex -space-x-1.5 mt-3">
            @foreach($t->members->take(5) as $m)
            <div class="w-6 h-6 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-bold {{ $m->sudah_bayar?'bg-green-500':'bg-gray-300' }}" title="{{ $m->user->name }}">
                {{ substr($m->user->name,0,1) }}
            </div>
            @endforeach
        </div>

        @if($t->jatuh_tempo)
        <div class="text-xs text-{{ $t->jatuh_tempo->isPast() && $t->status!=='lunas' ? 'red':'gray' }}-400 mt-2">
            📅 Jatuh tempo: {{ $t->jatuh_tempo->format('d M Y') }}
        </div>
        @endif
    </a>
    @empty
    <div class="col-span-3 bg-white rounded-2xl border border-gray-100 p-12 text-center">
        <div class="text-5xl mb-3">🤝</div>
        <div class="text-gray-500 text-sm mb-3">Belum ada split bill</div>
        <a href="{{ route('splitbill.create') }}" class="bg-indigo-600 text-white text-sm px-4 py-2 rounded-xl inline-block">Buat Split Bill</a>
    </div>
    @endforelse
</div>
@if($tagihan->hasPages())<div class="mt-4">{{ $tagihan->links() }}</div>@endif
@endsection
