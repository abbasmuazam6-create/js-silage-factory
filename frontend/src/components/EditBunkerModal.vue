<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-lg bg-white shadow-xl">
      <div class="flex items-center justify-between border-b border-slate-200 p-3">
        <h3 class="text-sm font-bold text-slate-700">Edit Bunker</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600">✕</button>
      </div>
      <div class="p-4">
        <form @submit.prevent="save">
          <div class="mb-3">
            <label class="block text-xs font-medium text-slate-700">Bunker Name</label>
            <input v-model="form.name" type="text" class="w-full rounded border p-1.5 text-sm" required />
          </div>
          <div class="mb-3">
            <label class="block text-xs font-medium text-slate-700">Location</label>
            <input v-model="form.location" type="text" class="w-full rounded border p-1.5 text-sm" required />
          </div>
          <div class="mb-3">
            <label class="block text-xs font-medium text-slate-700">Season</label>
            <select v-model="form.season_id" class="w-full rounded border p-1.5 text-sm">
              <option v-for="season in seasons" :key="season.id" :value="season.id">{{ season.name }}</option>
            </select>
          </div>
          <div class="flex justify-end gap-2">
            <button type="button" @click="$emit('close')" class="btn-secondary text-xs px-3 py-1">Cancel</button>
            <button type="submit" class="btn-primary text-xs px-3 py-1">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import { useBunkerStore } from '../stores/bunker'
import { useSeasonStore } from '../stores/season'

const props = defineProps({
  visible: Boolean,
  bunker: Object,
})

const emit = defineEmits(['close', 'saved'])

const store = useBunkerStore()
const seasonStore = useSeasonStore()
const seasons = ref([])

const form = ref({
  name: '',
  location: '',
  season_id: '',
})

watch(() => props.bunker, (val) => {
  if (val) {
    form.value.name = val.name || ''
    form.value.location = val.location || ''
    form.value.season_id = val.season_id || ''
  }
}, { immediate: true })

onMounted(async () => {
  await seasonStore.fetchSeasons()
  seasons.value = seasonStore.seasons
})

const save = async () => {
  try {
    await store.updateBunker(props.bunker.id, form.value)
    emit('saved')
    emit('close')
  } catch (err) {
    alert('Update failed: ' + err.message)
  }
}
</script>