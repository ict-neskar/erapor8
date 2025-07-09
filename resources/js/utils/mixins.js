const userData = useCookie('userData')
const sekolah = useCookie("sekolah");
const semester = useCookie("semester");
const roles = useCookie("roles");
const profilePhotoPath = useCookie("profilePhotoPath");
export const $user = userData.value
export const $sekolah = sekolah.value
export const $semester = semester.value
export const $roles = roles.value
export const $profilePhotoPath = profilePhotoPath.value
export const tingkatKelas = [
    {
        title: 'Kelas 10',
        value: 10,
    },
    {
        title: 'Kelas 11',
        value: 11,
    },
    {
        title: 'Kelas 12',
        value: 12,
    },
    {
        title: 'Kelas 13',
        value: 13,
    },
]
export const dataKompetensi = [
    {
        value: 1,
        title: 'Pengetahuan',
    },
    {
        value: 2,
        title: 'Keterampilan',
    },
    {
        value: 3,
        title: 'Pusat Keunggulan',
    },
]
export const dataCapaian = [
    {
        value: '0',
        title: 'Tidak tercapai',
    },
    {
        value: '1',
        title: 'Tercapai',
    }
]
export const bentukPenilaian = [
    {
        value: 'asesmen',
        title: 'Nilai Asesmen',
    },
    {
        value: 'akhir',
        title: 'Nilai Akhir',
    }
]
