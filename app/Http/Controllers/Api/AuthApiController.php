<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User; // Pastikan model User di-import
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Gunakan Hash bawaan Laravel
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class AuthApiController extends Controller
{
    // --- Method register akan kita tambahkan di sini ---
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama'     => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email'    => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'paket_id' => 'required|integer|exists:paket_iklan,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Cek juga di tabel verifikasi agar tidak ada duplikasi
        $isPending = DB::table('users_verification')
                        ->where('email', $request->email)
                        ->orWhere('username', $request->username)
                        ->exists();

        if ($isPending) {
            return response()->json(['message' => 'Email atau username sudah terdaftar dan menunggu verifikasi.'], 422);
        }

        $token = Str::random(60);

        DB::table('users_verification')->insert([
            'nama'            => $request->nama,
            'email'           => $request->email,
            'username'        => $request->username,
            'password'        => Hash::make($request->password),
            'paket_id'        => $request->paket_id,
            'token'           => $token,
            'tanggal_expired' => Carbon::now()->addDays(3),
            'created_at'      => now(),
            'updated_at'      => now()
        ]);

        // Kirim email verifikasi, menggunakan view yang sudah ada
        // Pastikan view 'emails.verification' ada di resources/views/emails/
        Mail::send('emails.verification', ['token' => $token], function($message) use ($request){
            $message->to($request->email);
            $message->subject('Verifikasi Alamat Email Anda - Waisaka Property');
        });

        return response()->json([
            'message' => 'Pendaftaran berhasil. Silakan cek email Anda untuk link aktivasi.'
        ], 201);
    }

    // --- Method login akan kita tambahkan di sini ---

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('username', $request->username)
                    ->orWhere('email', $request->username)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Kredensial tidak valid.'], 401);
        }

        // Ambil data staff yang terhubung
        $staff = DB::table('staff')->where('id_user', $user->id_user)->first();

        // Buat token
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'token' => $token,
            'user' => [
                'id_user' => $user->id_user,
                'id_staff' => $staff ? $staff->id_staff : null,
                'nama' => $user->nama,
                'username' => $user->username,
                'email' => $user->email,
                'akses_level' => $user->akses_level,
                'sisa_kuota' => $staff ? $staff->sisa_kuota_iklan : 0,
            ]
        ], 200);
    }

    // --- Method logout akan kita tambahkan di sini ---

  public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil.'], 200);
    }

    public function verifyEmail($token)
    {
        $verificationData = DB::table('users_verification')->where('token', $token)->first();

        if (!$verificationData) {
            return response()->json(['message' => 'Token verifikasi tidak valid.'], 404);
        }

        if (Carbon::now()->gt($verificationData->tanggal_expired)) {
            DB::table('users_verification')->where('token', $token)->delete();
            return response()->json(['message' => 'Token verifikasi telah kedaluwarsa.'], 410);
        }

        // Ambil detail paket
        $paket = DB::table('paket_iklan')->where('id', $verificationData->paket_id)->first();
        if (!$paket) {
            // Hapus token jika paket tidak valid untuk mencegah percobaan ulang
            DB::table('users_verification')->where('token', $token)->delete();
            return response()->json(['message' => 'Paket iklan yang dipilih tidak valid.'], 400);
        }

        try {
            DB::transaction(function () use ($verificationData, $paket, $token) {
                // 1. Create User
                $user = User::create([
                    'nama'        => $verificationData->nama,
                    'email'       => $verificationData->email,
                    'username'    => $verificationData->username,
                    'password'    => $verificationData->password, // Password sudah di-hash
                    'akses_level' => 'User',
                    'paket_id'    => $verificationData->paket_id,
                ]);

                // 2. Create Staff associated with the User
                $staffId = DB::table('staff')->insertGetId([
                    'id_user'             => $user->id_user,
                    'nama_staff'          => $user->nama,
                    'email'               => $user->email,
                    'status_staff'        => 'Ya', // Langsung aktif
                    'id_kategori_staff'   => 1, // Asumsi 1 = Agen/Marketing
                    'slug_staff'          => Str::slug($user->nama, '-'),
                    'urutan'              => 100,
                    'total_kuota_iklan'   => $paket->kuota_iklan,
                    'sisa_kuota_iklan'    => $paket->kuota_iklan,
                    'id_provinsi'         => 0, // Default, bisa diupdate di profil
                    'id_kabupaten'        => 0, // Default
                    'id_kecamatan'        => 0, // Default
                ]);

                // 3. Jika paket berbayar, buat entri transaksi
                if ($paket->harga > 0) {
                    DB::table('transaksi_paket')->insert([
                        'user_id'           => $user->id_user,
                        'id_staff'          => $staffId,
                        'paket_id'          => $paket->id,
                        'kode_transaksi'    => 'WSA-' . strtoupper(Str::random(8)),
                        'status_pembayaran' => 'pending', // Menunggu pembayaran
                        'created_at'        => now(),
                        'updated_at'        => now(),
                    ]);
                }

                // 4. Delete the verification token
                DB::table('users_verification')->where('token', $token)->delete();
            });

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan saat aktivasi akun.',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json(['message' => 'Akun Anda telah berhasil diaktivasi. Silakan login.'], 200);
    }
}