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
}
