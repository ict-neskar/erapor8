<?php

namespace App\Imports;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\TujuanPembelajaran;
use App\Models\TpMapel;

class TemplateTp implements ToCollection, WithStartRow
{
    public function __construct($pembelajaran_id, $mata_pelajaran_id, $id) 
    {
        $this->pembelajaran_id = $pembelajaran_id;
        $this->mata_pelajaran_id = $mata_pelajaran_id;
        $this->id = $id;
    }
    public function startRow(): int
    {
        return 8;
    }
    public function collection(Collection $collection)
    {
        foreach($collection as $tp){
            $new_tp = TujuanPembelajaran::updateOrCreate(
                [
                    'kd_id' => Str::isUuid($this->id) ? $this->id : NULL,
                    'cp_id' => Str::isUuid($this->id) ? NULL : $this->id,
                    'deskripsi' => mb_convert_encoding($tp[1], 'UTF-8', 'UTF-8'),
                ],
                [
                    'last_sync' => now(),
                ]
            );
            if($new_tp){
                TpMapel::updateOrCreate([
                    'tp_id' => $new_tp->tp_id,
                    'pembelajaran_id' => $this->pembelajaran_id,
                ]);
            }
        }
    }
}
