<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaketIklan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session; // Tambahkan ini untuk session check

class Paket_iklan extends Controller
{
    /**
     * Menampilkan halaman utama untuk Kelola Paket Iklan.
     */
    public function index()
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        
        $paket_iklan = PaketIklan::orderBy('harga', 'asc')->paginate(10);

        $data = [
            'title'         => 'Kelola Paket Iklan',
            'paket_iklan'   => $paket_iklan,
            'content'       => 'admin/paket_iklan/index'
        ];
        return view('admin/layout/wrapper', $data);
    }

    /**
     * Menampilkan form untuk membuat paket baru.
     */
    public function create()
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}

        $data = [
            'title'   => 'Tambah Paket Iklan Baru',
            'content' => 'admin/paket_iklan/create'
        ];
        return view('admin/layout/wrapper', $data);
    }

    /**
     * Menyimpan paket baru ke database.
     */
    public function store(Request $request)
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}

        $request->validate([
            'nama_paket'  => 'required|string|max:100|unique:paket_iklan',
            'harga'       => 'required|numeric|min:0',
            'kuota_iklan' => 'required|integer|min:0',
        ]);

        PaketIklan::create($request->all());

        return redirect('admin/paket-iklan')->with('sukses', 'Data berhasil ditambahkan.');
    }

    /**
     * Menampilkan form untuk mengedit paket.
     */
    public function edit($id)
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}

        $paket = PaketIklan::findOrFail($id);

        $data = [
            'title'   => 'Edit Paket: ' . $paket->nama_paket,
            'paket'   => $paket,
            'content' => 'admin/paket_iklan/edit'
        ];
        return view('admin/layout/wrapper', $data);
    }
    
    public function update(Request $request, $id)
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}

        $request->validate([
            'nama_paket'  => 'required|string|max:100|unique:paket_iklan,nama_paket,' . $id,
            'harga'       => 'required|numeric|min:0',
            'kuota_iklan' => 'required|integer|min:0',
        ]);

        $paket = PaketIklan::findOrFail($id);
        $paket->update($request->all());

        return redirect('admin/paket-iklan')->with('sukses', 'Data berhasil diperbarui.');
    }

    /**
     * Menghapus paket dari database.
     */
    public function destroy($id)
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}

        // --- PERBAIKAN DI SINI ---
               DB::table('paket_iklan')->where('id',$id)->delete();


        // Redirect kembali ke halaman daftar dengan pesan sukses
        return redirect('admin/paket-iklan')->with(['sukses' => 'Data berhasil dihapus.']);
    }

    /**
     * FUNGSI BARU UNTUK PROSES HAPUS MASSAL
     */
    public function proses(Request $request)
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        
        // Ambil ID dari checkbox yang dipilih
        $id_paket = $request->id_paket;

        // Pastikan ada item yang dipilih
        if(empty($id_paket)) {
            return redirect('admin/paket-iklan')->with(['warning' => 'Anda belum memilih data untuk dihapus.']);
        }

        // Hapus data yang dipilih menggunakan DB Facades
        DB::table('paket_iklan')->whereIn('id', $id_paket)->delete();

        return redirect('admin/paket-iklan')->with(['sukses' => 'Data yang dipilih berhasil dihapus.']);
    }

    // ... (fungsi index, create, store, edit, update, delete tidak berubah) ...

    // --- FUNGSI UNTUK MENAMPILKAN HALAMAN BELI PAKET (DIPERBAIKI) ---
    public function beliUntukMember()
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}

        // Ambil data paket, rekening, dan site config
        $paket_iklan = PaketIklan::where('is_active', true)->orderBy('harga', 'asc')->get();
        $rekening = DB::table('rekening')->get();
        $site = DB::table('konfigurasi')->first();

        // --- AWAL LOGIKA BARU UNTUK DAFTAR STAFF ---
        $staff_list = null;
        if(Session::get('akses_level') == 'Admin') {
            // Jika Admin, ambil semua staff
            $staff_list = DB::table('staff')->orderBy('nama_staff', 'asc')->get();
        } else {
            // Jika User, ambil HANYA staff yang terhubung dengan user_id mereka
            $staff_list = DB::table('staff')->where('id_user', Session::get('id_user'))->get();
        }
        // --- AKHIR LOGIKA BARU ---

        $data = [
            'title'         => 'Beli Paket Iklan untuk Member',
            'paket_iklan'   => $paket_iklan,
            'rekening'      => $rekening,
            'site'          => $site,
            'staff_list'    => $staff_list, // Kirim daftar staff yang sudah difilter
            'content'       => 'admin/paket_iklan/beli_untuk_member'
        ];
        return view('admin/layout/wrapper', $data);
    }

    // --- FUNGSI BARU UNTUK MEMPROSES FORM PEMBELIAN ---
    public function prosesPembelian(Request $request)
    {
        if(Session::get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}

        $request->validate([
            'paket_id'          => 'required|exists:paket_iklan,id',
            'bukti_pembayaran'  => 'required|image|mimes:jpeg,png,jpg|max:2048',
            // Validasi staff_id hanya jika yang login adalah Admin
            'staff_id'          => (Session::get('akses_level') == 'Admin' ? 'required|exists:staff,id_staff' : 'nullable')
        ]);

        // Tentukan user_id berdasarkan siapa yang login
        if(Session::get('akses_level') == 'Admin') {
            $staff = DB::table('staff')->where('id_staff', $request->staff_id)->first();
            $user_id = $staff->id_user;
        } else {
            // Jika user biasa, ambil id_user dari session
            $user_id = Session::get('id_user');
        }

        // Proses upload gambar bukti pembayaran
        $image = $request->file('bukti_pembayaran');
        $nama_file = time().'_'.$image->getClientOriginalName();
        $tujuan_upload = 'assets/upload/bukti';
        $image->move($tujuan_upload, $nama_file);

        // Simpan data transaksi ke database
        DB::table('transaksi_paket')->insert([
            'user_id'           => $user_id,
            'paket_id'          => $request->paket_id,
            'kode_transaksi'    => 'WSP-'.strtoupper(uniqid()),
            'status_pembayaran' => 'pending',
            'bukti_pembayaran'  => $nama_file,
            'created_at'        => now(),
            'updated_at'        => now()
        ]);

        return redirect('admin/dasbor')->with('sukses', 'Pesanan Anda telah diterima dan akan segera diproses oleh Admin. Terima kasih.');
    }
}
