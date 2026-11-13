<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produks';

    protected $fillable = [
        'nama_produk',
        'kategori',
        'merek',
        'harga',
        'stok',
        'berat',
        'deskripsi',
        'status',
    ];

    public function scopeAktif($query)
    {
        return $query->where('status', 'Aktif');
    }
}
