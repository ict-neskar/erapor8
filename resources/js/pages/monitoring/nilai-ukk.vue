<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Waka',
    title: 'Monitoring \u00bb Penilaian UKK',
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
    key: 'jurusan',
    title: 'Program/Kompetensi Keahlian',
    sortable: false,
  },
  {
    key: 'kode',
    title: 'Kode Paket',
    sortable: false,
    align: 'center',
  },
  {
    key: 'nama',
    title: 'Nama Paket',
    sortable: false,
  },
  {
    key: 'guru_internal',
    title: 'Penguji Internal',
    sortable: false,
  },
  {
    key: 'guru_eksternal',
    title: 'Instruktur',
    sortable: false,
  },
  {
    key: 'pd_count',
    title: 'Jumlah PD',
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
  data: 'nilai-ukk',
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
      <VCardTitle>Monitoring &raquo; Penilaian UKK</VCardTitle>
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
            item-value="rombongan_belajar_id" item-title="nama" />
        </VCol>
        <VCol cols="12" md="4">
          <AppTextField v-model="options.searchQuery" placeholder="Cari Data" />
        </VCol>
      </VRow>
    </VCardText>
    <VDataTableServer :items="items" :server-items-length="total" :headers="headers" :options="options"
      :loading="loadingTable" loading-text="Loading..." @update:sortBy="updateSortBy">
      <template #item.jurusan="{ item }">
        {{ item.paket_ukk.jurusan.nama_jurusan }}
      </template>
      <template #item.kode="{ item }">
        {{ item.paket_ukk.nomor_paket }}
      </template>
      <template #item.nama="{ item }">
        {{ item.paket_ukk.nama_paket_id }}
      </template>
      <template #item.guru_internal="{ item }">
        <ProfilePtk :item="item.guru_internal" />
      </template>
      <template #item.guru_eksternal="{ item }">
        <ProfilePtk :item="item.guru_eksternal" />
      </template>
      <template #bottom>
        <TablePagination v-model:page="options.page" :items-per-page="options.itemsPerPage" :total-items="total" />
      </template>
    </VDataTableServer>
  </VCard>
</template>
