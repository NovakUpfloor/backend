<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Berita as BeritaModel;

class BeritaApiController extends Controller
{
    /**
     * Menampilkan daftar semua berita.
     */
    public function index()
    {
        $beritaModel = new BeritaModel();
        $berita = $beritaModel->listing(); // Menggunakan method listing yang sudah ada

        return response()->json([
            'success' => true,
            'data' => $berita
        ]);
    }

    /**
     * Menampilkan detail satu berita.
     */
    public function show($id)
    {
        $beritaModel = new BeritaModel();
        $berita = $beritaModel->detail($id);

        if (!$berita) {
            return response()->json(['success' => false, 'message' => 'Berita tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $berita
        ]);
    }
}