<template>
  <div class="p-4">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-sm font-semibold text-slate-700">Manage Expense Categories</h3>
      <button @click="openAddModal" class="btn-primary px-3 py-1.5 text-sm">+ Add Category</button>
    </div>

    <div v-if="loading" class="text-sm text-slate-500">Loading...</div>
    <div v-else-if="categories.length === 0" class="text-sm text-slate-500">No expense categories added yet.</div>
    <div v-else class="space-y-2">
      <div
        v-for="category in categories"
        :key="category.id"
        class="flex items-center justify-between border-b border-slate-100 py-2"
      >
        <div class="flex items-center gap-3">
          <span
            class="inline-block w-3 h-3 rounded-full"
            :style="{ backgroundColor: category.color || '#6B7280' }"
          ></span>
          <span class="text-sm font-medium">{{ category.name }}</span>
          <span class="text-xs text-slate-400">
            {{ category.available_in || 'both' }}
          </span>
        </div>
        <div class="flex gap-2">
          <button @click="editCategory(category)" class="text-blue-600 hover:text-blue-800 text-sm">✏️</button>
          <button @click="deleteCategory(category)" class="text-red-600 hover:text-red-800 text-sm">🗑</button>
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <div v-if="modalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h3 class="text-base font-bold text-slate-700 mb-4">
          {{ editingId ? 'Edit Category' : 'Add Category' }}
        </h3>
        <form @submit.prevent="saveCategory">
          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700">Category Name *</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
              placeholder="e.g., Labour, Transport, Machinery"
            />
          </div>

          <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700">Color</label>
            <input
              v-model="form.color"
              type="color"
              class="w-full h-10 rounded-lg border border-slate-300 p-1"
            />
          </div>

          <div class="mt-3">
            <label class="block text-sm font-medium text-slate-700">Available In</label>
            <select
              v-model="form.available_in"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            >
              <option value="both">Both (Silage & Wanda)</option>
              <option value="silage">Silage Only</option>
              <option value="wanda">Wanda Only</option>
            </select>
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

const categories = ref([])
const loading = ref(true)

const modalVisible = ref(false)
const editingId = ref(null)
const form = ref({
  name: '',
  color: '#6B7280',
  available_in: 'both',
})
const saving = ref(false)
const modalError = ref('')

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const fetchCategories = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/expense-categories', {
      headers: getAuthHeader()
    })
    categories.value = data
  } catch (err) {
    console.error('Failed to fetch categories:', err)
  } finally {
    loading.value = false
  }
}

const openAddModal = () => {
  editingId.value = null
  form.value = {
    name: '',
    color: '#6B7280',
    available_in: 'both',
  }
  modalError.value = ''
  modalVisible.value = true
}

const editCategory = (category) => {
  editingId.value = category.id
  form.value = {
    name: category.name,
    color: category.color || '#6B7280',
    available_in: category.available_in || 'both',
  }
  modalError.value = ''
  modalVisible.value = true
}

const closeModal = () => {
  modalVisible.value = false
  form.value = {
    name: '',
    color: '#6B7280',
    available_in: 'both',
  }
  editingId.value = null
  modalError.value = ''
}

const saveCategory = async () => {
  modalError.value = ''
  if (!form.value.name.trim()) {
    modalError.value = 'Category name is required'
    return
  }

  saving.value = true
  try {
    if (editingId.value) {
      await axios.put(`/expense-categories/${editingId.value}`, form.value, {
        headers: getAuthHeader()
      })
    } else {
      await axios.post('/expense-categories', form.value, {
        headers: getAuthHeader()
      })
    }
    await fetchCategories()
    closeModal()
  } catch (err) {
    modalError.value = err.response?.data?.message || 'Failed to save category'
  } finally {
    saving.value = false
  }
}

const deleteCategory = async (category) => {
  if (!confirm(`Delete category "${category.name}"? This cannot be undone.`)) return
  try {
    await axios.delete(`/expense-categories/${category.id}`, {
      headers: getAuthHeader()
    })
    await fetchCategories()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete category')
  }
}

onMounted(fetchCategories)
</script>