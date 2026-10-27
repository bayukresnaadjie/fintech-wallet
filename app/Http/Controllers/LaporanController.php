<?php
namespace App\Http\Controllers;
use App\Models\Transfer;
use App\Models\TopUpRequest;
use App\Models\LedgerEntry;
use Illuminate\Http\Request;
class LaporanController extends Controller {
    public function index(Request $request) {
        $user  = auth()->user();
        $bulan = $request->get('bulan', now()->format('Y-m'));
        [$tahun,$bln] = explode('-', $bulan);
        $ledger = LedgerEntry::where('wallet_id', $user->wallet->id)
            ->whereYear('created_at',$tahun)->whereMonth('created_at',$bln)
            ->orderByDesc('created_at')->paginate(20);
        $ringkasan = [
            'total_masuk'   => LedgerEntry::where('wallet_id',$user->wallet->id)->where('tipe','credit')->whereYear('created_at',$tahun)->whereMonth('created_at',$bln)->sum('jumlah'),
            'total_keluar'  => LedgerEntry::where('wallet_id',$user->wallet->id)->where('tipe','debit')->whereYear('created_at',$tahun)->whereMonth('created_at',$bln)->sum('jumlah'),
            'total_transfer'=> Transfer::where('pengirim_id',$user->id)->where('status','success')->whereYear('created_at',$tahun)->whereMonth('created_at',$bln)->count(),
            'total_topup'   => TopUpRequest::where('user_id',$user->id)->where('status','success')->whereYear('created_at',$tahun)->whereMonth('created_at',$bln)->count(),
        ];
        $perKategori = Transfer::where('pengirim_id',$user->id)->where('status','success')
            ->whereYear('created_at',$tahun)->whereMonth('created_at',$bln)
            ->whereNotNull('kategori')
            ->selectRaw('kategori, SUM(jumlah) as total, COUNT(*) as jumlah_transaksi')
            ->groupBy('kategori')->orderByDesc('total')->get();
        $grafikHarian = collect(range(1, cal_days_in_month(CAL_GREGORIAN,(int)$bln,(int)$tahun)))->map(fn($d) => [
            'tgl'    => $d,
            'masuk'  => LedgerEntry::where('wallet_id',$user->wallet->id)->where('tipe','credit')->whereYear('created_at',$tahun)->whereMonth('created_at',$bln)->whereDay('created_at',$d)->sum('jumlah'),
            'keluar' => LedgerEntry::where('wallet_id',$user->wallet->id)->where('tipe','debit')->whereYear('created_at',$tahun)->whereMonth('created_at',$bln)->whereDay('created_at',$d)->sum('jumlah'),
        ]);
        return view('laporan.index', compact('ledger','ringkasan','perKategori','grafikHarian','bulan'));
    }
}
