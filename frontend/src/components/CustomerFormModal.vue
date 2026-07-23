<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md max-h-[90vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
      <h3 class="text-base font-bold text-slate-700 mb-4">
        {{ customer ? 'Edit Customer' : 'Add Customer' }}
      </h3>

      <form @submit.prevent="saveCustomer" class="space-y-3">
        <!-- Name -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Name *</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            placeholder="Enter customer name"
          />
        </div>

        <!-- Phone -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Phone</label>
          <input
            v-model="form.phone"
            type="text"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            placeholder="Enter phone number"
          />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            placeholder="Enter email address"
          />
        </div>

        <!-- Address -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Address</label>
          <textarea
            v-model="form.address"
            rows="2"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            placeholder="Enter address"
          ></textarea>
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Notes</label>
          <textarea
            v-model="form.notes"
            rows="2"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            placeholder="Additional notes"
          ></textarea>
        </div>

        <!-- Active Status -->
        <div class="flex items-center gap-2">
          <input
            v-model="form.is_active"
            type="checkbox"
            id="is_active"
            class="h-4 w-4 rounded border-slate-300 text-primary-600"
          />
          <label for="is_active" class="text-sm text-slate-700">Active</label>
        </div>

        <!-- Error -->
        <div v-if="error" class="text-sm text-red-600">{{ error }}</div>

        <!-- Buttons -->
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
  customer: Object,
})

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const error = ref('')

const form = reactive({
  name: '',
  phone: '',
  email: '',
  address: '',
  notes: '',
  is_active: true,
})

const resetForm = () => {
  form.name = ''
  form.phone = ''
  form.email = ''
  form.address = ''
  form.notes = ''
  form.is_active = true
  error.value = ''
}

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

watch(() => props.customer, (customer) => {
  if (customer) {
    form.name = customer.name || ''
    form.phone = customer.phone || ''
    form.email = customer.email || ''
    form.address = customer.address || ''
    form.notes = customer.notes || ''
    form.is_active = customer.is_active !== undefined ? customer.is_active : true
  } else {
    resetForm()
  }
}, { immediate: true })

const saveCustomer = async () => {
  error.value = ''
  if (!form.name.trim()) {
    error.value = 'Customer name is required'
    return
  }

  loading.value = true
  try {
    if (props.customer) {
      await axios.put(`/customers/${props.customer.id}`, form, {
        headers: getAuthHeader()
      })
    } else {
      await axios.post('/customers', form, {
        headers: getAuthHeader()
      })
    }
    emit('saved')
    emit('close')
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to save customer'
  } finally {
    loading.value = false
  }
}
</script>