@extends('layouts.app')
@section('title','Buat Split Bill')
@section('content')
<div class="max-w-lg">
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
<form action="{{ route('splitbill.store') }}" method="POST" class="space-y-5">
    @csrf

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Judul Tagihan <span class="text-red-500">*</span></label>
        <input type="text" name="judul" value="{{ old('judul') }}" required placeholder="Makan malam, sewa kos, dll..."
               class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500"/>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Deskripsi</label>
        <textarea name="deskripsi" rows="2" class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500 resize-none">{{ old('deskripsi') }}</textarea>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Total Tagihan <span class="text-red-500">*</span></label>
        <div class="relative">
            <span class="absolute left-3.5 top-3 text-gray-400 text-sm font-semibold">Rp</span>
            <input type="number" name="total" id="total-input" value="{{ old('total') }}" min="1000" required placeholder="0"
                   class="w-full border-2 border-gray-200 rounded-xl pl-10 pr-4 py-3 text-sm font-semibold focus:outline-none focus:border-indigo-500"
                   oninput="updatePerOrang()"/>
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-2">Tambah Anggota <span class="text-red-500">*</span></label>
        <div id="anggota-list" class="space-y-2 mb-2">
            <div class="flex gap-2 anggota-row">
                <input type="text" name="anggota[]" placeholder="No HP atau Email" class="flex-1 border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500" oninput="updatePerOrang()"/>
                <button type="button" onclick="removeAnggota(this)" class="text-red-400 hover:text-red-600 px-2">✕</button>
            </div>
        </div>
        <button type="button" onclick="addAnggota()" class="text-xs text-indigo-600 hover:underline">+ Tambah anggota</button>
        <p class="text-xs text-gray-400 mt-1">Kamu otomatis termasuk dalam tagihan ini</p>
    </div>

    <div class="bg-indigo-50 rounded-xl p-4 text-sm">
        <div class="flex justify-between mb-1">
            <span class="text-gray-500">Total</span>
            <span id="s-total" class="font-semibold text-gray-900">Rp 0</span>
        </div>
        <div class="flex justify-between mb-1">
            <span class="text-gray-500">Jumlah Orang</span>
            <span id="s-orang" class="font-semibold text-gray-900">1 orang</span>
        </div>
        <div class="flex justify-between border-t border-indigo-200 pt-2 mt-2">
            <span class="font-bold text-indigo-700">Per Orang</span>
            <span id="s-per-orang" class="font-bold text-2xl text-indigo-700">Rp 0</span>
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold text-gray-600 mb-1">Jatuh Tempo</label>
        <input type="date" name="jatuh_tempo" value="{{ old('jatuh_tempo') }}" min="{{ today()->addDay()->format('Y-m-d') }}"
               class="w-full border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500"/>
    </div>

    <div class="flex gap-3 pt-2 border-t border-gray-100">
        <a href="{{ route('splitbill.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 text-sm font-medium py-2.5 rounded-xl hover:bg-gray-200 transition">Batal</a>
        <button type="submit" class="flex-1 text-white font-bold py-2.5 rounded-xl transition" style="background:linear-gradient(135deg,#6366F1,#A855F7)">🤝 Buat Tagihan</button>
    </div>
</form>
</div>
</div>
<script>
function formatRp(n) { return 'Rp ' + parseInt(n||0).toLocaleString('id-ID'); }
function addAnggota() {
    const div = document.createElement('div');
    div.className = 'flex gap-2 anggota-row';
    div.innerHTML = `<input type="text" name="anggota[]" placeholder="No HP atau Email" class="flex-1 border-2 border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:border-indigo-500" oninput="updatePerOrang()"/><button type="button" onclick="removeAnggota(this)" class="text-red-400 hover:text-red-600 px-2">✕</button>`;
    document.getElementById('anggota-list').appendChild(div);
    updatePerOrang();
}
function removeAnggota(btn) { btn.parentElement.remove(); updatePerOrang(); }
function updatePerOrang() {
    const total  = parseInt(document.getElementById('total-input').value) || 0;
    const anggota = document.querySelectorAll('[name="anggota[]"]').length;
    const orang  = anggota + 1; // +1 pembuat
    const perOrang = orang > 0 ? Math.round(total/orang) : 0;
    document.getElementById('s-total').textContent   = formatRp(total);
    document.getElementById('s-orang').textContent   = orang + ' orang';
    document.getElementById('s-per-orang').textContent = formatRp(perOrang);
}
</script>
@endsection
