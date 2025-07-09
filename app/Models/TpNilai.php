<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TpNilai extends Model
{
    use HasUuids;
    protected $table = 'tp_nilai';
	protected $primaryKey = 'tp_nilai_id';
	protected $guarded = [];
	public function rencana_penilaian()
	{
		return $this->hasOne(RencanaPenilaian::class, 'rencana_penilaian_id', 'rencana_penilaian_id')->whereHas('pembelajaran', function($query){
			$query->where('semester_id', session('semester_aktif'));
		});
	}
	public function tp()
	{
		return $this->hasOne(TujuanPembelajaran::class, 'tp_id', 'tp_id');
	}
	public function kd()
	{
		return $this->hasOne(KompetensiDasar::class, 'kompetensi_dasar_id', 'kd_id');
	}
	public function nilai_tp()
	{
		return $this->hasMany(Nilai::class, 'tp_nilai_id', 'tp_nilai_id');
	}
	public function tp_mapel()
	{
		return $this->hasOne(TpMapel::class, 'tp_id', 'tp_id');
	}
	public function anggota_rombel()
	{
		return $this->belongsTo(AnggotaRombel::class, 'anggota_rombel_id', 'anggota_rombel_id');
	}
}
