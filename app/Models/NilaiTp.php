<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class NilaiTp extends Model
{
    use HasUuids;
    protected $table = 'nilai_tp';
	protected $primaryKey = 'nilai_tp_id';
	protected $guarded = [];
	public function tp_nilai(){
        return $this->hasOne(TpNilai::class, 'tp_nilai_id', 'tp_nilai_id');
    }
    public function capaian_kompeten(){
		return $this->hasOne(TpNilai::class, 'tp_id', 'tp_id');
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
    public function tp()
    {
        return $this->belongsTo(TujuanPembelajaran::class, 'tp_id', 'tp_id');
    }
}
