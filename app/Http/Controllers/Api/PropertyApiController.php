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
        // Menggunakan query builder untuk join
        $property = $myproperty->semua_raw()
                        ->where('property_db.id_property', $id)
                        ->first();

        if (!$property) {
            return response()->json(['success' => false, 'message' => 'Properti tidak ditemukan'], 404);
        }

        // Mengambil gambar-gambar terkait
        $images = DB::table('property_img')->where('id_property', $id)->orderBy('index_img')->get()->pluck('gambar');

        // Mengambil data agent/staff
        $agent = DB::table('staff')->where('id_staff', $property->id_staff)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'property' => $property,
                'images' => $images,
                'agent' => $agent
            ]
        ]);
    }
	
	    /**
     * --- FUNGSI BARU UNTUK PENCARIAN ---
     * Menampilkan daftar properti berdasarkan filter.
     */
    public function search(Request $request)
    {
        $myproperty = new PropertyModel();
        $query = $myproperty->semua_raw(); // Mengambil query builder dasar

        // Filter berdasarkan lokasi jika ada
        if ($request->has('location')) {
            $location = strtolower($request->input('location'));
            $query->where(function ($q) use ($location) {
                $q->where(DB::raw('LOWER(kabupaten.nama)'), 'like', '%' . $location . '%')
                  ->orWhere(DB::raw('LOWER(provinsi.nama)'), 'like', '%' . $location . '%');
            });
        }

        // Filter berdasarkan tipe properti jika ada
        if ($request->has('type')) {
             $type = strtolower($request->input('type'));
             // Anda mungkin perlu mencocokkan dengan slug atau nama kategori di sini
             $query->whereHas('kategori_property', function ($q) use ($type) {
                $q->where(DB::raw('LOWER(nama_kategori_property)'), 'like', '%' . $type . '%');
             });
        }

        $properties = $query->orderBy('tanggal', 'desc')->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $properties
        ]);
    }
}