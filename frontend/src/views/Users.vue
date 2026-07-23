<template>
  <div class="space-y-3 p-3 sm:p-4">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-lg font-bold text-slate-700">👥 Users Management</h2>
      <div class="flex flex-wrap items-center gap-1.5">
        <button @click="openAddUser" class="btn-primary text-xs py-1.5 px-3">+ Add User</button>
        <button @click="refresh" class="btn-outline text-xs py-1.5 px-2">🔄</button>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-1.5">
      <select
        v-model="filters.role"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @change="applyFilters"
      >
        <option value="all">All Roles</option>
        <option value="admin">Admin</option>
        <option value="manager">Manager</option>
        <option value="staff">Staff</option>
      </select>

      <select
        v-model="filters.status"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @change="applyFilters"
      >
        <option value="all">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>

      <input
        v-model="filters.search"
        type="text"
        placeholder="Search users..."
        class="flex-1 min-w-[150px] rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @input="applyFilters"
      />
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-xs text-slate-500">Loading...</div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
      <table class="w-full divide-y divide-slate-200 text-xs">
        <thead class="sticky top-0 z-10 bg-primary-50">
          <tr>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">#</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Name</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Email</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Role</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Status</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Created</th>
            <th class="px-2 py-1.5 text-center font-semibold text-slate-700">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
          <tr v-for="(user, index) in paginatedUsers" :key="user.id" class="hover:bg-slate-50">
            <td class="px-2 py-1.5">{{ index + 1 }}</td>
            <td class="px-2 py-1.5 font-medium">{{ user.name }}</td>
            <td class="px-2 py-1.5 text-slate-600">{{ user.email }}</td>
            <td class="px-2 py-1.5">
              <span 
                class="px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase"
                :class="{
                  'bg-purple-100 text-purple-700': user.role === 'admin',
                  'bg-blue-100 text-blue-700': user.role === 'manager',
                  'bg-slate-100 text-slate-700': user.role === 'staff'
                }"
              >
                {{ user.role }}
              </span>
            </td>
            <td class="px-2 py-1.5">
              <span 
                class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                :class="user.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700'"
              >
                {{ user.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-2 py-1.5 text-slate-500">{{ formatDate(user.created_at) }}</td>
            <td class="px-2 py-1.5 text-center">
              <div class="flex items-center justify-center gap-1">
                <button @click="editUser(user)" class="text-slate-400 hover:text-blue-600 text-sm">✎</button>
                <button @click="toggleStatus(user)" class="text-slate-400 hover:text-amber-600 text-sm">
                  {{ user.is_active ? '🔴' : '🟢' }}
                </button>
                <button @click="deleteUser(user)" class="text-slate-400 hover:text-rose-600 text-sm">✕</button>
              </div>
            </td>
          </tr>
          <tr v-if="filteredUsers.length === 0">
            <td colspan="7" class="px-4 py-4 text-center text-slate-500 text-xs">No users found.</td>
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
        <span>Showing {{ paginatedRange.start }} - {{ paginatedRange.end }} of {{ filteredUsers.length }}</span>
      </div>
      <div class="flex items-center gap-1">
        <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1" class="px-2 py-0.5 border rounded">‹</button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages" class="px-2 py-0.5 border rounded">›</button>
      </div>
    </div>

    <!-- Modals -->
    <AddUserModal
      :visible="userModalVisible"
      :edit-data="editingUser"
      @close="closeUserModal"
      @saved="refresh"
    />

    <ConfirmModal
      :visible="confirmModalVisible"
      title="Delete User"
      message="Are you sure you want to delete this user? This action cannot be undone."
      confirm-text="Delete"
      @confirm="executeDelete"
      @cancel="confirmModalVisible = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import AddUserModal from '../components/AddUserModal.vue'
import ConfirmModal from '../components/ConfirmModal.vue'

// State
const users = ref([])
const loading = ref(true)
const perPage = ref(10)
const currentPage = ref(1)

// Filters
const filters = ref({
  role: 'all',
  status: 'all',
  search: '',
})

// Modal states
const userModalVisible = ref(false)
const editingUser = ref(null)
const confirmModalVisible = ref(false)
const userToDelete = ref(null)

// Computed
const filteredUsers = computed(() => {
  let result = users.value

  if (filters.value.role !== 'all') {
    result = result.filter(u => u.role === filters.value.role)
  }

  if (filters.value.status !== 'all') {
    result = result.filter(u => {
      const isActive = filters.value.status === 'active'
      return u.is_active === isActive
    })
  }

  if (filters.value.search) {
    const search = filters.value.search.toLowerCase()
    result = result.filter(u => 
      u.name.toLowerCase().includes(search) ||
      u.email.toLowerCase().includes(search)
    )
  }

  return result
})

const perPageNum = computed(() => {
  if (perPage.value === 'All') return filteredUsers.value.length || 1
  return Number(perPage.value)
})

const totalPages = computed(() => {
  const count = filteredUsers.value.length
  const per = perPageNum.value
  return per > 0 ? Math.ceil(count / per) : 1
})

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value
  const end = start + perPageNum.value
  return filteredUsers.value.slice(start, end)
})

const paginatedRange = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value + 1
  const end = Math.min(currentPage.value * perPageNum.value, filteredUsers.value.length)
  return { start, end }
})

// Methods
const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString()
}

const fetchUsers = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.role !== 'all') params.role = filters.value.role
    if (filters.value.status !== 'all') params.status = filters.value.status
    if (filters.value.search) params.search = filters.value.search

    const { data } = await axios.get('/users', {
      headers: getAuthHeader(),
      params
    })
    users.value = data
  } catch (err) {
    console.error('Failed to fetch users:', err)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchUsers()
}

const refresh = () => {
  fetchUsers()
}

const openAddUser = () => {
  editingUser.value = null
  userModalVisible.value = true
}

const editUser = (user) => {
  editingUser.value = user
  userModalVisible.value = true
}

const closeUserModal = () => {
  userModalVisible.value = false
  editingUser.value = null
}

const toggleStatus = async (user) => {
  if (!confirm(`Are you sure you want to ${user.is_active ? 'deactivate' : 'activate'} ${user.name}?`)) {
    return
  }

  try {
    await axios.post(`/users/${user.id}/toggle-active`, {}, {
      headers: getAuthHeader()
    })
    await fetchUsers()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to update user status')
  }
}

const deleteUser = (user) => {
  userToDelete.value = user
  confirmModalVisible.value = true
}

const executeDelete = async () => {
  if (!userToDelete.value) return
  
  try {
    await axios.delete(`/users/${userToDelete.value.id}`, {
      headers: getAuthHeader()
    })
    confirmModalVisible.value = false
    userToDelete.value = null
    await fetchUsers()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete user')
  }
}

onMounted(() => {
  fetchUsers()
})
</script>