<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\TopUpRequest;
use App\Models\LedgerEntry;
use App\Models\Notifikasi;

class DashboardController extends Controller
{
    public function index()
    {
        $user   = auth()->user();
        $wallet = $user->wallet;

        // Transaksi terbaru (gabungan)
        $transferMasuk  = Transfer::where('penerima_id', $user->id)->where('status','success')->latest()->take(5)->get();
        $transferKeluar = Transfer::where('pengirim_id', $user->id)->where('status','success')->latest()->take(5)->get();
        $topupTerbaru   = TopUpRequest::where('user_id', $user->id)->where('status','success')->latest()->take(5)->get();

        // Gabung & sort
        $transaksiTerbaru = collect()
            ->merge($transferMasuk->map(fn($t) => ['tipe'=>'masuk','data'=>$t,'waktu'=>$t->created_at]))
            ->merge($transferKeluar->map(fn($t) => ['tipe'=>'keluar','data'=>$t,'waktu'=>$t->created_at]))
            ->merge($topupTerbaru->map(fn($t) => ['tipe'=>'topup','data'=>$t,'waktu'=>$t->created_at]))
            ->sortByDesc('waktu')
            ->take(8)
            ->values();

        // Statistik bulan ini
        $bulanIni = [
            'pemasukan'   => Transfer::where('penerima_id',$user->id)->where('status','success')->whereMonth('created_at',now()->month)->sum('jumlah_diterima')
                           + TopUpRequest::where('user_id',$user->id)->where('status','success')->whereMonth('created_at',now()->month)->sum('jumlah_diterima'),
            'pengeluaran' => Transfer::where('pengirim_id',$user->id)->where('status','success')->whereMonth('created_at',now()->month)->sum('jumlah'),
            'transaksi'   => Transfer::where(fn($q) => $q->where('pengirim_id',$user->id)->orWhere('penerima_id',$user->id))->where('status','success')->whereMonth('created_at',now()->month)->count(),
        ];

        // Grafik cashflow 7 hari
        $grafikCashflow = collect(range(6,0))->map(function($day) use ($user) {
            $tgl = today()->subDays($day);
            return [
                'tanggal'   => $tgl->format('d M'),
                'masuk'     => Transfer::where('penerima_id',$user->id)->where('status','success')->whereDate('created_at',$tgl)->sum('jumlah_diterima')
                             + TopUpRequest::where('user_id',$user->id)->where('status','success')->whereDate('created_at',$tgl)->sum('jumlah_diterima'),
                'keluar'    => Transfer::where('pengirim_id',$user->id)->where('status','success')->whereDate('created_at',$tgl)->sum('jumlah'),
            ];
        });

        // Pengeluaran per kategori bulan ini
        $perKategori = Transfer::where('pengirim_id',$user->id)->where('status','success')
            ->whereMonth('created_at',now()->month)
            ->whereNotNull('kategori')
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->orderByDesc('total')
            ->get();

        // Notifikasi belum dibaca
        $notifCount = $user->notifikasi()->where('dibaca',false)->count();

        return view('dashboard', compact('user','wallet','transaksiTerbaru','bulanIni','grafikCashflow','perKategori','notifCount'));
    }
}
