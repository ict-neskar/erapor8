<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class RencanaUkk extends Model
{
    use HasUuids;
    protected $table = 'rencana_ukk';
	protected $primaryKey = 'rencana_ukk_id';
	protected $guarded = [];
	public function guru_internal(){
		return $this->hasOne(Ptk::class, 'guru_id', 'internal');
	}
	public function guru_eksternal(){
		return $this->hasOne(Ptk::class, 'guru_id', 'eksternal')->with('dudi');
	}
	public function paket_ukk(){
		return $this->hasOne(PaketUkk::class, 'paket_ukk_id', 'paket_ukk_id');
	}
	public function nilai_ukk(){
		return $this->hasOne(NilaiUkk::class, 'rencana_ukk_id', 'rencana_ukk_id');
	}
	public function pd()
	{
		return $this->hasManyThrough(
			PesertaDidik::class, 
			NilaiUkk::class,
			'rencana_ukk_id',
			'peserta_didik_id',
			'rencana_ukk_id',
			'peserta_didik_id',
		);
	}
}
