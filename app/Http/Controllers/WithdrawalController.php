<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Models\Notifikasi;
use App\Models\Wallet;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function index()
    {
        $withdrawals = WithdrawalRequest::where('user_id', auth()->id())
            ->with('bankAccount')
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('withdrawal.index', compact('withdrawals'));
    }

    public function create()
    {
        $user     = auth()->user();
        $bankList = $user->bankAccounts()->where('is_verified', true)->get();
        return view('withdrawal.create', compact('bankList'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'bank_account_id' => 'required|exists:bank_accounts,id',
            'jumlah'          => 'required|numeric|min:50000|max:50000000',
            'pin'             => 'required|string|size:6',
        ]);

        if (!$user->verifyPin($validated['pin'])) {
            return back()->withErrors(['pin' => 'PIN salah.'])->withInput();
        }

        $jumlah    = (float) $validated['jumlah'];
        $biaya     = 6500;
        $diterima  = $jumlah - $biaya;

        if ((float) $user->wallet->saldo < $jumlah + $biaya) {
            return back()->withErrors(['jumlah' => 'Saldo tidak mencukupi (termasuk biaya admin Rp 6.500).'])->withInput();
        }

        // Tahan saldo
        $user->wallet->debit($jumlah + $biaya, 'HOLD-' . now()->format('YmdHis'), 'Penahanan saldo tarik tunai');

        $withdrawal = WithdrawalRequest::create([
            'user_id'         => $user->id,
            'wallet_id'       => $user->wallet->id,
            'bank_account_id' => $validated['bank_account_id'],
            'nomor_transaksi' => Wallet::generateNomorTransaksi('WD'),
            'jumlah'          => $jumlah,
            'biaya_admin'     => $biaya,
            'jumlah_diterima' => $diterima,
            'status'          => 'pending',
        ]);

        Notifikasi::kirim($user->id, 'Permintaan Tarik Tunai', 'Permintaan tarik tunai Rp ' . number_format($jumlah,0,',','.') . ' sedang diproses.', 'withdraw', $jumlah);

        return redirect()->route('withdrawal.index')->with('success', 'Permintaan tarik tunai berhasil dibuat. Estimasi 1x24 jam.');
    }

    // ── Admin ─────────────────────────────────────────────────────────────────
    public function proses(Request $request, WithdrawalRequest $withdrawal)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $request->validate(['aksi' => 'required|in:approve,reject']);

        if ($request->aksi === 'approve') {
            $withdrawal->update(['status'=>'success','diproses_oleh'=>auth()->id(),'diproses_at'=>now()]);
            Notifikasi::kirim($withdrawal->user_id, 'Tarik Tunai Berhasil ✓', 'Tarik tunai ' . $withdrawal->jumlah_format . ' ke ' . $withdrawal->bankAccount->nama_bank . ' berhasil diproses.', 'withdraw', (float)$withdrawal->jumlah);
        } else {
            // Kembalikan saldo
            $withdrawal->user->wallet->kredit((float)$withdrawal->jumlah + (float)$withdrawal->biaya_admin, $withdrawal->nomor_transaksi . '-REFUND', 'Refund tarik tunai ditolak');
            $withdrawal->update(['status'=>'failed','diproses_oleh'=>auth()->id(),'diproses_at'=>now()]);
            Notifikasi::kirim($withdrawal->user_id, 'Tarik Tunai Ditolak', 'Permintaan tarik tunai ' . $withdrawal->jumlah_format . ' ditolak. Saldo dikembalikan.', 'sistem');
        }

        return back()->with('success', 'Withdrawal berhasil diproses.');
    }
}
