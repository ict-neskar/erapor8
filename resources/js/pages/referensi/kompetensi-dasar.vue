<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Guru',
    title: 'Ref. Kompetensi Dasar',
  },
})

const options = ref({
  tingkat: null,
  rombongan_belajar_id: null,
  pembelajaran_id: null,
  page: 1,
  itemsPerPage: 10,
  searchQuery: '',
  sortby: 'updated_at',
  sortbydesc: 'DESC',
});
// Headers
const headers = [
  {
    key: 'mata_pelajaran_id',
    title: 'Mata Pelajaran',
  },
  {
    key: 'id_kompetensi',
    title: 'Kode',
    align: 'center',
  },
  {
    key: 'kelas',
    title: 'kelas',
    align: 'center',
    sortable: false,
    nowrap: true,
  },
  {
    key: 'kompetensi_dasar',
    title: 'Deskripsi Kompetensi Dasar',
  },
  {
    key: 'kurikulum',
    title: 'Kurikulum',
    align: 'center',
  },
  {
    key: 'status',
    title: 'Status',
    sortable: false,
    align: 'center',
  },
  {
    key: 'actions',
    title: 'Aksi',
    align: 'center',
    sortable: false,
    nowrap: true,
  },
]
const items = ref([])
const total = ref(0)
const data_rombel = ref([])
const data_mapel = ref([])
const loadingTable = ref(false)
onMounted(async () => {
  await fetchData();
});
watch(options, async () => {
  await fetchData();
}, { deep: true });
watch(
  () => options.value.searchQuery,
  () => {
    options.value.page = 1
  }
)
const updateSortBy = (val) => {
  options.value.sortby = val[0]?.key
  options.value.sortbydesc = val[0]?.order
}
const fetchData = async () => {
  loadingTable.value = true;
  try {
    const response = await useApi(createUrl('/referensi/kompetensi-dasar', {
      query: {
        user_id: $user.user_id,
        guru_id: $user.guru_id,
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        periode_aktif: $semester.nama,
        tingkat: options.value.tingkat,
        rombongan_belajar_id: options.value.rombongan_belajar_id,
        q: options.value.searchQuery,
        page: options.value.page,
        per_page: options.value.itemsPerPage,
        sortby: options.value.sortby,
        sortbydesc: options.value.sortbydesc,
      },
    }));
    let getData = response.data
    items.value = getData.value.data.data
    total.value = getData.value.data.total
  } catch (error) {
    console.error(error);
  } finally {
    loadingTable.value = false;
  }
}
const isConfirmDialogVisible = ref(false)
const notif = ref({
  color: null,
  title: null,
  text: null,
})
const filter = ref({
  tingkat: null,
  rombongan_belajar_id: null,
  pembelajaran_id: null,
})
const isNotifVisible = ref(false)
const loadingRombel = ref(false)
const loadingMapel = ref(false)
const changeTingkat = async (val) => {
  loadingRombel.value = true
  data_rombel.value = []
  data_mapel.value = []
  filter.value.rombongan_belajar_id = null
  filter.value.pembelajaran_id = null
  options.value.tingkat = val
  if (val) {
    await $api('/referensi/get-data', {
      method: 'POST',
      body: {
        data: 'rombel',
        tingkat: val,
        user_id: $user.user_id,
        guru_id: $user.guru_id,
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        periode_aktif: $semester.nama,
      },
      onResponse({ response }) {
        let getData = response._data
        loadingRombel.value = false
        data_rombel.value = getData
      }
    })
  } else {
    loadingRombel.value = false
  }
}
const changeRombel = async (val) => {
  data_mapel.value = []
  loadingMapel.value = true
  filter.value.pembelajaran_id = null
  options.value.rombongan_belajar_id = val
  if (val) {
    await $api('/referensi/get-data', {
      method: 'POST',
      body: {
        data: 'mapel',
        rombongan_belajar_id: val,
        user_id: $user.user_id,
        guru_id: $user.guru_id,
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        periode_aktif: $semester.nama,
      },
      onResponse({ response }) {
        let getData = response._data
        loadingMapel.value = false
        data_mapel.value = getData.mapel
      }
    })
  } else {
    loadingMapel.value = false
  }
}
const changeMapel = async (val) => {
  options.value.pembelajaran_id = val
}
const isDialogVisible = ref(false)
const dialogTitle = ref('')
const isAddNew = ref(true)
const form = ref({
  kompetensi_dasar_id: null,
  semester_id: $semester.semester_id,
  user_id: $user.user_id,
  guru_id: $user.guru_id,
  sekolah_id: $user.sekolah_id,
  tingkat: null,
  rombongan_belajar_id: null,
  mata_pelajaran_id: null,
  kompetensi_id: null,
  id_kompetensi: null,
  kompetensi_dasar: null,
  kompetensi_dasar_alias: null,
})
const errors = ref({
  tingkat: null,
  rombongan_belajar_id: null,
  mata_pelajaran_id: null,
  kompetensi_id: null,
  id_kompetensi: null,
  kompetensi_dasar: null,
  kompetensi_dasar_alias: null,
})
const dataRombel = ref([])
const dataMapel = ref([])
const addNewData = () => {
  isAddNew.value = true
  isDialogVisible.value = true
  dialogTitle.value = 'Tambah Data Kompetensi Dasar'
}
const edit = async (item) => {
  form.value = {
    kompetensi_dasar_id: item.kompetensi_dasar_id,
    kompetensi_dasar: item.kompetensi_dasar,
    kompetensi_dasar_alias: item.kompetensi_dasar_alias ?? item.kompetensi_dasar,
  }
  isAddNew.value = false
  isDialogVisible.value = true
  dialogTitle.value = 'Ubah Ringkasan Kompetensi Dasar'
}
const hapus = async (kompetensi_dasar_id) => {
  getItem.value = { kompetensi_dasar_id: kompetensi_dasar_id }
  isConfirmDialogVisible.value = true
  confirmationText.value = 'Tindakan ini akan mengembalikan isi deskripsi ke bawaan aplikasi!'
}
const getItem = ref()
const hapusGanda = async (item) => {
  getItem.value = item
  isConfirmDialogVisible.value = true
  confirmationText.value = 'Tindakan ini akan menghapus data ganda Kompetensi Dasar!'
}
const confirmationText = ref()
const changeStatus = async (kompetensi_dasar_id, aktif) => {
  getItem.value = { kompetensi_dasar_id: kompetensi_dasar_id, aktif: aktif }
  isConfirmDialogVisible.value = true
  confirmationText.value = (aktif) ? 'Tindakan ini akan menonaktifkan data Kompetensi Dasar!' : 'Tindakan ini akan mengaktifkan data Kompetensi Dasar!'
}
const confirmDelete = async (val) => {
  if (val) {
    await $api('/referensi/kompetensi-dasar/update', {
      method: 'POST',
      body: getItem.value,
      onResponse({ response }) {
        let getData = response._data
        getItem.value = null
        isDialogVisible.value = false
        isNotifVisible.value = true
        notif.value = getData
      },
    })
  }
}
const confirmClose = async () => {
  await fetchData();
}
const postData = async () => {
  await $api('/referensi/kompetensi-dasar/save', {
    method: 'POST',
    body: form.value,
    onResponse({ response }) {
      let getData = response._data
      if (getData.errors) {
        errors.value = getData.errors
      } else {
        isDialogVisible.value = false
        isNotifVisible.value = true
        notif.value = getData
        form.value = {
          kompetensi_dasar_id: null,
          semester_id: $semester.semester_id,
          user_id: $user.user_id,
          guru_id: $user.guru_id,
          sekolah_id: $user.sekolah_id,
          tingkat: null,
          rombongan_belajar_id: null,
          mata_pelajaran_id: null,
          kompetensi_id: null,
          id_kompetensi: null,
          kompetensi_dasar: null,
          kompetensi_dasar_alias: null,
        }
        errors.value = {
          tingkat: null,
          rombongan_belajar_id: null,
          mata_pelajaran_id: null,
          kompetensi_id: null,
          id_kompetensi: null,
          kompetensi_dasar: null,
          kompetensi_dasar_alias: null,
        }
      }
    },
  })
}
const saveData = async (val) => {
  if (val) {
    await postData()
  }
}
const changeFormTingkat = async (val) => {
  if (val) {
    const newForm = { data: 'rombel', add_kd: 1 };
    const mergedForm = { ...newForm, ...form.value };
    await $api('/referensi/get-data', {
      method: 'POST',
      body: mergedForm,
      onResponse({ response }) {
        let getData = response._data
        dataRombel.value = getData
      }
    })
  }
}
const changeFormRombel = async (val) => {
  if (val) {
    const newForm = { data: 'mapel', add_kd: 1 };
    const mergedForm = { ...newForm, ...form.value };
    await $api('/referensi/get-data', {
      method: 'POST',
      body: mergedForm,
      onResponse({ response }) {
        let getData = response._data
        dataMapel.value = getData.mapel
      }
    })
  }
}
</script>

<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Referensi Kompetensi Dasar</VCardTitle>
      </VCardItem>
      <VDivider />
      <VCardText>
        <VRow>
          <VCol cols="12" sm="4">
            <AppSelect v-model="filter.tingkat" placeholder="== Filter Tingkat ==" :items="tingkatKelas" clearable
              clear-icon="tabler-x" @update:model-value="changeTingkat" />
          </VCol>
          <VCol cols="12" sm="4">
            <AppSelect v-model="filter.rombongan_belajar_id" placeholder="== Filter Rombel == " :items="data_rombel"
              clearable clear-icon="tabler-x" @update:model-value="changeRombel" item-value="rombongan_belajar_id"
              item-title="nama" :loading="loadingRombel" :disabled="loadingRombel" />
          </VCol>
          <VCol cols="12" sm="4">
            <AppSelect v-model="filter.pembelajaran_id" placeholder="== Filter Mapel ==" :items="data_mapel" clearable
              clear-icon="tabler-x" @update:model-value="changeMapel" item-value="pembelajaran_id"
              item-title="nama_mata_pelajaran" :loading="loadingMapel" :disabled="loadingMapel" />
          </VCol>
        </VRow>
      </VCardText>
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
          <!-- 👉 Search  -->
          <div style="inline-size: 15.625rem;">
            <AppTextField v-model="options.searchQuery" placeholder="Cari Data" />
          </div>

          <!-- 👉 Add user button -->
          <VBtn prepend-icon="tabler-plus" @click="addNewData">
            Tambah Data
          </VBtn>
        </div>
      </VCardText>

      <VDivider />

      <!-- SECTION datatable -->
      <VDataTableServer :items="items" :server-items-length="total" :headers="headers" :options="options"
        :loading="loadingTable" loading-text="Loading..." @update:sortBy="updateSortBy">
        <template #item.mata_pelajaran_id="{ item }">
          {{ item.mata_pelajaran.nama }}
        </template>
        <template #item.kelas="{ item }">
          <span v-if="item.kelas_10">10</span>
          <span v-if="item.kelas_11">&nbsp;11</span>
          <span v-if="item.kelas_12">&nbsp;12</span>
          <span v-if="item.kelas_13">&nbsp;13</span>
        </template>
        <template #item.kompetensi_dasar="{ item }">
          {{ item.kompetensi_dasar_alias ?? item.kompetensi_dasar }}
        </template>
        <template #item.status="{ item }">
          <VChip size="x-small" color="success" variant="elevated" v-if="item.aktif">
            Aktif
          </VChip>
          <VChip size="x-small" color="error" variant="elevated" v-else>
            Non Aktif
          </VChip>
        </template>
        <template #item.actions="{ item }">
          <IconBtn @click="edit(item)">
            <VTooltip activator="parent" location="top">
              Ubah Ringkat
            </VTooltip>
            <VIcon icon="tabler-pencil" />
          </IconBtn>

          <IconBtn @click="hapus(item.kompetensi_dasar_id)">
            <VTooltip activator="parent" location="top">
              Reset isi ringkasan
            </VTooltip>
            <VIcon icon="tabler-trash" />
          </IconBtn>
          <VBtn icon variant="text" color="medium-emphasis">
            <VIcon icon="tabler-dots-vertical" />
            <VMenu activator="parent">
              <VList>
                <VListItem @click="changeStatus(item.kompetensi_dasar_id, item.aktif)">
                  <template #prepend>
                    <VIcon icon="tabler-x" v-if="item.aktif" />
                    <VIcon icon="tabler-check" v-else />
                  </template>
                  <VListItemTitle v-if="item.aktif">Non Aktifkan</VListItemTitle>
                  <VListItemTitle v-else>Aktifkan</VListItemTitle>
                </VListItem>
                <VListItem @click="hapusGanda(item)">
                  <template #prepend>
                    <VIcon icon="tabler-power" />
                  </template>
                  <VListItemTitle>Hapus Data Ganda</VListItemTitle>
                </VListItem>
              </VList>
            </VMenu>
          </VBtn>
        </template>
        <!-- pagination -->
        <template #bottom>
          <TablePagination v-model:page="options.page" :items-per-page="options.itemsPerPage" :total-items="total" />
        </template>
      </VDataTableServer>
      <!-- SECTION -->
    </VCard>
    <DefaultDialog v-model:isDialogVisible="isDialogVisible" :dialog-title="dialogTitle" :errors="errors"
      @confirm="saveData">
      <template #content>
        <template v-if="isAddNew">
          <VRow>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" class="d-flex align-items-center">
                  <label class="v-label text-body-2 text-high-emphasis" for="semester_id">Tahun Pelajaran</label>
                </VCol>
                <VCol cols="12" md="9">
                  <AppTextField id="semester_id" :value="$semester.nama" disabled />
                </VCol>
              </VRow>
            </VCol>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" class="d-flex align-items-center">
                  <label class="v-label text-body-2 text-high-emphasis" for="tingkat">Tingkat Kelas</label>
                </VCol>
                <VCol cols="12" md="9">
                  <AppSelect v-model="form.tingkat" placeholder="== Pilih Tingkat kelas ==" :items="tingkatKelas"
                    clearable clear-icon="tabler-x" @update:model-value="changeFormTingkat"
                    :error-messages="errors.tingkat" />
                </VCol>
              </VRow>
            </VCol>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" class="d-flex align-items-center">
                  <label class="v-label text-body-2 text-high-emphasis" for="rombongan_belajar_id">Rombongan
                    Belajar</label>
                </VCol>
                <VCol cols="12" md="9">
                  <AppSelect v-model="form.rombongan_belajar_id" placeholder="== Pilih Rombongan Belajar == "
                    :items="dataRombel" clearable clear-icon="tabler-x" @update:model-value="changeFormRombel"
                    item-value="rombongan_belajar_id" item-title="nama" :loading="loadingRombel"
                    :disabled="loadingRombel" :error-messages="errors.rombongan_belajar_id" />
                </VCol>
              </VRow>
            </VCol>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" class="d-flex align-items-center">
                  <label class="v-label text-body-2 text-high-emphasis" for="mata_pelajaran_id">Mata Pelajaran</label>
                </VCol>
                <VCol cols="12" md="9">
                  <AppSelect v-model="form.mata_pelajaran_id" placeholder="== Pilih Mata Pelajaran =="
                    :items="dataMapel" clearable clear-icon="tabler-x" item-value="mata_pelajaran_id"
                    item-title="nama_mata_pelajaran" :loading="loadingMapel" :disabled="loadingMapel"
                    :error-messages="errors.mata_pelajaran_id" />
                </VCol>
              </VRow>
            </VCol>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" class="d-flex align-items-center">
                  <label class="v-label text-body-2 text-high-emphasis" for="kompetensi_id">Aspek Penilaian</label>
                </VCol>
                <VCol cols="12" md="9">
                  <AppSelect v-model="form.kompetensi_id" placeholder="== Pilih Aspek Penilaian =="
                    :items="dataKompetensi" clearable clear-icon="tabler-x" :loading="loadingMapel"
                    :disabled="loadingMapel" :error-messages="errors.kompetensi_id" />
                </VCol>
              </VRow>
            </VCol>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" class="d-flex align-items-center">
                  <label class="v-label text-body-2 text-high-emphasis" for="id_kompetensi">Kode Kompetensi
                    Dasar</label>
                </VCol>
                <VCol cols="12" md="9">
                  <AppTextField id="id_kompetensi" v-model="form.id_kompetensi"
                    placeholder="3.x untuk pengetahuan, 4.x untuk keterampilan" persistent-placeholder
                    :error-messages="errors.id_kompetensi" />
                </VCol>
              </VRow>
            </VCol>
            <VCol cols="12">
              <VRow no-gutters>
                <VCol cols="12" md="3" class="d-flex align-items-center">
                  <label class="v-label text-body-2 text-high-emphasis" for="kompetensi_dasar">Deskripsi Kompetensi
                    Dasar</label>
                </VCol>
                <VCol cols="12" md="9">
                  <AppTextarea v-model="form.kompetensi_dasar" placeholder="Deskripsi Kompetensi Dasar" auto-grow
                    :error-messages="errors.kompetensi_dasar" />
                </VCol>
              </VRow>
            </VCol>
          </VRow>
        </template>
        <template v-else>
          <AppTextarea v-model="form.kompetensi_dasar" label="Deskripsi Kompetensi Dasar Baru"
            placeholder="Deskripsi Kompetensi Dasar Mana" auto-grow disabled />
          <AppTextarea v-model="form.kompetensi_dasar_alias" label="Deskripsi Kompetensi Dasar Baru"
            placeholder="Deskripsi Kompetensi Dasar Baru" auto-grow :error-messages="errors.kompetensi_dasar_alias" />
        </template>
      </template>
    </DefaultDialog>
    <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
      confirmation-question="Apakah Anda yakin?" :confirmation-text="confirmationText" :confirm-color="notif.color"
      :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmDelete" @close="confirmClose" />
  </section>
</template>
<style lang="scss">
.scrollable-dialog {
  overflow: visible !important;
}
</style>
