<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffModel extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'staff';

    // Menentukan primary key
    protected $primaryKey = 'id_staff';

    // Laravel tidak mengelola timestamps (created_at, updated_at) untuk tabel ini
    public $timestamps = false;

    // Mendefinisikan kolom yang bisa diisi secara massal (mass assignable)
    protected $fillable = [
        'id_user', 'id_kategori_staff', 'nickname_staff', 'nama_staff',
        'slug_staff', 'jabatan', 'pendidikan', 'expertise', 'email',
        'telepon', 'id_provinsi', 'id_kabupaten', 'id_kecamatan', 'isi',
        'gambar', 'status_staff', 'keywords', 'urutan'
    ];

    /**
     * Mendefinisikan relasi ke model KategoriStaff.
     */
    public function kategoriStaff()
    {
        return $this->belongsTo(KategoriStaff::class, 'id_kategori_staff');
    }
}
