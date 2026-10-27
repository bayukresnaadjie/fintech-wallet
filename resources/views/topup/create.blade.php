@extends('layouts.app')
@section('title','Top Up Saldo')
@section('content')
<div class="max-w-lg">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
<form action="{{ route('topup.store') }}" method="POST" class="space-y-5">
    @csrf

    {{-- Nominal --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">Pilih Nominal</label>
        <div class="grid grid-cols-3 gap-2 mb-3">
            @foreach([50000,100000,200000,500000,1000000,2000000] as $nom)
            <button type="button" onclick="setNominal({{ $nom }})"
                    class="nominal-btn border-2 border-gray-200 rounded-xl py-2.5 text-sm font-semibold text-gray-700 hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700 transition">
                Rp {{ number_format($nom, 0, ',', '.') }}
            </button>
            @endforeach
        </div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Atau masukkan nominal lain</label>
        <div class="relative">
            <span class="absolute left-3.5 top-3 text-gray-400 text-sm font-semibold">Rp</span>
            <input type="number" name="jumlah" id="nominal-input" value="{{ old('jumlah') }}"
                   min="10000" max="10000000" required placeholder="0"
                   class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm font-semibold focus:outline-none focus:border-indigo-500"/>
        </div>
        <p class="text-xs text-gray-400 mt-1">Minimal Rp 10.000 · Maksimal Rp 10.000.000</p>
    </div>

    {{-- Metode --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">Metode Pembayaran</label>
        <div class="space-y-2">
            @php
            $metodes = [
                'transfer_bank'   => ['label'=>'Transfer Bank',   'sub'=>'BCA, Mandiri, BNI, BRI + biaya Rp 2.500',  'icon'=>'🏦'],
                'virtual_account' => ['label'=>'Virtual Account', 'sub'=>'Tanpa biaya, proses otomatis',              'icon'=>'💳'],
                'minimarket'      => ['label'=>'Minimarket',      'sub'=>'Indomaret, Alfamart, Alfamidi',             'icon'=>'🏪'],
                'qris'            => ['label'=>'QRIS',            'sub'=>'Scan QR dari app bank manapun',             'icon'=>'📱'],
            ];
            @endphp
            @foreach($metodes as $val => $opt)
            <label class="flex items-center gap-3 border-2 border-gray-100 rounded-xl p-3.5 cursor-pointer hover:border-indigo-300 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 transition">
                <input type="radio" name="metode" value="{{ $val }}" {{ old('metode','virtual_account')===$val?'checked':'' }} class="w-4 h-4 text-indigo-600"/>
                <span class="text-2xl">{{ $opt['icon'] }}</span>
                <div>
                    <div class="text-sm font-semibold text-gray-900">{{ $opt['label'] }}</div>
                    <div class="text-xs text-gray-400">{{ $opt['sub'] }}</div>
                </div>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Ringkasan --}}
    <div class="bg-gray-50 rounded-xl p-4 text-sm space-y-2">
        <div class="flex justify-between"><span class="text-gray-500">Nominal Top Up</span><span id="sum-nominal" class="font-semibold">Rp 0</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Biaya Admin</span><span id="sum-biaya" class="font-semibold">Rp 0</span></div>
        <div class="flex justify-between border-t border-gray-200 pt-2"><span class="font-bold text-gray-900">Total Dibayar</span><span id="sum-total" class="font-bold text-indigo-600">Rp 0</span></div>
    </div>

    <button type="submit" class="w-full text-white font-bold py-3 rounded-xl transition" style="background:linear-gradient(135deg,#6366F1,#A855F7)">
        Lanjutkan Top Up →
    </button>
</form>
</div>
</div>

<script>
function setNominal(n) {
    document.getElementById('nominal-input').value = n;
    document.querySelectorAll('.nominal-btn').forEach(b => b.classList.remove('border-indigo-500','bg-indigo-50','text-indigo-700'));
    event.target.classList.add('border-indigo-500','bg-indigo-50','text-indigo-700');
    updateSummary();
}
function formatRp(n) { return 'Rp ' + parseInt(n||0).toLocaleString('id-ID'); }
function updateSummary() {
    const n    = parseInt(document.getElementById('nominal-input').value) || 0;
    const isTf = document.querySelector('[name=metode]:checked')?.value === 'transfer_bank';
    const b    = isTf ? 2500 : 0;
    document.getElementById('sum-nominal').textContent = formatRp(n);
    document.getElementById('sum-biaya').textContent   = formatRp(b);
    document.getElementById('sum-total').textContent   = formatRp(n + b);
}
document.getElementById('nominal-input').addEventListener('input', updateSummary);
document.querySelectorAll('[name=metode]').forEach(r => r.addEventListener('change', updateSummary));
updateSummary();
</script>
@endsection
