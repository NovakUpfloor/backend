<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class Transaksi extends Controller
{
    /**
     * Menampilkan daftar transaksi yang menunggu konfirmasi.
     */
    public function index()
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}

        $transaksi_pending = DB::table('transaksi_paket')
            ->join('users', 'transaksi_paket.user_id', '=', 'users.id_user')
            ->join('paket_iklan', 'transaksi_paket.paket_id', '=', 'paket_iklan.id')
            ->select('transaksi_paket.*', 'users.nama as nama_user', 'paket_iklan.nama_paket')
            ->where('transaksi_paket.status_pembayaran', 'pending')
            ->orderBy('transaksi_paket.created_at', 'asc')
            ->paginate(10);

        $data = [
            'title'             => 'Permintaan Aktivasi Paket',
            'transaksi_pending' => $transaksi_pending,
            'content'           => 'admin/transaksi/index'
        ];
        return view('admin/layout/wrapper', $data);
    }

    /**
     * Mengkonfirmasi pembayaran dan menambah kuota user.
     */
    public function confirm($id)
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}

        $transaksi = DB::table('transaksi_paket')->where('id', $id)->first();
        if(!$transaksi) {
            return redirect('admin/transaksi')->with(['warning' => 'Data transaksi tidak ditemukan.']);
        }

        $paket = DB::table('paket_iklan')->where('id', $transaksi->paket_id)->first();
        if(!$paket) {
            return redirect('admin/transaksi')->with(['warning' => 'Data paket iklan tidak ditemukan.']);
        }

        $staff = DB::table('staff')->where('id_user', $transaksi->user_id)->first();
        if(!$staff) {
            return redirect('admin/transaksi')->with(['warning' => 'Profil staff untuk user ini tidak ditemukan. Pastikan user telah verifikasi email.']);
        }

        // --- LOGIKA BARU SESUAI PERMINTAAN ---
        
        // 1. Dapatkan urutan terakhir dan tentukan urutan baru
        $lastOrder = DB::table('staff')->max('urutan');
        $newOrder = $lastOrder + 1;

        // 2. Update tabel staff: status, urutan, dan kuota
        DB::table('staff')->where('id_staff', $staff->id_staff)->update([
            'status_staff'      => 'Ya',
            'urutan'            => $newOrder,
            'total_kuota_iklan' => DB::raw('total_kuota_iklan + ' . $paket->kuota_iklan),
            'sisa_kuota_iklan'  => DB::raw('sisa_kuota_iklan + ' . $paket->kuota_iklan)
        ]);

        // 3. Update status transaksi menjadi 'confirmed'
        DB::table('transaksi_paket')->where('id', $id)->update([
            'status_pembayaran' => 'confirmed',
            'dikonfirmasi_oleh' => Session::get('id_user'),
            'tanggal_konfirmasi' => now()
        ]);

        return redirect('admin/transaksi')->with(['sukses' => 'Transaksi berhasil dikonfirmasi. Profil staff telah diaktifkan dan kuota diperbarui.']);
    }

    /**
     * Menolak pembayaran.
     */
    public function reject($id)
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}

        DB::table('transaksi_paket')->where('id', $id)->update([
            'status_pembayaran' => 'rejected',
            'dikonfirmasi_oleh' => Session::get('id_user'),
            'tanggal_konfirmasi' => now()
        ]);

        return redirect('admin/transaksi')->with(['sukses' => 'Transaksi telah ditolak.']);
    }

    /**
     * Menandai pembayaran sebagai tidak terverifikasi.
     */
    public function unverify($id)
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}

        DB::table('transaksi_paket')->where('id', $id)->update([
            'status_pembayaran' => 'unverified',
            'dikonfirmasi_oleh' => Session::get('id_user'),
            'tanggal_konfirmasi' => now()
        ]);

        return redirect('admin/transaksi')->with(['sukses' => 'Transaksi telah ditandai sebagai tidak terverifikasi.']);
    }
}
