<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class ForgotPasswordController extends Controller
{
    /**
     * Menampilkan halaman form untuk memasukkan email
     */
    public function showLinkRequestForm()
    {
        $site = DB::table('konfigurasi')->first();
        $data = [
            'title' => 'Lupa Password',
            'site'  => $site
        ];
        return view('login.forgot-password', $data);
    }

    /**
     * Mengirim link reset password ke email pengguna
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = DB::table('users')->where('email', $request->email)->first();

        if (!$user) {
            return back()->with(['warning' => 'Email tidak terdaftar di sistem kami.']);
        }

        // Buat token reset password
        $token = Str::random(60);

        // Simpan token ke database (kita akan buat tabel baru)
        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $token,
            'created_at' => Carbon::now()
        ]);

        // Kirim email
        Mail::send('emails.password-reset-link', ['token' => $token], function($message) use ($request) {
            $message->to($request->email);
            $message->subject('Reset Password Akun Waisaka Property');
        });

        return redirect('login')->with(['sukses' => 'Link reset password telah dikirim ke email Anda.']);
    }

    /**
     * Menampilkan form untuk reset password
     */
    public function showResetForm(Request $request, $token = null)
    {
        $site = DB::table('konfigurasi')->first();
        $data = [
            'title' => 'Reset Password',
            'site'  => $site,
            'token' => $token
        ];
        return view('login.reset-password', $data);
    }

    /**
     * Memproses reset password
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6',
        ]);

        $reset = DB::table('password_resets')
                    ->where('email', $request->email)
                    ->where('token', $request->token)
                    ->first();

        if (!$reset) {
            return back()->with(['warning' => 'Token reset password tidak valid atau email salah.']);
        }
        
        // Update password di tabel users
        DB::table('users')->where('email', $request->email)->update([
            'password' => sha1($request->password) // Enkripsi menggunakan sha1
        ]);

        // Hapus token yang sudah digunakan
        DB::table('password_resets')->where('email', $request->email)->delete();

        return redirect('login')->with(['sukses' => 'Password Anda berhasil diubah. Silakan login dengan password baru.']);
    
    
    
    }

}