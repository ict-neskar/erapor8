<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Waka',
    title: 'Monitoring \u00bb Penilaian Ekstrakurikuler',
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
    key: 'nama_ekskul',
    title: 'Nama Ekstrakurikuler',
    sortable: false,
  },
  {
    key: 'pembina',
    title: 'Guru Pembina',
    sortable: false,
  },
  {
    key: 'anggota_rombel_count',
    title: 'Jumlah Anggota',
    sortable: false,
    align: 'center',
  },
  {
    key: 'dinilai',
    title: 'Jml Anggota Dinilai',
    sortable: false,
    align: 'center',
  },
  /*{
    key: 'actions',
    title: 'Detil',
    sortable: false,
    align: 'center',
  },*/
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
  data: 'nilai-ekskul',
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
    //dataRombel.value = getData.rombel
  } catch (error) {
    console.error(error)
  } finally {
    loadingTable.value = false
  }
}
</script>
<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Monitoring &raquo; Penilaian Ekstrakurikuler</VCardTitle>
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
        <!--VCol cols="12" md="4">
          <AppAutocomplete v-model="options.rombongan_belajar_id" :items="dataRombel" placeholder="== Filter Rombel =="
            item-value="rombongan_belajar_id" item-title="nama" />
        </VCol-->
        <VCol cols="12" md="4" offset-md="4">
          <AppTextField v-model="options.searchQuery" placeholder="Cari Data" />
        </VCol>
      </VRow>
    </VCardText>
    <VDataTableServer :items="items" :server-items-length="total" :headers="headers" :options="options"
      :loading="loadingTable" loading-text="Loading..." @update:sortBy="updateSortBy">
      <template #item.nama_ekskul="{ item }">
        {{ item.nama }}
      </template>
      <template #item.pembina="{ item }">
        <ProfilePtk :item="item.wali_kelas" />
      </template>
      <template #bottom>
        <TablePagination v-model:page="options.page" :items-per-page="options.itemsPerPage" :total-items="total" />
      </template>
    </VDataTableServer>
  </VCard>
</template>
