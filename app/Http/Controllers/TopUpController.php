<?php

namespace App\Http\Controllers;

use App\Models\TopUpRequest;
use App\Models\Notifikasi;
use App\Models\Wallet;
use Illuminate\Http\Request;

class TopUpController extends Controller
{
    public function index()
    {
        $user    = auth()->user();
        $topups  = TopUpRequest::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('topup.index', compact('topups'));
    }

    public function create()
    {
        return view('topup.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'jumlah' => 'required|numeric|min:10000|max:10000000',
            'metode' => 'required|in:transfer_bank,virtual_account,minimarket,qris',
            'kode_bank' => 'nullable|string|max:10',
        ]);

        $biaya    = $validated['metode'] === 'transfer_bank' ? 2500 : 0;
        $diterima = $validated['jumlah'] - $biaya;

        $topup = TopUpRequest::create([
            'user_id'         => $user->id,
            'wallet_id'       => $user->wallet->id,
            'nomor_transaksi' => Wallet::generateNomorTransaksi('TU'),
            'jumlah'          => $validated['jumlah'],
            'biaya_admin'     => $biaya,
            'jumlah_diterima' => $diterima,
            'metode'          => $validated['metode'],
            'kode_bank'       => $validated['kode_bank'] ?? null,
            'nomor_va'        => $validated['metode'] === 'virtual_account' ? '8877' . rand(10000000, 99999999) : null,
            'status'          => 'pending',
            'expired_at'      => now()->addHours(24),
        ]);

        return redirect()->route('topup.show', $topup)->with('success', 'Permintaan top up berhasil dibuat.');
    }

    public function show(TopUpRequest $topup)
    {
        if ($topup->user_id !== auth()->id()) abort(403);
        return view('topup.show', compact('topup'));
    }

    public function uploadBukti(Request $request, TopUpRequest $topup)
    {
        if ($topup->user_id !== auth()->id()) abort(403);
        if ($topup->status !== 'pending') return back()->with('error', 'Status tidak memungkinkan upload bukti.');

        $request->validate(['bukti' => 'required|image|max:2048']);
        $path = $request->file('bukti')->store('bukti-topup', 'public');
        $topup->update(['bukti_transfer' => $path]);

        return back()->with('success', 'Bukti transfer berhasil diupload. Menunggu konfirmasi admin.');
    }

    // ── Admin: Konfirmasi Top Up ──────────────────────────────────────────────
    public function konfirmasi(Request $request, TopUpRequest $topup)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        $request->validate(['aksi' => 'required|in:approve,reject']);

        if ($request->aksi === 'approve') {
            // Kredit wallet user
            $topup->user->wallet->kredit(
                (float) $topup->jumlah_diterima,
                $topup->nomor_transaksi,
                'Top Up via ' . $topup->metode_label
            );
            $topup->update([
                'status'           => 'success',
                'dikonfirmasi_at'  => now(),
                'dikonfirmasi_oleh'=> auth()->id(),
            ]);
            Notifikasi::kirim($topup->user_id, 'Top Up Berhasil ✓', 'Top up ' . $topup->jumlah_format . ' berhasil masuk ke dompet Anda.', 'topup', (float)$topup->jumlah_diterima);
        } else {
            $topup->update(['status' => 'failed']);
            Notifikasi::kirim($topup->user_id, 'Top Up Ditolak', 'Top up ' . $topup->jumlah_format . ' ditolak oleh admin.', 'sistem');
        }

        return back()->with('success', 'Top up berhasil ' . ($request->aksi === 'approve' ? 'dikonfirmasi' : 'ditolak') . '.');
    }
}
