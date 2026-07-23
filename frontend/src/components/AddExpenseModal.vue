<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-sm max-h-[90vh] overflow-y-auto rounded-lg bg-white p-4 shadow-xl">
      <h3 class="mb-3 text-base font-bold text-slate-700">
  {{ isEditing ? 'Edit Expense' : 'Add Expense' }}
  <span v-if="preSelectedBunker" class="text-xs font-normal text-slate-500 block">
    Bunker: {{ preSelectedBunker.name }} ({{ preSelectedBunker.location || 'No location' }})
  </span>
</h3>

      <form @submit.prevent="saveExpense" class="space-y-3">
        <!-- Category -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Category *</label>
          <div class="flex gap-1.5">
            <select
              v-model="form.category_id"
              required
              class="flex-1 rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
            >
              <option value="">Select Category</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
            <button type="button" @click="openAddCategory" class="btn-primary px-2.5 py-1.5 text-xs">+</button>
          </div>
        </div>

        <!-- Amount -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Amount (Rs.) *</label>
          <input
            v-model.number="form.amount"
            type="number"
            step="0.01"
            min="0.01"
            required
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
          />
        </div>

        <!-- Date -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Date *</label>
          <input
            v-model="form.date"
            type="date"
            required
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
          />
        </div>

        <!-- Errors -->
        <div v-if="error" class="rounded-lg bg-red-50 p-2 text-xs text-red-700">
          {{ error }}
        </div>

        <!-- Buttons -->
        <div class="flex justify-between border-t border-slate-200 pt-3">
          <button
            v-if="isEditing"
            type="button"
            @click="deleteExpense"
            class="btn-danger px-3 py-1.5 text-xs"
          >
            Delete
          </button>
          <div class="flex gap-2 ml-auto">
            <button type="button" @click="closeModal" class="btn-outline px-3 py-1.5 text-xs">Cancel</button>
            <button type="submit" class="btn-primary px-3 py-1.5 text-xs" :disabled="loading">
              {{ loading ? 'Saving...' : isEditing ? 'Update' : 'Save' }}
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Add Category Modal -->
    <div v-if="categoryModalVisible" class="fixed inset-0 z-60 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-sm max-h-[80vh] overflow-y-auto rounded-lg bg-white p-4 shadow-xl">
        <h4 class="mb-3 text-base font-bold text-slate-700">Add Category</h4>
        <form @submit.prevent="saveCategory" class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-slate-700">Name *</label>
            <input v-model="newCategory.name" type="text" required class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs" />
          </div>
          <div v-if="categoryError" class="text-xs text-red-600">{{ categoryError }}</div>
          <div class="flex justify-end gap-2 border-t border-slate-200 pt-3">
            <button type="button" @click="categoryModalVisible = false" class="btn-outline px-3 py-1.5 text-xs">Cancel</button>
            <button type="submit" class="btn-primary px-3 py-1.5 text-xs" :disabled="categoryLoading">
              {{ categoryLoading ? 'Saving...' : 'Add' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :visible="confirmModalVisible"
      title="Delete Expense"
      message="Are you sure you want to delete this expense?"
      confirm-text="Delete"
      @confirm="executeDelete"
      @cancel="confirmModalVisible = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import axios from 'axios'
import ConfirmModal from './ConfirmModal.vue'

const props = defineProps({
  visible: Boolean,
  bunkerId: String,
  seasonId: String,
  editData: Object, // for edit mode
    preSelectedBunker: Object,
})

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const error = ref('')
const categories = ref([])

const categoryModalVisible = ref(false)
const categoryLoading = ref(false)
const categoryError = ref('')
const newCategory = reactive({ name: '' })

const form = reactive({
  category_id: '',
  amount: 0,
  date: new Date().toISOString().split('T')[0],
})

const editingId = ref(null)
const isEditing = computed(() => !!props.editData?.id)
const confirmModalVisible = ref(false)

const fetchCategories = async () => {
  try {
    const res = await axios.get('/expense-categories')
    categories.value = res.data
  } catch (err) {
    console.error('Failed to fetch categories:', err)
  }
}

const openAddCategory = () => {
  newCategory.name = ''
  categoryError.value = ''
  categoryModalVisible.value = true
}

const saveCategory = async () => {
  if (!newCategory.name.trim()) {
    categoryError.value = 'Category name is required'
    return
  }
  categoryLoading.value = true
  categoryError.value = ''
  try {
    const res = await axios.post('/expense-categories', newCategory)
    categories.value.push(res.data)
    form.category_id = res.data.id
    categoryModalVisible.value = false
  } catch (err) {
    categoryError.value = err.response?.data?.message || 'Failed to add category'
  } finally {
    categoryLoading.value = false
  }
}

const resetForm = () => {
  form.category_id = ''
  form.amount = 0
  form.date = new Date().toISOString().split('T')[0]
  editingId.value = null
  error.value = ''
  confirmModalVisible.value = false
}

const saveExpense = async () => {
  error.value = ''
  if (!form.category_id) {
    error.value = 'Please select a category'
    return
  }
  if (!form.amount || form.amount <= 0) {
    error.value = 'Please enter a valid amount'
    return
  }

  loading.value = true
  try {
    const payload = {
      bunker_id: props.bunkerId,
      season_id: props.seasonId,
      expense_category_id: form.category_id,
      amount: form.amount,
      date: form.date,
      notes: null,
    }

    if (isEditing.value) {
      await axios.put(`/expenses/${editingId.value}`, payload)
    } else {
      await axios.post('/expenses', payload)
    }

    emit('saved')
    closeModal()
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to save expense'
  } finally {
    loading.value = false
  }
}

const deleteExpense = () => {
  confirmModalVisible.value = true
}

const executeDelete = async () => {
  confirmModalVisible.value = false
  loading.value = true
  error.value = ''
  try {
    await axios.delete(`/expenses/${editingId.value}`)
    emit('saved')
    closeModal()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete'
  } finally {
    loading.value = false
  }
}

const closeModal = () => {
  resetForm()
  emit('close')
}

// Watch editData to prefill
watch(() => props.editData, (data) => {
  if (data && data.id) {
    editingId.value = data.id
    form.category_id = data.expense_category_id || data.category_id || ''
    form.amount = data.amount || 0
    form.date = data.date ? new Date(data.date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0]
  } else {
    resetForm()
  }
}, { immediate: true })

watch(() => props.visible, (val) => {
  if (val) {
    fetchCategories()
  }
})
</script>