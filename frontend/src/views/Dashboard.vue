<template>
  <div class="space-y-6">
    <!-- Show Loader while loading -->
    <DashboardLoader v-if="loading" />

    <!-- Dashboard Content -->
    <template v-else>
      <!-- Header -->
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">Dashboard</h1>
          <p class="text-sm text-slate-500">Welcome back, {{ user?.name || 'User' }} 👋</p>
        </div>
        <div class="flex items-center gap-2">
          <select
            v-model="selectedSeason"
            @change="fetchDashboardData"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          >
            <option value="all">All Seasons</option>
            <option v-for="season in seasons" :key="season.id" :value="season.id">
              {{ season.name }}
            </option>
          </select>
          <button
            @click="fetchDashboardData"
            class="rounded-lg bg-primary-600 px-3 py-2 text-sm text-white hover:bg-primary-700"
          >
            🔄 Refresh
          </button>
        </div>
      </div>

      <!-- Row 1: KPI Cards -->
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <!-- Card: Bunkers -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-xs font-medium uppercase text-slate-500">Total Bunkers</p>
              <p class="mt-1 text-3xl font-bold text-slate-800">{{ dashboard.total_bunkers || 0 }}</p>
            </div>
            <span class="rounded-full bg-primary-50 p-2 text-2xl">🏠</span>
          </div>
          <div class="mt-3 flex items-center gap-4 text-sm">
            <span class="flex items-center gap-1">
              <span class="h-2 w-2 rounded-full bg-green-500"></span>
              Active: {{ dashboard.active_bunkers || 0 }}
            </span>
            <span class="flex items-center gap-1">
              <span class="h-2 w-2 rounded-full bg-slate-400"></span>
              Empty: {{ dashboard.empty_bunkers || 0 }}
            </span>
          </div>
        </div>

        <!-- Card: Purchased (was Total Stock) -->
        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex items-start justify-between">
            <div>
              <p class="text-xs font-medium uppercase text-slate-500">Purchased</p>
              <p class="mt-1 text-3xl font-bold text-slate-800">{{ formatNumber(dashboard.total_purchased_kg) }} kg</p>
            </div>
            <span class="rounded-full bg-blue-50 p-2 text-2xl">📦</span>
          </div>
          <div class="mt-3 flex items-center gap-4 text-sm">
            <span class="flex items-center gap-1">
              Sold: {{ formatNumber(dashboard.total_sold_kg) }} kg
            </span>
            <span class="flex items-center gap-1">
              Available: {{ formatNumber(dashboard.total_available_kg) }} kg
            </span>
          </div>
          <div class="mt-2 h-1.5 w-full rounded-full bg-slate-200">
            <div
              class="h-1.5 rounded-full bg-primary-600 transition-all"
              :style="{ width: stockPercentage + '%' }"
            ></div>
          </div>
          <p class="mt-0.5 text-[10px] text-slate-400">
            {{ stockPercentage }}% of total stock sold
          </p>
        </div>
      </div>

      <!-- Row 2: Location Cards -->
      <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div
          v-for="loc in displayLocations"
          :key="loc.name"
          class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm"
        >
          <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-semibold text-slate-700">📍 {{ loc.name }}</h3>
            <span class="text-xs text-slate-500">
              {{ loc.active_bunkers || 0 }} active · {{ loc.empty_bunkers || 0 }} empty
            </span>
          </div>

          <div class="mt-3 grid grid-cols-2 gap-y-2 gap-x-4 text-sm">
            <div>
              <span class="text-slate-500">Shrinkage</span>
              <p class="font-medium text-slate-700">
                {{ formatNumber(loc.shrinkage_kg) }} kg
                <span class="text-xs text-slate-400">({{ loc.shrinkage_percentage || 0 }}%)</span>
              </p>
            </div>
            <div>
              <span class="text-slate-500">Sold</span>
              <p class="font-medium text-slate-700">{{ formatNumber(loc.sold_kg) }} kg</p>
            </div>
            <div>
              <span class="text-slate-500">Available</span>
              <p class="font-medium text-slate-700">{{ formatNumber(loc.available_kg) }} kg</p>
            </div>
            <div>
              <span class="text-slate-500">Purchased</span>
              <p class="font-medium text-slate-700">{{ formatNumber(loc.purchased_kg) }} kg</p>
            </div>
            <div class="col-span-2">
              <span class="text-slate-500">Expenses</span>
              <p class="font-medium text-slate-700">Rs. {{ formatNumber(loc.expenses) }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="displayLocations.length === 0" class="rounded-xl border border-slate-200 bg-white p-8 text-center">
        <p class="text-slate-500">No locations found. Add locations from Settings to see data.</p>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'
import DashboardLoader from '../components/DashboardLoader.vue'

const authStore = useAuthStore()
const user = authStore.user

const loading = ref(true)
const dashboard = ref({
  total_bunkers: 0,
  active_bunkers: 0,
  empty_bunkers: 0,
  total_purchased_kg: 0,
  total_sold_kg: 0,
  total_available_kg: 0,
  locations: [],
})
const seasons = ref([])
const selectedSeason = ref('all')

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const displayLocations = computed(() => {
  if (dashboard.value.locations && dashboard.value.locations.length > 0) {
    return dashboard.value.locations
  }
  return []
})

const stockPercentage = computed(() => {
  const total = dashboard.value.total_sold_kg + dashboard.value.total_available_kg
  if (total === 0) return 0
  return Math.round((dashboard.value.total_sold_kg / total) * 100)
})

const formatNumber = (val) => {
  if (val === null || val === undefined) return '0'
  const num = Number(val)
  if (isNaN(num)) return '0'
  return num.toLocaleString()
}

const fetchDashboardData = async () => {
  loading.value = true
  try {
    const params = { season_id: selectedSeason.value }
    const { data } = await axios.get('/dashboard', {
      headers: getAuthHeader(),
      params
    })
    dashboard.value = data
  } catch (err) {
    console.error('Failed to fetch dashboard:', err)
  } finally {
    // Small delay for smoother transition
    setTimeout(() => {
      loading.value = false
    }, 500)
  }
}

const fetchSeasons = async () => {
  try {
    const { data } = await axios.get('/seasons', {
      headers: getAuthHeader()
    })
    seasons.value = data
    const current = data.find(s => s.is_current)
    if (current) selectedSeason.value = current.id
  } catch (err) {
    console.error('Failed to fetch seasons:', err)
  }
}

onMounted(async () => {
  await fetchSeasons()
  await fetchDashboardData()
})
</script>