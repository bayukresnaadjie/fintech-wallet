<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Daftar · PayKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-sm">
    <div class="text-center mb-6">
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl shadow-lg" style="background:linear-gradient(135deg,#6366F1,#A855F7)">💳</div>
        <h1 class="font-bold text-gray-900 text-xl">Buat Akun PayKu</h1>
        <p class="text-gray-400 text-xs mt-1">Gratis & langsung aktif</p>
    </div>
    <div class="bg-white rounded-2xl shadow-xl p-6">
        @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-xl mb-4"><ul class="list-disc list-inside space-y-0.5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
        <form method="POST" action="{{ route('register.post') }}" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Nomor HP <span class="text-red-500">*</span></label>
                <input type="tel" name="nomor_hp" value="{{ old('nomor_hp') }}" required placeholder="0812xxxx" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Password <span class="text-red-500">*</span></label>
                <input type="password" name="password" required minlength="8" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">Konfirmasi Password <span class="text-red-500">*</span></label>
                <input type="password" name="password_confirmation" required class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">PIN 6 Digit <span class="text-red-500">*</span></label>
                <input type="password" name="pin" required maxlength="6" inputmode="numeric" pattern="[0-9]{6}" placeholder="••••••" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 tracking-widest text-center text-xl"/>
                <p class="text-xs text-gray-400 mt-1">PIN digunakan untuk konfirmasi setiap transaksi</p>
            </div>
            <button type="submit" class="w-full text-white font-bold py-3 rounded-xl transition mt-2" style="background:linear-gradient(135deg,#6366F1,#A855F7)">Buat Akun</button>
        </form>
        <p class="text-center text-sm text-gray-500 mt-4">Sudah punya akun? <a href="{{ route('login') }}" class="text-indigo-600 font-semibold hover:underline">Masuk</a></p>
    </div>
</div>
</body>
</html>
