@extends('layouts.app')
@section('title','Transfer')
@section('content')
<div class="max-w-lg">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
<form action="{{ route('transfer.store') }}" method="POST" class="space-y-5">
    @csrf

    {{-- Cari Penerima --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">Penerima</label>
        <div class="flex gap-2">
            <input type="text" id="input-penerima" placeholder="No HP / Email / No Wallet"
                   class="flex-1 border-2 border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:border-indigo-500"/>
            <button type="button" onclick="cariPenerima()" class="bg-indigo-600 text-white text-sm font-medium px-4 rounded-xl hover:bg-indigo-700 transition">Cari</button>
        </div>
        <div id="result-penerima" class="hidden mt-3 bg-indigo-50 border border-indigo-200 rounded-xl p-3">
            <div class="flex items-center gap-3">
                <img id="penerima-avatar" src="" class="w-10 h-10 rounded-full object-cover"/>
                <div>
                    <div id="penerima-name" class="font-semibold text-gray-900 text-sm"></div>
                    <div id="penerima-wallet" class="font-mono text-xs text-indigo-600"></div>
                </div>
                <button type="button" onclick="resetPenerima()" class="ml-auto text-gray-400 hover:text-red-500">✕</button>
            </div>
        </div>
        <div id="result-notfound" class="hidden mt-2 text-xs text-red-500">User tidak ditemukan</div>
        <input type="hidden" name="penerima_id" id="penerima-id"/>
    </div>

    {{-- Nominal --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">Nominal Transfer</label>
        <div class="grid grid-cols-3 gap-2 mb-3">
            @foreach([20000,50000,100000,200000,500000,1000000] as $nom)
            <button type="button" onclick="setNominal({{ $nom }})"
                    class="tr-nom border-2 border-gray-200 rounded-xl py-2 text-xs font-semibold text-gray-700 hover:border-indigo-400 hover:text-indigo-700 transition">
                Rp {{ number_format($nom,0,',','.') }}
            </button>
            @endforeach
        </div>
        <div class="relative">
            <span class="absolute left-3.5 top-3 text-gray-400 text-sm font-semibold">Rp</span>
            <input type="number" name="jumlah" id="jumlah-input" value="{{ old('jumlah') }}"
                   min="1000" max="10000000" required placeholder="0"
                   class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm font-semibold focus:outline-none focus:border-indigo-500"
                   oninput="updateSummary()"/>
        </div>
        <div class="flex justify-between text-xs text-gray-400 mt-1">
            <span>Minimal Rp 1.000</span>
            <span>Saldo: <strong class="text-indigo-600">{{ auth()->user()->wallet->saldo_format }}</strong></span>
        </div>
    </div>

    {{-- Kategori --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">Kategori (opsional)</label>
        <div class="flex flex-wrap gap-2">
            @foreach(['makan'=>'🍔','transport'=>'🚗','belanja'=>'🛍️','tagihan'=>'📋','hiburan'=>'🎮','kesehatan'=>'🏥','pendidikan'=>'📚'] as $kat => $icon)
            <label class="cursor-pointer">
                <input type="radio" name="kategori" value="{{ $kat }}" class="sr-only"/>
                <span class="flex items-center gap-1 text-xs px-3 py-1.5 rounded-full border-2 border-gray-200 hover:border-indigo-400 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50 transition cursor-pointer">
                    {{ $icon }} {{ ucfirst($kat) }}
                </span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- Catatan --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Catatan (opsional)</label>
        <input type="text" name="catatan" value="{{ old('catatan') }}" maxlength="100" placeholder="Bayar makan, dll..."
               class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500"/>
    </div>

    {{-- Ringkasan --}}
    <div class="bg-gray-50 rounded-xl p-4 text-sm space-y-2">
        <div class="flex justify-between"><span class="text-gray-500">Nominal</span><span id="s-nominal" class="font-semibold">Rp 0</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Biaya Admin</span><span id="s-biaya" class="font-semibold text-gray-400">Gratis</span></div>
        <div class="flex justify-between border-t border-gray-200 pt-2"><span class="font-bold">Total Dikurangi</span><span id="s-total" class="font-bold text-indigo-600">Rp 0</span></div>
    </div>

    {{-- PIN --}}
    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Masukkan PIN <span class="text-red-500">*</span></label>
        <input type="password" name="pin" maxlength="6" inputmode="numeric" pattern="[0-9]{6}" required
               placeholder="••••••" class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-center text-2xl tracking-widest focus:outline-none focus:border-indigo-500"/>
        <p class="text-xs text-gray-400 mt-1 text-center">Masukkan PIN 6 digit untuk konfirmasi</p>
    </div>

    <button type="submit" id="btn-submit" class="w-full text-white font-bold py-3.5 rounded-xl transition" style="background:linear-gradient(135deg,#6366F1,#A855F7)">
        💸 Kirim Transfer
    </button>
</form>
</div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
function formatRp(n) { return 'Rp ' + parseInt(n||0).toLocaleString('id-ID'); }

function setNominal(n) {
    document.getElementById('jumlah-input').value = n;
    document.querySelectorAll('.tr-nom').forEach(b => b.classList.remove('border-indigo-500','bg-indigo-50','text-indigo-700'));
    event.target.classList.add('border-indigo-500','bg-indigo-50','text-indigo-700');
    updateSummary();
}

function updateSummary() {
    const n = parseInt(document.getElementById('jumlah-input').value) || 0;
    const b = n >= 50000 ? 0 : (n > 0 ? 1000 : 0);
    document.getElementById('s-nominal').textContent = formatRp(n);
    document.getElementById('s-biaya').textContent   = b > 0 ? formatRp(b) : 'Gratis';
    document.getElementById('s-total').textContent   = formatRp(n + b);
}

async function cariPenerima() {
    const val = document.getElementById('input-penerima').value.trim();
    if (!val) return;
    const res = await fetch('{{ route("transfer.cari") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
        body: JSON.stringify({nomor: val})
    });
    const data = await res.json();
    if (data.found) {
        document.getElementById('penerima-id').value       = data.id;
        document.getElementById('penerima-name').textContent = data.name;
        document.getElementById('penerima-wallet').textContent = data.nomor_wallet;
        document.getElementById('penerima-avatar').src     = data.avatar_url;
        document.getElementById('result-penerima').classList.remove('hidden');
        document.getElementById('result-notfound').classList.add('hidden');
    } else {
        document.getElementById('result-penerima').classList.add('hidden');
        document.getElementById('result-notfound').classList.remove('hidden');
    }
}

function resetPenerima() {
    document.getElementById('penerima-id').value = '';
    document.getElementById('input-penerima').value = '';
    document.getElementById('result-penerima').classList.add('hidden');
}

document.getElementById('input-penerima').addEventListener('keydown', e => { if(e.key==='Enter'){e.preventDefault();cariPenerima();} });
</script>
@endsection
