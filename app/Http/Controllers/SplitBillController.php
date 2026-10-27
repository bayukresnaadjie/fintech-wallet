<?php

namespace App\Http\Controllers;

use App\Models\SplitBill;
use App\Models\SplitBillMember;
use App\Models\Transfer;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Notifikasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SplitBillController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $tagihan = SplitBill::where('dibuat_oleh', $user->id)
            ->orWhereHas('members', fn($q) => $q->where('user_id', $user->id))
            ->with(['pembuatTagihan','members.user'])
            ->orderByDesc('created_at')
            ->paginate(10);
        return view('splitbill.index', compact('tagihan'));
    }

    public function create()
    {
        return view('splitbill.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'judul'        => 'required|string|max:100',
            'deskripsi'    => 'nullable|string',
            'total'        => 'required|numeric|min:1000',
            'anggota'      => 'required|array|min:1',
            'anggota.*'    => 'required|string',
            'jatuh_tempo'  => 'nullable|date|after:today',
        ]);

        // Cari user berdasarkan nomor HP/email
        $anggotaUsers = collect();
        foreach ($validated['anggota'] as $kontak) {
            $u = User::where('nomor_hp', $kontak)->orWhere('email', $kontak)->first();
            if ($u && $u->id !== $user->id) $anggotaUsers->push($u);
        }

        $jumlahOrang = $anggotaUsers->count() + 1; // +1 pembuat
        $perOrang    = round($validated['total'] / $jumlahOrang, 0);

        DB::transaction(function () use ($user, $validated, $anggotaUsers, $jumlahOrang, $perOrang) {
            $split = SplitBill::create([
                'dibuat_oleh'  => $user->id,
                'nomor_tagihan'=> 'SB' . now()->format('YmdHis'),
                'judul'        => $validated['judul'],
                'deskripsi'    => $validated['deskripsi'] ?? null,
                'total'        => $validated['total'],
                'per_orang'    => $perOrang,
                'jumlah_orang' => $jumlahOrang,
                'jatuh_tempo'  => $validated['jatuh_tempo'] ?? null,
            ]);

            // Pembuat otomatis sudah bayar
            SplitBillMember::create(['split_bill_id'=>$split->id,'user_id'=>$user->id,'jumlah_tagihan'=>$perOrang,'sudah_bayar'=>true,'dibayar_at'=>now()]);

            // Anggota lain
            foreach ($anggotaUsers as $anggota) {
                SplitBillMember::create(['split_bill_id'=>$split->id,'user_id'=>$anggota->id,'jumlah_tagihan'=>$perOrang]);
                Notifikasi::kirim($anggota->id, '🤝 Tagihan Split Bill', $user->name . ' mengundang Anda dalam tagihan "' . $split->judul . '" sebesar Rp ' . number_format($perOrang,0,',','.'), 'split', $perOrang);
            }
        });

        return redirect()->route('splitbill.index')->with('success', 'Split bill berhasil dibuat.');
    }

    public function show(SplitBill $splitBill)
    {
        $user = auth()->user();
        $isMember = $splitBill->dibuat_oleh === $user->id ||
            $splitBill->members()->where('user_id', $user->id)->exists();
        if (!$isMember) abort(403);

        $splitBill->load(['pembuatTagihan','members.user','members.transfer']);
        $myMember = $splitBill->members()->where('user_id', $user->id)->first();

        return view('splitbill.show', compact('splitBill','myMember'));
    }

    public function bayar(Request $request, SplitBill $splitBill)
    {
        $user     = auth()->user();
        $member   = $splitBill->members()->where('user_id', $user->id)->firstOrFail();
        $pembuat  = $splitBill->pembuatTagihan;

        if ($member->sudah_bayar) return back()->with('info', 'Sudah dibayar.');

        $request->validate(['pin' => 'required|string|size:6']);
        if (!$user->verifyPin($request->pin)) {
            return back()->withErrors(['pin' => 'PIN salah.']);
        }

        $jumlah = (float) $member->jumlah_tagihan;
        if ((float) $user->wallet->saldo < $jumlah) {
            return back()->with('error', 'Saldo tidak mencukupi.');
        }

        DB::transaction(function () use ($user, $pembuat, $member, $splitBill, $jumlah) {
            $transfer = Transfer::create([
                'nomor_transaksi'    => Wallet::generateNomorTransaksi('SB'),
                'pengirim_id'        => $user->id,
                'penerima_id'        => $pembuat->id,
                'wallet_pengirim_id' => $user->wallet->id,
                'wallet_penerima_id' => $pembuat->wallet->id,
                'jumlah'             => $jumlah,
                'biaya_admin'        => 0,
                'jumlah_diterima'    => $jumlah,
                'catatan'            => 'Split Bill: ' . $splitBill->judul,
                'kategori'           => 'split_bill',
                'status'             => 'success',
                'konfirmasi_pin'     => true,
                'diproses_at'        => now(),
            ]);

            $user->wallet->debit($jumlah, $transfer->nomor_transaksi, 'Bayar split bill: ' . $splitBill->judul);
            $pembuat->wallet->kredit($jumlah, $transfer->nomor_transaksi, 'Terima split bill dari ' . $user->name);

            $member->update(['sudah_bayar'=>true,'dibayar_at'=>now(),'transfer_id'=>$transfer->id]);

            // Cek apakah semua sudah bayar
            if ($splitBill->members()->where('sudah_bayar',false)->count() === 0) {
                $splitBill->update(['status'=>'lunas']);
            }

            Notifikasi::kirim($pembuat->id, '✓ Split Bill Dibayar', $user->name . ' membayar tagihan "' . $splitBill->judul . '"', 'split', $jumlah);
        });

        return back()->with('success', 'Pembayaran split bill berhasil!');
    }
}
