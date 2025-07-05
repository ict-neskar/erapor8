<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\RombonganBelajar;
use App\Models\Pembelajaran;
use App\Models\KompetensiDasar;
use App\Models\CapaianPembelajaran;
use App\Exports\TemplateTp;

class DownloadController extends Controller
{
    public function template_tp(){
        if(request()->route('id')){
			$rombongan_belajar = RombonganBelajar::find(request()->route('rombongan_belajar_id'));
			$pembelajaran = Pembelajaran::find(request()->route('pembelajaran_id'));
			if(Str::isUuid(request()->route('id'))){
				$kd = KompetensiDasar::find(request()->route('id'));
				$nama_file = 'Template TP Mata Pelajaran ' . $pembelajaran->nama_mata_pelajaran . ' Kelas '.$rombongan_belajar->nama;
				$nama_file = clean($nama_file);
				$nama_file = $nama_file . '.xlsx';
				return (new TemplateTp)->query(request()->route('id'), $pembelajaran, $rombongan_belajar)->download($nama_file);
			} else {
				$cp = CapaianPembelajaran::with(['pembelajaran'])->find(request()->route('id'));
				$nama_file = 'Template TP Mata Pelajaran ' . $pembelajaran->nama_mata_pelajaran. ' Kelas '.$rombongan_belajar->nama;
				$nama_file = clean($nama_file);
				$nama_file = $nama_file . '.xlsx';
				return (new TemplateTp)->query(request()->route('id'), $pembelajaran, $rombongan_belajar)->download($nama_file);
			}
		} else {
			echo 'Akses tidak sah!';
		}
    }
}
