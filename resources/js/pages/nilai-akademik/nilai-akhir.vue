<script setup>
definePage({
  meta: {
    action: "read",
    subject: "Guru",
    title: "Input Nilai Akhir",
  },
});
const form = ref({
  tingkat: null,
  rombongan_belajar_id: null,
  pembelajaran_id: null,
  bentuk_penilaian: null,
});
const nilai = ref({
  akhir: {},
  kompeten: {},
});
const errors = ref({
  tingkat: null,
  rombongan_belajar_id: null,
  pembelajaran_id: null,
  bentuk_penilaian: null,
});
const arrayData = ref({
  rombel: [],
  mapel: [],
  teknik: [],
  siswa: [],
  tp: [],
});
const loading = ref({
  rombel: false,
  mapel: false,
  bentuk_penilaian: false,
  body: false,
});
const showBtn = ref(false);
const confirmed = ref(false);
const isConfirmDialogVisible = ref(false);
const isNotifVisible = ref(false);
const confirmationText = ref("");
const notif = ref({
  color: "",
  title: "",
  text: "",
});
const onSubmit = async () => {
  confirmed.value = true;
  const mergedForm = { ...defaultForm, ...form.value, ...nilai.value };
  await $api("/penilaian/simpan", {
    method: "POST",
    body: mergedForm,
    onResponse({ response }) {
      let getData = response._data;
      confirmed.value = false;
      if (getData.errors) {
        errors.value = getData.errors;
      } else {
        isNotifVisible.value = true;
        notif.value = getData;
      }
    },
  });
};
const defaultForm = {
  opsi: "nilai-akhir",
  semester_id: $semester.semester_id,
  user_id: $user.user_id,
  guru_id: $user.guru_id,
  sekolah_id: $user.sekolah_id,
  merdeka: false,
};
const resetForm = () => {
  form.value = {
    tingkat: null,
    rombongan_belajar_id: null,
    pembelajaran_id: null,
    bentuk_penilaian: null,
  }
  nilai.value = {
    akhir: {},
    kompeten: {}
  }
  errors.value = {
    tingkat: null,
    rombongan_belajar_id: null,
    pembelajaran_id: null,
    bentuk_penilaian: null,
  }
  arrayData.value = {
    rombel: [],
    mapel: [],
    teknik: [],
    siswa: [],
    tp: [],
  }
  loading.value = {
    rombel: false,
    mapel: false,
    bentuk_penilaian: false,
    body: false,
  }
  showBtn.value = false;
  confirmed.value = false;
  isConfirmDialogVisible.value = false;
  isNotifVisible.value = false;
  confirmationText.value = ""
  notif.value = {
    color: "",
    title: "",
    text: "",
  }
  defaultForm.opsi = "nilai-akhir"
};
const getData = async (postData) => {
  const mergedForm = { ...postData, ...defaultForm };
  await $api("/referensi/get-data", {
    method: "POST",
    body: mergedForm,
    onResponse({ response }) {
      let getData = response._data;
      if (postData.data == "rombel") {
        arrayData.value.rombel = getData;
      }
      if (postData.data == "mapel") {
        arrayData.value.mapel = getData.mapel;
        defaultForm.merdeka = getData.merdeka
      }
      if (postData.data == "teknik") {
        arrayData.value.teknik = getData;
      }
    },
  });
};
const changeTingkat = async (val) => {
  form.value.rombongan_belajar_id = null;
  form.value.pembelajaran_id = null;
  if (val) {
    loading.value.rombel = true;
    await getData({
      data: "rombel",
      nilai: 1,
      tingkat: val,
    }).then(() => {
      loading.value.rombel = false;
    });
  }
};
const changeRombel = async (val) => {
  form.value.pembelajaran_id = null;
  if (val) {
    loading.value.mapel = true;
    await getData({
      data: "mapel",
      nilai: 1,
      rombongan_belajar_id: val,
    }).then(() => {
      loading.value.mapel = false;
    });
  }
};
const changeMapel = async (val) => {
  form.value.bentuk_penilaian = null;
  if (val) {
    loading.value.bentuk_penilaian = true;
    await getData({
      data: "teknik",
    }).then(() => {
      loading.value.bentuk_penilaian = false;
    });
  }
};
const isDisabled = ref(false)
const changeTeknik = async (val) => {
  arrayData.value.siswa = []
  arrayData.value.tp = []
  if (val) {
    const mergedForm = { ...form.value, ...defaultForm };
    loading.value.body = true;
    showBtn.value = false;
    await $api("/penilaian/get-nilai-akhir", {
      method: "POST",
      body: mergedForm,
      onResponse({ response }) {
        let getData = response._data;
        arrayData.value.siswa = getData.data_siswa;
        arrayData.value.tp = getData.data_tp;
        if (form.value.bentuk_penilaian == 'asesmen') {
          isDisabled.value = true
        } else {
          isDisabled.value = false
        }
        getData.data_siswa.forEach((siswa) => {
          if (form.value.bentuk_penilaian == 'asesmen') {
            nilai.value.akhir[siswa.anggota_rombel_id] = siswa.nilai_asesmen
          } else {
            nilai.value.akhir[siswa.anggota_rombel_id] = siswa.nilai_akhir
          }
          getData.data_tp.forEach((tp) => {
            const capaian = siswa.capaian_kompeten.find(item => item.tp_id === tp.tp_id)
            nilai.value.kompeten[siswa.anggota_rombel_id + '#' + tp.tp_id] = capaian?.kompeten
          })
        })
        showBtn.value = true;
        loading.value.body = false;
      },
    });
  }
};
const confirmClose = () => {
  resetForm();
}
const calculateAverage = (array) => {
  var total = 0;
  var count = 0;
  array.forEach(function (item, index) {
    total += item;
    count++;
  });
  var angka = total / count;
  return angka.toFixed(0);
}
const setRerata = (anggota_rombel_id, jenis) => {
  var getRerata = 0;
  var nilai_nontes = nilai.value.sumatif[`${anggota_rombel_id}#non-tes`]
  var nilai_tes = nilai.value.sumatif[`${anggota_rombel_id}#tes`]
  if (nilai_nontes && nilai_tes) {
    getRerata = calculateAverage([parseInt(nilai_nontes), parseInt(nilai_tes)])
  } else if (nilai_nontes && !nilai_tes) {
    getRerata = nilai_nontes
  } else if (!nilai_nontes && nilai_tes) {
    getRerata = nilai_tes
  }
  nilai.value.sumatif[`${anggota_rombel_id}#na`] = getRerata
};
</script>
<template>
  <VCard class="mb-6">
    <VCardItem class="pb-4">
      <VCardTitle>Input Nilai Akhir</VCardTitle>
    </VCardItem>
    <VDivider />
    <VForm @submit.prevent="onSubmit">
      <VCardText>
        <VRow>
          <DefaultForm v-model:form="form" v-model:errors="errors" v-model:arrayData="arrayData"
            v-model:loading="loading" @tingkat="changeTingkat" @rombongan_belajar_id="changeRombel"
            @pembelajaran_id="changeMapel"></DefaultForm>
          <VCol cols="12">
            <VRow no-gutters>
              <VCol cols="12" md="3" class="d-flex align-items-center">
                <label class="v-label text-body-2 text-high-emphasis" for="bentuk_penilaian">Bentuk Penilaian</label>
              </VCol>
              <VCol cols="12" md="9">
                <AppSelect v-model="form.bentuk_penilaian" placeholder="== Pilih Bentuk Penilaian =="
                  :items="bentukPenilaian" clearable clear-icon="tabler-x" @update:model-value="changeTeknik"
                  :error-messages="errors.bentuk_penilaian" :loading="loading.bentuk_penilaian"
                  :disabled="loading.bentuk_penilaian" />
              </VCol>
            </VRow>
          </VCol>
        </VRow>
        <div class="text-center" v-if="loading.body">
          <VProgressCircular :size="60" indeterminate color="error" class="my-10" />
        </div>
      </VCardText>
      <template v-if="arrayData.siswa.length">
        <VTable>
          <thead>
            <tr>
              <th class="text-center">No.</th>
              <th class="text-center">Nama Peserta Didik</th>
              <th class="text-center">Nilai Akhir</th>
              <th class="text-center">Ketercapaian Kompetensi</th>
              <th>Deskripsi Tujuan Pembelajaran</th>
            </tr>
          </thead>
          <tbody>
            <template v-for="(siswa, index) in arrayData.siswa">
              <tr>
                <td class="text-center pt-2" :rowspan="arrayData.tp.length + 1" style="vertical-align: top;">{{ index +
                  1 }}
                </td>
                <td :rowspan="arrayData.tp.length + 1" class="text-no-wrap pt-2" style="vertical-align: top;">
                  <ProfileSiswa :item="siswa" />
                </td>
                <td :rowspan="arrayData.tp.length + 1" style="vertical-align: top;" class="pt-2">
                  <div style="inline-size: 6rem;">
                    <AppTextField type="number" v-model="nilai.akhir[siswa.anggota_rombel_id]" :disabled="isDisabled" />
                  </div>
                </td>
              </tr>
              <template v-for="(tp, i) in arrayData.tp">
                <tr>
                  <td>
                    <AppSelect v-model="nilai.kompeten[siswa.anggota_rombel_id + '#' + tp.tp_id]"
                      placeholder="== Pilih Capaian ==" :items="dataCapaian" clearable clear-icon="tabler-x"
                      style="inline-size: 12rem;" />
                  </td>
                  <td>{{ tp.deskripsi }}</td>
                </tr>
              </template>
            </template>
          </tbody>
        </VTable>
      </template>
      <VDivider />
      <VCardText class="d-flex justify-end flex-wrap gap-3 pt-5 overflow-visible" v-if="showBtn">
        <VBtn variant="elevated" type="submit" :loading="confirmed" :disabled="confirmed">
          Simpan
        </VBtn>
      </VCardText>
    </VForm>
    <ConfirmDialog v-model:isDialogVisible="isConfirmDialogVisible" v-model:isNotifVisible="isNotifVisible"
      confirmation-question="Apakah Anda yakin?" :confirmation-text="confirmationText" :confirm-color="notif.color"
      :confirm-title="notif.title" :confirm-msg="notif.text" @close="confirmClose" />
  </VCard>
</template>
