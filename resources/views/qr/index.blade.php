@extends('layouts.app')
@section('title','QR Payment')
@section('content')
<div class="max-w-2xl">
<div class="grid grid-cols-2 gap-4">

    {{-- QR Saya --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 text-center">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">📱 QR Code Saya</h2>
        <div class="bg-gray-50 rounded-2xl p-4 mb-4 inline-block">
            <img src="{{ $myQr->qr_url }}" class="w-48 h-48 mx-auto"/>
        </div>
        <div class="text-sm font-semibold text-gray-900 mb-1">{{ auth()->user()->name }}</div>
        <div class="text-xs font-mono text-indigo-600 mb-3">{{ auth()->user()->wallet->nomor_wallet }}</div>
        <div class="text-xs text-gray-400 mb-4">QR ini bisa dipakai berulang kali</div>

        {{-- Generate QR dengan nominal --}}
        <div class="border-t border-gray-100 pt-4">
            <div class="text-xs font-semibold text-gray-600 mb-2">Generate QR dengan nominal:</div>
            <div class="flex gap-2">
                <input type="number" id="qr-jumlah" placeholder="Nominal (opsional)" min="1000"
                       class="flex-1 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
                <button onclick="generateQR()" class="bg-indigo-600 text-white text-xs font-medium px-3 py-2 rounded-xl hover:bg-indigo-700 transition">Generate</button>
            </div>
            <div id="new-qr" class="hidden mt-3">
                <img id="new-qr-img" src="" class="w-40 h-40 mx-auto rounded-xl border border-gray-200"/>
                <div class="text-xs text-gray-400 mt-2">Berlaku 24 jam</div>
            </div>
        </div>
    </div>

    {{-- Scan QR --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">🔍 Bayar via QR</h2>
        <div class="text-sm text-gray-500 mb-4">Masukkan kode QR yang diberikan penerima:</div>

        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Kode QR</label>
                <input type="text" id="qr-kode" placeholder="QRXXXXXXXXXX..."
                       class="w-full border-2 border-gray-200 rounded-xl px-4 py-3 text-sm font-mono focus:outline-none focus:border-indigo-500"/>
            </div>
            <button onclick="scanQR()" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium py-3 rounded-xl transition">
                🔍 Cek & Bayar
            </button>
        </div>

        <div class="mt-6 pt-4 border-t border-gray-100">
            <div class="text-xs font-semibold text-gray-500 mb-3">Atau gunakan contoh QR demo:</div>
            <div class="space-y-2">
                <button onclick="document.getElementById('qr-kode').value='QRBUDI001STATIC';scanQR()"
                        class="w-full text-left bg-gray-50 hover:bg-indigo-50 rounded-xl px-4 py-3 transition">
                    <div class="text-sm font-medium text-gray-900">QR Budi Santoso</div>
                    <div class="text-xs text-gray-400 font-mono">QRBUDI001STATIC · Nominal bebas</div>
                </button>
                <button onclick="document.getElementById('qr-kode').value='QRSITI001AMOUNT';scanQR()"
                        class="w-full text-left bg-gray-50 hover:bg-indigo-50 rounded-xl px-4 py-3 transition">
                    <div class="text-sm font-medium text-gray-900">QR Siti Rahayu</div>
                    <div class="text-xs text-gray-400 font-mono">QRSITI001AMOUNT · Rp 50.000</div>
                </button>
            </div>
        </div>
    </div>
</div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

async function generateQR() {
    const jumlah = document.getElementById('qr-jumlah').value || null;
    const res = await fetch('{{ route("qr.generate") }}', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken},
        body: JSON.stringify({jumlah})
    });
    const data = await res.json();
    document.getElementById('new-qr-img').src = data.qr_url;
    document.getElementById('new-qr').classList.remove('hidden');
}

function scanQR() {
    const kode = document.getElementById('qr-kode').value.trim();
    if (!kode) return;
    window.location.href = `/qr/scan/${kode}`;
}
</script>
@endsection
