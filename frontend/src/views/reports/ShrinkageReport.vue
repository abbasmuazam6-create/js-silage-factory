<template>
  <div class="space-y-6">
    <!-- Report Header -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl shadow-lg p-6 text-white">
      <div class="flex flex-wrap items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold">💧 Shrinkage Report</h2>
          <p class="text-primary-100 text-sm mt-0.5">Moisture loss analysis across bunkers</p>
        </div>
        <div class="text-right">
          <p class="text-lg font-bold">{{ businessName || 'JS Silage & Wanda Factory' }}</p>
          <p class="text-primary-200 text-sm">Generated: {{ currentDate }}</p>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
      <div class="flex flex-wrap items-end gap-4">
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Season</label>
          <select v-model="filters.season_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm min-w-[140px] bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent" @change="fetchData">
            <option value="all">All Seasons</option>
            <option v-for="season in seasons" :key="season.id" :value="season.id">{{ season.name }}</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Location</label>
          <select v-model="filters.location" class="rounded-lg border border-slate-300 px-3 py-2 text-sm min-w-[140px] bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent" @change="fetchData">
            <option value="all">All Locations</option>
            <option v-for="loc in locations" :key="loc" :value="loc">{{ loc }}</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button @click="fetchData" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-5 py-2 rounded-lg text-sm transition shadow-sm">Refresh</button>
          <button @click="exportPDF" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg text-sm transition shadow-sm">PDF</button>
        </div>
      </div>
    </div>

    <!-- Professional Loading -->
    <div v-if="loading" class="bg-white rounded-xl border border-slate-200 shadow-sm p-12">
      <div class="flex flex-col items-center justify-center space-y-4">
        <div class="relative w-16 h-16">
          <div class="absolute inset-0 border-4 border-slate-200 rounded-full"></div>
          <div class="absolute inset-0 border-4 border-primary-600 rounded-full animate-spin" style="border-top-color: transparent; border-right-color: transparent;"></div>
        </div>
        <div class="text-center">
          <p class="text-slate-700 font-medium">Loading Shrinkage Report...</p>
          <p class="text-slate-400 text-sm mt-1">Please wait while we prepare your report</p>
        </div>
      </div>
    </div>

    <!-- Content -->
    <template v-else-if="data && data.bunkers && data.bunkers.length > 0">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition">
          <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Purchased</p>
          <p class="text-2xl font-bold text-blue-600 mt-1">{{ numberFormat(data.total_purchased) }} kg</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition">
          <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Sold</p>
          <p class="text-2xl font-bold text-emerald-600 mt-1">{{ numberFormat(data.total_sold) }} kg</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition">
          <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Shrinkage</p>
          <p class="text-2xl font-bold text-red-600 mt-1">{{ numberFormat(data.total_shrinkage) }} kg</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition">
          <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Avg Shrinkage</p>
          <p class="text-2xl font-bold text-amber-600 mt-1">{{ numberFormat(data.avg_shrinkage) }}%</p>
        </div>
      </div>

      <!-- Chart Section -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-base font-semibold text-slate-700">📊 Shrinkage by Bunker</h4>
          <div class="flex items-center gap-4 text-xs">
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-amber-500"></span> < 10%</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-500"></span> > 10%</span>
          </div>
        </div>

        <div class="relative w-full" style="height: 380px;">
          <div class="absolute left-0 top-0 bottom-0 flex flex-col justify-between text-xs text-slate-400 pr-2" style="width: 40px; padding-bottom: 30px;">
            <span>Max</span><span>20</span><span>15</span><span>10</span><span>5</span><span>0</span>
          </div>
          <div class="absolute left-12 right-0 top-0 bottom-0" style="padding-bottom: 30px;">
            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none" style="padding-bottom: 30px;">
              <div class="border-b border-slate-100"></div>
              <div class="border-b border-slate-100"></div>
              <div class="border-b border-slate-100"></div>
              <div class="border-b border-slate-100"></div>
              <div class="border-b border-slate-100"></div>
              <div class="border-b border-slate-100"></div>
            </div>
            <div class="relative w-full h-full flex items-end gap-3" style="padding-bottom: 30px;">
              <div v-for="item in data.bunkers" :key="item.id" class="flex-1 flex flex-col items-center h-full justify-end group min-w-[45px]">
                <div class="relative w-full max-w-16 flex flex-col items-center h-full justify-end">
                  <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-slate-800 text-white text-xs rounded-lg px-3 py-1.5 mb-1 whitespace-nowrap shadow-lg z-10 pointer-events-none">
                    <div class="font-bold">{{ item.name }}</div>
                    <div class="text-slate-300 text-[10px]">{{ item.location || 'No location' }}</div>
                    <div>Shrinkage: {{ numberFormat(item.shrinkage_kg) }} kg</div>
                    <div>Percentage: {{ numberFormat(item.shrinkage_percentage) }}%</div>
                  </div>
                  <div class="w-full rounded-t-lg transition-all duration-700 cursor-pointer shadow-sm" :style="{ height: getBarHeight(item.shrinkage_kg, data.max_shrinkage), backgroundColor: item.shrinkage_percentage > 10 ? '#ef4444' : '#f59e0b', minHeight: '6px' }">
                    <div class="absolute inset-0 rounded-t-lg opacity-0 group-hover:opacity-20 transition" style="background: linear-gradient(to top, rgba(255,255,255,0.4), transparent)"></div>
                  </div>
                  <div class="mt-1 text-center w-full">
                    <p class="text-[10px] font-semibold text-slate-700 truncate w-full">{{ item.name }}</p>
                    <p class="text-[8px] text-slate-400 truncate w-full">{{ item.location || 'No location' }}</p>
                  </div>
                  <p class="text-[9px] font-bold mt-0.5" :class="item.shrinkage_percentage > 10 ? 'text-red-600' : 'text-amber-600'">{{ numberFormat(item.shrinkage_percentage) }}%</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200">
                <th class="px-5 py-3.5 text-left font-semibold text-slate-700 text-xs uppercase tracking-wider">Bunker</th>
                <th class="px-5 py-3.5 text-left font-semibold text-slate-700 text-xs uppercase tracking-wider">Location</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-700 text-xs uppercase tracking-wider">Purchased</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-700 text-xs uppercase tracking-wider">Sold</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-700 text-xs uppercase tracking-wider">Shrinkage</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-700 text-xs uppercase tracking-wider">%</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in data.bunkers" :key="item.id" class="border-b border-slate-100 hover:bg-slate-50 transition">
                <td class="px-5 py-3 font-medium text-slate-700">{{ item.name }}</td>
                <td class="px-5 py-3 text-slate-600">{{ item.location || '-' }}</td>
                <td class="px-5 py-3 text-right text-slate-600">{{ numberFormat(item.purchased) }}</td>
                <td class="px-5 py-3 text-right text-slate-600">{{ numberFormat(item.sold) }}</td>
                <td class="px-5 py-3 text-right font-bold text-red-600">{{ numberFormat(item.shrinkage_kg) }}</td>
                <td class="px-5 py-3 text-right font-bold" :class="item.shrinkage_percentage > 10 ? 'text-red-600' : 'text-amber-600'">{{ numberFormat(item.shrinkage_percentage) }}%</td>
              </tr>
            </tbody>
            <tfoot class="bg-slate-50 border-t-2 border-slate-200">
              <tr>
                <td class="px-5 py-3.5 font-bold text-slate-700" colspan="2">TOTAL</td>
                <td class="px-5 py-3.5 text-right font-bold text-slate-700">{{ numberFormat(data.total_purchased) }}</td>
                <td class="px-5 py-3.5 text-right font-bold text-slate-700">{{ numberFormat(data.total_sold) }}</td>
                <td class="px-5 py-3.5 text-right font-bold text-red-700">{{ numberFormat(data.total_shrinkage) }}</td>
                <td class="px-5 py-3.5 text-right font-bold text-amber-700">{{ numberFormat(data.avg_shrinkage) }}%</td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <div class="text-center text-xs text-slate-400 border-t border-slate-200 pt-4">Report generated on {{ currentDate }}</div>
    </template>

    <div v-else class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center">
      <p class="text-lg font-medium text-slate-700">No data available</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { fetchSeasons, getCurrentSeason } from '../../utils/seasons'

const loading = ref(false)
const data = ref(null)
const seasons = ref([])
const locations = ref([])
const businessName = ref('JS Silage & Wanda Factory')

const currentDate = computed(() => {
  return new Date().toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' })
})

const filters = ref({ season_id: 'all', location: 'all' })

const getAuthHeader = () => { const token = localStorage.getItem('token'); return { Authorization: `Bearer ${token}` } }
const numberFormat = (val) => { if (val === null || val === undefined) return '0'; const num = Number(val); if (isNaN(num)) return '0'; return num.toLocaleString() }

const getBarHeight = (value, max) => {
  if (max === 0 || max === null || max === undefined) return '4%'
  const absValue = Math.abs(value); const absMax = Math.abs(max)
  if (absMax === 0) return '4%'
  let percentage = (absValue / absMax) * 85
  if (absValue > 0 && percentage < 3) percentage = 3
  return Math.min(percentage, 85) + '%'
}

const fetchData = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.season_id !== 'all') params.season_id = filters.value.season_id
    if (filters.value.location !== 'all') params.location = filters.value.location
    const response = await axios.get('/reports/shrinkage', { headers: getAuthHeader(), params })
    data.value = response.data
  } catch (err) { console.error('Failed to fetch shrinkage data:', err) } finally { loading.value = false }
}

const exportPDF = () => { alert('PDF Export coming soon!') }

const loadSeasons = async () => {
  const data = await fetchSeasons(); seasons.value = data
  const current = getCurrentSeason(data); if (current) filters.value.season_id = current.id
}

const loadLocations = async () => {
  try { const { data } = await axios.get('/locations', { headers: getAuthHeader() }); locations.value = data.map(l => l.name) } catch (err) { console.error('Failed to fetch locations:', err) }
}

onMounted(async () => { await loadSeasons(); await loadLocations(); await fetchData() })
</script>