<template>
  <div class="space-y-6">
    <!-- Report Header -->
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 rounded-xl shadow-lg p-6 text-white">
      <div class="flex flex-wrap items-center justify-between">
        <div>
          <h2 class="text-2xl font-bold">Profit & Loss Report</h2>
          <p class="text-primary-100 text-sm mt-0.5">Financial performance across all bunkers</p>
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
          <select
            v-model="filters.season_id"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm min-w-[140px] bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @change="fetchData"
          >
            <option value="all">All Seasons</option>
            <option v-for="season in seasons" :key="season.id" :value="season.id">
              {{ season.name }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Location</label>
          <select
            v-model="filters.location"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm min-w-[140px] bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @change="fetchData"
          >
            <option value="all">All Locations</option>
            <option v-for="loc in locations" :key="loc" :value="loc">
              {{ loc }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Date From</label>
          <input
            v-model="filters.date_from"
            type="date"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @change="fetchData"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-600 mb-1">Date To</label>
          <input
            v-model="filters.date_to"
            type="date"
            class="rounded-lg border border-slate-300 px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-primary-500 focus:border-transparent"
            @change="fetchData"
          />
        </div>
        <div class="flex gap-2">
          <button @click="fetchData" class="bg-primary-600 hover:bg-primary-700 text-white font-medium px-5 py-2 rounded-lg text-sm transition shadow-sm">
            Refresh
          </button>
          <button @click="resetFilters" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-4 py-2 rounded-lg text-sm transition">
            Reset
          </button>
          <button @click="exportPDF" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2 rounded-lg text-sm transition shadow-sm">
            PDF
          </button>
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
          <p class="text-slate-700 font-medium">Loading Report Data...</p>
          <p class="text-slate-400 text-sm mt-1">Please wait while we prepare your report</p>
        </div>
      </div>
    </div>

    <!-- Content -->
    <template v-else-if="data && data.bunkers && data.bunkers.length > 0">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition">
          <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Revenue</p>
          <p class="text-2xl font-bold text-emerald-600 mt-1">Rs. {{ numberFormat(data.total_revenue) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition">
          <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Cost</p>
          <p class="text-2xl font-bold text-rose-600 mt-1">Rs. {{ numberFormat(data.total_cost) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition">
          <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Profit</p>
          <p class="text-2xl font-bold mt-1" :class="data.total_profit >= 0 ? 'text-indigo-600' : 'text-rose-600'">
            Rs. {{ numberFormat(data.total_profit) }}
          </p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition">
          <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">Margin</p>
          <p class="text-2xl font-bold text-amber-600 mt-1">{{ profitMargin }}%</p>
        </div>
      </div>

      <!-- Chart Section - FULL HEIGHT BARS -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6">
        <div class="flex items-center justify-between mb-4">
          <h4 class="text-base font-semibold text-slate-700">Profit by Bunker</h4>
          <div class="flex items-center gap-4 text-xs">
            <span class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded bg-indigo-500"></span> Profit
            </span>
            <span class="flex items-center gap-1.5">
              <span class="w-3 h-3 rounded bg-rose-500"></span> Loss
            </span>
          </div>
        </div>

        <!-- Chart Container with fixed height -->
        <div class="relative w-full" style="height: 380px;">
          
          <!-- Y-axis Labels -->
          <div class="absolute left-0 top-0 bottom-0 flex flex-col justify-between text-xs text-slate-400 pr-2" style="width: 40px; padding-bottom: 30px;">
            <span>Max</span>
            <span>20</span>
            <span>15</span>
            <span>10</span>
            <span>5</span>
            <span>0</span>
          </div>

          <!-- Chart Area (right of labels) -->
          <div class="absolute left-12 right-0 top-0 bottom-0" style="padding-bottom: 30px;">
            
            <!-- Grid Lines -->
            <div class="absolute inset-0 flex flex-col justify-between pointer-events-none" style="padding-bottom: 30px;">
              <div class="border-b border-slate-100"></div>
              <div class="border-b border-slate-100"></div>
              <div class="border-b border-slate-100"></div>
              <div class="border-b border-slate-100"></div>
              <div class="border-b border-slate-100"></div>
              <div class="border-b border-slate-100"></div>
            </div>

            <!-- Bars Container - flex-end with full height -->
            <div class="relative w-full h-full flex items-end gap-3" style="padding-bottom: 30px;">
              <div
                v-for="item in data.bunkers"
                :key="item.id"
                class="flex-1 flex flex-col items-center h-full justify-end group min-w-[45px]"
              >
                <div class="relative w-full max-w-16 flex flex-col items-center h-full justify-end">
                  
                  <!-- Tooltip on Hover -->
                  <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-slate-800 text-white text-xs rounded-lg px-3 py-1.5 mb-1 whitespace-nowrap shadow-lg z-10 pointer-events-none">
                    <div class="font-bold">{{ item.name }}</div>
                    <div class="text-slate-300 text-[10px]">{{ item.location || 'No location' }}</div>
                    <div>Profit: Rs. {{ numberFormat(item.profit) }}</div>
                    <div>Revenue: Rs. {{ numberFormat(item.revenue) }}</div>
                    <div class="text-slate-300 text-[10px]">Cost: Rs. {{ numberFormat(item.cost) }}</div>
                  </div>

                  <!-- Bar - using full height -->
                  <div
                    class="w-full rounded-t-lg transition-all duration-700 cursor-pointer shadow-sm relative"
                    :style="{
                      height: getBarHeight(item.profit, data.max_profit),
                      backgroundColor: item.profit >= 0 ? '#6366f1' : '#f43f5e',
                      minHeight: '6px',
                    }"
                  >
                    <!-- Gradient Overlay -->
                    <div
                      class="absolute inset-0 rounded-t-lg opacity-0 group-hover:opacity-20 transition"
                      :style="{
                        background: 'linear-gradient(to top, rgba(255,255,255,0.4), transparent)'
                      }"
                    ></div>
                    
                    <!-- Value inside bar for larger bars -->
                    <div 
                      v-if="Math.abs(item.profit) > 1000"
                      class="absolute -top-5 left-1/2 -translate-x-1/2 text-[9px] font-bold text-slate-700 whitespace-nowrap"
                    >
                      Rs. {{ numberFormat(item.profit) }}
                    </div>
                  </div>

                  <!-- Labels below bar -->
                  <div class="mt-1 text-center w-full">
                    <p class="text-[10px] font-semibold text-slate-700 truncate w-full">
                      {{ item.name }}
                    </p>
                    <p class="text-[8px] text-slate-400 truncate w-full">
                      {{ item.location || 'No location' }}
                    </p>
                  </div>
                  <p class="text-[9px] font-bold mt-0.5" :class="item.profit >= 0 ? 'text-indigo-600' : 'text-rose-600'">
                    Rs. {{ numberFormat(item.profit) }}
                  </p>
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
                <th class="px-5 py-3.5 text-right font-semibold text-slate-700 text-xs uppercase tracking-wider">Revenue</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-700 text-xs uppercase tracking-wider">Cost</th>
                <th class="px-5 py-3.5 text-right font-semibold text-slate-700 text-xs uppercase tracking-wider">Profit</th>
                <th class="px-5 py-3.5 text-center font-semibold text-slate-700 text-xs uppercase tracking-wider">Margin</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in data.bunkers" :key="item.id" class="border-b border-slate-100 hover:bg-slate-50 transition">
                <td class="px-5 py-3 font-medium text-slate-700">{{ item.name }}</td>
                <td class="px-5 py-3 text-slate-600">{{ item.location || '-' }}</td>
                <td class="px-5 py-3 text-right text-slate-600">{{ numberFormat(item.purchased) }}</td>
                <td class="px-5 py-3 text-right text-slate-600">{{ numberFormat(item.sold) }}</td>
                <td class="px-5 py-3 text-right font-medium text-emerald-600">Rs. {{ numberFormat(item.revenue) }}</td>
                <td class="px-5 py-3 text-right font-medium text-rose-600">Rs. {{ numberFormat(item.cost) }}</td>
                <td class="px-5 py-3 text-right font-bold" :class="item.profit >= 0 ? 'text-indigo-600' : 'text-rose-600'">
                  Rs. {{ numberFormat(item.profit) }}
                </td>
                <td class="px-5 py-3 text-center">
                  <span
                    class="inline-block px-2.5 py-1 rounded-full text-xs font-medium"
                    :class="getMarginClass(item.profit, item.revenue)"
                  >
                    {{ getMarginPercentage(item.profit, item.revenue) }}%
                  </span>
                </td>
              </tr>
            </tbody>
            <tfoot class="bg-slate-50 border-t-2 border-slate-200">
              <tr>
                <td class="px-5 py-3.5 font-bold text-slate-700" colspan="2">TOTAL</td>
                <td class="px-5 py-3.5 text-right font-bold text-slate-700">{{ numberFormat(data.total_purchased) }}</td>
                <td class="px-5 py-3.5 text-right font-bold text-slate-700">{{ numberFormat(data.total_sold) }}</td>
                <td class="px-5 py-3.5 text-right font-bold text-emerald-700">Rs. {{ numberFormat(data.total_revenue) }}</td>
                <td class="px-5 py-3.5 text-right font-bold text-rose-700">Rs. {{ numberFormat(data.total_cost) }}</td>
                <td class="px-5 py-3.5 text-right font-bold" :class="data.total_profit >= 0 ? 'text-indigo-700' : 'text-rose-700'">
                  Rs. {{ numberFormat(data.total_profit) }}
                </td>
                <td class="px-5 py-3.5 text-center">
                  <span
                    class="inline-block px-2.5 py-1 rounded-full text-xs font-bold"
                    :class="getMarginClass(data.total_profit, data.total_revenue)"
                  >
                    {{ profitMargin }}%
                  </span>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>

      <!-- Footer -->
      <div class="text-center text-xs text-slate-400 border-t border-slate-200 pt-4">
        Report generated on {{ currentDate }} | All amounts are in Rupees (Rs.)
      </div>
    </template>

    <!-- Empty State -->
    <div v-else-if="data && data.bunkers && data.bunkers.length === 0" class="bg-white rounded-xl border border-slate-200 shadow-sm p-12 text-center">
      <p class="text-lg font-medium text-slate-700">No data available</p>
      <p class="text-sm text-slate-500 mt-1">Try changing the filters or add some bunkers with data.</p>
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
  return new Date().toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
})

const profitMargin = computed(() => {
  if (!data.value || data.value.total_revenue === 0) return 0
  return ((data.value.total_profit / data.value.total_revenue) * 100).toFixed(1)
})

const filters = ref({
  season_id: 'all',
  location: 'all',
  date_from: '',
  date_to: '',
})

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

// ✅ FIXED: Bar height using 85% of available chart space
const getBarHeight = (value, max) => {
  if (max === 0 || max === null || max === undefined) return '4%'
  
  const absValue = Math.abs(value)
  const absMax = Math.abs(max)
  
  if (absMax === 0) return '4%'
  
  // Use 85% of available height for max bar
  let percentage = (absValue / absMax) * 85
  
  // Minimum height for visibility
  if (absValue > 0 && percentage < 3) {
    percentage = 3
  }
  
  return Math.min(percentage, 85) + '%'
}

const getMarginPercentage = (profit, revenue) => {
  if (revenue === 0 || revenue === null) return 0
  return ((profit / revenue) * 100).toFixed(1)
}

const getMarginClass = (profit, revenue) => {
  const margin = revenue === 0 ? 0 : (profit / revenue) * 100
  if (margin > 20) return 'bg-emerald-100 text-emerald-700'
  if (margin > 0) return 'bg-indigo-100 text-indigo-700'
  if (margin > -10) return 'bg-amber-100 text-amber-700'
  return 'bg-rose-100 text-rose-700'
}

const fetchData = async () => {
  loading.value = true
  data.value = null
  try {
    const params = {}
    if (filters.value.season_id !== 'all') params.season_id = filters.value.season_id
    if (filters.value.location !== 'all') params.location = filters.value.location
    if (filters.value.date_from) params.date_from = filters.value.date_from
    if (filters.value.date_to) params.date_to = filters.value.date_to

    const response = await axios.get('/reports/profit-loss', {
      headers: getAuthHeader(),
      params
    })
    data.value = response.data
    console.log('✅ Data loaded:', data.value)
  } catch (err) {
    console.error('❌ Failed to fetch profit/loss data:', err)
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = {
    season_id: 'all',
    location: 'all',
    date_from: '',
    date_to: '',
  }
  const current = getCurrentSeason(seasons.value)
  if (current) filters.value.season_id = current.id
  fetchData()
}

const exportPDF = () => {
  alert('PDF Export coming soon!')
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