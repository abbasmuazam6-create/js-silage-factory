<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
      <h3 class="mb-4 text-xl font-bold text-yellow-700">Mark Bunker as Empty</h3>
      <p class="mb-2 text-sm text-slate-600">
        Bunker: <strong>{{ bunker?.name }}</strong>
      </p>
      <p class="mb-4 text-sm text-slate-600">
        Recorded Remaining: <strong>{{ numberFormat(bunker?.remaining_weight_kg) }} kg</strong>
      </p>
      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-700">Actual Remaining (kg) *</label>
        <input
          v-model.number="actualRemaining"
          type="number"
          step="0.01"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
          required
        />
      </div>
      <div v-if="bunker && actualRemaining >= 0" class="mb-4 text-sm">
        <p>Shrinkage: <strong>{{ numberFormat(shrinkage) }} kg</strong></p>
        <p class="text-xs text-slate-500">(Recorded - Actual)</p>
      </div>
      <div>
        <label class="block text-sm font-medium text-slate-700">Notes</label>
        <textarea v-model="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
      </div>
      <div class="mt-6 flex justify-end gap-3">
        <button type="button" @click="$emit('close')" class="btn-outline">Cancel</button>
        <button
          type="button"
          @click="confirmMarkEmpty"
          class="btn-primary"
          :disabled="loading || !canSubmit"
        >
          {{ loading ? 'Processing...' : 'Confirm Empty' }}
        </button>
      </div>
      <div v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, defineProps, defineEmits } from 'vue'
import { useBunkerStore } from '../stores/bunker'

const store = useBunkerStore()
const props = defineProps({
  visible: Boolean,
  bunker: Object,
})
const emit = defineEmits(['close', 'saved'])

const actualRemaining = ref(0)
const notes = ref('')
const loading = ref(false)
const error = ref('')

const shrinkage = computed(() => {
  if (!props.bunker) return 0
  const recorded = props.bunker.remaining_weight_kg || 0
  const actual = actualRemaining.value || 0
  return Math.max(0, recorded - actual)
})

const canSubmit = computed(() => {
  return actualRemaining.value >= 0 && actualRemaining.value <= (props.bunker?.remaining_weight_kg || 0)
})

const numberFormat = (val) => {
  if (val === null || val === undefined) return '0'
  return Number(val).toLocaleString()
}

const confirmMarkEmpty = async () => {
  if (!canSubmit.value) return
  loading.value = true
  error.value = ''
  try {
    await store.markEmpty(props.bunker.id, {
      actual_remaining_kg: actualRemaining.value,
      notes: notes.value,
    })
    emit('saved')
    emit('close')
  } catch (err) {
    error.value = err?.response?.data?.message || err.message || 'Failed to mark empty'
  } finally {
    loading.value = false
  }
}
</script>