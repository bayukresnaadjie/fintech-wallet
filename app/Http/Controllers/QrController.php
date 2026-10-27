<?php

namespace App\Http\Controllers;

use App\Models\QrCode;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QrController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $myQr   = QrCode::where('user_id', $user->id)->latest()->first();
        if (!$myQr) {
            $myQr = QrCode::generate($user->id, null, null, true);
        }
        return view('qr.index', compact('myQr'));
    }

    public function generate(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'jumlah'  => 'nullable|numeric|min:1000',
            'catatan' => 'nullable|string|max:100',
        ]);

        $qr = QrCode::generate($user->id, $validated['jumlah'] ?? null, $validated['catatan'] ?? null, false);
        return response()->json(['kode' => $qr->kode, 'qr_url' => $qr->qr_url]);
    }

    public function scan(string $kode)
    {
        $qr = QrCode::where('kode', $kode)->with('user')->firstOrFail();
        if (!$qr->isValid()) abort(404, 'QR Code tidak valid atau sudah expired.');
        if ($qr->user_id === auth()->id()) return back()->with('error', 'Tidak bisa membayar ke diri sendiri.');

        return view('qr.scan', compact('qr'));
    }

    public function bayar(Request $request, string $kode)
    {
        $user = auth()->user();
        $qr   = QrCode::where('kode', $kode)->with('user')->firstOrFail();

        if (!$qr->isValid()) return back()->with('error', 'QR Code tidak valid.');
        if ($qr->user_id === $user->id) return back()->with('error', 'Tidak bisa membayar ke diri sendiri.');

        $request->validate([
            'jumlah' => 'required|numeric|min:1000',
            'pin'    => 'required|string|size:6',
        ]);

        if (!$user->verifyPin($request->pin)) {
            return back()->withErrors(['pin' => 'PIN salah.']);
        }

        $jumlah   = (float) $request->jumlah;
        $penerima = $qr->user;

        if ((float) $user->wallet->saldo < $jumlah) {
            return back()->with('error', 'Saldo tidak mencukupi.');
        }

        DB::transaction(function () use ($user, $penerima, $qr, $jumlah) {
            $transfer = Transfer::create([
                'nomor_transaksi'    => Wallet::generateNomorTransaksi('QR'),
                'pengirim_id'        => $user->id,
                'penerima_id'        => $penerima->id,
                'wallet_pengirim_id' => $user->wallet->id,
                'wallet_penerima_id' => $penerima->wallet->id,
                'jumlah'             => $jumlah,
                'biaya_admin'        => 0,
                'jumlah_diterima'    => $jumlah,
                'catatan'            => 'Bayar via QR Code' . ($qr->catatan ? ' - '.$qr->catatan : ''),
                'status'             => 'success',
                'konfirmasi_pin'     => true,
                'diproses_at'        => now(),
            ]);

            $user->wallet->debit($jumlah, $transfer->nomor_transaksi, 'Bayar QR ke ' . $penerima->name);
            $penerima->wallet->kredit($jumlah, $transfer->nomor_transaksi, 'QR Payment dari ' . $user->name);

            $qr->increment('sudah_digunakan');

            Notifikasi::kirim($user->id,     'QR Payment Berhasil', 'Pembayaran Rp ' . number_format($jumlah,0,',','.') . ' ke ' . $penerima->name . ' berhasil.', 'transfer', $jumlah);
            Notifikasi::kirim($penerima->id, '💰 QR Payment Diterima', $user->name . ' membayar Rp ' . number_format($jumlah,0,',','.') . ' via QR.', 'transfer', $jumlah);
        });

        return redirect()->route('transfer.show', Transfer::where('nomor_transaksi','like','QR%')->latest()->first())
            ->with('success', 'Pembayaran QR berhasil!');
    }
}
