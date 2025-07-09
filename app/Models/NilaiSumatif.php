<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class NilaiSumatif extends Model
{
    use HasUuids;
    protected $table = 'nilai_sumatif';
	protected $primaryKey = 'nilai_sumatif_id';
	protected $guarded = [];
	public function pembelajaran(){
        return $this->hasOne(Pembelajaran::class, 'pembelajaran_id', 'pembelajaran_id');
    }
	public function siswa(){
		return $this->hasOneThrough(
            AnggotaRombel::class,
            PesertaDidik::class,
            'peserta_didik_id',
            'peserta_didik_id',
            'anggota_rombel_id',
            'peserta_didik_id'
        );
	}
    public function anggota_rombel()
    {
        return $this->hasOne(AnggotaRombel::class, 'anggota_rombel_id', 'anggota_rombel_id');
    }
}
