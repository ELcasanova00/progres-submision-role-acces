<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekspedisi extends Model
{
    protected $table = 'ekspedisis';

    protected $fillable = [
        'nama_kurir',
        'kode',
        'jenis_layanan',
        'zona',
        'tarif',
        'estimasi',
        'status',
    ];

    public function scopeAktif($query)
    {
        return $query->where('status', 'Aktif');
    }

    public function label(): string
    {
        return $this->nama_kurir.' '.$this->jenis_layanan.' - '.$this->zona;
    }
}
