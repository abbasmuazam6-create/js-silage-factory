<template>
  <div class="space-y-3 p-3 sm:p-4">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-lg font-bold text-slate-700">🏢 Suppliers</h2>
      <div class="flex flex-wrap items-center gap-1">
        <button @click="refresh" class="btn-outline text-xs py-1 px-2">🔄</button>
        <button @click="openAddModal" class="btn-primary text-xs py-1 px-2">+ Add</button>
        <button @click="exportPDF" class="btn-outline text-xs py-1 px-2">📄 PDF</button>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-1.5">
      <input
        v-model="filters.search"
        type="text"
        placeholder="Search suppliers..."
        class="flex-1 min-w-[150px] rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @input="applyFilters"
      />
      <select
        v-model="filters.is_active"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @change="applyFilters"
      >
        <option value="all">All Status</option>
        <option value="true">Active</option>
        <option value="false">Inactive</option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-xs text-slate-500">Loading...</div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
      <table class="w-full divide-y divide-slate-200 text-xs">
        <thead class="sticky top-0 z-10 bg-primary-50">
          <tr>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Name</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Contact Person</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Phone</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Email</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Status</th>
            <th class="px-2 py-1.5 text-center font-semibold text-slate-700">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
          <tr
            v-for="supplier in paginatedSuppliers"
            :key="supplier.id"
            class="hover:bg-slate-50"
          >
            <td class="px-2 py-1.5 font-medium">{{ supplier.name }}</td>
            <td class="px-2 py-1.5">{{ supplier.contact_person || '-' }}</td>
            <td class="px-2 py-1.5">{{ supplier.phone || '-' }}</td>
            <td class="px-2 py-1.5">{{ supplier.email || '-' }}</td>
            <td class="px-2 py-1.5">
              <span
                class="px-1.5 py-0.5 rounded-full text-[10px]"
                :class="supplier.is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
              >
                {{ supplier.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-2 py-1.5 text-center">
              <div class="flex justify-center gap-1">
                <button @click="toggleActive(supplier)" class="text-green-600 hover:text-green-800 text-sm" :title="supplier.is_active ? 'Deactivate' : 'Activate'">
                  {{ supplier.is_active ? '✅' : '⭕' }}
                </button>
                <button @click="editSupplier(supplier)" class="text-blue-600 hover:text-blue-800 text-sm">✏️</button>
                <button @click="deleteSupplier(supplier)" class="text-red-600 hover:text-red-800 text-sm">🗑</button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredSuppliers.length === 0">
            <td colspan="6" class="px-4 py-4 text-center text-slate-500 text-xs">No suppliers found.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex flex-wrap items-center justify-between gap-1 text-xs text-slate-600">
      <div class="flex items-center gap-1">
        <span>Records:</span>
        <select v-model="perPage" class="rounded border border-slate-300 px-1 py-0.5 text-xs" @change="currentPage = 1">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="All">All</option>
        </select>
        <span>Showing {{ paginatedRange.start }} - {{ paginatedRange.end }} of {{ filteredSuppliers.length }}</span>
      </div>
      <div class="flex items-center gap-1">
        <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1" class="px-2 py-0.5 border rounded">‹</button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages" class="px-2 py-0.5 border rounded">›</button>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <SupplierFormModal
      :visible="modalVisible"
      :supplier="selectedSupplier"
      @close="closeModal"
      @saved="refresh"
    />

    <!-- Confirm Modal -->
    <ConfirmModal
      :visible="confirmModalVisible"
      :title="confirmTitle"
      :message="confirmMessage"
      :confirm-text="confirmButtonText"
      @confirm="executeConfirmedAction"
      @cancel="confirmModalVisible = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import SupplierFormModal from '../components/SupplierFormModal.vue'
import ConfirmModal from '../components/ConfirmModal.vue'

const suppliers = ref([])
const loading = ref(true)
const perPage = ref(10)
const currentPage = ref(1)

const filters = ref({
  search: '',
  is_active: 'all',
})

const modalVisible = ref(false)
const selectedSupplier = ref(null)
const confirmModalVisible = ref(false)
const confirmTitle = ref('Confirm')
const confirmMessage = ref('Are you sure?')
const confirmButtonText = ref('Confirm')
let pendingAction = null

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const filteredSuppliers = computed(() => {
  return suppliers.value
})

const perPageNum = computed(() => {
  if (perPage.value === 'All') return filteredSuppliers.value.length || 1
  return Number(perPage.value)
})

const totalPages = computed(() => {
  const count = filteredSuppliers.value.length
  const per = perPageNum.value
  return per > 0 ? Math.ceil(count / per) : 1
})

const paginatedSuppliers = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value
  const end = start + perPageNum.value
  return filteredSuppliers.value.slice(start, end)
})

const paginatedRange = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value + 1
  const end = Math.min(currentPage.value * perPageNum.value, filteredSuppliers.value.length)
  return { start, end }
})

const fetchSuppliers = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.is_active !== 'all') params.is_active = filters.value.is_active

    const { data } = await axios.get('/suppliers', {
      headers: getAuthHeader(),
      params
    })
    suppliers.value = data
  } catch (err) {
    console.error('Failed to fetch suppliers:', err)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchSuppliers()
}

const refresh = () => {
  fetchSuppliers()
}

const openAddModal = () => {
  selectedSupplier.value = null
  modalVisible.value = true
}

const editSupplier = (supplier) => {
  selectedSupplier.value = supplier
  modalVisible.value = true
}

const closeModal = () => {
  modalVisible.value = false
  selectedSupplier.value = null
}

const toggleActive = async (supplier) => {
  try {
    await axios.post(`/suppliers/${supplier.id}/toggle-active`, {}, {
      headers: getAuthHeader()
    })
    await fetchSuppliers()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to toggle status')
  }
}

const deleteSupplier = (supplier) => {
  confirmTitle.value = 'Delete Supplier'
  confirmMessage.value = `Are you sure you want to delete "${supplier.name}"? This cannot be undone.`
  confirmButtonText.value = 'Delete'
  pendingAction = { type: 'delete', id: supplier.id }
  confirmModalVisible.value = true
}

const executeConfirmedAction = async () => {
  confirmModalVisible.value = false
  if (pendingAction?.type === 'delete') {
    try {
      await axios.delete(`/suppliers/${pendingAction.id}`, {
        headers: getAuthHeader()
      })
      await fetchSuppliers()
    } catch (err) {
      alert(err.response?.data?.message || 'Failed to delete supplier')
    }
  }
  pendingAction = null
}

const exportPDF = () => {
  alert('PDF Export coming soon!')
}

onMounted(fetchSuppliers)
</script>