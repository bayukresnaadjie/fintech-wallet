<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $transfers = Transfer::where('pengirim_id', $user->id)
            ->orWhere('penerima_id', $user->id)
            ->with(['pengirim','penerima'])
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('transfer.index', compact('transfers'));
    }

    public function create(Request $request)
    {
        $tujuan = null;
        if ($request->filled('nomor')) {
            $tujuan = User::where('nomor_hp', $request->nomor)
                ->orWhere('email', $request->nomor)
                ->orWhereHas('wallet', fn($q) => $q->where('nomor_wallet', $request->nomor))
                ->first();
        }
        return view('transfer.create', compact('tujuan'));
    }

    public function cariPenerima(Request $request)
    {
        $request->validate(['nomor' => 'required|string']);
        $user = User::where('nomor_hp', $request->nomor)
            ->orWhere('email', $request->nomor)
            ->orWhereHas('wallet', fn($q) => $q->where('nomor_wallet', $request->nomor))
            ->with('wallet')
            ->first();

        if (!$user || $user->id === auth()->id()) {
            return response()->json(['found' => false, 'message' => 'User tidak ditemukan']);
        }

        return response()->json([
            'found'         => true,
            'id'            => $user->id,
            'name'          => $user->name,
            'nomor_wallet'  => $user->wallet->nomor_wallet,
            'avatar_url'    => $user->avatar_url,
            'kyc_status'    => $user->kyc_status,
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'penerima_id' => 'required|exists:users,id|different:'.auth()->id(),
            'jumlah'      => 'required|numeric|min:1000|max:10000000',
            'catatan'     => 'nullable|string|max:100',
            'kategori'    => 'nullable|string|max:50',
            'pin'         => 'required|string|size:6',
        ]);

        // Verifikasi PIN
        if (!$user->verifyPin($validated['pin'])) {
            return back()->withErrors(['pin' => 'PIN salah. Silakan coba lagi.'])->withInput();
        }

        $wallet    = $user->wallet;
        $penerima  = User::with('wallet')->findOrFail($validated['penerima_id']);
        $jumlah    = (float) $validated['jumlah'];
        $biaya     = $jumlah >= 50000 ? 0 : 1000;
        $diterima  = $jumlah - $biaya;

        // Validasi saldo
        if ((float) $wallet->saldo < $jumlah) {
            return back()->withErrors(['jumlah' => 'Saldo tidak mencukupi.'])->withInput();
        }

        // Validasi limit harian
        if (!$wallet->cekLimitHarian($jumlah)) {
            return back()->withErrors(['jumlah' => 'Melebihi limit transaksi harian.'])->withInput();
        }

        DB::transaction(function () use ($user, $penerima, $wallet, $jumlah, $biaya, $diterima, $validated) {
            // Buat record transfer
            $transfer = Transfer::create([
                'nomor_transaksi'    => Wallet::generateNomorTransaksi('TR'),
                'pengirim_id'        => $user->id,
                'penerima_id'        => $penerima->id,
                'wallet_pengirim_id' => $wallet->id,
                'wallet_penerima_id' => $penerima->wallet->id,
                'jumlah'             => $jumlah,
                'biaya_admin'        => $biaya,
                'jumlah_diterima'    => $diterima,
                'catatan'            => $validated['catatan'] ?? null,
                'kategori'           => $validated['kategori'] ?? null,
                'status'             => 'success',
                'konfirmasi_pin'     => true,
                'diproses_at'        => now(),
            ]);

            // Debit pengirim
            $wallet->debit($jumlah, $transfer->nomor_transaksi, 'Transfer ke ' . $penerima->name);

            // Kredit penerima
            $penerima->wallet->kredit($diterima, $transfer->nomor_transaksi, 'Transfer dari ' . $user->name);

            // Notifikasi
            Notifikasi::kirim($user->id,     'Transfer Berhasil ✓', 'Transfer ' . 'Rp '.number_format($jumlah,0,',','.') . ' ke ' . $penerima->name . ' berhasil.', 'transfer', $jumlah);
            Notifikasi::kirim($penerima->id, '💰 Uang Masuk!',       $user->name . ' mengirim Rp ' . number_format($diterima,0,',','.') . ' ke dompet Anda.', 'transfer', $diterima);

            session(['last_transfer_id' => $transfer->id]);
        });

        return redirect()->route('transfer.show', session('last_transfer_id'))->with('success', 'Transfer berhasil!');
    }

    public function show(Transfer $transfer)
    {
        if ($transfer->pengirim_id !== auth()->id() && $transfer->penerima_id !== auth()->id()) abort(403);
        $transfer->load(['pengirim','penerima','walletPengirim','walletPenerima']);
        return view('transfer.show', compact('transfer'));
    }
}
