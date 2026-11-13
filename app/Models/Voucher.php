<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $table = 'vouchers';

    protected $fillable = [
        'kode',
        'nama',
        'tipe_diskon',
        'nilai',
        'min_belanja',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'aktif' => 'boolean',
        ];
    }

    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Hitung nominal diskon untuk subtotal tertentu.
     */
    public function hitungDiskon(int $subtotal): int
    {
        if ($this->tipe_diskon === 'persen') {
            return intdiv($subtotal * (int) $this->nilai, 100);
        }

        return min((int) $this->nilai, $subtotal);
    }

    public function deskripsi(): string
    {
        $diskon = $this->tipe_diskon === 'persen'
            ? $this->nilai.'%'
            : 'Rp '.number_format($this->nilai, 0, ',', '.');

        return 'Diskon '.$diskon.' ['.$this->kode.'] (Min. belanja Rp '.number_format($this->min_belanja, 0, ',', '.').')';
    }
}
