<template>
  <div>
    <!-- Filters -->
    <div class="flex flex-wrap items-center gap-3 mb-4">
      <select
        v-model="filters.season_id"
        class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
        @change="fetchData"
      >
        <option value="all">All Seasons</option>
        <option v-for="season in seasons" :key="season.id" :value="season.id">
          {{ season.name }}
        </option>
      </select>
      <select
        v-model="filters.location"
        class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm"
        @change="fetchData"
      >
        <option value="all">All Locations</option>
        <option v-for="loc in locations" :key="loc" :value="loc">
          {{ loc }}
        </option>
      </select>
      <button @click="fetchData" class="btn-primary px-3 py-1.5 text-sm">🔄 Refresh</button>
      <button @click="exportPDF" class="btn-outline px-3 py-1.5 text-sm">📄 PDF</button>
    </div>

    <div v-if="loading" class="py-8 text-center text-slate-500">Loading...</div>

    <div v-else-if="data && data.bunkers && data.bunkers.length > 0">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 mb-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 text-center">
          <p class="text-[10px] text-slate-500">Bunkers</p>
          <p class="text-xl font-bold text-slate-800">{{ data.total_bunkers }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 text-center">
          <p class="text-[10px] text-slate-500">Purchased</p>
          <p class="text-xl font-bold text-slate-800">{{ numberFormat(data.total_purchased) }} kg</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 text-center">
          <p class="text-[10px] text-slate-500">Sold</p>
          <p class="text-xl font-bold text-slate-800">{{ numberFormat(data.total_sold) }} kg</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 text-center">
          <p class="text-[10px] text-slate-500">Available</p>
          <p class="text-xl font-bold text-slate-800">{{ numberFormat(data.total_available) }} kg</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 text-center">
          <p class="text-[10px] text-slate-500">Profit</p>
          <p class="text-xl font-bold text-slate-800">Rs. {{ numberFormat(data.total_profit) }}</p>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm border border-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-3 py-2 text-left font-semibold text-slate-700 text-xs uppercase">Bunker</th>
              <th class="px-3 py-2 text-left font-semibold text-slate-700 text-xs uppercase">Location</th>
              <th class="px-3 py-2 text-left font-semibold text-slate-700 text-xs uppercase">Status</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Purchased</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Sold</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Available</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Shrinkage</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Cost/KG</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Profit</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="item in data.bunkers"
              :key="item.id"
              class="border-t border-slate-100 hover:bg-slate-50 cursor-pointer"
              @click="viewBunker(item.id)"
            >
              <td class="px-3 py-2 font-medium">{{ item.name }}</td>
              <td class="px-3 py-2">{{ item.location || '-' }}</td>
              <td class="px-3 py-2">
                <span class="px-2 py-0.5 rounded-full text-xs" :class="item.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
                  {{ item.status }}
                </span>
              </td>
              <td class="px-3 py-2 text-right">{{ numberFormat(item.purchased) }}</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(item.sold) }}</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(item.available) }}</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(item.shrinkage_kg) }} kg ({{ numberFormat(item.shrinkage_percentage) }}%)</td>
              <td class="px-3 py-2 text-right">Rs. {{ numberFormat(item.cost_per_kg) }}</td>
              <td class="px-3 py-2 text-right font-bold" :class="item.profit >= 0 ? 'text-green-600' : 'text-red-600'">
                Rs. {{ numberFormat(item.profit) }}
              </td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-200">
            <tr>
              <td class="px-3 py-2" colspan="3">TOTAL</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(data.total_purchased) }}</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(data.total_sold) }}</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(data.total_available) }}</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(data.total_shrinkage) }} kg</td>
              <td class="px-3 py-2"></td>
              <td class="px-3 py-2 text-right" :class="data.total_profit >= 0 ? 'text-green-700' : 'text-red-700'">
                Rs. {{ numberFormat(data.total_profit) }}
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <div v-else class="py-8 text-center text-slate-500">No data found.</div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { fetchSeasons, getCurrentSeason } from '../../utils/seasons'
import { useTabsStore } from '../../stores/tabs'

const loading = ref(false)
const data = ref(null)
const seasons = ref([])
const locations = ref([])
const filterDisplay = ref({ season: 'All', location: 'All' })

const filters = ref({
  season_id: 'all',
  location: 'all',
})

const tabsStore = useTabsStore()

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const numberFormat = (val) => {
  if (val === null || val === undefined) return '0'
  const num = Number(val)
  if (isNaN(num)) return '0'
  return num.toLocaleString()
}

const fetchData = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.season_id !== 'all') params.season_id = filters.value.season_id
    if (filters.value.location !== 'all') params.location = filters.value.location

    const { data: response } = await axios.get('/reports/bunkers', {
      headers: getAuthHeader(),
      params
    })
    data.value = response
    // ✅ Store filter names for display
    filterDisplay.value = {
      season: response.filter_season || 'All',
      location: response.filter_location || 'All'
    }
  } catch (err) {
    console.error('Failed to fetch bunkers report:', err)
  } finally {
    loading.value = false
  }
}

const exportPDF = async () => {
  try {
    const params = new URLSearchParams()
    if (filters.value.season_id !== 'all') params.append('season_id', filters.value.season_id)
    if (filters.value.location !== 'all') params.append('location', filters.value.location)

    const response = await axios.get(`/reports/bunkers/export?${params.toString()}`, {
      headers: getAuthHeader()
    })

    const newTab = window.open('', '_blank')
    if (newTab) {
      newTab.document.write(response.data)
      newTab.document.close()
    }
  } catch (error) {
    console.error('Failed to export PDF:', error)
    alert('Failed to generate PDF')
  }
}

const loadSeasons = async () => {
  const data = await fetchSeasons()
  seasons.value = data
  const current = getCurrentSeason(data)
  if (current) filters.value.season_id = current.id
}

const loadLocations = async () => {
  try {
    const { data } = await axios.get('/locations', {
      headers: getAuthHeader()
    })
    locations.value = data.map(l => l.name)
  } catch (err) {
    console.error('Failed to fetch locations:', err)
  }
}

onMounted(async () => {
  await loadSeasons()
  await loadLocations()
  await fetchData()
})
</script>