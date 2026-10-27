<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\TopUpRequest;
use App\Models\WithdrawalRequest;
use App\Models\Transfer;
use App\Models\UserKyc;
use Illuminate\Http\Request;
class AdminController extends Controller {
    public function __construct() { if(!auth()->check()||!auth()->user()->isAdmin()) abort(403); }
    public function dashboard() {
        $stats = [
            'total_user'    => User::count(),
            'kyc_pending'   => User::where('kyc_status','pending')->count(),
            'topup_pending' => TopUpRequest::where('status','pending')->count(),
            'wd_pending'    => WithdrawalRequest::where('status','pending')->count(),
            'total_transfer'=> Transfer::where('status','success')->sum('jumlah'),
            'total_topup'   => TopUpRequest::where('status','success')->sum('jumlah_diterima'),
        ];
        $topupPending = TopUpRequest::where('status','pending')->with('user')->latest()->take(10)->get();
        $wdPending    = WithdrawalRequest::where('status','pending')->with(['user','bankAccount'])->latest()->take(10)->get();
        $kycPending   = UserKyc::where('status','pending')->with('user')->latest()->take(10)->get();
        return view('admin.dashboard', compact('stats','topupPending','wdPending','kycPending'));
    }
    public function users(Request $request) {
        $users = User::with('wallet')->when($request->search, fn($q) => $q->where('name','like','%'.$request->search.'%')->orWhere('email','like','%'.$request->search.'%')->orWhere('nomor_hp','like','%'.$request->search.'%'))->orderByDesc('created_at')->paginate(20);
        return view('admin.users', compact('users'));
    }
    public function verifikasiKyc(Request $request, UserKyc $kyc) {
        $request->validate(['aksi'=>'required|in:verify,reject','catatan'=>'nullable|string']);
        $status = $request->aksi === 'verify' ? 'verified' : 'rejected';
        $kyc->update(['status'=>$status,'catatan_admin'=>$request->catatan,'diverifikasi_oleh'=>auth()->id(),'diverifikasi_at'=>now()]);
        $kyc->user->update(['kyc_status'=>$status]);
        return back()->with('success','KYC berhasil '.$status.'.');
    }
    public function suspendUser(User $user) {
        if($user->id === auth()->id()) return back()->with('error','Tidak bisa suspend diri sendiri.');
        $user->update(['is_suspended'=>!$user->is_suspended]);
        return back()->with('success','Status user berhasil diubah.');
    }
}
