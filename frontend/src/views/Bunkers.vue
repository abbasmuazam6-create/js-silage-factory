<template>
  <div class="space-y-3 p-3 sm:p-4">
    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-2">
      <h2 class="text-lg font-bold text-slate-700">Bunkers</h2>
      <div class="flex flex-wrap items-center gap-1">
        <button @click="refresh" class="btn-outline text-xs py-1 px-2">🔄</button>
        <button @click="openForm()" class="btn-primary text-xs py-1 px-2">+ Add</button>
        <button @click="exportPDF" class="btn-outline text-xs py-1 px-2">📄 PDF</button>
      </div>
    </div>

    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-1.5">
      <input
        v-model="search"
        type="text"
        placeholder="Search bunkers..."
        class="flex-1 min-w-[100px] rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
        @input="refresh"
      />
      <select v-model="locationFilter" class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs" @change="refresh">
        <option value="all">All Locations</option>
        <option v-for="loc in locations" :key="loc" :value="loc">
          {{ loc }}
        </option>
      </select>
      <select v-model="seasonId" class="rounded-lg border border-slate-300 px-2 py-1.5 text-xs" @change="refresh">
        <option value="all">All Seasons</option>
        <option v-for="season in seasons" :key="season.id" :value="season.id">
          {{ season.name }}
        </option>
      </select>
    </div>

    <!-- Loading -->
    <div v-if="store.loading" class="text-xs text-slate-500">Loading...</div>

    <!-- Table -->
    <div v-else class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
      <table class="w-full divide-y divide-slate-200 text-xs">
        <thead class="sticky top-0 z-10 bg-primary-50">
          <tr>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Name</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700 hidden sm:table-cell">Location</th>
            <th class="px-2 py-1.5 text-right font-semibold text-slate-700">Available (kg)</th>
            <th class="px-2 py-1.5 text-left font-semibold text-slate-700">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 bg-white">
          <tr
            v-for="bunker in paginatedBunkers"
            :key="bunker.id"
            class="hover:bg-slate-50 cursor-pointer"
            @dblclick="viewBunker(bunker)"
          >
            <td class="px-2 py-1.5 font-medium">{{ bunker.name }}</td>
            <td class="px-2 py-1.5 hidden sm:table-cell">{{ bunker.location || '-' }}</td>
            <td class="px-2 py-1.5 text-right font-bold">{{ numberFormat(bunker.available_weight || 0) }}</td>
            <td class="px-2 py-1.5">
              <span :class="statusClass(bunker.status)" class="rounded-full px-1.5 py-0.5 text-[10px]">
                {{ bunker.status }}
              </span>
            </td>
          </tr>
          <tr v-if="filteredBunkers.length === 0">
            <td colspan="4" class="px-4 py-4 text-center text-slate-500 text-xs">No bunkers found.</td>
          </tr>
        </tbody>
        <tfoot v-if="filteredBunkers.length > 0" class="sticky bottom-0 z-10 bg-slate-50 text-xs font-semibold">
          <tr>
            <td class="px-2 py-1.5" colspan="2">Total</td>
            <td class="px-2 py-1.5 text-right">{{ numberFormat(totalAvailable) }}</td>
            <td class="px-2 py-1.5"></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Pagination -->
    <div class="flex flex-wrap items-center justify-between gap-1 text-xs text-slate-600">
      <div class="flex items-center gap-1">
        <span>Records:</span>
        <select v-model="perPage" class="rounded border border-slate-300 px-1 py-0.5 text-xs">
          <option value="10">10</option>
          <option value="25">25</option>
          <option value="50">50</option>
          <option value="All">All</option>
        </select>
        <span>Showing {{ paginatedRange.start }} - {{ paginatedRange.end }} of {{ filteredBunkers.length }}</span>
      </div>
      <div class="flex items-center gap-1">
        <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1" class="px-2 py-0.5 border rounded">‹</button>
        <span>Page {{ currentPage }} of {{ totalPages }}</span>
        <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages" class="px-2 py-0.5 border rounded">›</button>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toastMessage" class="fixed bottom-4 right-4 z-50 rounded-lg px-3 py-2 text-white shadow-lg text-sm" :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'">
      {{ toastMessage }}
      <button @click="toastMessage = ''" class="ml-3 opacity-75 hover:opacity-100">✕</button>
    </div>

    <!-- Modals -->
    <BunkerForm :visible="formVisible" :bunker="selectedBunker" @close="closeForm" @saved="handleSaved" />
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
import { useBunkerStore } from '../stores/bunker'
import { useTabsStore } from '../stores/tabs'
import BunkerForm from '../components/BunkerForm.vue'
import BunkerDetail from './BunkerDetail.vue' // ✅ Add this import
import ConfirmModal from '../components/ConfirmModal.vue'
import { fetchSeasons, getCurrentSeason } from '../utils/seasons'

const store = useBunkerStore()
const tabsStore = useTabsStore()

const search = ref('')
const seasonId = ref('all')
const locationFilter = ref('all')
const perPage = ref(10)
const currentPage = ref(1)
const formVisible = ref(false)
const confirmModalVisible = ref(false)
const selectedBunker = ref(null)
const toastMessage = ref('')
const toastType = ref('success')
const seasons = ref([])
const locations = ref([])

const confirmTitle = ref('Confirm')
const confirmMessage = ref('Are you sure?')
const confirmButtonText = ref('Confirm')
let pendingAction = null

// Computed
const filteredBunkers = computed(() => {
  let list = store.bunkers
  if (search.value) {
    const s = search.value.toLowerCase()
    list = list.filter(b => b.name.toLowerCase().includes(s) || (b.location && b.location.toLowerCase().includes(s)))
  }
  if (locationFilter.value !== 'all') {
    list = list.filter(b => b.location === locationFilter.value)
  }
  return list
})

const totalAvailable = computed(() => {
  return filteredBunkers.value.reduce((sum, b) => sum + Number(b.available_weight || 0), 0)
})

const perPageNum = computed(() => {
  if (perPage.value === 'All') return filteredBunkers.value.length || 1
  return Number(perPage.value)
})

const totalPages = computed(() => {
  const count = filteredBunkers.value.length
  const per = perPageNum.value
  return per > 0 ? Math.ceil(count / per) : 1
})

const paginatedBunkers = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value
  const end = start + perPageNum.value
  return filteredBunkers.value.slice(start, end)
})

const paginatedRange = computed(() => {
  const start = (currentPage.value - 1) * perPageNum.value + 1
  const end = Math.min(currentPage.value * perPageNum.value, filteredBunkers.value.length)
  return { start, end }
})

watch([search, seasonId, locationFilter], () => {
  currentPage.value = 1
  refresh()
}, { debounce: 300 })

watch(perPage, () => { currentPage.value = 1 })

// Extract unique locations from bunkers
const updateLocations = () => {
  const locs = store.bunkers.map(b => b.location).filter(Boolean)
  locations.value = [...new Set(locs)]
}

// Load seasons
const loadSeasons = async () => {
  const data = await fetchSeasons()
  seasons.value = data
  const current = getCurrentSeason(data)
  if (current) {
    seasonId.value = current.id
  } else if (data.length > 0) {
    seasonId.value = data[0].id
  }
}

// Refresh data
const refresh = async () => {
  const params = { season_id: seasonId.value, search: search.value }
  await store.fetchBunkers(params)
  updateLocations()
}

const showToast = (msg, type = 'success') => {
  toastMessage.value = msg
  toastType.value = type
  setTimeout(() => { toastMessage.value = '' }, 3000)
}

const openForm = (bunker = null) => {
  selectedBunker.value = bunker
  formVisible.value = true
}

const closeForm = () => {
  formVisible.value = false
  selectedBunker.value = null
  refresh()
}

const handleSaved = () => {
  selectedBunker.value = null
  refresh()
}

const viewBunker = (bunker) => {
  tabsStore.openTab({
    path: `bunker-detail-${bunker.id}`,
    label: bunker.name,
    icon: '📦',
    component: BunkerDetail, // ✅ Pass actual component
    props: { id: bunker.id, seasonId: seasonId.value }
  })
}

const exportPDF = () => {
  showToast('Export PDF - Coming soon', 'success')
}

const executeConfirmedAction = async () => {
  confirmModalVisible.value = false
  const action = pendingAction
  pendingAction = null
}

const numberFormat = (val) => {
  if (val === null || val === undefined) return '0'
  return Number(val).toLocaleString()
}

const statusClass = (status) => ({
  active: 'bg-green-100 text-green-800',
  warning: 'bg-yellow-100 text-yellow-800',
  empty: 'bg-red-100 text-red-800',
  blocked: 'bg-gray-100 text-gray-800'
}[status] || 'bg-gray-100 text-gray-800')

onMounted(() => {
  loadSeasons().then(() => refresh())
})
</script>