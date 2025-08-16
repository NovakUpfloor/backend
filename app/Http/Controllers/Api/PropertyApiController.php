<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Property as PropertyModel;
use Illuminate\Support\Facades\DB;

class PropertyApiController extends Controller
{
    /**
     * Menampilkan daftar semua properti.
     */
    public function index()
    {
        $myproperty = new PropertyModel();
        // Menggunakan method 'semua_raw' untuk fleksibilitas
        $properties = $myproperty->semua_raw()
                        ->orderBy('tanggal', 'desc')
                        ->paginate(10); // Menggunakan paginasi untuk performa

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    /**
     * Menampilkan detail satu properti dan menambah view count.
     */
    public function show($id)
    {
        // Menambah view count +1
        DB::table('property_db')->where('id_property', $id)->increment('view_count');

        $myproperty = new PropertyModel();
        $property = $myproperty->detail($id);

        if (!$property) {
            return response()->json(['success' => false, 'message' => 'Properti tidak ditemukan'], 404);
        }

        // Mengambil gambar-gambar terkait
        $images = DB::table('property_img')->where('id_property', $id)->orderBy('index_img')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'property' => $property,
                'images' => $images
            ]
        ]);
    }
	
	    /**
     * Mengambil daftar properti terbaru (misal: 10 properti).
     */
    public function latest()
    {
        $myproperty = new PropertyModel();
        $properties = $myproperty->semua_raw(['property_db.status' => 1])
                        ->orderBy('tanggal', 'desc')
                        ->limit(10)
                        ->get();

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }

    /**
     * Mencari properti dengan filter lanjutan.
     */
    public function search(Request $request)
    {
        $myproperty = new PropertyModel();
        $query = $myproperty->semua_raw(['property_db.status' => 1]);

        // Filter berdasarkan tipe (Jual/Sewa)
        if ($request->has('tipe') && in_array($request->tipe, ['Jual', 'Sewa'])) {
            $query->where('property_db.tipe', $request->tipe);
        }

        // Filter berdasarkan kata kunci
        if ($request->has('keyword')) {
            $keyword = $request->keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('property_db.nama_property', 'like', '%' . $keyword . '%')
                  ->orWhere('property_db.isi', 'like', '%' . $keyword . '%')
                  ->orWhere('kabupaten.nama', 'like', '%' . $keyword . '%')
                  ->orWhere('provinsi.nama', 'like', '%' . $keyword . '%');
            });
        }

        // Filter harga
        if ($request->has('min_price')) {
            $query->where('property_db.harga', '>=', $request->min_price);
        }
        if ($request->has('max_price')) {
            $query->where('property_db.harga', '<=', $request->max_price);
        }

        // Filter kamar tidur
        if ($request->has('bedrooms')) {
            $query->where('property_db.kamar_tidur', '=', $request->bedrooms);
        }

        // Filter kamar mandi
        if ($request->has('bathrooms')) {
            $query->where('property_db.kamar_mandi', '=', $request->bathrooms);
        }

        $properties = $query->orderBy('property_db.tanggal', 'desc')->paginate(15);

        return response()->json($properties);
    }
}