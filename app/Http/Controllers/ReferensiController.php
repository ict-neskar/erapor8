<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\MataPelajaran;
use App\Models\Ekstrakurikuler;
use App\Models\Dudi;
use App\Models\PesertaDidik;
use App\Models\KompetensiDasar;
use App\Models\RombonganBelajar;
use App\Models\Pembelajaran;
use App\Models\CapaianPembelajaran;
use App\Models\TujuanPembelajaran;
use App\Models\TpMapel;
use App\Models\JurusanSp;
use App\Models\Kurikulum;
use App\Imports\TemplateTp;
use Storage;

class ReferensiController extends Controller
{
    public function index(){
        $data = MataPelajaran::whereHas('pembelajaran', function($query){
            $query->where('sekolah_id', request()->sekolah_id);
            $query->where('semester_id', request()->semester_id);
        })->orderBy(request()->sortby, request()->sortbydesc)
        ->when(request()->q, function($query) {
            $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            $query->orWhere('mata_pelajaran_id', 'ILIKE', '%' . request()->q . '%');
        })->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    public function ekstrakurikuler(){
        $data = Ekstrakurikuler::where(function($query){
            $query->whereHas('rombongan_belajar', function($query){
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
            });
        })->with([
            'guru' => function($query){
                $query->select('guru_id', 'nama', 'gelar_depan', 'gelar_belakang');
            },
            'rombongan_belajar' => function($query){
                $query->select('rombongan_belajar_id');
                $query->withCount('anggota_rombel');
            }
        ])
        ->orderBy(request()->sortby, request()->sortbydesc)
        ->when(request()->q, function($query) {
            $query->where('nama_ekskul', 'ILIKE', '%' . request()->q . '%');
            $query->orWhere('nama_ketua', 'ILIKE', '%' . request()->q . '%');
            $query->orWhereHas('guru', function($query){
                $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            });
        })->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    public function dudi(){
        $data = Dudi::where(function($query){
            $query->where('sekolah_id', request()->sekolah_id);
        })->withCount(['akt_pd'])->orderBy(request()->sortby, request()->sortbydesc)
        ->when(request()->q, function($query) {
            $query->where('nama', 'ILIKE', '%' . request()->q . '%');
            $query->orWhere('nama_bidang_usaha', 'ILIKE', '%' . request()->q . '%');
        })->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    public function detil_dudi(){
        $data = Dudi::with(['mou' => function($query){
            $query->with(['akt_pd' => function($query){
                $query->with([
                    'bimbing_pd' => function($query){
                        $query->with(['guru' => function($query){
                            $query->select('guru_id', 'nama', 'gelar_depan', 'gelar_belakang', 'photo', 'email');
                        }]);
                        $query->orderBy('urutan_pembimbing');
                    }
                ]);
                $query->withCount(['anggota_akt_pd' => function($query){
                    $query->whereHas('anggota_rombel', function($query){
                        $query->where('semester_id', request()->semester_id);
                    });
                }]);
            }]);
        }])->find(request()->dudi_id);
        return response()->json($data);
    }
    public function anggota_prakerin(){
        $data = PesertaDidik::whereHas('anggota_akt_pd', function($query){
            $query->where('akt_pd_id', request()->akt_pd_id);
            $query->whereHas('anggota_rombel', function($query){
                $query->where('semester_id', request()->semester_id);
            });
        })->with([
            'anggota_akt_pd' => function($query){
                $query->where('akt_pd_id', request()->akt_pd_id);
                $query->whereHas('anggota_rombel', function($query){
                    $query->where('semester_id', request()->semester_id);
                });
            },
            'agama',
            'kelas' => function($query){
                $query->where('jenis_rombel', 1);
                $query->where('rombongan_belajar.semester_id', request()->semester_id);
            }
        ])->get();
        return response()->json($data);
    }
    private function kondisiPembelajaran($mata_pelajaran_id = NULL){
        return function($query) use ($mata_pelajaran_id){
            $query->where('sekolah_id', request()->sekolah_id);
            $query->where('semester_id', request()->semester_id);
            if($mata_pelajaran_id){
                $query->where('mata_pelajaran_id', $mata_pelajaran_id);
            }
            if(request()->pembelajaran_id){
                $query->where('pembelajaran.pembelajaran_id', request()->pembelajaran_id);
            }
            if(request()->rombongan_belajar_id){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            }
            if(hasRole('pembimbing', request()->periode_aktif)){
                $query->where('guru_id', request()->guru_id);
                $query->where('mata_pelajaran_id', '800001000');
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
                $query->orWhere('guru_id', request()->guru_id);
                $query->whereNotNull('kelompok_id');
                $query->whereNotNull('no_urut');
                //$query->whereNull('induk_pembelajaran_id');
                if(request()->add_kd){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%REV%');
                        });
                    });
                }
                if(request()->add_cp){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%Merdeka%');
                        });
                    });
                }
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
                $query->orWhere('guru_pengajar_id', request()->guru_id);
                $query->whereNotNull('kelompok_id');
                $query->whereNotNull('no_urut');
                //$query->whereNull('induk_pembelajaran_id');
                if(request()->add_kd){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%REV%');
                        });
                    });
                } 
                if(request()->add_cp){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%Merdeka%');
                        });
                    });
                }
            } else {
                $query->where('guru_id', request()->guru_id);
                $query->whereNotNull('kelompok_id');
                $query->whereNotNull('no_urut');
                //$query->whereNull('induk_pembelajaran_id');
                if(request()->add_kd){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%REV%');
                        });
                    });
                }
                if(request()->add_cp){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%Merdeka%');
                        });
                    });
                }
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
                $query->orWhere('guru_pengajar_id', request()->guru_id);
                $query->whereNotNull('kelompok_id');
                $query->whereNotNull('no_urut');
                //$query->whereNull('induk_pembelajaran_id');
                if(request()->add_kd){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%REV%');
                        });
                    });
                } 
                if(request()->add_cp){
                    $query->whereHas('rombongan_belajar', function($query){
                        $query->whereHas('kurikulum', function($query){
                            $query->where('nama_kurikulum', 'ILIKE', '%Merdeka%');
                        });
                    });
                }
            }
        };
    }
    public function kompetensi_dasar(){
        $data = KompetensiDasar::withWhereHas('mata_pelajaran')->where(function($query){
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
            $query->whereNotIn('kurikulum', [2006, 2013, 2022]);
        })
        ->orderBy(request()->sortby, request()->sortbydesc)
        ->when(request()->q, function($query) {
            $query->where('id_kompetensi', 'ilike', '%'.request()->q.'%');
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
            $query->orWhere('kompetensi_dasar', 'ilike', '%'.request()->q.'%');
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
            $query->orWhere('kurikulum', 'ilike', '%'.request()->q.'%');
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
            $query->orWhereHas('mata_pelajaran', function($query){ 
                $query->where('mata_pelajaran_id', 'ilike', '%'.request()->q.'%');
                $query->orWhere('nama', 'ilike', '%'.request()->q.'%');
            });
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
        })
        ->when(request()->tingkat, function($query) {
            $query->where('kelas_'.request()->tingkat, '1');
        })
        ->when(request()->rombongan_belajar_id, function($query) {
            $query->whereHas('pembelajaran', function($query){
                $query->where('guru_id', request()->guru_id);
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
                $query->whereNotNull('kelompok_id');
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
                $query->orWhere('guru_pengajar_id', request()->guru_id);
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
                $query->whereNotNull('kelompok_id');
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
            });
        })
        ->when(request()->pembelajaran_id, function($query) {
            $query->whereHas('pembelajaran', function($query){
                $query->where('pembelajaran_id', request()->pembelajaran_id);
            });
        })
        ->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    private function cariPembelajaran(){
        return function($query){
            $query->where('guru_id', request()->guru_id);
            if(request()->rombongan_belajar_id){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            }
            $query->whereNotNull('kelompok_id');
            $query->where('sekolah_id', request()->sekolah_id);
            $query->where('semester_id', request()->semester_id);
            $query->orWhere('guru_pengajar_id', request()->guru_id);
            if(request()->rombongan_belajar_id){
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            }
            $query->whereNotNull('kelompok_id');
            $query->where('sekolah_id', request()->sekolah_id);
            $query->where('semester_id', request()->semester_id);
        };
    }
    public function get_data(){
        $data = [];
        if(request()->data == 'rombel'){
            $mata_pelajaran_id = NULL;
            if(request()->mapping){
                $find = TujuanPembelajaran::with(['cp', 'kd'])->find(request()->tp_id);
                if($find){
                    if($find->cp){
                        $mata_pelajaran_id = $find->cp->mata_pelajaran_id;
                    }
                    if($find->kd){
                        $mata_pelajaran_id = $find->kd->mata_pelajaran_id;
                    }
                }
            }
            $data = RombonganBelajar::where(function($query) use ($mata_pelajaran_id){
                $query->where('tingkat', request()->tingkat);
                $query->where('semester_id', request()->semester_id);
                $query->where('sekolah_id', request()->sekolah_id);
                if(request()->jenis_rombel){
                    $query->where('jenis_rombel', request()->jenis_rombel);
                } else {
                    $query->whereIn('jenis_rombel', [1, 16]);   
                }
                if(request()->add_kd || request()->add_cp || request()->mapping){
                    $query->whereHas('pembelajaran', $this->kondisiPembelajaran($mata_pelajaran_id));
                } else {
                    $query->whereHas('pembelajaran', $this->cariPembelajaran());
                }
            })->orderBy('nama')->get();
        }
        if(request()->data == 'mapel'){
            $rombel = RombonganBelajar::find(request()->rombongan_belajar_id);
            $merdeka = (merdeka($rombel->kurikulum->nama_kurikulum)) ? TRUE : FALSE;
            if(request()->add_kd || request()->add_cp){
                $data = [
                    'mapel' => Pembelajaran::where($this->kondisiPembelajaran())->orderBy('nama_mata_pelajaran')->get(),
                    'merdeka' => $merdeka,
                ];
            } else {
                $data = [
                    'mapel' => Pembelajaran::where($this->cariPembelajaran())->orderBy('nama_mata_pelajaran')->get(),
                    'merdeka' => $merdeka,
                ];
            }
        }
        if(request()->data == 'cp_kd'){
            $pembelajaran = Pembelajaran::where(function($query){
                $query->where('guru_id', request()->guru_id);
                $query->where('mata_pelajaran_id', request()->mata_pelajaran_id);
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
                $query->orWhere('guru_pengajar_id', request()->guru_id);
                $query->where('sekolah_id', request()->sekolah_id);
                $query->where('semester_id', request()->semester_id);
                $query->where('mata_pelajaran_id', request()->mata_pelajaran_id);
                $query->where('rombongan_belajar_id', request()->rombongan_belajar_id);
            })->first();
            $fase = (request()->tingkat == 10) ? 'E' : 'F';
            $data_cp = [];
            $data_kd = [];
            if(request()->merdeka){
                $data_cp = CapaianPembelajaran::where(function($query) use ($fase){
                    $query->where('mata_pelajaran_id', request()->mata_pelajaran_id);
                    $query->where('fase', $fase);
                    $query->where('aktif', 1);
                })->orderBy('cp_id')->get();
            } else {
                $data_kd = KompetensiDasar::where(function($query){
                    $query->where('mata_pelajaran_id', request()->mata_pelajaran_id);
                    $query->where('kelas_'.request()->tingkat, 1);
                    $query->where('kompetensi_id', request()->kompetensi_id);
                    $query->where('aktif', 1);
                })->orderBy('id_kompetensi')->get();
            }
            $data = [
                'pembelajaran_id' => ($pembelajaran) ? $pembelajaran->pembelajaran_id : request()->pembelajaran_id,
                'cp' => $data_cp,
                'kd' => $data_kd,
            ];
        }
        if(request()->data == 'jurusan'){
            $data = JurusanSp::where(function($query){
                $query->where('sekolah_id', request()->sekolah_id);
                $query->whereHas('rombongan_belajar', function($query){
                    $query->whereIn('tingkat', [12, 13]);
                    $query->where('semester_id', request()->semester_id);
                });
                $query->has('kurikulum');
            })->orderBy('nama_jurusan_sp')->get();
        }
        if(request()->data == 'kurikulum'){
            $data = Kurikulum::where('jurusan_id', request()->jurusan_id)->get();
        }
        return response()->json($data);
    }
    private function kurikulum($string){
        if(Str::contains($string, 'REV')){
            $kurikulum = 2017;
        } elseif(Str::contains($string, 'KTSP')){
            $kurikulum = 2006;
        } elseif(Str::contains($string, 'Pusat')){
            $kurikulum = 2021;
        } else {
            $kurikulum = 2013;
        }
        return $kurikulum;
    }
    public function save_kd(){
        request()->validate(
            [
                'tingkat' => 'required_if:add_kd,1',
                'rombongan_belajar_id' => 'required_if:add_kd,1',
                'mata_pelajaran_id' => 'required_if:add_kd,1',
                'kompetensi_id' => 'required_if:add_kd,1',
                'id_kompetensi' => 'required_if:add_kd,1',
                'kompetensi_dasar' => 'required_if:add_kd,1',
                'kompetensi_dasar_alias' => 'required_with:kompetensi_dasar_id',
            ],
            [
                'tingkat.required_if' => 'Tingkat Kelas tidak boleh kosong!!',
                'rombongan_belajar_id.required_if' => 'Rombongan Belajar tidak boleh kosong!!',
                'mata_pelajaran_id.required_if' => 'Mata Pelajaran tidak boleh kosong!!',
                'kompetensi_id.required_if' => 'Aspek Penilaian tidak boleh kosong!!',
                'id_kompetensi.required_if' => 'Kode Kompetensi Dasar tidak boleh kosong!!',
                'kompetensi_dasar.required_if' => 'Deskripsi Kompetensi Dasar tidak boleh kosong!!',
                'kompetensi_dasar.required_with' => 'Deskripsi Kompetensi Dasar Baru tidak boleh kosong!!',
            ]
        );
        if(request()->add_kd){
            $rombel = RombonganBelajar::select('rombongan_belajar_id', 'kurikulum_id')->with(['kurikulum' => function($query){
                $query->select('kurikulum_id', 'nama_kurikulum');
            }])->find(request()->rombongan_belajar_id);
            $kurikulum = $this->kurikulum($rombel->kurikulum->nama_kurikulum);
            $insert = KompetensiDasar::create([
                'kompetensi_dasar_id' => Str::uuid(),
                'id_kompetensi' => request()->id_kompetensi,
                'kompetensi_id' => request()->kompetensi_id,
                'mata_pelajaran_id' => request()->mata_pelajaran_id,
                'kelas_10' => (request()->tingkat == 10) ? 1 : 0,
                'kelas_11' => (request()->tingkat == 11) ? 1 : 0,
                'kelas_12' => (request()->tingkat == 12) ? 1 : 0,
                'kelas_13' => (request()->tingkat == 13) ? 1 : 0,
                'aktif'				=> 1,
                'kurikulum'			=> $kurikulum,
                'kompetensi_dasar' => request()->kompetensi_dasar,
                'user_id' => request()->user_id,
                'last_sync' => now(),
            ]);
            if($insert){
                $data = [
                    'color' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Data Kompetensi Dasar berhasil disimpan',
                ];
            } else {
                $data = [
                    'color' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Data Kompetensi Dasar gagal disimpan. Silahkan coba beberapa saat lagi!',
                ];
            }
        } else {
            $kd = KompetensiDasar::find(request()->kompetensi_dasar_id);
            $kd->kompetensi_dasar_alias = request()->kompetensi_dasar_alias;
            if($kd->save()){
                $data = [
                    'color' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Deskripsi Kompetensi Dasar berhasil diperbaharui',
                ];
            } else {
                $data = [
                    'color' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Deskripsi Kompetensi Dasar gagal diperbaharui. Silahkan coba beberapa saat lagi!',
                ];
            }
        }
        return response()->json($data);
    }
    public function update_kd(){
        $insert = NULL;
        $kd = KompetensiDasar::find(request()->kompetensi_dasar_id);
        if(request()->mata_pelajaran_id){
            $mapel = MataPelajaran::withWhereHas('kompetensi_dasar')->find(request()->mata_pelajaran_id);
            foreach($mapel->kompetensi_dasar as $kd){
                $kompetensi_dasar_id[str_replace('.','',$kd->id_kompetensi)] = $kd->kompetensi_dasar_id;
            }
            $insert = KompetensiDasar::where('mata_pelajaran_id', request()->mata_pelajaran_id)->whereNotIn('kompetensi_dasar_id', $kompetensi_dasar_id)->update(['aktif' => 0]);
            $text = 'KD Mapel '.$mapel->nama.' dinonaktifkan sebanyak ('.$insert.')';
        } else {
            if(request()->has('aktif')){
                $kd->aktif = (request()->aktif) ? 0 : 1;
                $text = (request()->aktif) ? 'Data KD berhasil di non aktifkan!' : 'Data KD berhasil di aktifkan!';
            } else {
                $kd->kompetensi_dasar_alias = request()->kompetensi_dasar_alias;
                $text = 'Deskripsi Kompetensi Dasar berhasil diperbaharui';
            }
            $insert = $kd->save();
        }
        if($insert){
            $data = [
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => $text,
            ];
        } else {
            $data = [
                'color' => 'error',
                'title' => 'Gagal!',
                'text' => 'Data KD gagal diperbaharui. Silahkan coba beberapa saat lagi!',
            ];
        }
        return response()->json($data);
    }
    public function capaian_pembelajaran(){
        $data = CapaianPembelajaran::with(['mata_pelajaran'])->withCount('tp')->where(function($query){
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
        })
        ->orderBy(request()->sortby, request()->sortbydesc)
        ->when(request()->q, function($query) {
            $query->where('elemen', 'ILIKE', '%' . request()->q . '%');
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
            $query->where('capaian_pembelajaran', 'ILIKE', '%' . request()->q . '%');
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
        })
        ->when(request()->tingkat, function($query) {
            if(request()->tingkat == 10){
                $query->where('fase', 'E');
            } else {
                $query->where('fase', 'F');
            }
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
        })
        ->when(request()->rombongan_belajar_id, function($query) {
            $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
        })
        ->when(request()->pembelajaran_id, function($query) {
            $query->whereHas('pembelajaran', function($query){
                $query->where('pembelajaran_id', request()->pembelajaran_id);
            });
        })
        ->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    public function save_cp(){
        request()->validate(
            [
                'tingkat' => 'required',
                'rombongan_belajar_id' => 'required',
                'mata_pelajaran_id' => 'required',
                'elemen' => 'required',
                'capaian_pembelajaran' => 'required',
            ],
            [
                'tingkat.required' => 'Tingkat Kelas tidak boleh kosong!!',
                'rombongan_belajar_id.required' => 'Rombongan Belajar tidak boleh kosong!!',
                'mata_pelajaran_id.required' => 'Mata Pelajaran tidak boleh kosong!!',
                'elemen.required' => 'Elemen tidak boleh kosong!!',
                'capaian_pembelajaran.required' => 'Capaian Pembelajaran tidak boleh kosong!!',
            ]
        );
        $fase = fase(request()->tingkat);
        $last_id_ref = CapaianPembelajaran::where('is_dir', 1)->count();
        $last_id_non_ref = CapaianPembelajaran::where('is_dir', 0)->count();
        $cp_id = $last_id_ref + 1000;
        if($last_id_non_ref){
            $cp_id = ($last_id_ref + $last_id_non_ref) + 1;
        }
        $insert = $this->simpan_cp($cp_id, $fase);
        if($insert){
            $data = [
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Data Capaian Kompetensi berhasil disimpan',
            ];
        } else {
            $data = [
                'color' => 'error',
                'title' => 'Gagal!',
                'text' => 'Data Capaian Kompetensi gagal disimpan. Silahkan coba beberapa saat lagi!',
            ];
        }
        return response()->json($data);
    }
    public function simpan_cp($cp_id, $fase){
        $insert = NULL;
        $find = CapaianPembelajaran::find($cp_id);
        if($find){
            $cp_id = $cp_id + 1;
            $insert = $this->simpan_cp($cp_id, $fase);
        } else {
            $insert = CapaianPembelajaran::create([
                'cp_id' => $cp_id,
                'mata_pelajaran_id' => request()->mata_pelajaran_id,
                'fase' => $fase,
                'elemen' => request()->elemen,
                'deskripsi' => request()->capaian_pembelajaran,
                'aktif' => 1,
                'last_sync' => now(),
            ]);
        }
        return $insert;
    }
    public function update_cp(){
        $find = CapaianPembelajaran::find(request()->cp_id);
        $find->aktif = (request()->aktif) ? 0 : 1;
        $text = (request()->aktif) ? 'Data CP berhasil di non aktifkan!' : 'Data CP berhasil di aktifkan!';
        if($find->save()){
            $data = [
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => $text,
            ];
        } else {
            $data = [
                'color' => 'error',
                'title' => 'Gagal!',
                'text' => 'Data CP gagal diperbaharui. Silahkan coba beberapa saat lagi!',
            ];
        }
        return response()->json($data);
    }
    public function tujuan_pembelajaran(){
        $data = TujuanPembelajaran::with(['cp.mata_pelajaran', 'kd.mata_pelajaran', 'tp_mapel' => function($query){
            $query->where($this->kondisiPembelajaran());
            $query->withWhereHas('rombongan_belajar', function($query){
                $query->where('semester_id', request()->semester_id);
                $query->where('sekolah_id', request()->sekolah_id);
            });
        }])
        ->where($this->kondisiTp())
        ->orderBy(request()->sortby, request()->sortbydesc)
        ->orderBy('updated_at', request()->sortbydesc)
        ->when(request()->q, function($query){
            $query->where('deskripsi', 'ILIKE', '%' . request()->q . '%');
        })
        ->when(request()->tingkat, function($query) {
            $query->where($this->kondisiTp());
            
        })
        ->when(request()->rombongan_belajar_id, function($query) {
            $query->where($this->kondisiTp());
        })
        ->when(request()->pembelajaran_id, function($query) {
            $query->where($this->kondisiTp());
        })
        ->paginate(request()->per_page);
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    private function kondisiTp(){
        $callback = function($query){
            $query->whereHas('cp', function($query){
                if(request()->tingkat){
                    if(request()->tingkat == 10){
                        $query->where('fase', 'E');
                    } else {
                        $query->where('fase', 'F');
                    }
                }
                $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
            });
            $query->orWhereHas('kd', function($query){
                if(request()->tingkat){
                    $query->where('kelas_'.request()->tingkat, '1');
                }
                $query->whereHas('pembelajaran', $this->kondisiPembelajaran());
            });
        };
        return $callback;
    }
    public function hapus_tp(){
        $find = TujuanPembelajaran::find(request()->tp_id);
        if($find->delete()){
            $data = [
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Data Tujuan Pembelajaran berhasil di hapus',
            ];
        } else {
            $data = [
                'color' => 'error',
                'title' => 'Gagal!',
                'text' => 'Data Tujuan Pembelajaran gagal di hapus. Silahkan coba beberapa saat lagi!',
            ];
        }
        return response()->json($data);
    }
    public function save_tp(){
        $data = [
            'color' => 'error',
            'title' => 'Gagal!',
            'text' => 'Aksi tidak ditemukan!',
        ];
        if(request()->aksi == 'mapping'){
            request()->validate(
                [
                    'tingkat' => 'required',
                    'tp_id' => 'required',
                    'rombongan_belajar_id' => 'required',
                ],
                [
                    'tingkat.required' => 'Tingkat Kelas tidak boleh kosong!!',
                    'tp_id.required' => 'Tujuan Pembelajaran tidak ditemukan!!',
                    'rombongan_belajar_id.required' => 'Rombongan Belajar tidak boleh kosong!!',
                ]
            );
            $insert = 0;
            $tp = TujuanPembelajaran::with(['cp', 'kd'])->find(request()->tp_id);
            if($tp->cp){
                $mata_pelajaran_id = $tp->cp->mata_pelajaran_id;
            } else {
                $mata_pelajaran_id = $tp->kd->mata_pelajaran_id;
            }
            foreach(request()->rombongan_belajar_id as $rombongan_belajar_id){
                $pembelajaran = Pembelajaran::where(function($query) use ($rombongan_belajar_id, $mata_pelajaran_id){
                    $query->where('guru_id', request()->guru_id);
                    $query->where('semester_id', request()->semester_id);
                    $query->where('sekolah_id', request()->sekolah_id);
                    $query->where('mata_pelajaran_id', $mata_pelajaran_id);
                    $query->where('rombongan_belajar_id', $rombongan_belajar_id);
                    $query->orWhere('guru_pengajar_id', request()->guru_id);
                    $query->where('semester_id', request()->semester_id);
                    $query->where('sekolah_id', request()->sekolah_id);
                    $query->where('mata_pelajaran_id', $mata_pelajaran_id);
                    $query->where('rombongan_belajar_id', $rombongan_belajar_id);
                })->get();
                if($pembelajaran->count()){
                    foreach($pembelajaran as $mapel){
                        $insert++;
                        TpMapel::updateOrCreate([
                            'tp_id' => request()->tp_id,
                            'pembelajaran_id' => $mapel->pembelajaran_id,
                        ]);
                    }
                }
            }
            if($insert){
                $data = [
                    'color' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Tujuan Pembelajaran berhasil di mapping!',
                ];
            } else {
                $data = [
                    'color' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Tujuan Pembelajaran gagal di mapping. Silahkan coba beberapa saat lagi!',
                ];
            }
        } elseif(request()->aksi == 'add'){
            request()->validate(
                [
                    'tingkat' => 'required',
                    'rombongan_belajar_id' => 'required',
                    'mata_pelajaran_id' => 'required',
                    'cp_id' => 'required',
                    'template_excel' => 'required|mimes:xlsx',
                ],
                [
                    'tingkat.required' => 'Tingkat Kelas tidak boleh kosong!!',
                    'rombongan_belajar_id.required' => 'Rombongan Belajar tidak boleh kosong!!',
                    'mata_pelajaran_id.required' => 'Mata Pelajaran tidak boleh kosong!!',
                    'cp_id.required' => 'CP tidak boleh kosong!!',
                    'template_excel.required' => 'Template TP tidak boleh kosong!!',
                    'template_excel.mimes' => 'Template TP harus berupa file dengan ekstensi: xlsx.',
                ]
            );
            $file_path = request()->template_excel->store('files', 'public');
            $id = (request()->cp_id) ?? request()->kd_id;
            Excel::import(new TemplateTp(request()->pembelajaran_id, request()->mata_pelajaran_id, $id), storage_path('/app/public/'.$file_path));
            Storage::disk('public')->delete($file_path);
            $data = [
                'icon' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Data Tujuan Pembelajaran (TP) berhasil disimpan!',
                'file_path' => $file_path,
            ];
        } else {
            request()->validate(
                [
                    'deskripsi' => 'required',
                ],
                [
                    'deskripsi.required' => 'Deskripsi Tujuan Pembelajaran tidak boleh kosong!!',
                ]
            );
            $find = TujuanPembelajaran::find(request()->tp_id);
            $find->deskripsi = request()->deskripsi;
            if($find->save()){
                $data = [
                    'color' => 'success',
                    'title' => 'Berhasil!',
                    'text' => 'Data Tujuan Pembelajaran berhasil di perbaharui',
                ];
            } else {
                $data = [
                    'color' => 'error',
                    'title' => 'Gagal!',
                    'text' => 'Data Tujuan Pembelajaran gagal di perbaharui. Silahkan coba beberapa saat lagi!',
                ];
            }
        }
        return response()->json($data);
    }
    public function bobot_penilaian(){
        if(request()->isMethod('POST')){
            foreach(request()->all() as $pembelajaran){
                Pembelajaran::where('pembelajaran_id', $pembelajaran['pembelajaran_id'])->update([
                    'bobot_sumatif_materi' => $pembelajaran['bobot_sumatif_materi'],
                    'bobot_sumatif_akhir' => $pembelajaran['bobot_sumatif_akhir'],
                ]);
            }
            $data = [
                'color' => 'success',
                'title' => 'Berhasil!',
                'text' => 'Bobot Penilaian berhasil disimpan',
            ];
        } else {
            $data = Pembelajaran::with('rombongan_belajar')->where($this->kondisiPembelajaran())->orderBy('mata_pelajaran_id', 'asc')->get();
        }
        return response()->json($data);
    }
}
