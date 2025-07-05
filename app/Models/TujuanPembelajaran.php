<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TujuanPembelajaran extends Model
{
    use HasUuids;
    protected $table = 'tujuan_pembelajaran';
	protected $primaryKey = 'tp_id';
	protected $guarded = [];
	
    public function cp()
    {
        return $this->belongsTo(CapaianPembelajaran::class, 'cp_id', 'cp_id');
    }
    public function kd()
    {
        return $this->belongsTo(KompetensiDasar::class, 'kd_id', 'kompetensi_dasar_id');
    }
    public function tp_mapel()
    {
        return $this->hasManyThrough(
            Pembelajaran::class,
            TpMapel::class,
            'tp_id',
            'pembelajaran_id',
            'tp_id',
            'pembelajaran_id'
        );
    }
    public function tp_pkl()
    {
        return $this->hasOne(TpPkl::class, 'tp_id', 'tp_id');
    }
}
