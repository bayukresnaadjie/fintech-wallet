@extends('layouts.app')
@section('title','Tarik Tunai ke Bank')
@section('content')
<div class="max-w-lg">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">

@if($bankList->count() === 0)
<div class="text-center py-8">
    <div class="text-4xl mb-3">🏦</div>
    <div class="font-semibold text-gray-700 mb-1">Belum ada rekening bank</div>
    <div class="text-sm text-gray-400 mb-4">Tambahkan rekening bank terverifikasi untuk tarik tunai</div>
    <a href="{{ route('dashboard') }}" class="bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-xl">Kembali</a>
</div>
@else
<form action="{{ route('withdrawal.store') }}" method="POST" class="space-y-5">
    @csrf

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">Rekening Tujuan <span class="text-red-500">*</span></label>
        <div class="space-y-2">
            @foreach($bankList as $bank)
            <label class="flex items-center gap-3 border-2 border-gray-100 rounded-xl p-3.5 cursor-pointer hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 transition">
                <input type="radio" name="bank_account_id" value="{{ $bank->id }}" {{ $bank->is_default?'checked':'' }} required class="w-4 h-4 text-indigo-600"/>
                <div class="flex-1">
                    <div class="font-semibold text-gray-900 text-sm">{{ $bank->nama_bank }}</div>
                    <div class="text-xs text-gray-400 font-mono">{{ $bank->nomor_rekening }} · {{ $bank->nama_pemilik }}</div>
                </div>
                @if($bank->is_verified)<span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">✓ Verified</span>@endif
            </label>
            @endforeach
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">Jumlah Tarik <span class="text-red-500">*</span></label>
        <div class="relative">
            <span class="absolute left-3.5 top-3 text-gray-400 text-sm font-semibold">Rp</span>
            <input type="number" name="jumlah" id="jumlah-wd" value="{{ old('jumlah') }}"
                   min="50000" max="50000000" required placeholder="0"
                   class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm font-semibold focus:outline-none focus:border-indigo-500"
                   oninput="updateSummary()"/>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1">
            <span>Minimal Rp 50.000</span>
            <span>Saldo: <strong class="text-indigo-600">{{ auth()->user()->wallet->saldo_format }}</strong></span>
        </div>
    </div>

    <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-sm space-y-2">
        <div class="flex justify-between"><span class="text-gray-500">Jumlah Tarik</span><span id="wd-nominal" class="font-semibold">Rp 0</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Biaya Admin</span><span class="font-semibold text-amber-600">Rp 6.500</span></div>
        <div class="flex justify-between border-t border-amber-200 pt-2"><span class="font-bold">Saldo Dikurangi</span><span id="wd-total" class="font-bold text-red-500">Rp 0</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Diterima di Bank</span><span id="wd-diterima" class="font-bold text-green-600">Rp 0</span></div>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">PIN <span class="text-red-500">*</span></label>
        <input type="password" name="pin" maxlength="6" inputmode="numeric" required
               placeholder="••••••" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-center text-2xl tracking-widest focus:outline-none focus:border-indigo-500"/>
        <p class="text-xs text-gray-400 mt-1 text-center">Estimasi pencairan 1×24 jam kerja</p>
    </div>

    <button type="submit" class="w-full text-white font-bold py-3.5 rounded-xl" style="background:linear-gradient(135deg,#6366F1,#A855F7)">🏧 Ajukan Tarik Tunai</button>
</form>
@endif
</div>
</div>
<script>
function formatRp(n) { return 'Rp ' + parseInt(n||0).toLocaleString('id-ID'); }
function updateSummary() {
    const n = parseInt(document.getElementById('jumlah-wd').value) || 0;
    document.getElementById('wd-nominal').textContent   = formatRp(n);
    document.getElementById('wd-total').textContent     = formatRp(n + 6500);
    document.getElementById('wd-diterima').textContent  = formatRp(Math.max(0, n - 6500));
}
</script>
@endsection
