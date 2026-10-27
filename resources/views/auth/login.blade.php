<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login · PayKu</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-sm">
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 text-3xl shadow-lg" style="background:linear-gradient(135deg,#6366F1,#A855F7)">💳</div>
        <h1 class="font-bold text-gray-900 text-2xl">PayKu</h1>
        <p class="text-gray-500 text-sm mt-1">Dompet digital terpercaya Anda</p>
    </div>
    <div class="bg-white rounded-2xl shadow-xl p-7">
        @if($errors->any())<div class="bg-red-50 border border-red-200 text-red-600 text-sm px-4 py-3 rounded-xl mb-4">{{ $errors->first() }}</div>@endif
        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Password</label>
                <input type="password" name="password" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"/>
            </div>
            <button type="submit" class="w-full text-white font-bold py-3 rounded-xl transition" style="background:linear-gradient(135deg,#6366F1,#A855F7)">Masuk</button>
        </form>
        <p class="text-center text-sm text-gray-500 mt-4">Belum punya akun? <a href="{{ route('register') }}" class="text-indigo-600 font-semibold hover:underline">Daftar</a></p>
        <div class="mt-5 p-3 bg-gray-50 rounded-xl text-xs text-gray-500 space-y-1">
            <div class="font-semibold text-gray-600 mb-1.5">Akun Demo:</div>
            <div>👑 admin@fintech.test — Admin</div>
            <div>💰 budi@fintech.test — Saldo Rp 2.5jt</div>
            <div>💰 siti@fintech.test — Saldo Rp 1.75jt</div>
            <div>💰 dika@fintech.test — Saldo Rp 3.2jt</div>
            <div class="text-gray-400 mt-1">Password: password · PIN: 123456</div>
        </div>
    </div>
</div>
</body>
</html>
