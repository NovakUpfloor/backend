<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Helpers\Website;

class Login extends Controller
{
    // Main page
    public function index()
    {
    	$site = DB::table('konfigurasi')->first();
        $data = array(  'title'     => 'Login Administrator',
    					'site'		=> $site);

        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $captcha = '';
        for ($i = 0; $i < 4; $i++) {
            $captcha .= $characters[rand(0, strlen($characters) - 1)];
        }

        // Simpan CAPTCHA ke session
        Session::put('captcha_answer', $captcha);
        Session::put('captcha_expire', now()->addMinutes(5));

        $data = array(
            'title'     => 'Login Administrator',
            'captcha_question' => $captcha, 
        );
        return view('login/index', $data);
    }

    public function check(Request $request)
    {
        request()->validate([
            'username' => 'required',
            'password' => 'required',
            'captcha'  => 'required|string' // Validasi input captcha sebagai string
        ]);

        // --- VALIDASI JAWABAN CAPTCHA ---
        // Cek apakah captcha sudah kedaluwarsa
        if (Session::has('captcha_expire') && now()->gt(Session::get('captcha_expire'))) {
            Session::forget(['captcha_answer', 'captcha_expire']);
            return redirect('login')->with(['warning' => 'CAPTCHA sudah kedaluwarsa, silakan muat ulang dan coba lagi.']);
        }

        // Cek jawaban (case-insensitive)
        if (strtoupper($request->captcha) != Session::get('captcha_answer')) {
            return redirect('login')->with(['warning' => 'Kode verifikasi salah. Silakan coba lagi.']);
        }
        
        // Hapus session captcha setelah berhasil divalidasi
        Session::forget(['captcha_answer', 'captcha_expire']);
        // --- AKHIR VALIDASI CAPTCHA ---

        $username   = $request->username;
        $password   = $request->password;
        $model      = new User();
        $user       = $model->login($username,$password);
        if($user) {
            // --- LOGIKA BARU: CEK & BUAT STAFF ---
            $staff = DB::table('staff')->where('id_user', $user->id_user)->first();
            if(!$staff) {
                // Jika staff belum ada, buat baru dengan data dasar
                DB::table('staff')->insert([
                    'id_user'           => $user->id_user,
                    'nama_staff'        => $user->nama, // Ambil dari nama user
                    'email'             => $user->email,  // Ambil dari email user
                    'status_staff'      => 'Ya',
                    'tanggal'    => now()
                ]);
                // Ambil lagi data staff yang baru dibuat
                $staff = DB::table('staff')->where('id_user', $user->id_user)->first();
                }
            
            // --- AKHIR LOGIKA BARU ---

            $request->session()->put('id_user', $user->id_user);
            $request->session()->put('nama', $user->nama);
            $request->session()->put('nodbpswrd', $password);
            $request->session()->put('username', $user->username);
            $request->session()->put('akses_level', $user->akses_level);
            $request->session()->put('is_member', ($staff && $staff->total_kuota_iklan > 0));
            $request->session()->put('id_staff', $staff ? $staff->id_staff : null);
            return redirect('admin/dasbor')->with(['sukses' => 'Anda berhasil login']);
        }else{
            return redirect('login')->with(['warning' => 'Mohon maaf, Username atau password salah']);
        }
    }

    // Homepage
    public function logout()
    {
        Session()->forget('id_user');
        Session()->forget('nama');
        Session()->forget('username');
        Session()->forget('akses_level');
        return redirect('login')->with(['sukses' => 'Anda berhasil logout']);
    }

    // Forgot password
    public function fogot()
    {
    	$site = DB::table('konfigurasi')->first();
       	$data = array(  'title'     => 'Lupa Password',
    					'site'		=> $site);
        return view('login/lupa',$data);
    }
}
