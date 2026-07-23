<template>
  <div class="space-y-3 p-3 sm:p-4">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-lg font-bold text-slate-700">💸 Silage Expenses</h2>
      <div class="flex flex-wrap items-center gap-1">
        <button @click="refresh" class="btn-outline text-xs py-1 px-2">🔄</button>
        <button @click="openBunkerSelection" class="btn-primary text-xs py-1 px-2">+ Add</button>
        <button @click="exportPDF" class="btn-outline text-xs py-1 px-2">📄 PDF</button>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-1.5">
      <select
        v-model="filters.season_id"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @change="applyFilters"
      >
        <option value="all">All Seasons</option>
        <option v-for="season in seasons" :key="season.id" :value="season.id">
          {{ season.name }}
        </option>
      </select>

      <select
        v-model="filters.location"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @change="applyFilters"
      >
        <option value="all">All Locations</option>
        <option v-for="loc in locations" :key="loc" :value="loc">
          {{ loc }}
        </option>
      </select>

      <select
        v-model="filters.bunker_id"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @change="applyFilters"
      >
        <option value="all">All Bunkers</option>
        <option v-for="bunker in bunkers" :key="bunker.id" :value="bunker.id">
          {{ bunker.name }} ({{ bunker.location || 'No location' }})
        </option>
      </select>

      <select
        v-model="filters.category_id"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @change="applyFilters"
      >
        <option value="all">All Categories</option>
        <option v-for="category in categories" :key="category.id" :value="category.id">
          {{ category.name }}
        </option>
      </select>

      <input
        v-model="filters.search"
        type="text"
        placeholder="Search..."
        class="flex-1 min-w-[100px] rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
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
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Date</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Category</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Bunker</th>
            <th class="px-2 py-1.5 text-right font-semibold text-slate-700">Amount</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Notes</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
          <tr
            v-for="expense in paginatedExpenses"
            :key="expense.id"
            class="hover:bg-slate-50 cursor-pointer"
            @click="viewExpense(expense)"
          >
            <td class="px-2 py-1.5">{{ formatDate(expense.date) }}</td>
            <td class="px-2 py-1.5">
              <span
                class="inline-block w-2 h-2 rounded-full mr-1"
                :style="{ backgroundColor: expense.category?.color || '#6B7280' }"
              ></span>
              {{ expense.category?.name || 'Uncategorized' }}
            </td>
            <td class="px-2 py-1.5">{{ expense.bunker?.name || 'Unknown' }}</td>
            <td class="px-2 py-1.5 text-right font-medium">Rs. {{ numberFormat(expense.amount) }}</td>
            <td class="px-2 py-1.5 truncate max-w-[100px]">{{ expense.notes || '-' }}</td>
          </tr>
          <tr v-if="filteredExpenses.length === 0">
            <td colspan="5" class="px-4 py-4 text-center text-slate-500 text-xs">No expenses found.</td>
          </tr>
        </tbody>
        <tfoot v-if="filteredExpenses.length > 0" class="sticky bottom-0 z-10 bg-slate-50 font-semibold">
          <tr>
            <td class="px-2 py-1.5" colspan="3">TOTAL</td>
            <td class="px-2 py-1.5 text-right">Rs. {{ numberFormat(totalAmount) }}</td>
            <td class="px-2 py-1.5"></td>
          </tr>
        </tfoot>
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
        <span>Showing {{ paginatedRange.start }} - {{ paginatedRange.end }} of {{ filteredExpenses.length }}</span>
      </div>
      <div class="flex items-center gap-1">
        <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1" class="px-2 py-0.5 border rounded">‹</button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages" class="px-2 py-0.5 border rounded">›</button>
      </div>
    </div>

    <!-- Modals -->
    <BunkerSelectionModal
      :visible="bunkerSelectionVisible"
      @close="bunkerSelectionVisible = false"
      @select="handleBunkerSelected"
    />
    <AddExpenseModal
      :visible="expenseModalVisible"
      :bunker-id="selectedBunkerForExpense?.id"
      :season-id="filters.season_id !== 'all' ? filters.season_id : null"
      :pre-selected-bunker="selectedBunkerForExpense"
      @close="closeExpenseModal"
      @saved="refresh"
    />
    <ExpenseDetailModal
      :visible="expenseDetailVisible"
      :expense="selectedExpense"
      @close="expenseDetailVisible = false"
    />
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
import BunkerSelectionModal from '../components/BunkerSelectionModal.vue'
import AddExpenseModal from '../components/AddExpenseModal.vue'
import ExpenseDetailModal from '../components/ExpenseDetailModal.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import { fetchSeasons, getCurrentSeason } from '../utils/seasons'

// State
const expenses = ref([])
const seasons = ref([])
const locations = ref([])
const bunkers = ref([])
const categories = ref([])
const loading = ref(true)
const perPage = ref(10)
const currentPage = ref(1)

// Filters
const filters = ref({
  season_id: 'all',
  location: 'all',
  bunker_id: 'all',
  category_id: 'all',
  search: '',
})

// Modal states
const bunkerSelectionVisible = ref(false)
const expenseModalVisible = ref(false)
const selectedBunkerForExpense = ref(null)
const expenseDetailVisible = ref(false)
const selectedExpense = ref(null)
const confirmModalVisible = ref(false)
const confirmTitle = ref('Confirm')
const confirmMessage = ref('Are you sure?')
const confirmButtonText = ref('Confirm')

// Computed
const filteredExpenses = computed(() => expenses.value)

const perPageNum = computed(() => {
  if (perPage.value === 'All') return filteredExpenses.value.length || 1
  return Number(perPage.value)
})

const totalPages = computed(() => {
  const count = filteredExpenses.value.length
  const per = perPageNum.value
  return per > 0 ? Math.ceil(count / per) : 1
})

const paginatedExpenses = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value
  const end = start + perPageNum.value
  return filteredExpenses.value.slice(start, end)
})

const paginatedRange = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value + 1
  const end = Math.min(currentPage.value * perPageNum.value, filteredExpenses.value.length)
  return { start, end }
})

const totalAmount = computed(() => {
  return filteredExpenses.value.reduce((sum, e) => sum + (Number(e.amount) || 0), 0)
})

// Methods
const numberFormat = (val) => {
  if (val === null || val === undefined) return '0'
  const num = Number(val)
  if (isNaN(num)) return '0'
  return num.toLocaleString()
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString()
}

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const fetchData = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.season_id !== 'all') params.season_id = filters.value.season_id
    if (filters.value.location !== 'all') params.location = filters.value.location
    if (filters.value.bunker_id !== 'all') params.bunker_id = filters.value.bunker_id
    if (filters.value.category_id !== 'all') params.category_id = filters.value.category_id
    if (filters.value.search) params.search = filters.value.search

    const { data } = await axios.get('/expenses', {
      headers: getAuthHeader(),
      params
    })
    expenses.value = data

    // Fetch locations
    const { data: locationsData } = await axios.get('/locations', {
      headers: getAuthHeader()
    })
    locations.value = locationsData.map(l => l.name)

    // Fetch bunkers
    const { data: bunkersData } = await axios.get('/bunkers', {
      headers: getAuthHeader()
    })
    bunkers.value = bunkersData

    // Fetch categories
    const { data: categoriesData } = await axios.get('/expense-categories', {
      headers: getAuthHeader()
    })
    categories.value = categoriesData

  } catch (err) {
    console.error('Failed to fetch expenses:', err)
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  currentPage.value = 1
  fetchData()
}

const refresh = () => {
  fetchData()
}

const openBunkerSelection = () => {
  bunkerSelectionVisible.value = true
}

const handleBunkerSelected = (bunker) => {
  bunkerSelectionVisible.value = false
  selectedBunkerForExpense.value = bunker
  expenseModalVisible.value = true
}

const closeExpenseModal = () => {
  expenseModalVisible.value = false
  selectedBunkerForExpense.value = null
}

const viewExpense = (expense) => {
  selectedExpense.value = expense
  expenseDetailVisible.value = true
}

const exportPDF = () => {
  alert('PDF Export coming soon!')
}

const executeConfirmedAction = () => {
  confirmModalVisible.value = false
}

const loadSeasons = async () => {
  const data = await fetchSeasons()
  seasons.value = data
  const current = getCurrentSeason(data)
  if (current) {
    filters.value.season_id = current.id
  }
}

onMounted(async () => {
  await loadSeasons()
  await fetchData()
})
</script>