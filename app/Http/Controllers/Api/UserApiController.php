<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserApiController extends Controller
{
    /**
     * Mengambil data profil user dan staff yang sedang login.
     */
    public function profile(Request $request)
    {
        $user = $request->user();
        $staff = DB::table('staff as s')
            ->leftJoin('provinsi as p', 's.id_provinsi', '=', 'p.id')
            ->leftJoin('kabupaten as k', 's.id_kabupaten', '=', 'k.id')
            ->leftJoin('kecamatan as kec', 's.id_kecamatan', '=', 'kec.id')
            ->select('s.*', 'p.nama as provinsi', 'k.nama as kabupaten', 'kec.nama as kecamatan')
            ->where('s.id_user', $user->id_user)
            ->first();

        if (!$staff) {
            return response()->json(['message' => 'Profil staff tidak ditemukan.'], 404);
        }

        return response()->json(['data' => [
            'user' => $user,
            'staff_profile' => $staff
        ]]);
    }

    /**
     * Memperbarui data profil staff.
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        $staff = DB::table('staff')->where('id_user', $user->id_user)->first();

        if (!$staff) {
            return response()->json(['message' => 'Profil staff tidak ditemukan.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_staff' => 'required|string|max:255',
            'jabatan' => 'nullable|string|max:200',
            'telepon' => 'nullable|string|max:255',
            'pendidikan' => 'nullable|string|max:255',
            'expertise' => 'nullable|string|max:255',
            'id_provinsi' => 'required|integer|exists:provinsi,id',
            'id_kabupaten' => 'required|integer|exists:kabupaten,id',
            'id_kecamatan' => 'required|integer|exists:kecamatan,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::table('staff')->where('id_staff', $staff->id_staff)->update([
            'nama_staff' => $request->nama_staff,
            'jabatan' => $request->jabatan,
            'telepon' => $request->telepon,
            'pendidikan' => $request->pendidikan,
            'expertise' => $request->expertise,
            'id_provinsi' => $request->id_provinsi,
            'id_kabupaten' => $request->id_kabupaten,
            'id_kecamatan' => $request->id_kecamatan,
            'updated_at' => now(),
        ]);

        return response()->json(['message' => 'Profil berhasil diperbarui.']);
    }

    /**
     * Mengambil riwayat transaksi paket iklan pengguna.
     */
    public function purchaseHistory(Request $request)
    {
        $user = $request->user();
        $history = DB::table('transaksi_paket as tp')
            ->join('paket_iklan as pi', 'tp.paket_id', '=', 'pi.id')
            ->select('tp.kode_transaksi', 'pi.nama_paket', 'pi.harga', 'tp.status_pembayaran', 'tp.created_at')
            ->where('tp.user_id', $user->id_user)
            ->orderBy('tp.created_at', 'desc')
            ->get();

        return response()->json(['data' => $history]);
    }

    /**
     * Mengambil data untuk dashboard pengguna.
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $staff = DB::table('staff')->where('id_user', $user->id_user)->first();

        if (!$staff) {
            return response()->json(['message' => 'Profil staff tidak ditemukan.'], 404);
        }

        $total_ads = DB::table('property_db')->where('id_staff', $staff->id_staff)->count();
        $total_views = DB::table('property_db')->where('id_staff', $staff->id_staff)->sum('view_count');
        $sisa_kuota = $staff->sisa_kuota_iklan;

        $ads_per_category = DB::table('property_db as p')
            ->join('kategori_property as kp', 'p.id_kategori_property', '=', 'kp.id_kategori_property')
            ->select('kp.nama_kategori_property', DB::raw('count(p.id_property) as total'))
            ->where('p.id_staff', $staff->id_staff)
            ->groupBy('kp.nama_kategori_property')
            ->get();

        return response()->json([
            'data' => [
                'total_ads' => $total_ads,
                'total_views' => (int)$total_views,
                'remaining_quota' => $sisa_kuota,
                'ads_per_category' => $ads_per_category,
            ]
        ]);
    }
}
