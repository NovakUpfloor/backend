<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleApiController extends Controller
{
    /**
     * Mengambil daftar artikel (berita) terbaru.
     */
    public function latest()
    {
        $articles = DB::table('berita')
            ->select('id_berita', 'judul_berita', 'slug_berita', 'gambar', 'tanggal_publish')
            ->where('status_berita', 'Publish')
            ->orderBy('tanggal_publish', 'desc')
            ->limit(5)
            ->get();

        return response()->json(['data' => $articles]);
    }

    /**
     * Menampilkan detail satu artikel.
     */
    public function show($id)
    {
        $article = DB::table('berita')
            ->where('id_berita', $id)
            ->where('status_berita', 'Publish')
            ->first();

        if (!$article) {
            return response()->json(['message' => 'Artikel tidak ditemukan'], 404);
        }

        // Increment hits count
        DB::table('berita')->where('id_berita', $id)->increment('hits');

        return response()->json(['data' => $article]);
    }
}
