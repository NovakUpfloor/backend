<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use App\Models\Konfigurasi as KonfigurasiModel;

class Konfigurasi extends Controller
{
    // Main page
    public function index()
    {
    	if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        $mykonfigurasi 	= new KonfigurasiModel();
        $site 			= $mykonfigurasi->listing();

		$data = array(  'title'        => 'Data Konfigurasi',
						'site'         => $site,
                        'content'      => 'admin/konfigurasi/index'
                    );
        return view('admin/layout/wrapper',$data);
    }

    // logo
    public function logo()
    {
        if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        $mykonfigurasi  = new KonfigurasiModel();
        $site           = $mykonfigurasi->listing();

        $data = array(  'title'        => 'Update Logo',
                        'site'         => $site,
                        'content'      => 'admin/konfigurasi/logo'
                    );
        return view('admin/layout/wrapper',$data);
    }

    // --- AWAL PERBAIKAN ---

    // profil
    public function profil()
    {
        if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        // Menggunakan KonfigurasiModel, bukan Konfigurasi
        $mykonfigurasi  = new KonfigurasiModel();
        $site           = $mykonfigurasi->listing();

        $data = array(  'title'        => 'Profil '.$site->namaweb,
                        'site'         => $site,
                        'content'      => 'admin/konfigurasi/profil'
                    );
        return view('admin/layout/wrapper',$data);
    }

    public function profil_kontraktor()
    {
        if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        // Menggunakan KonfigurasiModel, bukan Konfigurasi
        $mykonfigurasi  = new KonfigurasiModel();
        $site           = $mykonfigurasi->listing();

        $data = array(  'title'        => 'Profil Kontraktor '.$site->namaweb,
                        'site'         => $site,
                        'content'      => 'admin/konfigurasi/profil_kontraktor'
                    );
        return view('admin/layout/wrapper',$data);
    }

    // gambar
    public function gambar()
    {
        if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        // Menggunakan KonfigurasiModel, bukan Konfigurasi
        $mykonfigurasi  = new KonfigurasiModel();
        $site           = $mykonfigurasi->listing();

        $data = array(  'title'        => 'Update Gambar Banner',
                        'site'         => $site,
                        'content'      => 'admin/konfigurasi/gambar'
                    );
        return view('admin/layout/wrapper',$data);
    }

    // icon
    public function icon()
    {
        if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        // Menggunakan KonfigurasiModel, bukan Konfigurasi
        $mykonfigurasi  = new KonfigurasiModel();
        $site           = $mykonfigurasi->listing();

        $data = array(  'title'        => 'Update Icon',
                        'site'         => $site,
                        'content'      => 'admin/konfigurasi/icon'
                    );
        return view('admin/layout/wrapper',$data);
    }


    // email
    public function email()
    {
        if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        // Menggunakan KonfigurasiModel, bukan Konfigurasi
        $mykonfigurasi  = new KonfigurasiModel();
        $site           = $mykonfigurasi->listing();

        $data = array(  'title'        => 'Update Setting Email',
                        'site'         => $site,
                        'content'      => 'admin/konfigurasi/email'
                    );
        return view('admin/layout/wrapper',$data);
    }

    // pembayaran
    public function pembayaran()
    {
        if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        // Menggunakan KonfigurasiModel, bukan Konfigurasi
        $mykonfigurasi  = new KonfigurasiModel();
        $site           = $mykonfigurasi->listing();

        $data = array(  'title'        => 'Update Panduan Pembayaran',
                        'site'         => $site,
                        'content'      => 'admin/konfigurasi/pembayaran'
                    );
        return view('admin/layout/wrapper',$data);
    }

    // --- AKHIR PERBAIKAN ---

    // ... (Semua fungsi proses_... tidak diubah) ...
    
    public function proses(Request $request)
    {
        //...
    }
    public function proses_email(Request $request)
    {
        //...
    }
    public function proses_logo(Request $request)
    {
        //...
    }
    public function proses_profil(Request $request)
    {
        //...
    }
    public function proses_profil_kontraktor(Request $request)
    {
        //...
    }
    public function proses_icon(Request $request)
    {
        //...
    }
    public function proses_gambar(Request $request)
    {
        //...
    }
    public function proses_pembayaran(Request $request)
    {
        //...
    }
}
