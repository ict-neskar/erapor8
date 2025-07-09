<script setup>
const props = defineProps({
    item: {
        type: Object,
        required: true,
    }
})
</script>
<template>
    <div class="d-flex align-center gap-x-4">
        <VAvatar size="34" :variant="!item.photo ? 'tonal' : undefined" :color="!item.photo ? 'success' : undefined">
            <VImg :src="item.photo" />
        </VAvatar>
        <div class="d-flex flex-column">
            <h6 class="text-base">{{ item.nama_lengkap }}</h6>
            <div class="text-sm">
                {{ item.email }}
            </div>
        </div>
    </div>
</template>
