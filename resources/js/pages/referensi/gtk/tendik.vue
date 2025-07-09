<script setup>
import Ptk from '@/views/referensi/Ptk.vue';
definePage({
  meta: {
    action: 'read',
    subject: 'Ref_Guru',
    title: 'Data Tenaga Kependidikan',
  },
})
</script>
<template>
  <Ptk :data="'tendik'" :titleCard="'Data Tenaga Kependidikan'"></Ptk>
</template>
