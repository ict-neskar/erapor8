<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\TeknikPenilaian;
use App\Models\PesertaDidik;
use App\Models\TujuanPembelajaran;
use App\Models\NilaiTp;
use App\Models\NilaiSumatif;
use App\Models\Pembelajaran;
use App\Models\NilaiAkhir;
use App\Models\TpNilai;
use Carbon\Carbon;

class PenilaianController extends Controller
{
    public function get_cp(){
        $get_mapel_agama = filter_agama_siswa(request()->pembelajaran_id, request()->rombongan_belajar_id);
        $metode = TeknikPenilaian::find(request()->teknik_penilaian_id);
        $show_cp = ($metode->nama == 'Sumatif Lingkup Materi') ? TRUE : FALSE;
        $data = [
            'show_cp' => $show_cp,
            'data_siswa' => PesertaDidik::withWhereHas('anggota_rombel', function($query){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
                $query->with([
                    'nilai_sumatif' => function($query){
                        $query->where('pembelajaran_id', request()->pembelajaran_id);
                    },
                    'nilai_tp' => function($query){
                        $query->where('pembelajaran_id', request()->pembelajaran_id);
                    }
                ]);
            })->where(function($query) use ($get_mapel_agama){
                if($get_mapel_agama){
                    $query->where('agama_id', $get_mapel_agama);
                }
            })->orderBy('nama')->get(),
            'data_tp' => $show_cp ? TujuanPembelajaran::where(function($query){
                $query->whereHas('tp_mapel', function($query){
                    $query->where('tp_mapel.pembelajaran_id', request()->pembelajaran_id);
                });
            })->orderBy('created_at')->get() : [],
        ];
        return response()->json($data);
    }
    public function simpan(){
        $insert = 0;
        $text = 'Unknow';
        if(request()->opsi == 'nilai-tp'){
            foreach(request()->tp as $uuid => $nilai){
                $segments = Str::of($uuid)->split('/[\s#]+/');
                $anggota_rombel_id = $segments->first();
                $tp_id = $segments->last();
                if($nilai > -1 && $nilai < 101){
                    $insert++;
                    NilaiTp::updateOrCreate(
                        [
                            'sekolah_id' => request()->sekolah_id,
                            'anggota_rombel_id' => $anggota_rombel_id,
                            'pembelajaran_id' => request()->pembelajaran_id,
                            'tp_id' => $tp_id,
                        ],
                        [
                            'nilai' => number_format($nilai,0),
                            'last_sync' => Carbon::now()->subDays(30),
                        ]
                    );
                } else {
                    if(Str::isUuid($anggota_rombel_id)){
                        NilaiTp::where('anggota_rombel_id', $anggota_rombel_id)->where('pembelajaran_id', request()->pembelajaran_id)->where('tp_id', $tp_id)->delete();
                    }
                }
            }
            $text = 'Nilai Sumatif Lingkup Materi';
        }
        if(request()->opsi == 'nilai-sumatif'){
            $text = 'Nilai Sumatif Akhir Semester';
            foreach(request()->sumatif as $uuid => $nilai){
                $segments = Str::of($uuid)->split('/[\s#]+/');
                $anggota_rombel_id = $segments->first();
                $jenis = $segments->last();
                if($nilai > -1 && $nilai < 101){
                    $insert++;
                    NilaiSumatif::updateOrCreate(
                        [
                            'sekolah_id' => request()->sekolah_id,
                            'anggota_rombel_id' => $anggota_rombel_id,
                            'pembelajaran_id' => request()->pembelajaran_id,
                            'jenis' => $jenis,
                        ],
                        [
                            'nilai' => number_format($nilai,0),
                            'last_sync' => Carbon::now()->subDays(30),
                        ]
                    );
                } else {
                    NilaiSumatif::where('anggota_rombel_id', $anggota_rombel_id)->where('pembelajaran_id', request()->pembelajaran_id)->where('jenis', $jenis)->delete();
                }
            }
        }
        if(request()->opsi == 'nilai-akhir'){
            $text = 'Nilai Akhir';
            $kompetensi_id = (request()->merdeka) ? 4 : 1;
            if(request()->mata_pelajaran_id !='800001000'){
                $sub_mapel = Pembelajaran::where(function($query){
                    $query->where('induk_pembelajaran_id', request()->pembelajaran_id);
                    $query->whereNotNull('kelompok_id');
                    $query->whereNotNull('no_urut');
                })->get();
                if($sub_mapel->count()){
                    $kompetensi_id = 99;
                }
            }
            foreach(request()->akhir as $anggota_rombel_id => $nilai_akhir){
                $insert++;
                if($nilai_akhir >= 0 && $nilai_akhir <= 100){
                    NilaiAkhir::updateOrCreate(
                        [
                            'sekolah_id' => request()->sekolah_id,
                            'anggota_rombel_id' => $anggota_rombel_id,
                            'pembelajaran_id' => request()->pembelajaran_id,
                            'kompetensi_id' => $kompetensi_id,
                        ],
                        [
                            'nilai' => ($nilai_akhir) ? number_format($nilai_akhir,0) : 0,
                            'last_sync' => now(),
                        ]
                    );
                } else {
                    NilaiAkhir::where('anggota_rombel_id', $anggota_rombel_id)->where('pembelajaran_id', request()->pembelajaran_id)->where('kompetensi_id', $kompetensi_id)->delete();
                }
            }
            $first = [];
            $last = [];
            foreach(request()->kompeten as $uuid => $kompeten){
                $segments = Str::of($uuid)->split('/[\s#]+/');
                $anggota_rombel_id = $segments->first();
                $tp_id = $segments->last();
                $first[] = $anggota_rombel_id;
                $last[] = $tp_id;
                $tp = TujuanPembelajaran::find($tp_id);
                if($tp){
                    if(request()->merdeka){
                        $update = [
                            'cp_id' => $tp->cp_id,
                            'last_sync' => now(),
                        ];
                    } else {
                        $update = [
                            'kd_id' => $tp->kd_id,
                            'last_sync' => now(),
                        ];
                    }
                    if($kompeten > -1){
                        TpNilai::updateOrCreate(
                            [
                                'sekolah_id' => request()->sekolah_id,
                                'anggota_rombel_id' => $anggota_rombel_id,
                                'tp_id' => $tp_id,
                                'kompeten' => $kompeten,
                            ],
                            $update
                        );
                    } else {
                        TpNilai::where('anggota_rombel_id', $anggota_rombel_id)->where('tp_id', $tp_id)->delete();
                    }
                }
            }
        }
        if($insert){
            $data = [
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => $text.' berhasil disimpan',
                'request' => request()->all(),
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
    private function wherehas($query, $mata_pelajaran_id){
        if(request()->merdeka){
            $query->whereHas('tp', function($query) use ($mata_pelajaran_id){
                $query->whereHas('cp', function($query) use ($mata_pelajaran_id){
                    $query->whereHas('pembelajaran', function($query) use ($mata_pelajaran_id){
                        $query->where('mata_pelajaran_id', $mata_pelajaran_id);
                        $query->where($this->kondisiPembelajaran());
                    });
                });
            });
        } else {
            $query->whereHas('kd', function($query) use ($mata_pelajaran_id){
                $query->whereHas('pembelajaran', function($query) use ($mata_pelajaran_id){
                    $query->where('mata_pelajaran_id', $mata_pelajaran_id);
                    $query->where($this->kondisiPembelajaran());
                });
            });
        }
    }
    private function kondisiPembelajaran(){
        return function($query){
            $query->where('guru_id', request()->guru_id);
            if(request()->rombongan_belajar_id){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            }
            $query->whereNotNull('kelompok_id');
            $query->whereNotNull('no_urut');
            $query->where('mata_pelajaran_id', '<>', '800001000');
            //$query->whereNull('induk_pembelajaran_id');
            $query->orWhere('guru_pengajar_id', request()->guru_id);
            if(request()->rombongan_belajar_id){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            }
            $query->whereNotNull('kelompok_id');
            $query->whereNotNull('no_urut');
            $query->where('mata_pelajaran_id', '<>', '800001000');
            //$query->whereNull('induk_pembelajaran_id');
        };
    }
    public function get_nilai_akhir(){
        $get_mapel_agama = filter_agama_siswa(request()->pembelajaran_id, request()->rombongan_belajar_id);
        $pembelajaran = Pembelajaran::find(request()->pembelajaran_id);
        $kompetensi_id = (request()->merdeka) ? 4 : 1;
        if($pembelajaran->mata_pelajaran_id !='800001000'){
            $sub_mapel = Pembelajaran::where(function($query){
                $query->where('induk_pembelajaran_id', request()->pembelajaran_id);
                $query->whereNotNull('kelompok_id');
                $query->whereNotNull('no_urut');
            })->get();
            if($sub_mapel->count()){
                $kompetensi_id = 99;
            }
        }
        $get_siswa = PesertaDidik::where(function($query) use ($get_mapel_agama){
            if($get_mapel_agama){
                $query->where('agama_id', $get_mapel_agama);
            }
        })->withWhereHas('anggota_rombel', function($query) use ($kompetensi_id, $pembelajaran){
            $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            $query->with([
                'capaian_kompeten' => function($query) use ($pembelajaran){
                    $this->wherehas($query, $pembelajaran->mata_pelajaran_id);
                },
                'tp_kompeten' => function($query) use ($pembelajaran){
                    $this->wherehas($query, $pembelajaran->mata_pelajaran_id);
                },
                'tp_inkompeten' => function($query) use ($pembelajaran){
                    $this->wherehas($query, $pembelajaran->mata_pelajaran_id);
                },
                'nilai_tp' => function($query){
                    $query->where('pembelajaran_id', request()->pembelajaran_id);
                },
                'nilai_sumatif_semester' => function($query){
                    $query->where('pembelajaran_id', request()->pembelajaran_id);
                },
                'nilai_akhir_mapel' => function($query) use ($kompetensi_id){
                    $query->where('kompetensi_id', $kompetensi_id);
                    $query->where('pembelajaran_id', request()->pembelajaran_id);
                },
                'nilai_akhir_kurmer' => function($query){
                    $query->where('kompetensi_id', 4);
                    $query->where('pembelajaran_id', request()->pembelajaran_id);
                },
                'nilai_akhir_induk' => function($query){
                    $query->where('kompetensi_id', 99);
                    $query->where('pembelajaran_id', request()->pembelajaran_id);
                },
            ]);
            $query->withAvg(['nilai_tp' => function($query){
                $query->where('pembelajaran_id', request()->pembelajaran_id);
            }], 'nilai');
        })->orderBy('nama')->get();
        $bobot_sumatif_materi = $pembelajaran->bobot_sumatif_materi;
        $bobot_sumatif_akhir = $pembelajaran->bobot_sumatif_akhir;
        $total_bobot = $bobot_sumatif_materi + $bobot_sumatif_akhir;
        $data_siswa = [];
        foreach($get_siswa as $siswa){
            $nilai_sumatif_materi = number_format($siswa->anggota_rombel->nilai_tp_avg_nilai, 2);
            $nilai_sumatif_semester = ($siswa->anggota_rombel->nilai_sumatif_semester) ? number_format($siswa->anggota_rombel->nilai_sumatif_semester->nilai, 2) : 0;
            $nilai_akhir = collect([$nilai_sumatif_materi, $nilai_sumatif_semester]);
            $nilai_asesmen = NULL;
            if($nilai_akhir->avg()){
                $nilai_asesmen = number_format(($bobot_sumatif_materi * $nilai_sumatif_materi / $total_bobot) + ($bobot_sumatif_akhir * $nilai_sumatif_semester / $total_bobot) , 0);
            }
            $nilai_akhir_jadi = NULL;
            if($siswa->anggota_rombel->nilai_akhir_mapel){
                $nilai_akhir_jadi = $siswa->anggota_rombel->nilai_akhir_mapel->nilai;
            } elseif($siswa->anggota_rombel->nilai_akhir_kurmer){
                $nilai_akhir_jadi = $siswa->anggota_rombel->nilai_akhir_kurmer->nilai;
            } elseif($siswa->anggota_rombel->nilai_akhir_induk){
                $nilai_akhir_jadi = $siswa->anggota_rombel->nilai_akhir_induk->nilai;
            }
            $data_siswa[] = [
                'nama' => $siswa->nama,
                'photo' => $siswa->photo,
                'nisn' => $siswa->nisn,
                'anggota_rombel_id' => $siswa->anggota_rombel->anggota_rombel_id,
                'pembelajaran_id' => request()->pembelajaran_id,
                'kompetensi_id' => $kompetensi_id,
                'nilai_akhir' => $nilai_akhir_jadi,
                'nilai_akhir_kurmer' => ($siswa->anggota_rombel->nilai_akhir_kurmer) ? $siswa->anggota_rombel->nilai_akhir_kurmer->nilai : NULL,
                'nilai_akhir_induk' => ($siswa->anggota_rombel->nilai_akhir_induk) ? $siswa->anggota_rombel->nilai_akhir_induk->nilai : NULL,
                'nilai_asesmen' => $nilai_asesmen,
                'tp_kompeten' => $siswa->anggota_rombel->tp_kompeten,
                'tp_inkompeten' => $siswa->anggota_rombel->tp_inkompeten,
                'capaian_kompeten' => $siswa->anggota_rombel->capaian_kompeten,
            ];
        }
        $data = [
            'data_siswa' => $data_siswa,
            'data_tp' => TujuanPembelajaran::where(function($query) use ($pembelajaran){
                $query->whereHas('tp_mapel', function($query){
                    $query->where('tp_mapel.pembelajaran_id', request()->pembelajaran_id);
                });
                if(request()->merdeka){
                    $query->whereHas('cp', function($query) use ($pembelajaran){
                        $query->where('mata_pelajaran_id', $pembelajaran->mata_pelajaran_id);
                    });
                } else {
                    $query->whereHas('kd', function($query){
                        $query->where('mata_pelajaran_id', $pembelajaran->mata_pelajaran_id);
                    });
                }
            })->orderBy('created_at')->get(),
            'pembelajaran' => $pembelajaran,
        ];
        return response()->json($data);
    }
}
