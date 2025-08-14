<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KategoriStaff extends Model
{
    use HasFactory;

    protected $table = 'kategori_staff';
    protected $primaryKey = 'id_kategori_staff';
    public $timestamps = false;

    /**
     * Relasi one-to-many ke StaffModel.
     */
    public function staff()
    {
        return $this->hasMany(StaffModel::class, 'id_kategori_staff', 'id_kategori_staff');
    }
}
