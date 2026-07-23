<template>
  <div>
    <div class="flex flex-wrap items-center gap-3 mb-4">
      <button @click="fetchData" class="btn-primary px-3 py-1.5 text-sm">🔄 Refresh</button>
      <button @click="exportPDF" class="btn-outline px-3 py-1.5 text-sm">📄 PDF</button>
    </div>

    <div v-if="loading" class="py-8 text-center text-slate-500">Loading...</div>

    <div v-else-if="data && data.seasons && data.seasons.length > 0">
      <!-- Summary Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 text-center">
          <p class="text-[10px] text-slate-500">Seasons</p>
          <p class="text-xl font-bold text-slate-800">{{ data.total_seasons }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 text-center">
          <p class="text-[10px] text-slate-500">Bunkers</p>
          <p class="text-xl font-bold text-slate-800">{{ data.total_bunkers }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 text-center">
          <p class="text-[10px] text-slate-500">Revenue</p>
          <p class="text-xl font-bold text-slate-800">Rs. {{ numberFormat(data.total_revenue) }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 text-center">
          <p class="text-[10px] text-slate-500">Profit</p>
          <p class="text-xl font-bold text-slate-800">Rs. {{ numberFormat(data.total_profit) }}</p>
        </div>
      </div>

      <!-- Chart -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4 mb-4">
        <h4 class="text-sm font-semibold text-slate-700 mb-3">Profit by Season</h4>
        <div class="flex items-end gap-4 h-32">
          <div
            v-for="item in data.seasons"
            :key="item.id"
            class="flex-1 flex flex-col items-center h-full justify-end"
          >
            <div
              class="w-full max-w-12 rounded-t"
              :style="{
                height: getBarHeight(item.profit, data.max_profit),
                backgroundColor: item.profit >= 0 ? '#1a56db' : '#dc2626'
              }"
            ></div>
            <p class="text-xs font-medium text-slate-700 mt-1">{{ item.name }}</p>
            <p class="text-[10px] font-bold" :class="item.profit >= 0 ? 'text-blue-600' : 'text-red-600'">
              Rs. {{ numberFormat(item.profit) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full text-sm border border-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-3 py-2 text-left font-semibold text-slate-700 text-xs uppercase">Season</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Bunkers</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Purchased</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Sold</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Revenue</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Cost</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Profit</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Margin</th>
              <th class="px-3 py-2 text-right font-semibold text-slate-700 text-xs uppercase">Shrinkage</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in data.seasons" :key="item.id" class="border-t border-slate-100">
              <td class="px-3 py-2 font-medium">{{ item.name }}</td>
              <td class="px-3 py-2 text-right">{{ item.bunkers }}</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(item.purchased) }}</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(item.sold) }}</td>
              <td class="px-3 py-2 text-right">Rs. {{ numberFormat(item.revenue) }}</td>
              <td class="px-3 py-2 text-right">Rs. {{ numberFormat(item.cost) }}</td>
              <td class="px-3 py-2 text-right font-bold" :class="item.profit >= 0 ? 'text-green-600' : 'text-red-600'">
                Rs. {{ numberFormat(item.profit) }}
              </td>
              <td class="px-3 py-2 text-right">{{ numberFormat(item.margin) }}%</td>
              <td class="px-3 py-2 text-right">{{ item.shrinkage_kg ? numberFormat(item.shrinkage_kg) + ' kg (' + numberFormat(item.shrinkage_percentage) + '%)' : '-' }}</td>
            </tr>
          </tbody>
          <tfoot class="bg-slate-50 font-bold border-t-2 border-slate-200">
            <tr>
              <td class="px-3 py-2">TOTAL</td>
              <td class="px-3 py-2 text-right">{{ data.total_bunkers }}</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(data.total_purchased) }}</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(data.total_sold) }}</td>
              <td class="px-3 py-2 text-right">Rs. {{ numberFormat(data.total_revenue) }}</td>
              <td class="px-3 py-2 text-right">Rs. {{ numberFormat(data.total_cost) }}</td>
              <td class="px-3 py-2 text-right" :class="data.total_profit >= 0 ? 'text-green-700' : 'text-red-700'">
                Rs. {{ numberFormat(data.total_profit) }}
              </td>
              <td class="px-3 py-2 text-right">{{ data.total_revenue > 0 ? numberFormat((data.total_profit / data.total_revenue) * 100) : 0 }}%</td>
              <td class="px-3 py-2 text-right">{{ numberFormat(data.total_shrinkage) }} kg</td>
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

const loading = ref(false)
const data = ref(null)

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

const getBarHeight = (value, max) => {
  if (max === 0 || max === null || max === undefined) return '4%'
  const absValue = Math.abs(value)
  const absMax = Math.abs(max)
  if (absMax === 0) return '4%'
  let percentage = (absValue / absMax) * 90
  if (absValue > 0 && percentage < 3) percentage = 3
  return Math.min(percentage, 90) + '%'
}

const fetchData = async () => {
  loading.value = true
  try {
    const { data: response } = await axios.get('/reports/seasonal', {
      headers: getAuthHeader()
    })
    data.value = response
  } catch (err) {
    console.error('Failed to fetch seasonal report:', err)
  } finally {
    loading.value = false
  }
}

const exportPDF = async () => {
  try {
    const response = await axios.get('/reports/seasonal/export', {
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

onMounted(fetchData)
</script>