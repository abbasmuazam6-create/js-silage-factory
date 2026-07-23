<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md max-h-[90vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
      <h3 class="text-base font-bold text-slate-700 mb-4">
        {{ supplier ? 'Edit Supplier' : 'Add Supplier' }}
      </h3>

      <form @submit.prevent="saveSupplier" class="space-y-3">
        <div>
          <label class="block text-xs font-medium text-slate-700">Name *</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700">Contact Person</label>
          <input
            v-model="form.contact_person"
            type="text"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700">Phone</label>
          <input
            v-model="form.phone"
            type="text"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700">Address</label>
          <textarea
            v-model="form.address"
            rows="2"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          ></textarea>
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700">Tax ID</label>
          <input
            v-model="form.tax_id"
            type="text"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-medium text-slate-700">Notes</label>
          <textarea
            v-model="form.notes"
            rows="2"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          ></textarea>
        </div>

        <div class="flex items-center gap-2">
          <input
            v-model="form.is_active"
            type="checkbox"
            id="is_active"
            class="h-4 w-4 rounded border-slate-300 text-primary-600"
          />
          <label for="is_active" class="text-sm text-slate-700">Active</label>
        </div>

        <div v-if="error" class="text-sm text-red-600">{{ error }}</div>

        <div class="flex justify-end gap-3 border-t border-slate-200 pt-3">
          <button type="button" @click="$emit('close')" class="btn-outline px-4 py-2 text-sm">Cancel</button>
          <button type="submit" class="btn-primary px-4 py-2 text-sm" :disabled="loading">
            {{ loading ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  visible: Boolean,
  supplier: Object,
})

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  contact_person: '',
  phone: '',
  email: '',
  address: '',
  tax_id: '',
  notes: '',
  is_active: true,
})

const resetForm = () => {
  form.name = ''
  form.contact_person = ''
  form.phone = ''
  form.email = ''
  form.address = ''
  form.tax_id = ''
  form.notes = ''
  form.is_active = true
  error.value = ''
}

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

watch(() => props.supplier, (supplier) => {
  if (supplier) {
    form.name = supplier.name || ''
    form.contact_person = supplier.contact_person || ''
    form.phone = supplier.phone || ''
    form.email = supplier.email || ''
    form.address = supplier.address || ''
    form.tax_id = supplier.tax_id || ''
    form.notes = supplier.notes || ''
    form.is_active = supplier.is_active !== undefined ? supplier.is_active : true
  } else {
    resetForm()
  }
}, { immediate: true })

const saveSupplier = async () => {
  error.value = ''
  if (!form.name.trim()) {
    error.value = 'Supplier name is required'
    return
  }

  loading.value = true
  try {
    if (props.supplier) {
      await axios.put(`/suppliers/${props.supplier.id}`, form, {
        headers: getAuthHeader()
      })
    } else {
      await axios.post('/suppliers', form, {
        headers: getAuthHeader()
      })
    }
    emit('saved')
    emit('close')
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save supplier'
  } finally {
    loading.value = false
  }
}
</script>