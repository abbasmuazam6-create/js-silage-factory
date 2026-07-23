<template>
  <div class="p-4">
    <h3 class="text-sm font-semibold text-slate-700">Business Information</h3>
    <p class="text-sm text-slate-500 mt-2">Manage your company details</p>

    <form @submit.prevent="saveSettings" class="mt-4 space-y-4 max-w-2xl">
      <!-- Business Name -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Business Name</label>
        <input
          v-model="form.business_name"
          type="text"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          placeholder="Enter business name"
        />
      </div>

      <!-- Address -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Address</label>
        <textarea
          v-model="form.business_address"
          rows="2"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          placeholder="Enter business address"
        ></textarea>
      </div>

      <!-- Contact -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Contact</label>
        <input
          v-model="form.phone"
          type="text"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          placeholder="Enter phone number"
        />
      </div>

      <!-- Email -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Email</label>
        <input
          v-model="form.email"
          type="email"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          placeholder="Enter email address"
        />
      </div>

      <!-- Currency Symbol -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Currency Symbol</label>
        <input
          v-model="form.currency_symbol"
          type="text"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          placeholder="e.g., Rs."
        />
      </div>

      <!-- Tax Rate -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Tax Rate (%)</label>
        <input
          v-model.number="form.tax_rate"
          type="number"
          step="0.01"
          min="0"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          placeholder="Enter tax rate"
        />
      </div>

      <!-- Invoice Prefix -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Invoice Prefix</label>
        <input
          v-model="form.invoice_prefix"
          type="text"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          placeholder="e.g., INV-"
        />
      </div>

      <!-- Date Format -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Date Format</label>
        <select
          v-model="form.date_format"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
        >
          <option value="DD/MM/YYYY">DD/MM/YYYY</option>
          <option value="MM/DD/YYYY">MM/DD/YYYY</option>
          <option value="YYYY-MM-DD">YYYY-MM-DD</option>
        </select>
      </div>

      <!-- Invoice Footer -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Invoice Footer Message</label>
        <textarea
          v-model="form.invoice_footer"
          rows="2"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          placeholder="Enter footer message for invoices"
        ></textarea>
      </div>

      <!-- Buttons -->
      <div class="flex justify-end">
        <button
          type="submit"
          class="btn-primary px-4 py-2 text-sm"
          :disabled="saving"
        >
          {{ saving ? 'Saving...' : 'Save Settings' }}
        </button>
      </div>

      <!-- Messages -->
      <div v-if="message" class="text-sm" :class="messageSuccess ? 'text-green-600' : 'text-red-600'">
        {{ message }}
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const form = ref({
  business_name: '',
  business_address: '',
  phone: '',
  email: '',
  currency_symbol: '',
  tax_rate: 0,
  invoice_prefix: '',
  date_format: '',
  invoice_footer: '',
})

const saving = ref(false)
const message = ref('')
const messageSuccess = ref(false)

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const loadSettings = async () => {
  try {
    const { data } = await axios.get('/settings', {
      headers: getAuthHeader()
    })
    form.value = {
      business_name: data.business_name || '',
      business_address: data.business_address || '',
      phone: data.phone || '',
      email: data.email || '',
      currency_symbol: data.currency_symbol || '$',
      tax_rate: data.tax_rate || 0,
      invoice_prefix: data.invoice_prefix || 'INV-',
      date_format: data.date_format || 'DD/MM/YYYY',
      invoice_footer: data.invoice_footer || '',
    }
  } catch (err) {
    console.error('Failed to load settings:', err)
    message.value = 'Failed to load settings'
    messageSuccess.value = false
  }
}

const saveSettings = async () => {
  saving.value = true
  message.value = ''
  try {
    await axios.post('/settings', form.value, {
      headers: getAuthHeader()
    })
    message.value = 'Settings saved successfully'
    messageSuccess.value = true
    await loadSettings()
  } catch (err) {
    message.value = err.response?.data?.message || 'Failed to save settings'
    messageSuccess.value = false
  } finally {
    saving.value = false
  }
}

onMounted(loadSettings)
</script>