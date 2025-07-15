<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Waka',
    title: 'Monitoring \u00bb Penilaian Akademik',
  },
})
const options = ref({
  itemsPerPage: 10,
  searchQuery: null,
  page: 1,
  sortby: 'updated_at',
  sortbydesc: 'DESC',
  rombongan_belajar_id: null,
})
const updateSortBy = (val) => {
  options.value.sortby = val[0]?.key
  options.value.sortbydesc = val[0]?.order
}
const dataRombel = ref([])
const items = ref([])
const total = ref(0)
const loadingTable = ref(false)
const headers = [
  {
    key: 'rombel',
    title: 'Rombel',
    sortable: false,
  },
  {
    key: 'nama_mata_pelajaran',
    title: 'Mata Pelajaran',
    sortable: false,
  },
  {
    key: 'guru',
    title: 'Guru Mata Pelajaran',
    sortable: false,
  },
  {
    key: 'pd_count',
    title: 'JML PD',
    sortable: false,
    align: 'center',
  },
  {
    key: 'pd_dinilai',
    title: 'JML PD Dinilai',
    sortable: false,
    align: 'center',
  },
  {
    key: 'actions',
    title: 'Detil',
    sortable: false,
    align: 'center',
  },
]
onMounted(async () => {
  await fetchData()
})
watch(options, async () => {
  await fetchData()
}, { deep: true })
watch(
  () => options.value.searchQuery,
  () => {
    options.value.page = 1
  }
)
const defaultForm = ref({
  user_id: $user.user_id,
  guru_id: $user.guru_id,
  sekolah_id: $user.sekolah_id,
  semester_id: $semester.semester_id,
  periode_aktif: $semester.nama,
  data: 'nilai-akademik',
})
const fetchData = async () => {
  loadingTable.value = true
  try {
    const response = await useApi(createUrl('/monitoring', {
      query: {
        user_id: defaultForm.value.user_id,
        guru_id: defaultForm.value.guru_id,
        sekolah_id: defaultForm.value.sekolah_id,
        semester_id: defaultForm.value.semester_id,
        periode_aktif: defaultForm.value.nama,
        data: defaultForm.value.data,
        q: options.value.searchQuery,
        page: options.value.page,
        per_page: options.value.itemsPerPage,
        sortby: options.value.sortby,
        sortbydesc: options.value.sortbydesc,
        rombongan_belajar_id: options.value.rombongan_belajar_id,
      },
    }))
    let getData = response.data.value
    items.value = getData.data
    total.value = getData.total
    dataRombel.value = getData.rombel
  } catch (error) {
    console.error(error)
  } finally {
    loadingTable.value = false
  }
}
const loadingBtn = ref([])
const isDialogVisible = ref(false)
const itemData = ref([])
const titleDetilNilai = ref()
const induk = ref()
const data_siswa = ref([])
const merdeka = ref(false)
const is_ppa = ref(false)
const sub_mapel = ref(0)
const isConfirmDialogVisible = ref(false)
const isNotifVisible = ref(false)
const notif = ref({
  color: null,
  title: null,
  text: null,
})
const textDialog = ref('')
const form = ref({
  pembelajaran_id: null,
  rombongan_belajar_id: null,
})
const detil = async (item) => {
  form.value.pembelajaran_id = item.pembelajaran_id
  form.value.rombongan_belajar_id = item.rombongan_belajar_id
  detilPenilaian({
    pembelajaran_id: item.pembelajaran_id,
    rombongan_belajar_id: item.rombongan_belajar_id,
  })
}
const detilPenilaian = async (params) => {
  loadingBtn.value[params.pembelajaran_id] = true
  await $api('/dashboard/detil-penilaian', {
    method: 'POST',
    body: {
      pembelajaran_id: params.pembelajaran_id,
      rombongan_belajar_id: params.rombongan_belajar_id,
    },
    onResponse({ response }) {
      let getData = response._data
      loadingBtn.value[params.pembelajaran_id] = false
      isDialogVisible.value = true
      itemData.value = getData.pembelajaran
      titleDetilNilai.value = `Detil Penilaian Mata Pelajaran ${getData.pembelajaran.nama_mata_pelajaran}`
      induk.value = getData.pembelajaran.induk
      if (getData.pembelajaran.mata_pelajaran_id == 800001000) {
        sub_mapel.value = 1
      } else {
        sub_mapel.value = getData.pembelajaran.tema_count
      }
      data_siswa.value = getData.data_siswa
      merdeka.value = getData.merdeka
      is_ppa.value = getData.is_ppa
    },
  })
}
const confirmClose = () => {
  isNotifVisible.value = false
  setTimeout(() => {
    notif.value = {
      color: '',
      title: '',
      text: '',
    }
  }, 300)
  fetchData()
}
const refreshNilai = () => {
  detilPenilaian(form.value)
}
</script>
<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Monitoring &raquo; Penilaian Akademik</VCardTitle>
    </VCardItem>
    <VDivider />
    <VCardText>
      <VRow>
        <VCol cols="12" md="4">
          <AppSelect v-model="options.itemsPerPage" :items="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' },
          ]" />
        </VCol>
        <VCol cols="12" md="4">
          <AppAutocomplete v-model="options.rombongan_belajar_id" :items="dataRombel" placeholder="== Filter Rombel =="
            item-value="rombongan_belajar_id" item-title="nama" clearable />
        </VCol>
        <VCol cols="12" md="4">
          <AppTextField v-model="options.searchQuery" placeholder="Cari Data" />
        </VCol>
      </VRow>
    </VCardText>
    <VDataTableServer :items="items" :server-items-length="total" :headers="headers" :options="options"
      :loading="loadingTable" loading-text="Loading..." @update:sortBy="updateSortBy">
      <template #item.actions="{ item }">
        <VBtn size="x-small" color="success" @click="detil(item)" :loading="loadingBtn[item.pembelajaran_id]"
          :disabled="loadingBtn[item.pembelajaran_id]">
          Detil
        </VBtn>
      </template>
      <template #bottom>
        <TablePagination v-model:page="options.page" :items-per-page="options.itemsPerPage" :total-items="total" />
      </template>
    </VDataTableServer>
  </VCard>
  <DetilNilaiDialog v-model:isDialogVisible="isDialogVisible" :item-data="itemData" :title-detil-nilai="titleDetilNilai"
    :merdeka="merdeka" :is-ppa="is_ppa" :data-siswa="data_siswa" :sub-mapel="sub_mapel" @refresh="refreshNilai">
  </DetilNilaiDialog>
  <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
    confirmation-question="Apakah Anda yakin?" :confirmation-text="textDialog" :confirm-color="notif.color"
    :confirm-title="notif.title" :confirm-msg="notif.text" @close="confirmClose" />
</template>
