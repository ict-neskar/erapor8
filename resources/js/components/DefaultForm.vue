<script setup>
const props = defineProps({
    arrayData: {
        type: Object,
        required: true,
    },
    loading: {
        type: Object,
        required: true,
    },
    form: {
        type: Object,
        required: true,
    },
    errors: {
        type: Object,
        required: true,
    }
})
const emit = defineEmits([
    'update:form',
    'tingkat',
    'rombongan_belajar_id',
    'pembelajaran_id'
])
const changeFormTingkat = val => {
    emit('update:form', {
        tingkat: val,
        rombongan_belajar_id: props.form.rombongan_belajar_id,
        pembelajaran_id: props.form.pembelajaran_id,
    })
    emit('tingkat', val)
}
const changeFormRombel = val => {
    emit('update:form', {
        tingkat: props.form.tingkat,
        rombongan_belajar_id: val,
        pembelajaran_id: props.form.pembelajaran_id,
    })
    emit('rombongan_belajar_id', val)
}
const changeMapel = val => {
    emit('update:form', {
        tingkat: props.form.tingkat,
        rombongan_belajar_id: props.form.rombongan_belajar_id,
        pembelajaran_id: val,
    })
    emit('pembelajaran_id', val)
}
</script>
<template>
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
                <AppSelect :model-value="props.form.tingkat" placeholder="== Pilih Tingkat kelas =="
                    :items="tingkatKelas" clearable clear-icon="tabler-x" @update:model-value="changeFormTingkat"
                    :error-messages="props.errors.tingkat" />
            </VCol>
        </VRow>
    </VCol>
    <VCol cols="12">
        <VRow no-gutters>
            <VCol cols="12" md="3" class="d-flex align-items-center">
                <label class="v-label text-body-2 text-high-emphasis" for="rombonganBelajarId">Rombongan
                    Belajar</label>
            </VCol>
            <VCol cols="12" md="9">
                <AppSelect :model-value="props.form.rombongan_belajar_id" placeholder="== Pilih Rombongan Belajar == "
                    :items="props.arrayData.rombel" clearable clear-icon="tabler-x"
                    @update:model-value="changeFormRombel" item-value="rombongan_belajar_id" item-title="nama"
                    :loading="props.loading.rombel" :disabled="props.loading.rombel"
                    :error-messages="props.errors.rombongan_belajar_id" />
            </VCol>
        </VRow>
    </VCol>
    <VCol cols="12">
        <VRow no-gutters>
            <VCol cols="12" md="3" class="d-flex align-items-center">
                <label class="v-label text-body-2 text-high-emphasis" for="mata_pelajaran_id">Mata Pelajaran</label>
            </VCol>
            <VCol cols="12" md="9">
                <AppSelect :model-value="props.form.pembelajaran_id" placeholder="== Pilih Mata Pelajaran =="
                    :items="props.arrayData.mapel" clearable clear-icon="tabler-x" @update:model-value="changeMapel"
                    item-value="pembelajaran_id" item-title="nama_mata_pelajaran" :loading="props.loading.mapel"
                    :disabled="props.loading.mapel" :error-messages="props.errors.mata_pelajaran_id" />
            </VCol>
        </VRow>
    </VCol>
</template>
