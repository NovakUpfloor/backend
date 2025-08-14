<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
// Menggunakan alias 'BeritaModel' untuk menghindari bentrok nama
use App\Models\Berita as BeritaModel; 

class Berita extends Controller
{
    // Halaman utama daftar berita
    public function index()
    {
        Paginator::useBootstrap();
        $site   = DB::table('konfigurasi')->first();
        $model  = new BeritaModel();

        $berita = $model->listing();
        $beritas = $model->home();

        $data = array(
            'title'     => 'Berita dan Update',
            'deskripsi' => 'Berita dan Update',
            'keywords'  => 'Berita dan Update',
            'site'      => $site,
            'berita'    => $berita,
            'beritas'   => $beritas,
            'content'   => 'berita/index'
        );
        return view('layout/wrapper', $data);
    }

    // Halaman kategori berita
    public function kategori($slug_kategori)
    {
        Paginator::useBootstrap();
        $site     = DB::table('konfigurasi')->first();
        $kategori = DB::table('kategori')->where('slug_kategori', $slug_kategori)->first();
        $model    = new BeritaModel();
        $berita   = $model->all_kategori($kategori->id_kategori);
        $beritas  = $model->home();

        $data = array(
            'title'     => $kategori->nama_kategori,
            'deskripsi' => $kategori->nama_kategori,
            'keywords'  => $kategori->nama_kategori,
            'site'      => $site,
            'berita'    => $berita,
            'beritas'   => $beritas,
            'content'   => 'berita/index'
        );
        return view('layout/wrapper', $data);
    }

    // Halaman detail berita
    public function read($slug_berita)
    {
        $site    = DB::table('konfigurasi')->first();
        $model   = new BeritaModel();
        $berita  = $model->read($slug_berita);
        $beritas = $model->home();

        // --- PERBAIKAN DI SINI ---
        // Jika berita tidak ditemukan, alihkan ke halaman utama berita
        if(!$berita)
        {
            return redirect('berita');
        }
        // --- AKHIR PERBAIKAN ---

        $data = array(
            'title'     => $berita->judul_berita,
            'deskripsi' => $berita->judul_berita,
            'keywords'  => $berita->judul_berita,
            'site'      => $site,
            'berita'    => $berita,
            'beritas'   => $beritas,
            'content'   => 'berita/read'
        );
        return view('layout/wrapper', $data);
    }

    public function layanan($slug_berita)
    {
        Paginator::useBootstrap();
        $site    = DB::table('konfigurasi')->first();
        $model   = new BeritaModel();
        $berita  = $model->read($slug_berita);
        $layanan = DB::table('berita')->where(array('jenis_berita' => 'Layanan','status_berita' => 'Publish'))->orderBy('urutan', 'ASC')->get();
        if(!$berita)
        {
            return redirect('berita');
        }

        $data = array(  'title'     => $berita->judul_berita,
                        'deskripsi' => $berita->judul_berita,
                        'keywords'  => $berita->judul_berita,
                        'site'      => $site,
                        'berita'    => $berita,
                        'layanan'   => $layanan,
                        'content'   => 'berita/layanan'
                    );
        return view('layout/wrapper', $data);
    }

    public function terjadi($slug_berita)
    {
        Paginator::useBootstrap();
        $site    = DB::table('konfigurasi')->first();
        $model   = new BeritaModel();
        $berita  = $model->read($slug_berita);
        $layanan = DB::table('berita')->where(array('jenis_berita' => 'Layanan','status_berita' => 'Publish'))->orderBy('urutan', 'ASC')->get();
        if(!$berita)
        {
            return redirect('berita');
        }

        $data = array(  'title'     => $berita->judul_berita,
                        'deskripsi' => $berita->judul_berita,
                        'keywords'  => $berita->judul_berita,
                        'site'      => $site,
                        'berita'    => $berita,
                        'layanan'   => $layanan,
                        'content'   => 'berita/terjadi'
                    );
        return view('layout/wrapper', $data);
    }
}
