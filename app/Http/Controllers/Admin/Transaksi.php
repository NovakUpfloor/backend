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

        $user = DB::table('users')->where('id_user', $transaksi->user_id)->first();

        // --- AWAL LOGIKA BARU ---

        // 1. Cek & Buat Profil Staff jika belum ada
        $staff = DB::table('staff')->where('id_user', $user->id_user)->first();

        if(!$staff) {
            // Jika staff belum ada, buat baru dengan data dasar
            DB::table('staff')->insert([
                'id_user'           => $user->id_user,
                'nama_staff'        => $user->nama,
                'email'             => $user->email,
                'status_staff'      => 'Aktif',
                'tanggal_gabung'    => now(),
                'total_kuota_iklan' => DB::raw('total_kuota_iklan + ' . $paket->kuota_iklan),
                'sisa_kuota'        => DB::raw('sisa_kuota + ' . $paket->kuota_iklan),
                'paket_id'          => $paket->id
            ]);

            $staff = DB::table('staff')->where('id_user', $user->id_user)->first();

            DB::table('transaksi_paket')->where('id', $id)->update([
                'id_staff'          => $staff->id_staff,
                'status_pembayaran' => 'confirmed',
                'dikonfirmasi_oleh' => Session::get('id_user'),
                'tanggal_konfirmasi' => now()
            ]);
        }else{
            // 2. Update kuota di tabel staff
            DB::table('staff')->where('id_user', $user->id_user)->update([
                'total_kuota_iklan' => DB::raw('total_kuota_iklan + ' . $paket->kuota_iklan),
                'sisa_kuota'        => DB::raw('sisa_kuota + ' . $paket->kuota_iklan),
                'paket_id'          => $paket->id
            ]);

            // 3. Update status transaksi
            DB::table('transaksi_paket')->where('id', $id)->update([
                'status_pembayaran' => 'confirmed',
                'dikonfirmasi_oleh' => Session::get('id_user'),
                'tanggal_konfirmasi' => now()
            ]);

            // --- AKHIR LOGIKA BARU ---    
        }

        

        return redirect('admin/transaksi')->with(['sukses' => 'Transaksi berhasil dikonfirmasi. Profil dan kuota member telah diperbarui.']);
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
}
