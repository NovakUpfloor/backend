<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaketIklan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PackageApiController extends Controller
{
    /**
     * Menampilkan semua paket iklan yang aktif.
     */
    public function index()
    {
        $packages = PaketIklan::where('is_active', 1)
                                ->orderBy('harga', 'asc')
                                ->get();

        return response()->json(['data' => $packages]);
    }

    /**
     * Memproses pembelian paket oleh pengguna yang terotentikasi.
     */
    public function purchase(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'paket_id' => 'required|integer|exists:paket_iklan,id',
            'bukti_pembayaran' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'nomor_whatsapp' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $staff = DB::table('staff')->where('id_user', $user->id_user)->first();

        if (!$staff) {
            return response()->json(['message' => 'Profil staff tidak ditemukan.'], 404);
        }

        // Handle file upload
        $filePath = null;
        if ($request->hasFile('bukti_pembayaran')) {
            $file = $request->file('bukti_pembayaran');
            $fileName = $user->id_user . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('bukti_pembayaran', $fileName, 'public');
        }

        // Buat transaksi baru
        DB::table('transaksi_paket')->insert([
            'user_id' => $user->id_user,
            'id_staff' => $staff->id_staff,
            'paket_id' => $request->paket_id,
            'kode_transaksi' => 'WSA-' . strtoupper(Str::random(8)),
            'status_pembayaran' => 'pending',
            'bukti_pembayaran' => $filePath,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update nomor whatsapp di tabel staff
        DB::table('staff')->where('id_staff', $staff->id_staff)->update(['telepon' => $request->nomor_whatsapp]);

        return response()->json(['message' => 'Permintaan pembelian telah dikirim. Mohon tunggu konfirmasi dari admin.'], 201);
    }
}