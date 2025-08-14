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
     * Mengambil daftar semua transaksi yang menunggu konfirmasi.
     */
    public function getActivations()
    {
        $pendingTransactions = DB::table('transaksi_paket')
            ->join('users', 'transaksi_paket.user_id', '=', 'users.id_user')
            ->join('paket_iklan', 'transaksi_paket.paket_id', '=', 'paket_iklan.id')
            ->select('transaksi_paket.*', 'users.nama as nama_user', 'paket_iklan.nama_paket')
            ->where('transaksi_paket.status_pembayaran', 'pending')
            ->orderBy('transaksi_paket.created_at', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $pendingTransactions]);
    }

    /**
     * Menyetujui sebuah transaksi dan menambahkan kuota ke user.
     */
    public function approveActivation(Request $request, $id)
    {
        $adminUserId = $request->user()->id_user;

        $transaksi = DB::table('transaksi_paket')->where('id', $id)->where('status_pembayaran', 'pending')->first();
        if (!$transaksi) {
            return response()->json(['success' => false, 'message' => 'Transaksi tidak ditemukan atau sudah diproses.'], 404);
        }

        $paket = DB::table('paket_iklan')->where('id', $transaksi->paket_id)->first();
        if (!$paket) {
            return response()->json(['success' => false, 'message' => 'Paket iklan terkait tidak ditemukan.'], 404);
        }

        // Update kuota di tabel staff
        DB::table('staff')->where('id_user', $transaksi->user_id)->increment('sisa_kuota', $paket->kuota_iklan);
        DB::table('staff')->where('id_user', $transaksi->user_id)->increment('total_kuota_iklan', $paket->kuota_iklan);
        
        // Update status transaksi
        DB::table('transaksi_paket')->where('id', $id)->update([
            'status_pembayaran' => 'confirmed',
            'dikonfirmasi_oleh' => $adminUserId,
            'tanggal_konfirmasi' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Transaksi berhasil dikonfirmasi dan kuota telah ditambahkan.']);
    } 
    
    /**
     * Mengambil daftar semua member (staff).
     * FUNGSI INI DIPINDAHKAN KE SINI
     */
    public function getMembers()
    {
        $members = DB::table('staff')
            ->join('users', 'staff.id_user', '=', 'users.id_user')
            ->select('staff.id_staff', 'staff.nama_staff', 'staff.email', 'staff.status_staff', 'staff.sisa_kuota', 'users.username')
            ->orderBy('staff.nama_staff', 'asc')
            ->get();

        return response()->json(['success' => true, 'data' => $members]);
    }

    /**
     * Mengubah status member (ban/unban).
     * FUNGSI INI DIPINDAHKAN KE SINI
     */
    public function updateStatus(Request $request, $id_staff)
    {
        $status = $request->input('status_staff'); // Harapannya 'Ya' atau 'Tidak'

        if (!in_array($status, ['Ya', 'Tidak'])) {
            return response()->json(['success' => false, 'message' => 'Status tidak valid. Gunakan "Ya" atau "Tidak".'], 422);
        }

        DB::table('staff')->where('id_staff', $id_staff)->update(['status_staff' => $status]);

        return response()->json(['success' => true, 'message' => 'Status member berhasil diperbarui.']);
    }

    /**
     * Menghapus member secara permanen. (Versi API)
     * FUNGSI INI DIPINDAHKAN KE SINI
     */
    public function deleteMember($id_staff)
    {
        $staff = DB::table('staff')->where('id_staff', $id_staff)->first();

        if (!$staff) {
            return response()->json(['success' => false, 'message' => 'Member tidak ditemukan.'], 404);
        }

        $user = DB::table('users')->where('id_user', $staff->id_user)->first();
        if (!$user) {
            DB::table('staff')->where('id_staff', $id_staff)->delete();
            return response()->json(['success' => true, 'message' => 'Data staff anomali berhasil dihapus.']);
        }

        // Hapus Properti & Gambar
        $properties = DB::table('property_db')->where('id_staff', $staff->id_staff)->get();
        foreach ($properties as $property) {
            $propertyImages = DB::table('property_img')->where('id_property', $property->id_property)->get();
            foreach ($propertyImages as $image) {
                File::delete(public_path('assets/upload/property/' . $image->gambar));
            }
            DB::table('property_img')->where('id_property', $property->id_property)->delete();
        }
        DB::table('property_db')->where('id_staff', $staff->id_staff)->delete();
        
        // Hapus Foto Staff
        File::delete(public_path('assets/upload/staff/' . $staff->gambar));
        File::delete(public_path('assets/upload/staff/thumbs/' . $staff->gambar));

        // Hapus Transaksi & Bukti Bayar
        $transactions = DB::table('transaksi_paket')->where('user_id', $user->id_user)->get();
        foreach ($transactions as $transaction) {
            File::delete(public_path('assets/upload/bukti/' . $transaction->bukti_pembayaran));
        }
        DB::table('transaksi_paket')->where('user_id', $user->id_user)->delete();

        // Hapus Foto Profil User
        File::delete(public_path('assets/upload/user/' . $user->gambar));
        File::delete(public_path('assets/upload/user/thumbs/' . $user->gambar));

        // Hapus data dari tabel staff dan users
        DB::table('staff')->where('id_user', $user->id_user)->delete();
        DB::table('users')->where('id_user', $user->id_user)->delete();

        return response()->json(['success' => true, 'message' => 'Member beserta semua data terkait berhasil dihapus.']);
    }
}
