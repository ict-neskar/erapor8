<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TpMapel extends Model
{
    use HasUuids;
    protected $table = 'tp_mapel';
	protected $primaryKey = 'tp_mapel_id';
	protected $guarded = [];
	public function tp()
	{
		return $this->belongsTo(TujuanPembelajaran::class, 'tp_id', 'tp_id');
	}
	public function pembelajaran()
	{
		return $this->belongsTo(Pembelajaran::class, 'pembelajaran_id', 'pembelajaran_id');
	}
}
