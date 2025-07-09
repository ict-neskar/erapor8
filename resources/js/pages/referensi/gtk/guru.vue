<script setup>
import Ptk from '@/views/referensi/Ptk.vue';
definePage({
  meta: {
    action: 'read',
    subject: 'Ref_Guru',
    title: 'Data Guru',
  },
})
</script>
<template>
  <Ptk :data="'guru'" :titleCard="'Data Guru'"></Ptk>
</template>
