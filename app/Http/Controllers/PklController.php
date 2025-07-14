<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\PraktikKerjaLapangan;
use App\Models\RombonganBelajar;
use App\Models\Dudi;
use App\Models\AktPd;
use App\Models\PesertaDidik;
use App\Models\TujuanPembelajaran;
use App\Models\TpPkl;
use App\Models\PdPkl;
use App\Models\NilaiPkl;
use App\Models\AbsensiPkl;
use App\Models\AnggotaAktPd;

class PklController extends Controller
{
    public function index(){
        $data = PraktikKerjaLapangan::where(function($query){
            $query->where('guru_id', request()->guru_id);
            $query->where('semester_id', request()->semester_id);
            $query->where('sekolah_id', request()->sekolah_id);
        })->with([
            'rombongan_belajar' => function($query){
                $query->withCount([
                    'anggota_rombel' => function($query){
                        $query->whereHas('peserta_didik', function($query){
                            $query->whereHas('pd_pkl', function($query){
                                $query->whereHas('praktik_kerja_lapangan', function($query){
                                    $query->where('guru_id', request()->guru_id);
                                });
                            });
                        });
                    },
                ]);
            },
            'akt_pd.dudi'
        ])->withCount('pd_pkl')->orderBy(request()->sortby, request()->sortbydesc)
        ->when(request()->q, function($query){
            $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            $query->orWhereHas('wali_kelas', function($query){
                $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            });
            $query->orWhereHas('jurusan_sp', function($query){
                $query->where('nama_jurusan_sp', 'ILIKE', '%' . request()->q . '%');
            });
            $query->orWhereHas('kurikulum', function($query){
                $query->where('nama_kurikulum', 'ILIKE', '%' . request()->q . '%');
            });
        })->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    public function simpan(){
        request()->validate(
            [
                'tingkat' => 'required',
                'rombongan_belajar_id' => 'required',
                'dudi_id' => 'required',
                'akt_pd_id' => 'required',
                'tanggal_mulai' => 'required',
                'tanggal_selesai' => 'required',
                'instruktur' => 'required',
            ],
            [
                'tingkat.required' => 'Tingkat Kelas tidak boleh kosong!',
                'rombongan_belajar_id.required' => 'Rombongan Belajar tidak boleh kosong!',
                'dudi_id.required' => 'DUDI tidak boleh kosong!',
                'akt_pd_id.required' => 'Perjanjian Kerja Sama (PKS) tidak boleh kosong!',
                'tanggal_mulai.required' => 'Tanggal Mulai tidak boleh kosong!',
                'tanggal_selesai.required' => 'Tanggal Selesai tidak boleh kosong!',
                'instruktur.required' => 'Nama Lengkap Instruktur tidak boleh kosong!',
            ]
        );
        $insert = 0;
        if(request()->tp_id){
            $pkl = PraktikKerjaLapangan::create([
                'sekolah_id' => request()->sekolah_id,
                'guru_id' => request()->guru_id,
                'rombongan_belajar_id' => request()->rombongan_belajar_id,
                'akt_pd_id' => request()->akt_pd_id,
                'tanggal_mulai' => request()->tanggal_mulai,
                'tanggal_selesai' => request()->tanggal_selesai,
                'semester_id' => request()->semester_id,
                'instruktur' => request()->instruktur,
                'nip' => request()->nip,
            ]);
            foreach(request()->tp_id as $tp_id){
                TpPkl::create([
                    'tp_id' => $tp_id,
                    'pkl_id' => $pkl->pkl_id,
                ]);
                $insert++;
            }
            $anggota_akt_pd = AnggotaAktPd::whereHas('anggota_rombel', function($query){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            })->where('akt_pd_id', request()->akt_pd_id)->get();
            foreach($anggota_akt_pd as $anggota){
                PdPkl::create([
                    'peserta_didik_id' => $anggota->peserta_didik_id,
                    'pkl_id' => $pkl->pkl_id,
                ]);
            }
            if($insert){
                $data = [
                    'color' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Rencana Penilaian PKL berhasil disimpan',
                ];
            } else {
                $data = [
                    'color' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Rencana Penilaian PKL gagal disimpan. Silahkan coba beberapa saat lagi!',
                ];
            }
        } else {
            $data = [
                'color' => 'error',
                'title' => 'Gagal!',
                'text' => 'Rencana Penilaian PKL gagal disimpan. Pastikan telah memilih Tujuan Pembelajaran!',
            ];
        }
        return response()->json($data);
    }
    public function get_data(){
        $aksi = str_replace('-', '_', request()->data);
        $function = 'get_'.$aksi;
        $data = $this->{$function}();
        return response()->json($data);
    }
    private function get_rombel(){
        $data = RombonganBelajar::where(function($query){
            $query->where('tingkat', request()->tingkat);
            $query->whereHas('pembelajaran', function($query){
                $query->where('guru_id', request()->guru_id);
                $query->where('mata_pelajaran_id', 800001000);
                $query->where('semester_id', request()->semester_id);
            });
        })->orderBy('nama')->get();
        return $data;
    }
    private function get_dudi(){
        $data = Dudi::where(function($query){
            $query->where('sekolah_id', request()->sekolah_id);
            $query->whereHas('akt_pd', function($query){
                $query->whereHas('anggota_akt_pd', function($query){
                    $query->whereHas('anggota_rombel', function($query){
                        $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
                    });
                });
                $query->whereHas('bimbing_pd', function($query){
                    $query->where('guru_id', request()->guru_id);
                });
            });
        })->orderBy('nama')->get();
        return $data;
    }
    private function get_akt_pd(){
        $data = AktPd::where(function($query){
            $query->where('sekolah_id', request()->sekolah_id);
            $query->whereHas('dudi', function($query){
                $query->where('mou.dudi_id', request()->dudi_id);
            });
            $query->whereHas('anggota_akt_pd', function($query){
                $query->whereHas('anggota_rombel', function($query){
                    $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
                });
            });
        })->orderBy('judul_akt_pd')->get();
        return $data;
    }
    private function get_tp(){
        $data = TujuanPembelajaran::whereHas('tp_mapel', function($query){
            $query->where('guru_id', request()->guru_id);
            $query->where('mata_pelajaran_id', 800001000);
            $query->where('semester_id', request()->semester_id);
        })->get();
        return $data;
    }
    public function get_detil(){
        $data = PraktikKerjaLapangan::with(['rombongan_belajar', 'tp_pkl'])->find(request()->pkl_id);
        return $data;
    }
    public function save(){
        $insert = 0;
        $text = 'Rencana Penilaian PKL';
        request()->validate(
            [
                'tingkat' => 'required',
                'rombongan_belajar_id' => 'required',
                'dudi_id' => 'required',
                'akt_pd_id' => 'required',
                'tanggal_mulai' => 'required',
                'tanggal_selesai' => 'required',
                'instruktur' => 'required',
            ],
            [
                'tingkat.required' => 'Tingkat Kelas tidak boleh kosong!',
                'rombongan_belajar_id.required' => 'Rombongan Belajar tidak boleh kosong!',
                'dudi_id.required' => 'DUDI tidak boleh kosong!',
                'akt_pd_id.required' => 'Perjanjian Kerja Sama (PKS) tidak boleh kosong!',
                'tanggal_mulai.required' => 'Tanggal Mulai tidak boleh kosong!',
                'tanggal_selesai.required' => 'Tanggal Selesai tidak boleh kosong!',
                'instruktur.required' => 'Nama Lengkap Instruktur tidak boleh kosong!',
            ]
        );
        if(request()->aksi == 'add'){
            if(request()->tp_id){
                $pkl = PraktikKerjaLapangan::create([
                    'sekolah_id' => request()->sekolah_id,
                    'guru_id' => request()->guru_id,
                    'rombongan_belajar_id' => request()->rombongan_belajar_id,
                    'akt_pd_id' => request()->akt_pd_id,
                    'tanggal_mulai' => request()->tanggal_mulai,
                    'tanggal_selesai' => request()->tanggal_selesai,
                    'semester_id' => request()->semester_id,
                    'instruktur' => request()->instruktur,
                    'nip' => request()->nip,
                ]);
                foreach(request()->tp_id as $tp_id){
                    TpPkl::create([
                        'tp_id' => $tp_id,
                        'pkl_id' => $pkl->pkl_id,
                    ]);
                    $insert++;
                }
                $anggota_akt_pd = AnggotaAktPd::whereHas('anggota_rombel', function($query){
                    $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
                })->where('akt_pd_id', request()->akt_pd_id)->get();
                foreach($anggota_akt_pd as $anggota){
                    PdPkl::create([
                        'peserta_didik_id' => $anggota->peserta_didik_id,
                        'pkl_id' => $pkl->pkl_id,
                    ]);
                }
            }
        }
        if(request()->aksi == 'edit'){
            $pkl = PraktikKerjaLapangan::find(request()->pkl_id);
            $pkl->rombongan_belajar_id = request()->rombongan_belajar_id;
            $pkl->akt_pd_id = request()->akt_pd_id;
            $pkl->tanggal_mulai = request()->tanggal_mulai;
            $pkl->tanggal_selesai = request()->tanggal_selesai;
            $pkl->semester_id = request()->semester_id;
            $pkl->instruktur = request()->instruktur;
            $pkl->nip = request()->nip;
            $pkl->save();
            foreach(request()->tp_id as $tp_id){
                TpPkl::updateOrCreate([
                    'tp_id' => $tp_id,
                    'pkl_id' => $pkl->pkl_id,
                ]);
                $insert++;
            }
            TpPkl::where('pkl_id', request()->pkl_id)->whereNotIn('tp_id', request()->tp_id)->delete();
        }
        if($insert){
            $data = [
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => $text.' berhasil disimpan',
            ];
        } else {
            $data = [
                'color' => 'error',
                'title' => 'Gagal!',
                'text' => $text.' gagal disimpan. Silahkan coba beberapa saat lagi!',
            ];
        }
        return response()->json($data);
    }
}
