<template>
  <div class="p-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-semibold text-slate-700">Manage Seasons</h3>
      <button @click="openAddModal" class="btn-primary px-3 py-1.5 text-sm">+ Add Season</button>
    </div>

    <div v-if="loading" class="text-sm text-slate-500">Loading...</div>
    <div v-else-if="seasons.length === 0" class="text-sm text-slate-500">No seasons added yet.</div>
    <div v-else class="space-y-2">
      <div
        v-for="season in seasons"
        :key="season.id"
        class="flex items-center justify-between border-b border-slate-100 py-2"
      >
        <div class="flex items-center gap-3">
          <span
            class="inline-block w-3 h-3 rounded-full"
            :style="{ backgroundColor: season.color || '#3B82F6' }"
          ></span>
          <span class="text-sm font-medium">{{ season.name }}</span>
          <span
            v-if="season.is_current"
            class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded"
          >
            Current
          </span>
          <span class="text-xs text-slate-400">
            {{ getMonthName(season.start_month) }} {{ season.start_day }} – {{ getMonthName(season.end_month) }} {{ season.end_day }}
          </span>
        </div>
        <div class="flex gap-2">
          <button @click="setCurrentSeason(season)" class="text-green-600 hover:text-green-800 text-sm" title="Set as current">
            ⭐
          </button>
          <button @click="editSeason(season)" class="text-blue-600 hover:text-blue-800 text-sm">✏️</button>
          <button @click="deleteSeason(season)" class="text-red-600 hover:text-red-800 text-sm">🗑</button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="modalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-base font-bold text-slate-700 mb-4">
          {{ editingId ? 'Edit Season' : 'Add Season' }}
        </h3>
        <form @submit.prevent="saveSeason">
          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700">Season Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
              placeholder="e.g., Kharif"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-medium text-slate-700">Start Month *</label>
              <select
                v-model.number="form.start_month"
                required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
              >
                <option value="">Select</option>
                <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">Start Day *</label>
              <select
                v-model.number="form.start_day"
                required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
              >
                <option value="">Select</option>
                <option v-for="d in 31" :key="d" :value="d">{{ d }}</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 mt-3">
            <div>
              <label class="block text-sm font-medium text-slate-700">End Month *</label>
              <select
                v-model.number="form.end_month"
                required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
              >
                <option value="">Select</option>
                <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700">End Day *</label>
              <select
                v-model.number="form.end_day"
                required
                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
              >
                <option value="">Select</option>
                <option v-for="d in 31" :key="d" :value="d">{{ d }}</option>
              </select>
            </div>
          </div>

          <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700">Color</label>
            <input
              v-model="form.color"
              type="color"
              class="w-full h-10 rounded-lg border border-slate-300 p-1"
            />
          </div>

          <div class="mt-3 flex items-center gap-2">
            <input
              v-model="form.is_current"
              type="checkbox"
              id="is_current"
              class="h-4 w-4 rounded border-slate-300 text-primary-600"
            />
            <label for="is_current" class="text-sm text-slate-700">Set as Current Season</label>
          </div>

          <div v-if="modalError" class="text-sm text-red-600 mt-2">{{ modalError }}</div>

          <div class="flex justify-end gap-3 mt-4 pt-3 border-t border-slate-200">
            <button type="button" @click="closeModal" class="btn-outline px-4 py-2 text-sm">Cancel</button>
            <button type="submit" class="btn-primary px-4 py-2 text-sm" :disabled="saving">
              {{ saving ? 'Saving...' : 'Save' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const seasons = ref([])
const loading = ref(true)

const modalVisible = ref(false)
const editingId = ref(null)
const form = ref({
  name: '',
  start_month: '',
  start_day: '',
  end_month: '',
  end_day: '',
  color: '#3B82F6',
  is_current: false,
})
const saving = ref(false)
const modalError = ref('')

const months = [
  { value: 1, label: 'January' },
  { value: 2, label: 'February' },
  { value: 3, label: 'March' },
  { value: 4, label: 'April' },
  { value: 5, label: 'May' },
  { value: 6, label: 'June' },
  { value: 7, label: 'July' },
  { value: 8, label: 'August' },
  { value: 9, label: 'September' },
  { value: 10, label: 'October' },
  { value: 11, label: 'November' },
  { value: 12, label: 'December' },
]

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const getMonthName = (month) => {
  const m = months.find(m => m.value === month)
  return m ? m.label : ''
}

const fetchSeasons = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/seasons', {
      headers: getAuthHeader()
    })
    seasons.value = data
  } catch (err) {
    console.error('Failed to fetch seasons:', err)
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  editingId.value = null
  form.value = {
    name: '',
    start_month: '',
    start_day: '',
    end_month: '',
    end_day: '',
    color: '#3B82F6',
    is_current: false,
  }
  modalError.value = ''
  modalVisible.value = true
}

const editSeason = (season) => {
  editingId.value = season.id
  form.value = {
    name: season.name,
    start_month: season.start_month,
    start_day: season.start_day,
    end_month: season.end_month,
    end_day: season.end_day,
    color: season.color || '#3B82F6',
    is_current: season.is_current || false,
  }
  modalError.value = ''
  modalVisible.value = true
}

const closeModal = () => {
  modalVisible.value = false
  form.value = {
    name: '',
    start_month: '',
    start_day: '',
    end_month: '',
    end_day: '',
    color: '#3B82F6',
    is_current: false,
  }
  editingId.value = null
  modalError.value = ''
}

const saveSeason = async () => {
  modalError.value = ''
  if (!form.value.name.trim()) {
    modalError.value = 'Season name is required'
    return
  }
  if (!form.value.start_month || !form.value.start_day || !form.value.end_month || !form.value.end_day) {
    modalError.value = 'Please select start and end dates'
    return
  }

  saving.value = true
  try {
    if (editingId.value) {
      await axios.put(`/seasons/${editingId.value}`, form.value, {
        headers: getAuthHeader()
      })
    } else {
      await axios.post('/seasons', form.value, {
        headers: getAuthHeader()
      })
    }
    await fetchSeasons()
    closeModal()
  } catch (err) {
    modalError.value = err.response?.data?.message || 'Failed to save season'
  } finally {
    saving.value = false
  }
}

const deleteSeason = async (season) => {
  if (!confirm(`Delete season "${season.name}"? This cannot be undone.`)) return
  try {
    await axios.delete(`/seasons/${season.id}`, {
      headers: getAuthHeader()
    })
    await fetchSeasons()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete season')
  }
}

const setCurrentSeason = async (season) => {
  try {
    await axios.post(`/seasons/${season.id}/set-current`, {}, {
      headers: getAuthHeader()
    })
    await fetchSeasons()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to set current season')
  }
}

onMounted(fetchSeasons)
</script>