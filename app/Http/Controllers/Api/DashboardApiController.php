<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DashboardApiController extends Controller
{
    public function purchasePackage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paket_id' => 'required|integer|exists:paket_iklan,id',
            'nomor_whatsapp' => 'required|string|max:20',
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $staff = DB::table('staff')->where('id_user', $user->id_user)->first();

        if (!$staff) {
            return response()->json(['message' => 'Data staff tidak ditemukan untuk user ini.'], 404);
        }

        try {
            DB::transaction(function () use ($request, $user, $staff) {
                // Handle file upload
                $path = $request->file('bukti_pembayaran')->store('public/proofs');

                // Create transaction record
                DB::table('transaksi_paket')->insert([
                    'user_id' => $user->id_user,
                    'id_staff' => $staff->id_staff,
                    'paket_id' => $request->paket_id,
                    'kode_transaksi' => 'TRX-' . strtoupper(Str::random(10)),
                    'status_pembayaran' => 'pending',
                    'bukti_pembayaran' => $path,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Update staff's phone number
                DB::table('staff')
                    ->where('id_staff', $staff->id_staff)
                    ->update(['telepon' => $request->nomor_whatsapp]);
            });

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat memproses pembelian Anda.',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json(['message' => 'Permintaan pembelian Anda telah berhasil dikirim. Mohon tunggu konfirmasi dari admin.'], 201);
    }

    public function getMyProperties(Request $request)
    {
        $user = Auth::user();
        $staff = DB::table('staff')->where('id_user', $user->id_user)->first();

        if (!$staff) {
            return response()->json(['message' => 'Data staff tidak ditemukan untuk user ini.'], 404);
        }

        $properties = DB::table('property_db')
                        ->where('id_staff', $staff->id_staff)
                        ->orderBy('tanggal', 'desc')
                        ->get();

        return response()->json(['data' => $properties], 200);
    }

    public function storeProperty(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_property' => 'required|string|max:255',
            'id_kategori_property' => 'required|integer|exists:kategori_property,id_kategori_property',
            'tipe' => 'required|string|in:Jual,Sewa',
            'harga' => 'required|numeric',
            'surat' => 'required|string|max:25',
            'lt' => 'required|integer',
            'lb' => 'required|integer',
            'kamar_tidur' => 'required|integer',
            'kamar_mandi' => 'required|integer',
            'lantai' => 'required|integer',
            'id_provinsi' => 'required|integer',
            'id_kabupaten' => 'required|integer',
            'id_kecamatan' => 'required|integer',
            'alamat' => 'required|string',
            'isi' => 'nullable|string',
            'property_images' => 'required|array|min:1',
            'property_images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $staff = DB::table('staff')->where('id_user', $user->id_user)->first();

        if (!$staff || $staff->sisa_kuota_iklan <= 0) {
            return response()->json(['message' => 'Anda tidak memiliki kuota iklan yang cukup.'], 403);
        }

        try {
            $propertyId = DB::transaction(function () use ($request, $staff) {
                // Decrement ad quota
                DB::table('staff')->where('id_staff', $staff->id_staff)->decrement('sisa_kuota_iklan');

                // Create property record
                $newPropertyId = DB::table('property_db')->insertGetId([
                    'id_kategori_property' => $request->id_kategori_property,
                    'nama_property' => $request->nama_property,
                    'slug_property' => Str::slug($request->nama_property, '-'),
                    'tipe' => $request->tipe,
                    'harga' => $request->harga,
                    'surat' => $request->surat,
                    'lt' => $request->lt,
                    'lb' => $request->lb,
                    'isi' => $request->isi,
                    'kamar_tidur' => $request->kamar_tidur,
                    'kamar_mandi' => $request->kamar_mandi,
                    'id_staff' => $staff->id_staff,
                    'alamat' => $request->alamat,
                    'id_provinsi' => $request->id_provinsi,
                    'id_kabupaten' => $request->id_kabupaten,
                    'id_kecamatan' => $request->id_kecamatan,
                    'lantai' => $request->lantai,
                    'status' => 1, // Default to active
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Handle image uploads
                foreach ($request->file('property_images') as $index => $imageFile) {
                    $path = $imageFile->store('public/properties');
                    DB::table('property_img')->insert([
                        'id_property' => $newPropertyId,
                        'gambar' => $path,
                        'index_img' => $index + 1,
                    ]);
                }

                return $newPropertyId;
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menyimpan properti.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => 'Properti berhasil ditambahkan.', 'property_id' => $propertyId], 201);
    }
}
