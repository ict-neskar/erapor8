<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TeknikPenilaian extends Model
{
    use SoftDeletes, HasUuids;
    protected $table = 'teknik_penilaian';
	protected $primaryKey = 'teknik_penilaian_id';
	protected $guarded = [];
}
