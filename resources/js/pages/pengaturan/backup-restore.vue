<script setup>
definePage({
  meta: {
    action: 'read',
    subject: 'Administrator',
    title: 'Backup Restore Database',
  },
})
onMounted(async () => {
  await fetchData();
});
const loadingBody = ref(true)
const btnLoading = ref(false)
const listBackup = ref([])
const basePath = ref()
const folder = ref()
const db = ref({})
const data = ref()
const fetchData = async () => {
  try {
    const response = await useApi(createUrl('/setting', {
      query: {
        data: 'backup',
      },
    }))
    let getData = response.data.value
    listBackup.value = getData.files
    basePath.value = getData.path
    folder.value = getData.folder
    db.value = getData.db
  } catch (error) {
    console.error(error);
  } finally {
    loadingBody.value = false;
  }
}
const prosesBackup = async () => {
  console.log('prosesBackup');
  btnLoading.value = true
  await $api('/setting/proses-backup', {
    method: 'POST',
    onResponse({ response }) {
      let getData = response._data
      data.value = getData
      btnLoading.value = false
      fetchData()
    }
  })
}
const form = ref({
  zip_file: null,
})
const errors = ref({
  zip_file: null,
})
const uploading = ref(false);
const progress = ref(0);
const uploadFile = async () => {
  uploading.value = true;
  const formData = new FormData();
  formData.append("zip_file", form.value.zip_file);
  try {
    await $api('/setting/upload-restore', {
      method: 'POST',
      body: formData,
      onUploadProgress: (progressEvent) => {
        progress.value = Math.round(
          (progressEvent.loaded * 100) / progressEvent.total
        );
      },
    })
  } catch (error) {
    console.error(error);
  } finally {
    form.value.zip_file = null
    uploading.value = false
    await fetchData();
  }
}
const isConfirmDialogVisible = ref(false)
const isNotifVisible = ref(false)
const notif = ref({
  color: '',
  title: '',
  text: '',
})
const loadings = ref([])
const fileToDelete = ref()
const hapusFile = async (file) => {
  fileToDelete.value = file
  isConfirmDialogVisible.value = true
}
const confirmDialog = async () => {
  loadings.value[fileToDelete.value] = true
  await $api('/setting/hapus-file', {
    method: 'POST',
    body: {
      zip_file: fileToDelete.value,
    },
    onResponse({ request, response, options }) {
      let getData = response._data
      loadings.value[fileToDelete.value] = false
      notif.value = getData
      isNotifVisible.value = true
    }
  })
}
const confirmClose = async () => {
  isNotifVisible.value = false
  setTimeout(() => {
    notif.value = {
      color: '',
      title: '',
      text: '',
    }
  }, 300)
  await fetchData();
}
</script>
<template>
  <section>
    <VCard class="mb-6">
      <VCardItem class="pb-6">
        <VCardTitle>Backup Database</VCardTitle>
        <template #append>
          <VBtn prepend-icon="tabler-database" @click="prosesBackup('unduh')" :loading="btnLoading"
            :disabled="btnLoading">
            Proses Backup
          </VBtn>
        </template>
      </VCardItem>
      <template v-if="loadingBody">
        <VDivider />
        <VCardText class="text-center">
          <VProgressCircular :size="60" indeterminate color="error" />
        </VCardText>
      </template>
      <template v-else>
        <template v-if="data">
          <VCardText>
            <VAlert :color="data.exitCode ? 'error' : 'success'" variant="tonal">
              <h2 class="mt-4 mb-4">{{ data.message }}</h2>
              <ul>
                <li v-for="output in data.output">
                  {{ output }}
                </li>
              </ul>
            </VAlert>
          </VCardText>
        </template>
        <VTable class="text-no-wrap">
          <thead>
            <tr>
              <th>Waktu Backup</th>
              <th>Ukuran Berkas</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="listBackup.length">
              <tr v-for="item in listBackup">
                <td>{{ item.fileDate }}</td>
                <td>{{ item.fileSize }} MB</td>
                <td class="text-center">
                  <VBtn size="small" color="success" :href="`/downloads/backup/${item.fileName}`" class="me-2">
                    <VIcon start icon="tabler-download" />
                    Unduh
                  </VBtn>
                  <VBtn size="small" color="error" @click="hapusFile(item.fileName)" :loading="loadings[item.fileName]"
                    :disabled="loadings[item.fileName]">
                    <VIcon start icon="tabler-trash" />
                    Hapus
                  </VBtn>
                </td>
              </tr>
            </template>
            <template v-else>
              <tr>
                <td class="text-center" colspan="3">Tidak ada data untuk ditampilkan</td>
              </tr>
            </template>
          </tbody>
        </VTable>
      </template>
      <template v-if="listBackup.length">
        <VCardText>
          <p>Proses restore database</p>
          <ol style="margin: 0 20px 0px;">
            <li>Buka cmd/ssh</li>
            <li>Jalankan:
              <ul style="margin: 0 15px 0px;">
                <li><code>cd {{ basePath }}</code>[Enter]</li>
                <li><code>php artisan app:restore</code>[Enter]</li>
                <li>Jika ada notifikasi
                  <code>Proceed to restore "{{ folder }}/nama-file-database.zip" using the "{{ db.driver }}" database
                  connection. (Database: nama-database, Host: {{ db.host }}, username:
                  {{ db.username }}): (yes/no) [<span class="text-warning">yes</span>]</code>, tekan
                  tombol Enter
                </li>
                <li>Tunggu sampai proses restore database selesai.</li>
              </ul>
            </li>
          </ol>
        </VCardText>
      </template>
    </VCard>
    <VCard class="mb-6">
      <VCardItem class="pb-6">
        <VCardTitle>Restore Database</VCardTitle>
      </VCardItem>
      <VDivider />
      <template v-if="loadingBody">
        <VDivider />
        <VCardText class="text-center">
          <VProgressCircular :size="60" indeterminate color="error" />
        </VCardText>
      </template>
      <VCardText>
        <VFileInput v-model="form.zip_file" :error-messages="errors.zip_file" accept=".zip"
          label="Unggah Berkas Database (.zip)" @update:model-value="uploadFile" />
        <v-progress-linear v-if="uploading" :value="progress" color="primary" height="25" class="mt-4">
          {{ progress }}%
        </v-progress-linear>
      </VCardText>
    </VCard>
    <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
      confirmation-question="Apakah Anda yakin?" confirmation-text="Tindakan ini tidak dapat dikembalikan!"
      :confirm-color="notif.color" :confirm-title="notif.title" :confirm-msg="notif.text" @confirm="confirmDialog"
      @close="confirmClose" />
  </section>
</template>
