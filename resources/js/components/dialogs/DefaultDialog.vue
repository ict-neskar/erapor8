<script setup>
import { watch } from 'vue'

const props = defineProps({
  isDialogVisible: {
    type: Boolean,
    required: true,
  },
  errors: {
    type: Object,
    required: false,
    default: {},
  },
  dialogTitle: {
    type: String,
    required: true,
  },
  dialogWith: {
    type: String,
    default: '90%',
  },
  isSubmitBtn: {
    type: Boolean,
    required: false,
    default: true,
  },
})
const confirmed = ref(false)
const emit = defineEmits([
  'update:isDialogVisible',
  'confirm',
])
const onConfirmation = () => {
  emit('confirm', true)
  confirmed.value = true
}

const onCancel = () => {
  emit('confirm', false)
  emit('update:isDialogVisible', false)
}
watch(props, () => {
  console.log(props.isDialogVisible);

  if (!props.isDialogVisible) {
    confirmed.value = false
  }
  if (Object.keys(props.errors).length) {
    confirmed.value = false
  }
})
</script>

<template>
  <VDialog scrollable content-class="scrollable-dialog" :max-width="props.dialogWith"
    :model-value="props.isDialogVisible" @update:model-value="onCancel">
    <DialogCloseBtn @click="onCancel" />
    <VCard style="position: relative;">
      <VCardItem class="pb-5">
        <VCardTitle>{{ props.dialogTitle }}</VCardTitle>
      </VCardItem>
      <VDivider />
      <VCardText style="overflow-y: visible;">
        <slot name="content" />
      </VCardText>
      <slot name="table" />
      <VDivider />
      <VCardText class="d-flex justify-end flex-wrap gap-3 pt-5 overflow-visible">
        <VBtn color="secondary" variant="tonal" @click="onCancel" :loading="confirmed" :disabled="confirmed">
          Tutup
        </VBtn>
        <VBtn variant="elevated" @click="onConfirmation" :loading="confirmed" :disabled="confirmed"
          v-if="props.isSubmitBtn">
          Simpan
        </VBtn>
      </VCardText>
      <v-overlay v-model="confirmed" contained persistent scroll-strategy="none" class="align-center justify-center">
        <v-progress-circular color="primary" size="64" indeterminate></v-progress-circular>
      </v-overlay>
    </VCard>
  </VDialog>
</template>
