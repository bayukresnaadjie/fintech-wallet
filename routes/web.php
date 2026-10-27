<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TopUpController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\SplitBillController;
use App\Http\Controllers\QrController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\AdminController;

// ─── AUTH ─────────────────────────────────────────────────────────────────────
Route::get('/login', fn() => view('auth.login'))->name('login');
Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate(['email'=>'required|email','password'=>'required']);
    if (\Illuminate\Support\Facades\Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        auth()->user()->update(['last_login'=>now(),'last_login_ip'=>$request->ip()]);
        return redirect()->intended(route('dashboard'));
    }
    return back()->withErrors(['email'=>'Email atau password salah.'])->onlyInput('email');
})->name('login.post');

Route::get('/register', fn() => view('auth.register'))->name('register');
Route::post('/register', function (\Illuminate\Http\Request $request) {
    $validated = $request->validate([
        'name'     => 'required|string|max:100',
        'email'    => 'required|email|unique:users',
        'nomor_hp' => 'required|string|unique:users|min:10|max:15',
        'password' => 'required|min:8|confirmed',
        'pin'      => 'required|digits:6',
    ]);
    $user = \App\Models\User::create([
        'name'     => $validated['name'],
        'email'    => $validated['email'],
        'nomor_hp' => $validated['nomor_hp'],
        'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        'pin'      => \Illuminate\Support\Facades\Hash::make($validated['pin']),
        'pin_aktif'=> true,
        'email_verified_at' => now(),
    ]);
    \Illuminate\Support\Facades\Auth::login($user);
    return redirect()->route('dashboard')->with('success', 'Selamat datang di Fintech Wallet!');
})->name('register.post');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    \Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout');

// ─── AUTHENTICATED ────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Top Up
    Route::get('/topup',                     [TopUpController::class, 'index'])->name('topup.index');
    Route::get('/topup/create',              [TopUpController::class, 'create'])->name('topup.create');
    Route::post('/topup',                    [TopUpController::class, 'store'])->name('topup.store');
    Route::get('/topup/{topup}',             [TopUpController::class, 'show'])->name('topup.show');
    Route::post('/topup/{topup}/bukti',      [TopUpController::class, 'uploadBukti'])->name('topup.bukti');
    Route::patch('/topup/{topup}/konfirmasi',[TopUpController::class, 'konfirmasi'])->name('topup.konfirmasi');

    // Transfer
    Route::get('/transfer',                  [TransferController::class, 'index'])->name('transfer.index');
    Route::get('/transfer/create',           [TransferController::class, 'create'])->name('transfer.create');
    Route::post('/transfer',                 [TransferController::class, 'store'])->name('transfer.store');
    Route::get('/transfer/{transfer}',       [TransferController::class, 'show'])->name('transfer.show');
    Route::post('/transfer/cari-penerima',   [TransferController::class, 'cariPenerima'])->name('transfer.cari');

    // Withdrawal
    Route::get('/tarik',                     [WithdrawalController::class, 'index'])->name('withdrawal.index');
    Route::get('/tarik/create',              [WithdrawalController::class, 'create'])->name('withdrawal.create');
    Route::post('/tarik',                    [WithdrawalController::class, 'store'])->name('withdrawal.store');
    Route::patch('/tarik/{withdrawal}/proses',[WithdrawalController::class,'proses'])->name('withdrawal.proses');

    // Split Bill
    Route::get('/split',                     [SplitBillController::class, 'index'])->name('splitbill.index');
    Route::get('/split/create',              [SplitBillController::class, 'create'])->name('splitbill.create');
    Route::post('/split',                    [SplitBillController::class, 'store'])->name('splitbill.store');
    Route::get('/split/{splitBill}',         [SplitBillController::class, 'show'])->name('splitbill.show');
    Route::post('/split/{splitBill}/bayar',  [SplitBillController::class, 'bayar'])->name('splitbill.bayar');

    // QR Payment
    Route::get('/qr',                        [QrController::class, 'index'])->name('qr.index');
    Route::post('/qr/generate',              [QrController::class, 'generate'])->name('qr.generate');
    Route::get('/qr/scan/{kode}',            [QrController::class, 'scan'])->name('qr.scan');
    Route::post('/qr/bayar/{kode}',          [QrController::class, 'bayar'])->name('qr.bayar');

    // Laporan
    Route::get('/laporan',                   [LaporanController::class, 'index'])->name('laporan.index');

    // Notifikasi
    Route::get('/notifikasi',                [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::delete('/notifikasi/hapus-semua', [NotifikasiController::class, 'hapusSemua'])->name('notifikasi.hapus-semua');

    // Admin
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/',                          [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/users',                     [AdminController::class, 'users'])->name('users');
        Route::patch('/kyc/{kyc}/verifikasi',    [AdminController::class, 'verifikasiKyc'])->name('kyc.verifikasi');
        Route::patch('/users/{user}/suspend',    [AdminController::class, 'suspendUser'])->name('users.suspend');
    });
});
