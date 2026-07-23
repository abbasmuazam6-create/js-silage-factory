<template>
  <div class="space-y-3 p-3 sm:p-4">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-lg font-bold text-slate-700">🌾 Silage Purchases</h2>
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
        v-model="filters.supplier_id"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @change="applyFilters"
      >
        <option value="all">All Suppliers</option>
        <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
          {{ supplier.name }}
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
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Code</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Supplier</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Bunker</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Date</th>
            <th class="px-2 py-1.5 text-right font-semibold text-slate-700">KG</th>
            <th class="px-2 py-1.5 text-right font-semibold text-slate-700">Cost</th>
            <th class="px-2 py-1.5 text-right font-semibold text-slate-700">Cost/KG</th>
            <th class="px-2 py-1.5 text-right font-semibold text-slate-700">Area</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
          <tr
            v-for="purchase in paginatedPurchases"
            :key="purchase.id"
            class="hover:bg-slate-50 cursor-pointer"
            @click="viewPurchase(purchase)"
          >
            <td class="px-2 py-1.5 font-medium">{{ purchase.purchase_code }}</td>
            <td class="px-2 py-1.5">{{ purchase.supplier?.name || 'Unknown' }}</td>
            <td class="px-2 py-1.5">{{ purchase.bunker?.name || 'Unknown' }}</td>
            <td class="px-2 py-1.5">{{ formatDate(purchase.purchase_date) }}</td>
            <td class="px-2 py-1.5 text-right">{{ numberFormat(purchase.weight_kg) }}</td>
            <td class="px-2 py-1.5 text-right">Rs. {{ numberFormat(purchase.cost) }}</td>
            <td class="px-2 py-1.5 text-right">Rs. {{ numberFormat(purchase.cost_per_kg) }}</td>
            <td class="px-2 py-1.5 text-right">{{ purchase.area ? numberFormat(purchase.area) + ' acres' : '-' }}</td>
          </tr>
          <tr v-if="filteredPurchases.length === 0">
            <td colspan="8" class="px-4 py-4 text-center text-slate-500 text-xs">No purchases found.</td>
          </tr>
        </tbody>
        <tfoot v-if="filteredPurchases.length > 0" class="sticky bottom-0 z-10 bg-slate-50 font-semibold">
          <tr>
            <td class="px-2 py-1.5" colspan="4">TOTAL</td>
            <td class="px-2 py-1.5 text-right">{{ numberFormat(totalKg) }}</td>
            <td class="px-2 py-1.5 text-right">Rs. {{ numberFormat(totalCost) }}</td>
            <td class="px-2 py-1.5"></td>
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
        <span>Showing {{ paginatedRange.start }} - {{ paginatedRange.end }} of {{ filteredPurchases.length }}</span>
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
    <AddPurchaseModal
      :visible="addPurchaseModalVisible"
      :bunker-id="selectedBunkerForPurchase?.id"
      :season-id="filters.season_id !== 'all' ? filters.season_id : null"
      :pre-selected-bunker="selectedBunkerForPurchase"
      @close="closeAddPurchaseModal"
      @saved="refresh"
    />
    <PurchaseDetailModal
      :visible="purchaseDetailVisible"
      :purchase="selectedPurchase"
      @close="purchaseDetailVisible = false"
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
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'
import BunkerSelectionModal from '../components/BunkerSelectionModal.vue'
import AddPurchaseModal from '../components/AddPurchaseModal.vue'
import PurchaseDetailModal from '../components/PurchaseDetailModal.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import { fetchSeasons, getCurrentSeason } from '../utils/seasons'

// State
const purchases = ref([])
const seasons = ref([])
const locations = ref([])
const bunkers = ref([])
const suppliers = ref([])
const loading = ref(true)
const perPage = ref(10)
const currentPage = ref(1)

// Filters
const filters = ref({
  season_id: 'all',
  location: 'all',
  bunker_id: 'all',
  supplier_id: 'all',
  search: '',
})

// Modal states
const bunkerSelectionVisible = ref(false)
const addPurchaseModalVisible = ref(false)
const selectedBunkerForPurchase = ref(null)
const purchaseDetailVisible = ref(false)
const selectedPurchase = ref(null)
const confirmModalVisible = ref(false)
const confirmTitle = ref('Confirm')
const confirmMessage = ref('Are you sure?')
const confirmButtonText = ref('Confirm')
let pendingAction = null

// Computed
const filteredPurchases = computed(() => purchases.value)

const perPageNum = computed(() => {
  if (perPage.value === 'All') return filteredPurchases.value.length || 1
  return Number(perPage.value)
})

const totalPages = computed(() => {
  const count = filteredPurchases.value.length
  const per = perPageNum.value
  return per > 0 ? Math.ceil(count / per) : 1
})

const paginatedPurchases = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value
  const end = start + perPageNum.value
  return filteredPurchases.value.slice(start, end)
})

const paginatedRange = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value + 1
  const end = Math.min(currentPage.value * perPageNum.value, filteredPurchases.value.length)
  return { start, end }
})

const totalKg = computed(() => {
  return filteredPurchases.value.reduce((sum, p) => sum + (Number(p.weight_kg) || 0), 0)
})

const totalCost = computed(() => {
  return filteredPurchases.value.reduce((sum, p) => sum + (Number(p.cost) || 0), 0)
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
    if (filters.value.supplier_id !== 'all') params.supplier_id = filters.value.supplier_id
    if (filters.value.search) params.search = filters.value.search

    const { data } = await axios.get('/silage-purchases', {
      headers: getAuthHeader(),
      params
    })
    purchases.value = data

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

    // Fetch suppliers
    const { data: suppliersData } = await axios.get('/suppliers', {
      headers: getAuthHeader()
    })
    suppliers.value = suppliersData

  } catch (err) {
    console.error('❌ Failed to fetch purchases:', err)
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
  selectedBunkerForPurchase.value = bunker
  addPurchaseModalVisible.value = true
}

const closeAddPurchaseModal = () => {
  addPurchaseModalVisible.value = false
  selectedBunkerForPurchase.value = null
}

const viewPurchase = (purchase) => {
  selectedPurchase.value = purchase
  purchaseDetailVisible.value = true
}

// ===== PDF Export =====
const exportPDF = async () => {
  try {
    const token = localStorage.getItem('token')
    
    // Build query params from current filters
    const params = new URLSearchParams()
    
    if (filters.value.season_id && filters.value.season_id !== 'all') {
      params.append('season_id', filters.value.season_id)
    }
    if (filters.value.location && filters.value.location !== 'all') {
      params.append('location', filters.value.location)
    }
    if (filters.value.bunker_id && filters.value.bunker_id !== 'all') {
      params.append('bunker_id', filters.value.bunker_id)
    }
    if (filters.value.supplier_id && filters.value.supplier_id !== 'all') {
      params.append('supplier_id', filters.value.supplier_id)
    }
    if (filters.value.search) {
      params.append('search', filters.value.search)
    }
    
    // Add token to URL
    if (token) {
      params.append('token', token)
    }
    
    // ✅ Make sure this matches your API URL
    const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'
    const url = `${baseUrl}/purchases/export-list?${params.toString()}`
    
    console.log('📤 Opening PDF URL:', url)
    
    // Open directly in new tab
    window.open(url, '_blank')
    
  } catch (err) {
    console.error('Failed to export PDF:', err)
    alert('Failed to generate PDF report')
  }
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