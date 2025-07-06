<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Guru',
    title: 'Ref. Bobot Penilaian',
  },
})
onMounted(async () => {
  await fetchData();
});
const items = ref([])
const loadingBody = ref(false)
const fetchData = async () => {
  loadingBody.value = true;
  try {
    const response = await useApi(createUrl('/referensi/bobot-penilaian', {
      query: {
        user_id: $user.user_id,
        guru_id: $user.guru_id,
        sekolah_id: $user.sekolah_id,
        semester_id: $semester.semester_id,
        periode_aktif: $semester.nama,
      },
    }));
    let getData = response.data
    items.value = getData.value
  } catch (error) {
    console.error(error);
  } finally {
    loadingBody.value = false;
  }
}
const isConfirmDialogVisible = ref(false)
const isNotifVisible = ref(false)
const notif = ref({
  color: null,
  title: null,
  text: null,
})
const loadingBtn = ref()
const onSubmit = async () => {
  loadingBtn.value = true
  await $api('/referensi/bobot-penilaian', {
    method: 'POST',
    body: items.value,
    onResponse({ response }) {
      loadingBtn.value = false
      let getData = response._data
      isNotifVisible.value = true
      notif.value = getData
    }
  })
}
</script>
<template>
  <div>
    <VCard class="mb-6">
      <VCardItem class="pb-4">
        <VCardTitle>Referensi Bobot Penilaian</VCardTitle>
      </VCardItem>
      <VDivider />
      <template v-if="loadingBody">
        <VCardText class="text-center">
          <VProgressCircular :size="60" indeterminate color="error" class="my-10" />
        </VCardText>
      </template>
      <template v-else>
        <VTable class="permission-table">
          <thead>
            <tr>
              <th class="text-center">No</th>
              <th class="text-center">mata pelajaran</th>
              <th class="text-center">Rombel</th>
              <th class="text-center">bobot sumatif materi</th>
              <th class="text-center">bobot sumatif akhir</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="items.length">
              <tr v-for="(item, index) in items">
                <td class="text-center">{{ index + 1 }}</td>
                <td>{{ item.nama_mata_pelajaran }}</td>
                <td class="text-center">{{ item.rombongan_belajar.nama }}</td>
                <td>
                  <AppTextField v-model="item.bobot_sumatif_materi" />
                </td>
                <td>
                  <AppTextField v-model="item.bobot_sumatif_akhir" />
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td class="text-center" colspan="5">Tidak ada data untuk ditampilkan</td>
              </tr>
            </template>
          </tbody>
        </VTable>
        <VCardText class="d-flex justify-end gap-3 flex-wrap">
          <VBtn @click="onSubmit" :loading="loadingBtn" :disabled="loadingBtn">Simpan</VBtn>
        </VCardText>
      </template>
    </VCard>
    <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
      confirmation-question="Apakah Anda yakin?" confirmation-text="confirmationText" :confirm-color="notif.color"
      :confirm-title="notif.title" :confirm-msg="notif.text" />
  </div>
</template>
<style lang="scss">
.permission-table {
  td {
    border-block-end: 1px solid rgba(var(--v-border-color), var(--v-border-opacity));
    padding-block: 0.5rem;

    &:not(:first-child) {
      padding-inline: 0.5rem;
    }

    .v-label {
      white-space: nowrap;
    }
  }
}
</style>
