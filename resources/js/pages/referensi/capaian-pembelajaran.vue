<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Guru',
    title: 'Ref. Capaian Pembelajaran'
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
    key: 'fase',
    title: 'fase',
    align: 'center',
  },
  {
    key: 'elemen',
    title: 'elemen',
    align: 'center',
    sortable: false,
  },
  {
    key: 'deskripsi',
    title: 'Deskripsi',
  },
  {
    key: 'tp_count',
    title: 'jml tp',
    align: 'center',
    sortable: false,
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
    const response = await useApi(createUrl('/referensi/capaian-pembelajaran', {
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
const filter = ref({
  tingkat: null,
  rombongan_belajar_id: null,
  pembelajaran_id: null,
})
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
        add_cp: 1,
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
const defaultForm = {
  semester_id: $semester.semester_id,
  user_id: $user.user_id,
  guru_id: $user.guru_id,
  sekolah_id: $user.sekolah_id
}
const form = ref({
  tingkat: null,
  rombongan_belajar_id: null,
  mata_pelajaran_id: null,
  elemen: null,
  capaian_pembelajaran: null,
})
const errors = ref({
  tingkat: null,
  rombongan_belajar_id: null,
  mata_pelajaran_id: null,
  elemen: null,
  capaian_pembelajaran: null,
})
const dataRombel = ref([])
const dataMapel = ref([])
const isDialogVisible = ref(false)
const addNewData = () => {
  isDialogVisible.value = true
}
const isConfirmDialogVisible = ref(false)
const isNotifVisible = ref(false)
const notif = ref({
  color: null,
  title: null,
  text: null,
})
const confirmationText = ref()
const getItem = ref({})
const changeStatus = async (cp_id, aktif) => {
  getItem.value = { cp_id: cp_id, aktif: aktif }
  isConfirmDialogVisible.value = true
  confirmationText.value = (aktif) ? 'Tindakan ini akan menonaktifkan data Capaian Pembelajaran!' : 'Tindakan ini akan mengaktifkan data Capaian Pembelajaran!'
}
const confirmDelete = async (val) => {
  if (val) {
    await $api('/referensi/capaian-pembelajaran/update', {
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
  const mergedForm = { ...defaultForm, ...form.value };
  await $api('/referensi/capaian-pembelajaran/save', {
    method: 'POST',
    body: mergedForm,
    onResponse({ response }) {
      let getData = response._data
      if (getData.errors) {
        errors.value = getData.errors
      } else {
        isDialogVisible.value = false
        isNotifVisible.value = true
        notif.value = getData
        form.value = {
          tingkat: null,
          rombongan_belajar_id: null,
          mata_pelajaran_id: null,
          elemen: null,
          capaian_pembelajaran: null,
        }
        errors.value = {
          tingkat: null,
          rombongan_belajar_id: null,
          mata_pelajaran_id: null,
          elemen: null,
          capaian_pembelajaran: null,
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
    loadingRombel.value = true
    const newForm = { data: 'rombel' };
    const mergedForm = { ...newForm, ...defaultForm, ...form.value };
    await $api('/referensi/get-data', {
      method: 'POST',
      body: mergedForm,
      onResponse({ response }) {
        let getData = response._data
        dataRombel.value = getData
        loadingRombel.value = false
      }
    })
  }
}
const changeFormRombel = async (val) => {
  if (val) {
    loadingMapel.value = true
    const newForm = { data: 'mapel' };
    const mergedForm = { ...newForm, ...defaultForm, ...form.value };
    await $api('/referensi/get-data', {
      method: 'POST',
      body: mergedForm,
      onResponse({ response }) {
        let getData = response._data
        dataMapel.value = getData.mapel
        loadingMapel.value = false
      }
    })
  }
}
</script>
<template>
  <div>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Referensi Capaian Pembelajaran</VCardTitle>
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
          <div style="inline-size: 15.625rem;">
            <AppTextField v-model="options.searchQuery" placeholder="Cari Data" />
          </div>
          <VBtn prepend-icon="tabler-plus" @click="addNewData">
            Tambah Data
          </VBtn>
        </div>
      </VCardText>
      <VDivider />
      <VDataTableServer :items="items" :server-items-length="total" :headers="headers" :options="options"
        :loading="loadingTable" loading-text="Loading..." @update:sortBy="updateSortBy">
        <template #item.mata_pelajaran_id="{ item }">
          {{ item.mata_pelajaran.nama }}
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
          <VBtn :icon="item.aktif ? 'tabler-x' : 'tabler-check'" :color="item.aktif ? 'error' : 'success'"
            @click="changeStatus(item.cp_id, item.aktif)">
            <VIcon icon="tabler-x" v-if="item.aktif" />
            <VIcon icon="tabler-check" v-else />
            <VTooltip location="top" activator="parent" transition="scale-transition">
              <span v-if="item.aktif">Non Aktifkan</span>
              <span v-else>Aktifkan</span>
            </VTooltip>
          </VBtn>
        </template>
        <template #bottom>
          <TablePagination v-model:page="options.page" :items-per-page="options.itemsPerPage" :total-items="total" />
        </template>
      </VDataTableServer>
    </VCard>
    <DefaultDialog v-model:isDialogVisible="isDialogVisible" :dialog-title="`Tambah Data Capaian Pembelajaran`"
      :errors="errors" @confirm="saveData">
      <template #content>
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
                  item-value="rombongan_belajar_id" item-title="nama" :loading="loadingRombel" :disabled="loadingRombel"
                  :error-messages="errors.rombongan_belajar_id" />
              </VCol>
            </VRow>
          </VCol>
          <VCol cols="12">
            <VRow no-gutters>
              <VCol cols="12" md="3" class="d-flex align-items-center">
                <label class="v-label text-body-2 text-high-emphasis" for="mata_pelajaran_id">Mata Pelajaran</label>
              </VCol>
              <VCol cols="12" md="9">
                <AppSelect v-model="form.mata_pelajaran_id" placeholder="== Pilih Mata Pelajaran ==" :items="dataMapel"
                  clearable clear-icon="tabler-x" item-value="mata_pelajaran_id" item-title="nama_mata_pelajaran"
                  :loading="loadingMapel" :disabled="loadingMapel" :error-messages="errors.mata_pelajaran_id" />
              </VCol>
            </VRow>
          </VCol>
          <VCol cols="12">
            <VRow no-gutters>
              <VCol cols="12" md="3" class="d-flex align-items-center">
                <label class="v-label text-body-2 text-high-emphasis" for="elemen">Elemen</label>
              </VCol>
              <VCol cols="12" md="9">
                <AppTextField id="elemen" v-model="form.elemen" placeholder="Elemen" persistent-placeholder
                  :error-messages="errors.elemen" />
              </VCol>
            </VRow>
          </VCol>
          <VCol cols="12">
            <VRow no-gutters>
              <VCol cols="12" md="3" class="d-flex align-items-center">
                <label class="v-label text-body-2 text-high-emphasis" for="capaian_pembelajaran">Deskripsi Capaian
                  Pembelajaran</label>
              </VCol>
              <VCol cols="12" md="9">
                <AppTextarea v-model="form.capaian_pembelajaran" placeholder="Deskripsi Capaian Pembelajaran" auto-grow
                  :error-messages="errors.capaian_pembelajaran" />
              </VCol>
            </VRow>
          </VCol>
        </VRow>
      </template>
    </DefaultDialog>
    <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
      confirmation-question="Apakah Anda yakin?" :confirmation-text="confirmationText" :confirm-color="notif.color"
      :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmDelete" @close="confirmClose" />
  </div>
</template>
