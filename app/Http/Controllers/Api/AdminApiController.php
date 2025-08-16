<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminApiController extends Controller
{
    /**
     * Mengambil daftar semua transaksi, bisa difilter by status.
     */
    public function getTransactions(Request $request)
    {
        $query = DB::table('transaksi_paket')
            ->join('users', 'transaksi_paket.user_id', '=', 'users.id_user')
            ->join('paket_iklan', 'transaksi_paket.paket_id', '=', 'paket_iklan.id')
            ->select(
                'transaksi_paket.id',
                'transaksi_paket.kode_transaksi',
                'transaksi_paket.status_pembayaran',
                'transaksi_paket.bukti_pembayaran',
                'transaksi_paket.created_at',
                'users.nama as nama_user',
                'paket_iklan.nama_paket'
            )
            ->orderBy('transaksi_paket.created_at', 'desc');

        if ($request->has('status')) {
            $query->where('transaksi_paket.status_pembayaran', $request->status);
        }

        $transactions = $query->get();

        foreach ($transactions as $transaction) {
            if ($transaction->bukti_pembayaran) {
                $transaction->bukti_pembayaran_url = asset('storage/' . str_replace('public/', '', $transaction->bukti_pembayaran));
            } else {
                $transaction->bukti_pembayaran_url = null;
            }
        }

        return response()->json(['data' => $transactions]);
    }

    /**
     * Mengubah status sebuah transaksi (confirm, reject, unverified).
     */
    public function updateTransactionStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:confirmed,rejected,unverified',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $newStatus = $request->status;
        $adminUserId = Auth::id();
        $transaksi = DB::table('transaksi_paket')->where('id', $id)->first();

        if (!$transaksi) {
            return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
        }

        if ($transaksi->status_pembayaran === 'confirmed' && $newStatus === 'confirmed') {
             return response()->json(['message' => 'Transaksi ini sudah pernah dikonfirmasi.'], 409);
        }

        try {
            DB::transaction(function () use ($newStatus, $transaksi, $adminUserId, $id) {
                if ($newStatus === 'confirmed') {
                    $paket = DB::table('paket_iklan')->where('id', $transaksi->paket_id)->first();
                    if (!$paket) throw new \Exception('Paket iklan terkait tidak ditemukan.');

                    DB::table('staff')->where('id_user', $transaksi->user_id)->increment('sisa_kuota_iklan', $paket->kuota_iklan);
                    DB::table('staff')->where('id_user', $transaksi->user_id)->increment('total_kuota_iklan', $paket->kuota_iklan);
                    DB::table('staff')->where('id_user', $transaksi->user_id)->update(['status_staff' => 'Ya']);
                }
                DB::table('transaksi_paket')->where('id', $id)->update([
                    'status_pembayaran' => $newStatus,
                    'dikonfirmasi_oleh' => $adminUserId,
                    'tanggal_konfirmasi' => now()
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui transaksi.', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['message' => "Status transaksi berhasil diubah menjadi '$newStatus'."]);
    }
    
    /**
     * Mengambil data ringkasan untuk dashboard admin.
     */
    public function getDashboardData()
    {
        $total_agents = DB::table('staff')->where('status_staff', 'Ya')->count();
        $pending_transactions = DB::table('transaksi_paket')->where('status_pembayaran', 'pending')->count();
        $total_properties = DB::table('property_db')->count();

        $agents = DB::table('staff')
            ->select('id_staff', 'nama_staff', 'email', 'status_staff', 'total_kuota_iklan', 'sisa_kuota_iklan')
            ->where('status_staff', 'Ya')
            ->orderBy('nama_staff', 'asc')
            ->get();

        return response()->json([
            'summary' => [
                'total_agents' => $total_agents,
                'pending_transactions' => $pending_transactions,
                'total_properties' => $total_properties,
            ],
            'agents' => $agents,
        ]);
    }

    /**
     * Mengambil daftar semua agen (staff) - (Fungsi terpisah jika diperlukan).
     */
    public function getAgents()
    {
        $agents = DB::table('staff')
            ->join('users', 'staff.id_user', '=', 'users.id_user')
            ->select('staff.id_staff', 'staff.nama_staff', 'staff.email', 'staff.status_staff', 'staff.sisa_kuota_iklan', 'users.username')
            ->orderBy('staff.nama_staff', 'asc')
            ->get();
        return response()->json(['data' => $agents]);
    }

    /**
     * Mengubah status properti (active/inactive).
     */
    public function togglePropertyStatus(Request $request, $id)
    {
        $property = DB::table('property_db')->where('id_property', $id);
        if (!$property->exists()) {
            return response()->json(['message' => 'Properti tidak ditemukan.'], 404);
        }
        $newStatus = $property->first()->status == 1 ? 0 : 1;
        $property->update(['status' => $newStatus]);
        $statusText = $newStatus == 1 ? 'active' : 'inactive';
        return response()->json(['message' => "Status properti berhasil diubah menjadi $statusText."]);
    }

    /**
     * Mengambil data analitik dasar.
     */
    public function getVisitorAnalytics(Request $request)
    {
        $perCategory = DB::table('property_db')
            ->join('kategori_property', 'property_db.id_kategori_property', '=', 'kategori_property.id_kategori_property')
            ->select('kategori_property.nama_kategori_property as category', DB::raw('SUM(property_db.view_count) as total_views'))
            ->groupBy('kategori_property.nama_kategori_property')
            ->orderBy('total_views', 'desc')
            ->get();
        
        $perAgent = DB::table('property_db')
            ->join('staff', 'property_db.id_staff', '=', 'staff.id_staff')
            ->select('staff.nama_staff as agent_name', DB::raw('SUM(property_db.view_count) as total_views'))
            ->groupBy('staff.nama_staff')
            ->orderBy('total_views', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'per_category' => $perCategory,
            'top_agents' => $perAgent,
        ]);
    }
}
