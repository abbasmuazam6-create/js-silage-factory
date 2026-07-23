<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
      <div class="flex items-center justify-between border-b border-slate-200 pb-3">
        <h3 class="text-base font-bold text-slate-700">Select Bunker</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600">✕</button>
      </div>

      <div class="mt-4">
        <p class="text-sm text-slate-500 mb-3">Choose a bunker to add a purchase to:</p>

        <div v-if="loading" class="text-center py-4 text-sm text-slate-500">Loading bunkers...</div>

        <div v-else-if="bunkers.length === 0" class="text-center py-4 text-sm text-slate-500">
          No bunkers available. Please create a bunker first.
        </div>

        <div v-else class="space-y-2 max-h-80 overflow-y-auto">
          <button
            v-for="bunker in filteredBunkers"
            :key="bunker.id"
            @click="$emit('select', bunker)"
            class="w-full text-left px-4 py-3 rounded-lg border border-slate-200 hover:border-primary-400 hover:bg-primary-50 transition flex items-center justify-between"
          >
            <div>
              <p class="text-sm font-medium text-slate-700">{{ bunker.name }}</p>
              <p class="text-xs text-slate-500">{{ bunker.location || 'No location' }}</p>
            </div>
            <span class="text-xs text-slate-400">{{ numberFormat(bunker.available_weight) }} kg available</span>
          </button>
        </div>
      </div>

      <div class="mt-4 border-t border-slate-200 pt-3 flex justify-end">
        <button @click="$emit('close')" class="btn-outline px-4 py-2 text-sm">Cancel</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios' // ✅ Fixed: was 'vue', now 'axios'

const props = defineProps({
  visible: Boolean,
})

const emit = defineEmits(['close', 'select'])

const bunkers = ref([])
const loading = ref(true)

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const numberFormat = (val) => {
  if (val === null || val === undefined) return '0'
  const num = Number(val)
  if (isNaN(num)) return '0'
  return num.toLocaleString()
}

const filteredBunkers = computed(() => {
  return bunkers.value.filter(b => b.status !== 'blocked')
})

const fetchBunkers = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/bunkers', {
      headers: getAuthHeader()
    })
    bunkers.value = data
  } catch (err) {
    console.error('Failed to fetch bunkers:', err)
  } finally {
    loading.value = false
  }
}

watch(() => props.visible, (val) => {
  if (val) {
    fetchBunkers()
  }
}, { immediate: true })
</script>