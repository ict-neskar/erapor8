<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TpPkl extends Model
{
    use HasUuids;
    protected $table = 'tp_pkl';
	protected $primaryKey = 'tp_pkl_id';
	protected $guarded = [];
	public function tp()
	{
		return $this->belongsTo(TujuanPembelajaran::class, 'tp_id', 'tp_id');
	}
	public function praktik_kerja_lapangan()
	{
		return $this->belongsTo(PraktikKerjaLapangan::class, 'pkl_id', 'pkl_id');
	}
}
