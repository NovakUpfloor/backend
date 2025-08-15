<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;

class User extends Controller
{

    public function index()
    {
    	if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
		$user 	= DB::table('users')->orderBy('id_user','DESC')->get();

		$data = array(  'title'     => 'Pengguna Website',
						'user'      => $user,
                        'content'   => 'admin/user/index'
                    );
        return view('admin/layout/wrapper',$data);
    }

    
    public function edit($id_user)
    {
        if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        $user   = DB::table('users')->where('id_user',$id_user)->orderBy('id_user','DESC')->first();

        $data = array(  'title'     => 'Edit Pengguna Website',
                        'user'      => $user,
                        'content'   => 'admin/user/edit'
                    );
        return view('admin/layout/wrapper',$data);
    }

   public function proses(Request $request)
    {
        $site   = DB::table('konfigurasi')->first();
        
        if(isset($_POST['hapus'])) {
            $id_usernya       = $request->id_user;
            for($i=0; $i < sizeof($id_usernya);$i++) {
                $this->_deleteUserData($id_usernya[$i]);
            }
            return redirect('admin/user')->with(['sukses' => 'Data telah dihapus']);
        }
    }


    public function tambah(Request $request)
    {
    	if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
    	request()->validate([
                            'nama'     => 'required',
					        'username' => 'required|unique:users',
					        'password' => 'required',
                            'email'    => 'required',
                            'gambar'   => 'file|image|mimes:jpeg,png,jpg|max:8024',
					        ]);
        // UPLOAD START
        $image                  = $request->file('gambar');
        if(!empty($image)) {
            $filenamewithextension  = $request->file('gambar')->getClientOriginalName();
            $filename               = pathinfo($filenamewithextension, PATHINFO_FILENAME);
            $input['nama_file']     = Str::slug($filename, '-').'-'.time().'.'.$image->getClientOriginalExtension();
            $destinationPath        = './assets/upload/user/thumbs/';
            $img = Image::make($image->getRealPath(),array(
                'width'     => 150,
                'height'    => 150,
                'grayscale' => false
            ));
            $img->save($destinationPath.'/'.$input['nama_file']);
            $destinationPath = './assets/upload/user/';
            $image->move($destinationPath, $input['nama_file']);
            // END UPLOAD
            DB::table('users')->insert([
                'nama'          => $request->nama,
                'email'	        => $request->email,
                'username'   	=> $request->username,
                'password'      => sha1($request->password),
                'akses_level'   => $request->akses_level,
                'gambar'        => $input['nama_file']
            ]);
        }else{
             DB::table('users')->insert([
                'nama'          => $request->nama,
                'email'         => $request->email,
                'username'      => $request->username,
                'password'      => sha1($request->password),
                'akses_level'   => $request->akses_level
            ]);
        }
        return redirect('admin/user')->with(['sukses' => 'Data telah ditambah']);
    }

    // edit
    public function proses_edit(Request $request)
    {
    	if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
    	request()->validate([
					        'nama'     => 'required',
                            'username' => 'required',
                            'password' => 'required',
                            'email'    => 'required',
                            'gambar'   => 'file|image|mimes:jpeg,png,jpg|max:8024',
					        ]);
        // UPLOAD START
        $image                  = $request->file('gambar');
        if(!empty($image)) {
            // UPLOAD START
            $filenamewithextension  = $request->file('gambar')->getClientOriginalName();
            $filename               = pathinfo($filenamewithextension, PATHINFO_FILENAME);
            $input['nama_file']     = Str::slug($filename, '-').'-'.time().'.'.$image->getClientOriginalExtension();
            $destinationPath        = './assets/upload/user/thumbs/';
            $img = Image::make($image->getRealPath(),array(
                'width'     => 150,
                'height'    => 150,
                'grayscale' => false
            ));
            $img->save($destinationPath.'/'.$input['nama_file']);
            $destinationPath = './assets/upload/user/';
            $image->move($destinationPath, $input['nama_file']);
            // END UPLOAD
            $slug_user = Str::slug($request->nama, '-');
            DB::table('users')->where('id_user',$request->id_user)->update([
                'nama'          => $request->nama,
                'email'         => $request->email,
                'username'      => $request->username,
                'password'      => sha1($request->password),
                'akses_level'   => $request->akses_level,
                'gambar'        => $input['nama_file']
            ]);
        }else{
            $slug_user = Str::slug($request->nama, '-');
            DB::table('users')->where('id_user',$request->id_user)->update([
                'nama'          => $request->nama,
                'email'         => $request->email,
                'username'      => $request->username,
                'password'      => sha1($request->password),
                'akses_level'   => $request->akses_level
            ]);
        }
        return redirect('admin/user')->with(['sukses' => 'Data telah diupdate']);
    }


     // --- FUNGSI BARU UNTUK MENAMPILKAN HALAMAN SIGN UP ---
    public function signup()
    {
        // Cukup tampilkan halaman view untuk pendaftaran
        return view('admin/user/signup');
    }


    // --- FUNGSI PROSES SIGN UP DIPERBARUI ---
    public function proses_signup(Request $request)
    {
    	request()->validate([
            'nama'     => 'required',
            'username' => 'required|unique:users,username|unique:users_verification,username',
            'password' => 'required|min:6',
            'email'    => 'required|email|unique:users,email|unique:users_verification,email',
            'gambar'   => 'nullable|file|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $token = Str::random(60); // Buat token verifikasi acak

        // Siapkan data untuk disimpan di tabel verifikasi
        $data = [
            'nama'          => $request->nama,
            'email'	        => $request->email,
            'username'   	=> $request->username,
            'password'      => sha1($request->password),
            'token'         => $token,
            'tanggal_expired' => Carbon::now()->addDays(3), // Set expired 3 hari dari sekarang
            'created_at'    => now(),
            'updated_at'    => now()
        ];

        // Proses upload gambar jika ada
        $image = $request->file('gambar');
        if(!empty($image)) {
            $filenamewithextension  = $request->file('gambar')->getClientOriginalName();
            $filename               = pathinfo($filenamewithextension, PATHINFO_FILENAME);
            $input['nama_file']     = Str::slug($filename, '-').'-'.time().'.'.$image->getClientOriginalExtension();
            $destinationPath        = './assets/upload/user/';
            $image->move($destinationPath, $input['nama_file']);
            $data['gambar'] = $input['nama_file'];
        }

        // Simpan data ke tabel users_verification
        DB::table('users_verification')->insert($data);

        // Kirim email verifikasi
        Mail::send('emails.verification', ['token' => $token], function($message) use ($request){
            $message->to($request->email);
            $message->subject('Verifikasi Alamat Email Anda - WaisakaProperty');
        });

        return redirect('login')->with(['sukses' => 'Pendaftaran berhasil. Silakan cek email Anda untuk link aktivasi yang berlaku selama 3 hari.']);
    }

    // --- FUNGSI BARU UNTUK MENANGANI VERIFIKASI ---
    public function verifyEmail(Request $request)
    {
        $token = $request->get('token');
        $verification_data = DB::table('users_verification')->where('token', $token)->first();

        // Cek jika token tidak ada
        if(!$verification_data) {
            return redirect('login')->with(['warning' => 'Token verifikasi tidak valid.']);
        }

        // Cek jika token sudah expired
        if(Carbon::now()->gt($verification_data->tanggal_expired)) {
            // Hapus data yang sudah expired
            DB::table('users_verification')->where('token', $token)->delete();
            return redirect('login')->with(['warning' => 'Waktu pendaftaran Anda sudah berakhir (expired). Silakan daftar kembali.']);
        }

        // Jika valid, pindahkan data ke tabel users dan dapatkan ID-nya
        $newUserId = DB::table('users')->insertGetId([
            'nama'          => $verification_data->nama,
            'email'         => $verification_data->email,
            'username'      => $verification_data->username,
            'password'      => $verification_data->password,
            'gambar'        => $verification_data->gambar,
            'akses_level'   => 'User',
            'created_at'    => now(),
            'updated_at'    => now()
        ]);

        // Buat entri staff baru untuk user ini dan dapatkan ID-nya
        $newStaffId = DB::table('staff')->insertGetId([
            'id_user'           => $newUserId,
            'nama_staff'        => $verification_data->nama,
            'email'             => $verification_data->email,
            'status_staff'      => 'Tidak', // "Tidak" berarti belum dikonfirmasi oleh admin
            'slug_staff'        => Str::slug($verification_data->nama, '-'),
            'id_kategori_staff' => 1, // Asumsi default kategori staff adalah 1 (misal: "Agent Baru")
            'total_kuota_iklan' => 0,
            'sisa_kuota_iklan'  => 0,
            'created_at'        => now(),
            'updated_at'        => now()
        ]);

        // Ambil data paket yang dibeli
        $paket = DB::table('paket_iklan')->where('id', $verification_data->paket_id)->first();

        // Buat entri transaksi untuk pembelian paket
        if ($paket) {
            DB::table('transaksi_paket')->insert([
                'user_id'           => $newUserId,
                'id_staff'          => $newStaffId,
                'paket_id'          => $verification_data->paket_id,
                'kode_transaksi'    => 'WPM-' . strtoupper(Str::random(8)),
                'status_pembayaran' => 'pending', // Menunggu konfirmasi admin
                'bukti_pembayaran'  => $verification_data->gambar, // Path dari kolom gambar
                'created_at'        => now(),
                'updated_at'        => now()
            ]);
        }

        // Hapus data dari tabel verifikasi
        DB::table('users_verification')->where('token', $token)->delete();

        // Redirect ke halaman login dengan pesan sukses yang informatif
        return redirect('login')->with(['sukses' => 'Verifikasi berhasil! Akun Anda sudah aktif. Silakan login untuk melihat status konfirmasi pembelian paket Anda.']);
    }

    // Delete (FUNGSI YANG DIPERBAIKI)
    public function delete($id_user)
    {
        if(Session()->get('username')=="") { return redirect('login')->with(['warning' => 'Mohon maaf, Anda belum login']);}
        
        $this->_deleteUserData($id_user);

    	return redirect('admin/user')->with(['sukses' => 'Data pengguna beserta semua data terkait telah berhasil dihapus.']);
    }

    // FUNGSI PRIVATE UNTUK LOGIKA PENGHAPUSAN
    private function _deleteUserData($id_user)
    {
        // 1. Ambil data user & staff
        $user = DB::table('users')->where('id_user', $id_user)->first();
        if (!$user) {
            return; // Jika user tidak ada, hentikan proses
        }
        $staff = DB::table('staff')->where('id_user', $id_user)->first();

        // 2. Hapus data properti dan gambar properti (jika user adalah staff)
        if ($staff) {
            $properties = DB::table('property_db')->where('id_staff', $staff->id_staff)->get();
            foreach ($properties as $property) {
                $propertyImages = DB::table('property_img')->where('id_property', $property->id_property)->get();
                foreach ($propertyImages as $image) {
                    File::delete(public_path('assets/upload/property/' . $image->gambar));
                }
                DB::table('property_img')->where('id_property', $property->id_property)->delete();
            }
            DB::table('property_db')->where('id_staff', $staff->id_staff)->delete();
            
            // Hapus foto staff
            File::delete(public_path('assets/upload/staff/' . $staff->gambar));
            File::delete(public_path('assets/upload/staff/thumbs/' . $staff->gambar));
        }

        // 3. Hapus data transaksi dan bukti pembayaran
        $transactions = DB::table('transaksi_paket')->where('user_id', $id_user)->get();
        foreach ($transactions as $transaction) {
            File::delete(public_path('assets/upload/bukti/' . $transaction->bukti_pembayaran));
        }
        DB::table('transaksi_paket')->where('user_id', $id_user)->delete();

        // 4. Hapus foto profil user
        File::delete(public_path('assets/upload/user/' . $user->gambar));
        File::delete(public_path('assets/upload/user/thumbs/' . $user->gambar));

        // 5. Hapus data dari tabel staff dan users
        DB::table('staff')->where('id_user', $id_user)->delete();
        DB::table('users')->where('id_user', $id_user)->delete();
    }
}
