@extends('layouts.app')
@section('title','Admin Panel')
@section('subtitle','Monitor & kelola semua transaksi')
@section('content')

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-5">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="text-xs text-gray-400 mb-1">Total User</div>
        <div class="text-2xl font-bold text-gray-900">{{ $stats['total_user'] }}</div>
        <div class="text-xs text-amber-500 mt-1">{{ $stats['kyc_pending'] }} KYC pending</div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="text-xs text-gray-400 mb-1">Top Up Pending</div>
        <div class="text-2xl font-bold {{ $stats['topup_pending'] > 0 ? 'text-amber-600':'text-gray-300' }}">{{ $stats['topup_pending'] }}</div>
        <div class="text-xs text-gray-400 mt-1">menunggu konfirmasi</div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="text-xs text-gray-400 mb-1">Withdrawal Pending</div>
        <div class="text-2xl font-bold {{ $stats['wd_pending'] > 0 ? 'text-red-500':'text-gray-300' }}">{{ $stats['wd_pending'] }}</div>
        <div class="text-xs text-gray-400 mt-1">menunggu proses</div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="text-xs text-gray-400 mb-1">Total Volume Transfer</div>
        <div class="text-xl font-bold text-indigo-600">Rp {{ number_format($stats['total_transfer']/1000000,1) }}jt</div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
        <div class="text-xs text-gray-400 mb-1">Total Top Up Masuk</div>
        <div class="text-xl font-bold text-green-600">Rp {{ number_format($stats['total_topup']/1000000,1) }}jt</div>
    </div>
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 flex items-center justify-center">
        <a href="{{ route('admin.users') }}" class="text-sm text-indigo-600 hover:underline font-medium">Kelola Semua User →</a>
    </div>
</div>

<div class="grid grid-cols-2 gap-4 mb-4">
    {{-- Top Up Pending --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">💰 Top Up Pending</h3>
            <span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">{{ $topupPending->count() }}</span>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($topupPending as $t)
            <div class="px-5 py-3 flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $t->user->name }}</div>
                    <div class="text-xs text-gray-400 font-mono">{{ $t->nomor_transaksi }}</div>
                    <div class="text-xs text-gray-500">{{ $t->metode_label }}</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-green-600 text-sm">{{ $t->jumlah_format }}</div>
                    <div class="flex gap-1 mt-1">
                        <form action="{{ route('topup.konfirmasi', $t) }}" method="POST" class="flex gap-1">
                            @csrf @method('PATCH')
                            <button name="aksi" value="approve" class="text-xs bg-green-600 text-white px-2 py-1 rounded-lg hover:bg-green-700">✓</button>
                            <button name="aksi" value="reject"  class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-lg hover:bg-red-200" onclick="return confirm('Tolak?')">✕</button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-gray-400">Tidak ada top up pending</div>
            @endforelse
        </div>
    </div>

    {{-- Withdrawal Pending --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
        <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900">🏧 Withdrawal Pending</h3>
            <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">{{ $wdPending->count() }}</span>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($wdPending as $w)
            <div class="px-5 py-3 flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $w->user->name }}</div>
                    <div class="text-xs text-gray-400">{{ $w->bankAccount->nama_bank }} — {{ $w->bankAccount->nomor_rekening }}</div>
                    <div class="text-xs text-gray-500">{{ $w->created_at->diffForHumans() }}</div>
                </div>
                <div class="text-right">
                    <div class="font-bold text-red-500 text-sm">{{ $w->jumlah_format }}</div>
                    <div class="flex gap-1 mt-1">
                        <form action="{{ route('withdrawal.proses', $w) }}" method="POST" class="flex gap-1">
                            @csrf @method('PATCH')
                            <button name="aksi" value="approve" class="text-xs bg-green-600 text-white px-2 py-1 rounded-lg hover:bg-green-700">✓ Proses</button>
                            <button name="aksi" value="reject"  class="text-xs bg-red-100 text-red-600 px-2 py-1 rounded-lg" onclick="return confirm('Tolak & kembalikan saldo?')">✕</button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="px-5 py-6 text-center text-sm text-gray-400">Tidak ada withdrawal pending</div>
            @endforelse
        </div>
    </div>
</div>

{{-- KYC Pending --}}
@if($kycPending->count() > 0)
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm">
    <div class="px-5 py-3 border-b border-gray-50 flex items-center justify-between">
        <h3 class="text-sm font-semibold text-gray-900">🪪 KYC Pending ({{ $kycPending->count() }})</h3>
    </div>
    <div class="divide-y divide-gray-50">
        @foreach($kycPending as $kyc)
        <div class="px-5 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ $kyc->user->avatar_url }}" class="w-9 h-9 rounded-full object-cover"/>
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $kyc->user->name }}</div>
                    <div class="text-xs text-gray-400">{{ $kyc->user->email }} · {{ $kyc->created_at->format('d M Y') }}</div>
                </div>
            </div>
            <form action="{{ route('admin.kyc.verifikasi', $kyc) }}" method="POST" class="flex gap-2">
                @csrf @method('PATCH')
                <button name="aksi" value="verify"  class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-xl hover:bg-green-700">✓ Verify</button>
                <button name="aksi" value="reject" class="text-xs bg-red-100 text-red-600 px-3 py-1.5 rounded-xl hover:bg-red-200" onclick="return confirm('Tolak KYC?')">✕ Tolak</button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
