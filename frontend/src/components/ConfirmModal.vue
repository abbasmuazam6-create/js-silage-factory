<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl">
      <h3 class="mb-2 text-xl font-bold text-red-600">{{ title }}</h3>
      <p class="mb-4 text-sm text-slate-600">{{ message }}</p>
      <div class="flex justify-end gap-3">
        <button @click="$emit('cancel')" class="btn-outline">Cancel</button>
        <button @click="$emit('confirm')" class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-4 rounded-md">
          {{ confirmText }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  visible: Boolean,
  title: { type: String, default: 'Confirm' },
  message: { type: String, default: 'Are you sure?' },
  confirmText: { type: String, default: 'Confirm' },
})
defineEmits(['confirm', 'cancel'])
</script>