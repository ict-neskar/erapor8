<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Kaprog',
    title: 'Ref. Uji Kompetensi Keahlian',
  },
})
const options = ref({
  page: 1,
  itemsPerPage: 10,
  searchQuery: '',
  sortby: 'name',
  sortbydesc: 'ASC',
});
// Headers
const headers = [
  {
    title: 'Kompetensi Keahlian',
    key: 'jurusan',
    sortable: false,
  },
  {
    title: 'Nomor Paket',
    key: 'nomor_paket',
    sortable: false,
  },
  {
    title: 'Nama Paket',
    key: 'nama_paket_en',
    sortable: false,
  },
  {
    title: 'Jml Unit',
    key: 'unit_ukk_count',
    align: 'center',
    sortable: false,
  },
  {
    title: 'Status',
    key: 'Status',
    align: 'center',
    sortable: false,
  },
  {
    title: 'Aksi',
    key: 'actions',
    align: 'center',
    sortable: false,
    nowrap: true,
  },
]
const items = ref([])
const total = ref(0)
const loadingTable = ref(false)
const isDialogVisible = ref(false)
const dialogTitle = ref()
const errors = ref({
  jurusan_id: null,
  kurikulum_id: null,
})
const form = ref({
  jurusan_id: null,
  kurikulum_id: null,
  nomor_paket: {},
  nama_paket_id: {},
  nama_paket_en: {},
  status: {},
})
const dataJurusan = ref([])
const dataKurikulum = ref([])
const loadingJurusan = ref(false)
const loadingKurikulum = ref(false)
const ambilData = async (data) => {
  const newForm = {
    data: data,
    user_id: $user.user_id,
    guru_id: $user.guru_id,
    sekolah_id: $user.sekolah_id,
    semester_id: $semester.semester_id,
    periode_aktif: $semester.nama
  };
  const mergedForm = { ...newForm, ...form.value };
  await $api('/referensi/get-data', {
    method: 'POST',
    body: mergedForm,
    onResponse({ response }) {
      let getData = response._data
      loadingJurusan.value = false
      loadingKurikulum.value = false
      if (data == 'jurusan') {
        dataJurusan.value = getData
      }
      if (data == 'kurikulum') {
        dataKurikulum.value = getData
      }
    }
  })
}
const addNew = async () => {
  loadingJurusan.value = true
  loadingKurikulum.value = true
  console.log('addNew');
  isDialogVisible.value = true
  dialogTitle.value = 'Tambah Data UKK'
  await ambilData('jurusan');
}
const changeJurusan = async (val) => {
  console.log('changeJurusan', val)
  await ambilData('kurikulum')
}
const changeKurikulum = async (val) => {
  console.log('changeKurikulum', val)
}
const saveData = async () => {

}
</script>
<template>
  <div>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Referensi Uji Kompetensi Keahlian</VCardTitle>
      </VCardItem>
      <VDivider />
      <VCardText class="d-flex flex-wrap gap-4">
        <div class="me-3 d-flex gap-3">
          <AppSelect v-model="options.itemsPerPage" :items="[
            { value: 10, title: '10' },
            { value: 25, title: '25' },
            { value: 50, title: '50' },
            { value: 100, title: '100' },
          ]" style="inline-size: 6.25rem;" />
        </div>
        <VSpacer />

        <div class="app-user-search-filter d-flex align-center flex-wrap gap-4">
          <div style="inline-size: 15.625rem;">
            <AppTextField v-model="options.searchQuery" placeholder="Cari Data" />
          </div>
          <VBtn prepend-icon="tabler-plus" @click="addNew">
            Tambah Data
          </VBtn>
        </div>
      </VCardText>
      <VDivider />
      <DefaultDialog v-model:isDialogVisible="isDialogVisible" :dialog-title="dialogTitle" :errors="errors"
        @confirm="saveData">
        <template #content>
          <VRow>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" class="d-flex align-items-center">
                  <label class="v-label text-body-2 text-high-emphasis" for="jurusan_id">Kompetensi Keahlian</label>
                </VCol>
                <VCol cols="12" md="9">
                  <AppSelect v-model="form.jurusan_id" placeholder="== Pilih Kompetensi Keahlian == "
                    :items="dataJurusan" clearable clear-icon="tabler-x" @update:model-value="changeJurusan"
                    item-value="jurusan_id" item-title="nama_jurusan_sp" :loading="loadingJurusan"
                    :disabled="loadingJurusan" :error-messages="errors.jurusan_id" />
                </VCol>
              </VRow>
            </VCol>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" class="d-flex align-items-center">
                  <label class="v-label text-body-2 text-high-emphasis" for="kurikulum_id">Kurikulum</label>
                </VCol>
                <VCol cols="12" md="9">
                  <AppSelect v-model="form.kurikulum_id" placeholder="== Pilih Kurikulum ==" :items="dataKurikulum"
                    clearable clear-icon="tabler-x" @update:model-value="changeKurikulum" item-value="kurikulum_id"
                    item-title="nama_kurikulum" :loading="loadingKurikulum" :disabled="loadingKurikulum"
                    :error-messages="errors.kurikulum_id" />
                </VCol>
              </VRow>
            </VCol>
          </VRow>
          <VTable>
            <thead>
              <tr>
                <th class="text-center">Nomor Paket</th>
                <th class="text-center">Judul Paket (ID)</th>
                <th class="text-center">Judul Paket (EN)</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="i in 10">
                <td>
                  <AppTextField v-model="form.nomor_paket[i]" />
                </td>
                <td>
                  <AppTextField v-model="form.nama_paket_id[i]" />
                </td>
                <td>
                  <AppTextField v-model="form.nama_paket_en[i]" />
                </td>
                <td>
                  <AppSelect v-model="form.status[i]" :items="[
                    { value: null, title: '== Pilih Status ==' },
                    { value: 1, title: 'Aktif' },
                    { value: 0, title: 'Tidak Aktif' },
                  ]" />
                </td>
              </tr>
            </tbody>
          </VTable>
        </template>
      </DefaultDialog>
    </VCard>
  </div>
</template>
