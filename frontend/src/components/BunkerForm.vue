<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-3xl max-h-[90vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
      <h3 class="mb-4 text-xl font-bold text-slate-700">
        {{ bunker ? 'Edit Bunker' : 'Add New Bunker' }}
      </h3>

      <form @submit.prevent="saveBunker" class="space-y-4">
        <!-- Basic Info -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-slate-700">Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Location</label>
            <select
              v-model="form.location"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            >
              <option value="">Select Location</option>
              <option v-for="loc in existingLocations" :key="loc" :value="loc">
                {{ loc }}
              </option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-slate-700">Season *</label>
            <select
              v-model="form.season_id"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            >
              <option value="">Select Season</option>
              <option v-for="season in seasons" :key="season.id" :value="season.id">
                {{ season.name }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Threshold (%)</label>
            <input
              v-model.number="form.threshold_percentage"
              type="number"
              min="0"
              max="100"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Notes</label>
          <textarea
            v-model="form.notes"
            rows="2"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          ></textarea>
        </div>

        <!-- Errors -->
        <div v-if="error" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
          {{ error }}
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
          <button type="button" @click="closeModal" class="btn-outline px-4 py-2 text-sm">
            Cancel
          </button>
          <button
            type="submit"
            class="btn-primary px-4 py-2 text-sm"
            :disabled="loading"
          >
            {{ loading ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { useBunkerStore } from '../stores/bunker'
import { fetchSeasons, getCurrentSeason } from '../utils/seasons'
import axios from 'axios'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  bunker: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'saved'])

const bunkerStore = useBunkerStore()

const loading = ref(false)
const error = ref('')
const seasons = ref([])
const existingLocations = ref([])

// Form data
const form = reactive({
  name: '',
  location: '',
  season_id: '',
  threshold_percentage: 10,
  notes: '',
})

// Reset form
const resetForm = () => {
  form.name = ''
  form.location = ''
  form.season_id = ''
  form.threshold_percentage = 10
  form.notes = ''
  error.value = ''
}

// Populate form for editing
const populateForm = (bunker) => {
  if (bunker) {
    form.name = bunker.name || ''
    form.location = bunker.location || ''
    form.season_id = bunker.season_id || ''
    form.threshold_percentage = bunker.threshold_percentage || 10
    form.notes = bunker.notes || ''
  } else {
    resetForm()
  }
}

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

// Load seasons
const loadSeasons = async () => {
  if (seasons.value.length === 0) {
    const data = await fetchSeasons()
    seasons.value = data
  }
  return seasons.value
}

// ✅ Set default season (current season)
const setDefaultSeason = () => {
  const current = getCurrentSeason(seasons.value)
  if (current) {
    form.season_id = current.id
  } else if (seasons.value.length > 0) {
    form.season_id = seasons.value[0].id
  }
}

// Watch for visibility changes
watch(() => props.visible, async (newVal) => {
  if (newVal) {
    await loadSeasons() // ✅ Load seasons first
    fetchExistingLocations()
    if (props.bunker) {
      populateForm(props.bunker)
    } else {
      resetForm()
      setDefaultSeason() // ✅ Auto-select current season
    }
  }
})

// Fetch existing locations
const fetchExistingLocations = async () => {
  try {
    const { data } = await axios.get('/locations', {
      headers: getAuthHeader()
    })
    existingLocations.value = data.map(loc => loc.name)
  } catch (err) {
    console.error('Failed to fetch locations:', err)
  }
}

// Save bunker
const saveBunker = async () => {
  error.value = ''

  if (!form.name.trim()) {
    error.value = 'Bunker name is required.'
    return
  }
  if (!form.season_id) {
    error.value = 'Please select a season.'
    return
  }

  loading.value = true

  try {
    const payload = {
      name: form.name.trim(),
      location: form.location?.trim() || null,
      season_id: form.season_id,
      threshold_percentage: form.threshold_percentage || 10,
      notes: form.notes?.trim() || null,
    }

    let response
    if (props.bunker) {
      response = await bunkerStore.updateBunker(props.bunker.id, payload)
    } else {
      response = await bunkerStore.createBunker(payload)
    }

    resetForm()
    emit('saved', response)
    emit('close')
  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = err.message || 'Failed to save bunker'
    }
  } finally {
    loading.value = false
  }
}

// Close modal
const closeModal = () => {
  resetForm()
  emit('close')
}

onMounted(async () => {
  if (props.visible) {
    await loadSeasons()
    fetchExistingLocations()
    if (props.bunker) populateForm(props.bunker)
  }
})
</script>