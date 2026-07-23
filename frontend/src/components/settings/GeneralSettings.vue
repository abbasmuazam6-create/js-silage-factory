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

      <!-- Logo Upload -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Logo</label>
        <div class="flex items-center gap-4">
          <img
            v-if="form.logo"
            :src="form.logo"
            alt="Logo"
            class="h-16 w-16 object-contain border rounded"
          />
          <div v-else class="h-16 w-16 border-2 border-dashed border-slate-300 rounded flex items-center justify-center text-slate-400 text-xs">
            No logo
          </div>
          <input
            type="file"
            accept="image/*"
            @change="uploadLogo"
            class="text-sm"
          />
        </div>
        <p v-if="logoUploading" class="text-xs text-slate-500 mt-1">Uploading...</p>
      </div>

      <!-- Address -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Address</label>
        <textarea
          v-model="form.address"
          rows="2"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          placeholder="Enter business address"
        ></textarea>
      </div>

      <!-- Contact -->
      <div>
        <label class="block text-sm font-medium text-slate-700">Contact</label>
        <input
          v-model="form.contact"
          type="text"
          class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          placeholder="Enter phone number"
        />
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
  logo: '',
  address: '',
  contact: '',
  invoice_footer: '',
})

const saving = ref(false)
const message = ref('')
const messageSuccess = ref(false)
const logoUploading = ref(false)

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
      logo: data.logo || '',
      address: data.address || '',
      contact: data.contact || '',
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
  } catch (err) {
    message.value = err.response?.data?.message || 'Failed to save settings'
    messageSuccess.value = false
  } finally {
    saving.value = false
  }
}

const uploadLogo = async (event) => {
  const file = event.target.files[0]
  if (!file) return

  const formData = new FormData()
  formData.append('logo', file)

  logoUploading.value = true
  try {
    const { data } = await axios.post('/settings/upload-logo', formData, {
      headers: {
        ...getAuthHeader(),
        'Content-Type': 'multipart/form-data'
      }
    })
    form.value.logo = data.logo_url
    message.value = 'Logo uploaded successfully'
    messageSuccess.value = true
  } catch (err) {
    message.value = err.response?.data?.message || 'Failed to upload logo'
    messageSuccess.value = false
  } finally {
    logoUploading.value = false
  }
}

onMounted(loadSettings)
</script>