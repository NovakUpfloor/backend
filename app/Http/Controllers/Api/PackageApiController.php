<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaketIklan;

class PackageApiController extends Controller
{
    /**
     * Menampilkan semua paket iklan yang aktif.
     */
    public function index()
    {
        $packages = PaketIklan::where('is_active', true)
                                ->orderBy('harga', 'asc')
                                ->get();

        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }
}