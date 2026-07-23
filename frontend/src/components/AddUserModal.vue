<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md max-h-[90vh] overflow-y-auto rounded-lg bg-white p-4 shadow-xl">
      <h3 class="mb-3 text-base font-bold text-slate-700">
        {{ isEditing ? 'Edit User' : 'Add User' }}
      </h3>

      <form @submit.prevent="saveUser" class="space-y-3">
        <!-- Name -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Name *</label>
          <input
            v-model="form.name"
            type="text"
            required
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
            placeholder="Enter full name"
          />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Email *</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
            placeholder="Enter email address"
          />
        </div>

        <!-- Password -->
        <div>
          <label class="block text-xs font-medium text-slate-700">
            {{ isEditing ? 'New Password (leave blank to keep current)' : 'Password *' }}
          </label>
          <div class="relative">
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              :required="!isEditing"
              class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs pr-8"
              placeholder="Enter password"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
            >
              {{ showPassword ? '👁️' : '👁️‍🗨️' }}
            </button>
          </div>
          <p v-if="isEditing" class="mt-0.5 text-[10px] text-slate-400">
            Leave blank to keep current password
          </p>
        </div>

        <!-- Role -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Role *</label>
          <select
            v-model="form.role"
            required
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
          >
            <option value="staff">Staff</option>
            <option value="manager">Manager</option>
            <option value="admin">Admin</option>
          </select>
          <p class="mt-0.5 text-[10px] text-slate-400">
            Admin: Full access | Manager: Can manage bunkers & reports | Staff: View only
          </p>
        </div>

        <!-- Active Status -->
        <div class="flex items-center gap-2">
          <input
            type="checkbox"
            v-model="form.is_active"
            id="is_active"
            class="w-3.5 h-3.5 rounded border-slate-300"
          />
          <label for="is_active" class="text-xs text-slate-700">Active</label>
        </div>

        <!-- Errors -->
        <div v-if="error" class="rounded-lg bg-red-50 p-2 text-xs text-red-700">
          {{ error }}
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-2 border-t border-slate-200 pt-3">
          <button type="button" @click="closeModal" class="btn-outline px-3 py-1.5 text-xs">Cancel</button>
          <button type="submit" class="btn-primary px-3 py-1.5 text-xs" :disabled="loading">
            {{ loading ? 'Saving...' : isEditing ? 'Update' : 'Save' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  visible: Boolean,
  editData: Object,
})

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const error = ref('')
const showPassword = ref(false)

const form = reactive({
  name: '',
  email: '',
  password: '',
  role: 'staff',
  is_active: true,
})

const isEditing = computed(() => !!props.editData?.id)

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const resetForm = () => {
  form.name = ''
  form.email = ''
  form.password = ''
  form.role = 'staff'
  form.is_active = true
  error.value = ''
}

const closeModal = () => {
  resetForm()
  emit('close')
}

const saveUser = async () => {
  error.value = ''

  if (!form.name.trim()) {
    error.value = 'Name is required'
    return
  }
  if (!form.email.trim()) {
    error.value = 'Email is required'
    return
  }
  if (!isEditing.value && !form.password) {
    error.value = 'Password is required'
    return
  }

  loading.value = true
  try {
    const payload = {
      name: form.name,
      email: form.email,
      role: form.role,
      is_active: form.is_active,
    }

    if (form.password) {
      payload.password = form.password
    }

    if (isEditing.value) {
      await axios.put(`/users/${props.editData.id}`, payload, {
        headers: getAuthHeader()
      })
    } else {
      await axios.post('/users', payload, {
        headers: getAuthHeader()
      })
    }

    emit('saved')
    closeModal()
  } catch (err) {
    error.value = err.response?.data?.message || err.response?.data?.errors || 'Failed to save user'
  } finally {
    loading.value = false
  }
}

watch(() => props.editData, (data) => {
  if (data && data.id) {
    form.name = data.name || ''
    form.email = data.email || ''
    form.password = ''
    form.role = data.role || 'staff'
    form.is_active = data.is_active !== undefined ? data.is_active : true
  } else {
    resetForm()
  }
}, { immediate: true })

watch(() => props.visible, (val) => {
  if (!val) {
    resetForm()
  }
})
</script>