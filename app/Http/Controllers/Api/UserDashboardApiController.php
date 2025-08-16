<?php
// File: app/Http/Controllers/Api/UserDashboardApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Property as PropertyModel;

class UserDashboardApiController extends Controller
{
    public function getProfile(Request $request)
    {
        $staff = DB::table('staff')->where('id_user', $request->user()->id_user)->first();

        if (!$staff) {
            return response()->json(['success' => false, 'message' => 'Profil tidak ditemukan'], 404);
        }

        return response()->json(['success' => true, 'data' => $staff]);
    }

    public function updateProfile(Request $request)
    {
        $staff = DB::table('staff')->where('id_user', $request->user()->id_user)->first();
        if (!$staff) {
            return response()->json(['success' => false, 'message' => 'Profil tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_staff' => 'required|string|max:255',
            'telepon'    => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::table('staff')->where('id_staff', $staff->id_staff)->update([
            'nama_staff' => $request->nama_staff,
            'telepon'    => $request->telepon,
            'jabatan'    => $request->jabatan,
        ]);

        return response()->json(['success' => true, 'message' => 'Profil berhasil diperbarui']);
    }

    public function purchasePackage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paket_id'         => 'required|exists:paket_iklan,id',
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $staff = DB::table('staff')->where('id_user', $user->id_user)->first();

        $image = $request->file('bukti_pembayaran');
        $nama_file = time() . '_' . $image->getClientOriginalName();
        $tujuan_upload = './assets/upload/bukti';
        $image->move($tujuan_upload, $nama_file);

        DB::table('transaksi_paket')->insert([
            'user_id'           => $user->id_user,
            'id_staff'          => $staff ? $staff->id_staff : null,
            'paket_id'          => $request->paket_id,
            'kode_transaksi'    => 'WSP-' . strtoupper(uniqid()),
            'status_pembayaran' => 'pending',
            'bukti_pembayaran'  => $nama_file,
            'created_at'        => now(),
            'updated_at'        => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Pengajuan pembelian paket berhasil. Mohon tunggu konfirmasi dari Admin.']);
    }

    public function storeProperty(Request $request)
    {
        $user = $request->user();
        $staff = DB::table('staff')->where('id_user', $user->id_user)->first();

        if (!$staff || $staff->sisa_kuota <= 0) {
            return response()->json(['success' => false, 'message' => 'Kuota iklan Anda habis. Silakan beli paket baru.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'nama_property' => 'required|string|max:255',
            'id_kategori_property' => 'required|exists:kategori_property,id_kategori_property',
            'tipe' => 'required|in:jual,sewa',
            'harga' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $slug_property = Str::slug($request->nama_property . '-' . time());

        DB::table('property_db')->insert([
            'id_staff'             => $staff->id_staff,
            'id_kategori_property' => $request->id_kategori_property,
            'slug_property'        => $slug_property,
            'nama_property'        => $request->nama_property,
            'tipe'                 => $request->tipe,
            'harga'                => $request->harga,
            'status_property'      => 'Publish',
            'tanggal'              => now(),
        ]);

        DB::table('staff')->where('id_staff', $staff->id_staff)->decrement('sisa_kuota');

        return response()->json(['success' => true, 'message' => 'Iklan properti berhasil di-upload.'], 201);
    }
	
	/**
     * --- FUNGSI BARU UNTUK FASE 4 ---
     * Menyediakan data statistik untuk dasbor pengguna.
     * Fungsi ini akan memberikan data yang berbeda untuk Admin dan User biasa.
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        if ($user->akses_level == 'Admin') {
            // Statistik untuk Admin
            $totalUsers = DB::table('users')->count();
            $totalProperties = DB::table('property_db')->count();
            $totalViews = DB::table('property_db')->sum('view_count');

            $viewsByCategory = DB::table('property_db')
                ->join('kategori_property', 'property_db.id_kategori_property', '=', 'kategori_property.id_kategori_property')
                ->select('kategori_property.nama_kategori_property as category', DB::raw('SUM(property_db.view_count) as views'))
                ->groupBy('kategori_property.nama_kategori_property')
                ->orderBy('views', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'role' => 'Admin',
                    'summary' => [
                        ['title' => 'Total Pengguna', 'value' => $totalUsers, 'icon' => 'users'],
                        ['title' => 'Total Properti', 'value' => $totalProperties, 'icon' => 'properties'],
                        ['title' => 'Total Dilihat', 'value' => $totalViews, 'icon' => 'views'],
                    ],
                    'views_by_category' => $viewsByCategory
                ]
            ]);

        } else {
            // Statistik untuk User biasa (Agen)
            $staff = DB::table('staff')->where('id_user', $user->id_user)->first();
            if (!$staff) {
                return response()->json(['success' => false, 'message' => 'Profil staff tidak ditemukan'], 404);
            }

            $totalPropertiesUser = DB::table('property_db')->where('id_staff', $staff->id_staff)->count();
            $totalViewsUser = DB::table('property_db')->where('id_staff', $staff->id_staff)->sum('view_count');

            $viewsByCategoryUser = DB::table('property_db')
                ->join('kategori_property', 'property_db.id_kategori_property', '=', 'kategori_property.id_kategori_property')
                ->select('kategori_property.nama_kategori_property as category', DB::raw('SUM(property_db.view_count) as views'))
                ->where('property_db.id_staff', $staff->id_staff)
                ->groupBy('kategori_property.nama_kategori_property')
                ->orderBy('views', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'role' => 'User',
                    'summary' => [
                        ['title' => 'Sisa Kuota Iklan', 'value' => $staff->sisa_kuota ?? 0, 'icon' => 'quota'],
                        ['title' => 'Properti Anda', 'value' => $totalPropertiesUser, 'icon' => 'properties'],
                        ['title' => 'Total Dilihat', 'value' => $totalViewsUser, 'icon' => 'views'],
                    ],
                    'views_by_category' => $viewsByCategoryUser
                ]
            ]);
        }
    }

    public function getPurchaseHistory(Request $request)
    {
        $user = $request->user();
        $history = DB::table('transaksi_paket')
            ->join('paket_iklan', 'transaksi_paket.paket_id', '=', 'paket_iklan.id')
            ->where('transaksi_paket.user_id', $user->id_user)
            ->select(
                'transaksi_paket.id',
                'transaksi_paket.kode_transaksi',
                'paket_iklan.nama_paket',
                'paket_iklan.harga',
                'transaksi_paket.status_pembayaran',
                'transaksi_paket.bukti_pembayaran',
                'transaksi_paket.created_at as tanggal_pembelian'
            )
            ->orderBy('transaksi_paket.created_at', 'desc')
            ->get();

        // Convert bukti_pembayaran to full URL
        foreach ($history as $item) {
            if ($item->bukti_pembayaran) {
                $item->bukti_pembayaran = asset('storage/' . str_replace('public/', '', $item->bukti_pembayaran));
            }
        }

        return response()->json(['success' => true, 'data' => $history]);
    }

    public function updatePaymentProof(Request $request, $transactionId)
    {
        $validator = Validator::make($request->all(), [
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $transaction = DB::table('transaksi_paket')
            ->where('id', $transactionId)
            ->where('user_id', $user->id_user)
            ->first();

        if (!$transaction) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
        }

        // Hanya izinkan update jika status 'unverified' atau 'rejected'
        if (!in_array($transaction->status_pembayaran, ['unverified', 'rejected'])) {
            return response()->json(['success' => false, 'message' => 'Tidak dapat mengunggah bukti untuk transaksi ini.'], 403);
        }

        $filePath = null;
        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('public/bukti_pembayaran', $fileName);
        }

        DB::table('transaksi_paket')->where('id', $transactionId)->update([
            'bukti_pembayaran' => $filePath,
            'status_pembayaran' => 'pending', // Set status kembali ke pending untuk direview admin
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Bukti pembayaran berhasil diunggah. Mohon tunggu konfirmasi dari Admin.']);
    }
}
