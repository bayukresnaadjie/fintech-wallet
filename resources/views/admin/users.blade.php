@extends('layouts.app')
@section('title','Manajemen User')
@section('subtitle', $users->total() . ' total user')
@section('header-action')
<a href="{{ route('admin.dashboard') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-xl transition">← Admin Panel</a>
@endsection
@section('content')

<form method="GET" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 flex gap-3">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, nomor HP..."
           class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
    <button type="submit" class="bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl">Cari</button>
</form>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100"><tr>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">User</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">Nomor HP</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400">Saldo</th>
            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-400">KYC</th>
            <th class="text-center px-5 py-3 text-xs font-semibold text-gray-400">Status</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400">Daftar</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-400">Aksi</th>
        </tr></thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($users as $u)
            <tr class="hover:bg-gray-50 {{ $u->is_suspended?'opacity-50':'' }}">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <img src="{{ $u->avatar_url }}" class="w-8 h-8 rounded-full object-cover flex-shrink-0"/>
                        <div>
                            <div class="font-medium text-gray-900 text-sm">{{ $u->name }}</div>
                            <div class="text-xs text-gray-400">{{ $u->email }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 font-mono text-xs text-gray-600">{{ $u->nomor_hp ?? '—' }}</td>
                <td class="px-5 py-3 text-right font-bold text-indigo-600">{{ $u->wallet?->saldo_format ?? 'Rp 0' }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $u->kyc_badge['class'] }}">{{ $u->kyc_badge['icon'] }} {{ $u->kyc_badge['label'] }}</span>
                </td>
                <td class="px-5 py-3 text-center">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $u->is_suspended?'bg-red-100 text-red-700':'bg-green-100 text-green-700' }}">
                        {{ $u->is_suspended?'Suspended':'Aktif' }}
                    </span>
                </td>
                <td class="px-5 py-3 text-xs text-gray-400">{{ $u->created_at->format('d M Y') }}</td>
                <td class="px-5 py-3 text-right">
                    @if($u->id !== auth()->id())
                    <form action="{{ route('admin.users.suspend', $u) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="text-xs {{ $u->is_suspended?'bg-green-100 text-green-700 hover:bg-green-200':'bg-red-100 text-red-600 hover:bg-red-200' }} px-2.5 py-1 rounded-lg transition"
                                onclick="return confirm('{{ $u->is_suspended?'Aktifkan':'Suspend' }} user ini?')">
                            {{ $u->is_suspended?'✓ Aktifkan':'⚠ Suspend' }}
                        </button>
                    </form>
                    @else
                    <span class="text-xs text-gray-300">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">Tidak ada user</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())<div class="px-5 py-3 border-t">{{ $users->appends(request()->query())->links() }}</div>@endif
</div>
@endsection
