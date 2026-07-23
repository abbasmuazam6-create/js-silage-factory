<template>
  <div class="p-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-semibold text-slate-700">Manage Locations</h3>
      <button @click="openAddModal" class="btn-primary px-3 py-1.5 text-sm">+ Add Location</button>
    </div>

    <div v-if="loading" class="text-sm text-slate-500">Loading...</div>
    <div v-else-if="locations.length === 0" class="text-sm text-slate-500">No locations added yet.</div>
    <div v-else class="space-y-2">
      <div
        v-for="loc in locations"
        :key="loc.id"
        class="flex items-center justify-between border-b border-slate-100 py-2"
      >
        <span class="text-sm">{{ loc.name }}</span>
        <div class="flex gap-2">
          <button @click="editLocation(loc)" class="text-blue-600 hover:text-blue-800 text-sm">✏️</button>
          <button @click="deleteLocation(loc)" class="text-red-600 hover:text-red-800 text-sm">🗑</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-base font-bold text-slate-700 mb-4">
          {{ editingId ? 'Edit Location' : 'Add Location' }}
        </h3>
        <form @submit.prevent="saveLocation">
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">Location Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
              placeholder="Enter location name"
            />
          </div>
          <div v-if="modalError" class="text-sm text-red-600 mb-2">{{ modalError }}</div>
          <div class="flex justify-end gap-3">
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

const locations = ref([])
const loading = ref(true)

const modalVisible = ref(false)
const editingId = ref(null)
const form = ref({ name: '' })
const saving = ref(false)
const modalError = ref('')

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const fetchLocations = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/locations', {
      headers: getAuthHeader()
    })
    locations.value = data
  } catch (err) {
    console.error('Failed to fetch locations:', err)
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  editingId.value = null
  form.value = { name: '' }
  modalError.value = ''
  modalVisible.value = true
}

const editLocation = (loc) => {
  editingId.value = loc.id
  form.value = { name: loc.name }
  modalError.value = ''
  modalVisible.value = true
}

const closeModal = () => {
  modalVisible.value = false
  form.value = { name: '' }
  editingId.value = null
  modalError.value = ''
}

const saveLocation = async () => {
  modalError.value = ''
  if (!form.value.name.trim()) {
    modalError.value = 'Location name is required'
    return
  }

  saving.value = true
  try {
    if (editingId.value) {
      await axios.put(`/locations/${editingId.value}`, form.value, {
        headers: getAuthHeader()
      })
    } else {
      await axios.post('/locations', form.value, {
        headers: getAuthHeader()
      })
    }
    await fetchLocations()
    closeModal()
  } catch (err) {
    modalError.value = err.response?.data?.message || 'Failed to save location'
  } finally {
    saving.value = false
  }
}

const deleteLocation = async (loc) => {
  if (!confirm(`Delete location "${loc.name}"?`)) return
  try {
    await axios.delete(`/locations/${loc.id}`, {
      headers: getAuthHeader()
    })
    await fetchLocations()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete location')
  }
}

onMounted(fetchLocations)
</script>