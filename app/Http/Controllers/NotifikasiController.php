<?php
namespace App\Http\Controllers;
use App\Models\Notifikasi;
class NotifikasiController extends Controller {
    public function index() {
        $notifikasi = Notifikasi::where('user_id',auth()->id())->orderByDesc('created_at')->paginate(20);
        Notifikasi::where('user_id',auth()->id())->where('dibaca',false)->update(['dibaca'=>true,'dibaca_at'=>now()]);
        return view('notifikasi.index', compact('notifikasi'));
    }
    public function hapusSemua() {
        Notifikasi::where('user_id',auth()->id())->delete();
        return back()->with('success','Semua notifikasi dihapus.');
    }
}
