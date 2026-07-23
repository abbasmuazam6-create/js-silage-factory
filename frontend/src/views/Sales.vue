<template>
  <div class="space-y-3 p-3 sm:p-4">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-lg font-bold text-slate-700">📈 Sales</h2>
      <div class="flex flex-wrap items-center gap-1">
        <button @click="refresh" class="btn-outline text-xs py-1 px-2">🔄</button>
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
        v-model="filters.customer_id"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @change="applyFilters"
      >
        <option value="all">All Customers</option>
        <option v-for="customer in customers" :key="customer.id" :value="customer.id">
          {{ customer.name }}
        </option>
      </select>

      <select
        v-model="filters.sale_type"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @change="applyFilters"
      >
        <option value="all">All Types</option>
        <option value="open">Open</option>
        <option value="bags">Bags</option>
        <option value="bales">Bales</option>
      </select>

      <select
        v-model="filters.payment_type_id"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @change="applyFilters"
      >
        <option value="all">All Payment Types</option>
        <option v-for="pt in paymentTypes" :key="pt.id" :value="pt.id">
          {{ pt.name }}
        </option>
      </select>

      <!-- ✅ Date Filters (before search) -->
      <input
        v-model="filters.date_from"
        type="date"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs min-w-[130px]"
        @change="applyFilters"
        placeholder="Date From"
      />
      <input
        v-model="filters.date_to"
        type="date"
        class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs min-w-[130px]"
        @change="applyFilters"
        placeholder="Date To"
      />

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
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Invoice</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Customer</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Bunker</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Type</th>
            <th class="px-2 py-1.5 text-right font-semibold text-slate-700">KG</th>
            <th class="px-2 py-1.5 text-right font-semibold text-slate-700">Price/KG</th>
            <th class="px-2 py-1.5 text-right font-semibold text-slate-700">Total</th>
            <th class="px-2 py-1.5 text-right font-semibold text-slate-700">Profit</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Payment</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Date</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
          <tr
            v-for="sale in paginatedSales"
            :key="sale.id"
            class="hover:bg-slate-50 cursor-pointer"
            @click="viewSale(sale)"
          >
            <td class="px-2 py-1.5 font-medium">{{ sale.invoice_number }}</td>
            <td class="px-2 py-1.5">{{ sale.customer?.name || 'Unknown' }}</td>
            <td class="px-2 py-1.5">{{ sale.bunker?.name || 'Unknown' }}</td>
            <td class="px-2 py-1.5 capitalize">{{ sale.sale_type }}</td>
            <td class="px-2 py-1.5 text-right">{{ numberFormat(sale.weight_kg) }}</td>
            <td class="px-2 py-1.5 text-right">Rs. {{ numberFormat(sale.price_per_kg) }}</td>
            <td class="px-2 py-1.5 text-right">Rs. {{ numberFormat(sale.total_price) }}</td>
            <td class="px-2 py-1.5 text-right" :class="sale.profit >= 0 ? 'text-green-600' : 'text-red-600'">
              Rs. {{ numberFormat(sale.profit) }}
            </td>
            <td class="px-2 py-1.5">{{ sale.payment_type?.name || '-' }}</td>
            <td class="px-2 py-1.5">{{ formatDate(sale.date) }}</td>
          </tr>
          <tr v-if="filteredSales.length === 0">
            <td colspan="10" class="px-4 py-4 text-center text-slate-500 text-xs">No sales found.</td>
          </tr>
        </tbody>
        <tfoot v-if="filteredSales.length > 0" class="sticky bottom-0 z-10 bg-slate-50 font-semibold">
          <tr>
            <td class="px-2 py-1.5" colspan="4">TOTAL</td>
            <td class="px-2 py-1.5 text-right">{{ numberFormat(totalKg) }}</td>
            <td class="px-2 py-1.5"></td>
            <td class="px-2 py-1.5 text-right">Rs. {{ numberFormat(totalRevenue) }}</td>
            <td class="px-2 py-1.5"></td>
            <td colspan="2"></td>
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
        <span>Showing {{ paginatedRange.start }} - {{ paginatedRange.end }} of {{ filteredSales.length }}</span>
      </div>
      <div class="flex items-center gap-1">
        <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1" class="px-2 py-0.5 border rounded">‹</button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages" class="px-2 py-0.5 border rounded">›</button>
      </div>
    </div>

    <!-- Modals -->
    <SaleDetailModal
      :visible="saleDetailVisible"
      :sale="selectedSale"
      @close="saleDetailVisible = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import SaleDetailModal from '../components/SaleDetailModal.vue'
import { fetchSeasons, getCurrentSeason } from '../utils/seasons'

// State
const sales = ref([])
const seasons = ref([])
const locations = ref([])
const bunkers = ref([])
const customers = ref([])
const paymentTypes = ref([])
const loading = ref(true)
const perPage = ref(10)
const currentPage = ref(1)

// Filters
const filters = ref({
  season_id: 'all',
  location: 'all',
  bunker_id: 'all',
  customer_id: 'all',
  sale_type: 'all',
  payment_type_id: 'all',
  search: '',
  date_from: '', // ✅ Added
  date_to: '',   // ✅ Added
})

// Modal states
const saleDetailVisible = ref(false)
const selectedSale = ref(null)

// Computed
const filteredSales = computed(() => sales.value)

const perPageNum = computed(() => {
  if (perPage.value === 'All') return filteredSales.value.length || 1
  return Number(perPage.value)
})

const totalPages = computed(() => {
  const count = filteredSales.value.length
  const per = perPageNum.value
  return per > 0 ? Math.ceil(count / per) : 1
})

const paginatedSales = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value
  const end = start + perPageNum.value
  return filteredSales.value.slice(start, end)
})

const paginatedRange = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value + 1
  const end = Math.min(currentPage.value * perPageNum.value, filteredSales.value.length)
  return { start, end }
})

const totalKg = computed(() => {
  return filteredSales.value.reduce((sum, s) => sum + (Number(s.weight_kg) || 0), 0)
})

const totalRevenue = computed(() => {
  return filteredSales.value.reduce((sum, s) => sum + (Number(s.total_price) || 0), 0)
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
    if (filters.value.customer_id !== 'all') params.customer_id = filters.value.customer_id
    if (filters.value.sale_type !== 'all') params.sale_type = filters.value.sale_type
    if (filters.value.payment_type_id !== 'all') params.payment_type_id = filters.value.payment_type_id
    if (filters.value.search) params.search = filters.value.search
    // ✅ Add date filters
    if (filters.value.date_from) params.date_from = filters.value.date_from
    if (filters.value.date_to) params.date_to = filters.value.date_to

    const { data } = await axios.get('/sales', {
      headers: getAuthHeader(),
      params
    })
    sales.value = data

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

    // Fetch customers
    const { data: customersData } = await axios.get('/customers', {
      headers: getAuthHeader()
    })
    customers.value = customersData

    // Fetch payment types
    const { data: paymentTypesData } = await axios.get('/payment-types', {
      headers: getAuthHeader()
    })
    paymentTypes.value = paymentTypesData

  } catch (err) {
    console.error('Failed to fetch sales:', err)
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

const viewSale = (sale) => {
  selectedSale.value = sale
  saleDetailVisible.value = true
}

// ===== PDF Export =====
const exportPDF = async () => {
  try {
    // Build query params from current filters
    const params = new URLSearchParams()
    
    // Replace these with your actual filter variable names
    if (filters.value.season_id && filters.value.season_id !== 'all') {
      params.append('season_id', filters.value.season_id)
    }
    if (filters.value.location && filters.value.location !== 'all') {
      params.append('location', filters.value.location)
    }
    if (filters.value.bunker_id && filters.value.bunker_id !== 'all') {
      params.append('bunker_id', filters.value.bunker_id)
    }
    if (filters.value.customer_id && filters.value.customer_id !== 'all') {
      params.append('customer_id', filters.value.customer_id)
    }
    if (filters.value.sale_type && filters.value.sale_type !== 'all') {
      params.append('sale_type', filters.value.sale_type)
    }
    if (filters.value.payment_type_id && filters.value.payment_type_id !== 'all') {
      params.append('payment_type_id', filters.value.payment_type_id)
    }
    if (filters.value.date_from) {
      params.append('date_from', filters.value.date_from)
    }
    if (filters.value.date_to) {
      params.append('date_to', filters.value.date_to)
    }
    if (filters.value.search) {
      params.append('search', filters.value.search)
    }
    
    const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'
    const url = `${baseUrl}/sales/export-list?${params.toString()}`
    
    console.log('📤 Opening PDF URL:', url)
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