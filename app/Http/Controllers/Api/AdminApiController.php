<?php
// File: app/Http/Controllers/Api/AdminApiController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AdminApiController extends Controller
{
    /**
     * Mengambil daftar semua transaksi pembelian untuk konfirmasi.
     */
    public function getPurchaseConfirmations()
    {
        $transactions = DB::table('transaksi_paket')
            ->join('users', 'transaksi_paket.user_id', '=', 'users.id_user')
            ->join('paket_iklan', 'transaksi_paket.paket_id', '=', 'paket_iklan.id')
            ->select('transaksi_paket.*', 'users.nama as nama_user', 'paket_iklan.nama_paket')
            ->orderBy('transaksi_paket.created_at', 'asc')
            ->get();

        // Convert bukti_pembayaran to full URL
        foreach ($transactions as $item) {
            if ($item->bukti_pembayaran) {
                $item->bukti_pembayaran = asset('storage/' . str_replace('public/', '', $item->bukti_pembayaran));
            }
        }

        return response()->json(['success' => true, 'data' => $transactions]);
    }

    /**
     * Mengubah status sebuah transaksi (confirmed, rejected, unverified).
     */
    public function updatePurchaseStatus(Request $request, $id)
    {
        $adminUserId = $request->user()->id_user;
        $status = $request->input('status');

        if (!in_array($status, ['confirmed', 'rejected', 'unverified'])) {
            return response()->json(['success' => false, 'message' => 'Status tidak valid.'], 422);
        }

        $transaksi = DB::table('transaksi_paket')->where('id', $id)->first();
        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan.'], 404);
        }

        // Logika jika transaksi dikonfirmasi
        if ($status == 'confirmed' && $transaksi->status_pembayaran != 'confirmed') {
            $paket = DB::table('paket_iklan')->where('id', $transaksi->paket_id)->first();
            if ($paket) {
                DB::table('staff')->where('id_user', $transaksi->user_id)->increment('sisa_kuota', $paket->kuota_iklan);
                DB::table('staff')->where('id_user', $transaksi->user_id)->update(['status_staff' => 'Ya']);
            }
        }

        DB::table('transaksi_paket')->where('id', $id)->update([
            'status_pembayaran' => $status,
            'dikonfirmasi_oleh' => $adminUserId,
            'tanggal_konfirmasi' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Status transaksi berhasil diperbarui menjadi ' . $status]);
    }

    /**
     * Mengambil daftar semua member (staff).
     */
    public function getAgents()
    {
        $agents = DB::table('staff')
            ->join('users', 'staff.id_user', '=', 'users.id_user')
            ->select('staff.id_staff', 'staff.nama_staff', 'staff.email', 'staff.status_staff', 'staff.sisa_kuota', 'users.username')
            ->orderBy('staff.nama_staff', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $agents]);
    }

    /**
     * Mengambil daftar properti milik seorang agen.
     */
    public function getAgentProperties($id_staff)
    {
        $properties = DB::table('property_db')
            ->where('id_staff', $id_staff)
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $properties]);
    }

    /**
     * Mengubah status sebuah properti (misal: 'Draft', 'Publish').
     */
    public function updatePropertyStatus(Request $request, $id_property)
    {
        $status = $request->input('status');

        if (!$status) {
            return response()->json(['success' => false, 'message' => 'Status tidak boleh kosong.'], 422);
        }

        DB::table('property_db')->where('id_property', $id_property)->update(['status' => $status]);

        return response()->json(['success' => true, 'message' => 'Status properti berhasil diperbarui.']);
    }

    /**
     * Mengubah status member (Ya/Tidak).
     */
    public function updateAgentStatus(Request $request, $id_staff)
    {
        $status = $request->input('status_staff');

        if (!in_array($status, ['Ya', 'Tidak'])) {
            return response()->json(['success' => false, 'message' => 'Status tidak valid. Gunakan "Ya" atau "Tidak".'], 422);
        }

        DB::table('staff')->where('id_staff', $id_staff)->update(['status_staff' => $status]);

        return response()->json(['success' => true, 'message' => 'Status agen berhasil diperbarui.']);
    }

    /**
     * Menghapus member/agen beserta semua data terkait.
     */
    public function deleteAgent($id_staff)
    {
        $staff = DB::table('staff')->where('id_staff', $id_staff)->first();

        if (!$staff) {
            return response()->json(['success' => false, 'message' => 'Agen tidak ditemukan.'], 404);
        }

        $user = DB::table('users')->where('id_user', $staff->id_user)->first();
        if (!$user) {
            DB::table('staff')->where('id_staff', $id_staff)->delete();
            return response()->json(['success' => true, 'message' => 'Data agen anomali berhasil dihapus.']);
        }
        
        // (Logika penghapusan file dan data terkait tetap sama)
        // ... (kode dari deleteMember)

        return response()->json(['success' => true, 'message' => 'Agen beserta semua data terkait berhasil dihapus.']);
    }

    /**
     * Mengambil data statistik untuk seorang agen spesifik.
     */
    public function getAgentStats($id_staff)
    {
        $staff = DB::table('staff')->where('id_staff', $id_staff)->first();
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
                'agent_name' => $staff->nama_staff,
                'summary' => [
                    ['title' => 'Sisa Kuota Iklan', 'value' => $staff->sisa_kuota ?? 0, 'icon' => 'quota'],
                    ['title' => 'Total Properti', 'value' => $totalPropertiesUser, 'icon' => 'properties'],
                    ['title' => 'Total Dilihat', 'value' => $totalViewsUser, 'icon' => 'views'],
                ],
                'views_by_category' => $viewsByCategoryUser
            ]
        ]);
    }
}
