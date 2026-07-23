<template>
  <div class="p-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-semibold text-slate-700">Manage Payment Types</h3>
      <button @click="openAddModal" class="btn-primary px-3 py-1.5 text-sm">+ Add Payment Type</button>
    </div>

    <div v-if="loading" class="text-sm text-slate-500">Loading...</div>
    <div v-else-if="paymentTypes.length === 0" class="text-sm text-slate-500">No payment types added yet.</div>
    <div v-else class="space-y-2">
      <div
        v-for="type in paymentTypes"
        :key="type.id"
        class="flex items-center justify-between border-b border-slate-100 py-2"
      >
        <div class="flex items-center gap-3">
          <span
            class="inline-block w-3 h-3 rounded-full"
            :style="{ backgroundColor: type.color || '#6B7280' }"
          ></span>
          <span class="text-sm font-medium">{{ type.name }}</span>
          <span v-if="type.code" class="text-xs text-slate-400">({{ type.code }})</span>
          <span
            v-if="type.is_active"
            class="text-[10px] bg-green-100 text-green-700 px-1.5 py-0.5 rounded"
          >
            Active
          </span>
          <span
            v-else
            class="text-[10px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded"
          >
            Inactive
          </span>
        </div>
        <div class="flex gap-2">
          <button @click="toggleActive(type)" class="text-green-600 hover:text-green-800 text-sm" :title="type.is_active ? 'Deactivate' : 'Activate'">
            {{ type.is_active ? '✅' : '⭕' }}
          </button>
          <button @click="editPaymentType(type)" class="text-blue-600 hover:text-blue-800 text-sm">✏️</button>
          <button @click="deletePaymentType(type)" class="text-red-600 hover:text-red-800 text-sm">🗑</button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="modalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-base font-bold text-slate-700 mb-4">
          {{ editingId ? 'Edit Payment Type' : 'Add Payment Type' }}
        </h3>
        <form @submit.prevent="savePaymentType">
          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700">Payment Type Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
              placeholder="e.g., Cash, Bank Transfer, Credit Card"
            />
          </div>

          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700">Code</label>
            <input
              v-model="form.code"
              type="text"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
              placeholder="e.g., CASH, BT, CC"
            />
            <p class="text-xs text-slate-400 mt-1">Optional – auto-generated if left empty</p>
          </div>

          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700">Color</label>
            <input
              v-model="form.color"
              type="color"
              class="w-full h-10 rounded-lg border border-slate-300 p-1"
            />
          </div>

          <div class="mb-3 flex items-center gap-2">
            <input
              v-model="form.is_active"
              type="checkbox"
              id="is_active"
              class="h-4 w-4 rounded border-slate-300 text-primary-600"
            />
            <label for="is_active" class="text-sm text-slate-700">Active</label>
          </div>

          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700">Description</label>
            <textarea
              v-model="form.description"
              rows="2"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
              placeholder="Optional description"
            ></textarea>
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

const paymentTypes = ref([])
const loading = ref(true)

const modalVisible = ref(false)
const editingId = ref(null)
const form = ref({
  name: '',
  code: '',
  color: '#6B7280',
  is_active: true,
  description: '',
})
const saving = ref(false)
const modalError = ref('')

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const fetchPaymentTypes = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/payment-types', {
      headers: getAuthHeader()
    })
    paymentTypes.value = data
  } catch (err) {
    console.error('Failed to fetch payment types:', err)
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  editingId.value = null
  form.value = {
    name: '',
    code: '',
    color: '#6B7280',
    is_active: true,
    description: '',
  }
  modalError.value = ''
  modalVisible.value = true
}

const editPaymentType = (type) => {
  editingId.value = type.id
  form.value = {
    name: type.name,
    code: type.code || '',
    color: type.color || '#6B7280',
    is_active: type.is_active !== undefined ? type.is_active : true,
    description: type.description || '',
  }
  modalError.value = ''
  modalVisible.value = true
}

const closeModal = () => {
  modalVisible.value = false
  form.value = {
    name: '',
    code: '',
    color: '#6B7280',
    is_active: true,
    description: '',
  }
  editingId.value = null
  modalError.value = ''
}

const savePaymentType = async () => {
  modalError.value = ''
  if (!form.value.name.trim()) {
    modalError.value = 'Payment type name is required'
    return
  }

  saving.value = true
  try {
    if (editingId.value) {
      await axios.put(`/payment-types/${editingId.value}`, form.value, {
        headers: getAuthHeader()
      })
    } else {
      await axios.post('/payment-types', form.value, {
        headers: getAuthHeader()
      })
    }
    await fetchPaymentTypes()
    closeModal()
  } catch (err) {
    modalError.value = err.response?.data?.message || 'Failed to save payment type'
  } finally {
    saving.value = false
  }
}

const deletePaymentType = async (type) => {
  if (!confirm(`Delete payment type "${type.name}"? This cannot be undone.`)) return
  try {
    await axios.delete(`/payment-types/${type.id}`, {
      headers: getAuthHeader()
    })
    await fetchPaymentTypes()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete payment type')
  }
}

const toggleActive = async (type) => {
  try {
    await axios.post(`/payment-types/${type.id}/toggle-active`, {}, {
      headers: getAuthHeader()
    })
    await fetchPaymentTypes()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to toggle status')
  }
}

onMounted(fetchPaymentTypes)
</script>