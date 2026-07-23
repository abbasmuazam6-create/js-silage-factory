<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-3">
    <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-lg bg-white shadow-xl">
      <!-- ===== HEADER ===== -->
      <div class="sticky top-0 z-10 bg-white border-b border-slate-100 px-4 py-2.5 rounded-t-lg">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="h-8 w-8 rounded bg-emerald-50 flex items-center justify-center">
              <span class="text-sm">🧾</span>
            </div>
            <div>
              <h3 class="text-sm font-bold text-slate-800">{{ sale?.invoice_number || 'Invoice' }}</h3>
              <p class="text-[10px] text-slate-500">{{ formatDate(sale?.date) }}</p>
            </div>
          </div>
          <div class="flex items-center gap-2">
            <span 
              class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold capitalize"
              :class="{
                'bg-blue-50 text-blue-700': sale?.sale_type === 'open',
                'bg-emerald-50 text-emerald-700': sale?.sale_type === 'bags',
                'bg-purple-50 text-purple-700': sale?.sale_type === 'bales'
              }"
            >
              {{ sale?.sale_type || 'N/A' }}
            </span>
            <button 
              @click="$emit('close')" 
              class="text-slate-400 hover:text-slate-600 transition-colors text-base leading-none"
            >
              ✕
            </button>
          </div>
        </div>
      </div>

      <!-- ===== CONTENT ===== -->
      <div v-if="sale" class="px-4 py-3 space-y-3.5">
        
        <!-- ===== INFO GRID ===== -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2.5">
          <div>
            <label class="block text-[9px] font-semibold uppercase tracking-wide text-slate-500">Customer</label>
            <p class="text-xs font-semibold text-slate-800 mt-0.5 truncate">{{ sale.customer?.name || 'Unknown' }}</p>
          </div>
          <div>
            <label class="block text-[9px] font-semibold uppercase tracking-wide text-slate-500">Bunker</label>
            <p class="text-xs font-semibold text-slate-800 mt-0.5 truncate">{{ sale.bunker?.name || 'Unknown' }}</p>
            <p class="text-[10px] text-slate-500 truncate">{{ sale.bunker?.location || 'No location' }}</p>
          </div>
          <div>
            <label class="block text-[9px] font-semibold uppercase tracking-wide text-slate-500">Season</label>
            <p class="text-xs font-semibold text-slate-800 mt-0.5 truncate">{{ sale.season?.name || 'N/A' }}</p>
          </div>
          <div>
            <label class="block text-[9px] font-semibold uppercase tracking-wide text-slate-500">Payment</label>
            <p class="text-xs font-semibold text-slate-800 mt-0.5 truncate">{{ sale.payment_type?.name || 'N/A' }}</p>
          </div>
        </div>

        <!-- ===== STATUS BADGE ===== -->
        <div class="flex items-center gap-2 bg-slate-50 rounded px-3 py-1.5">
          <span class="text-[10px] font-medium text-slate-500">Status</span>
          <span
            class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold"
            :class="{
              'bg-emerald-100 text-emerald-700': sale.due_amount <= 0,
              'bg-amber-100 text-amber-700': sale.due_amount > 0 && sale.paid_amount > 0,
              'bg-rose-100 text-rose-700': sale.due_amount > 0 && sale.paid_amount <= 0
            }"
          >
            {{ sale.due_amount <= 0 ? 'Paid ✓' : sale.due_amount > 0 && sale.paid_amount > 0 ? 'Partial' : 'Due' }}
          </span>
          <span class="text-[10px] text-slate-500 ml-auto font-medium">
            {{ sale.due_amount > 0 ? 'Balance: Rs. ' + numberFormat(sale.due_amount) : 'Fully Paid' }}
          </span>
        </div>

        <!-- ===== SALE ITEMS TABLE ===== -->
        <div>
          <h4 class="text-[9px] font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Sale Items</h4>
          <div class="border border-slate-200 rounded overflow-hidden">
            <table class="w-full text-[11px]">
              <thead class="bg-slate-50">
                <tr>
                  <th class="text-left px-3 py-1.5 text-[9px] font-semibold uppercase tracking-wide text-slate-500">Item</th>
                  <th v-if="sale.sale_type !== 'open'" class="text-right px-3 py-1.5 text-[9px] font-semibold uppercase tracking-wide text-slate-500">Units</th>
                  <th class="text-right px-3 py-1.5 text-[9px] font-semibold uppercase tracking-wide text-slate-500">KG</th>
                  <th class="text-right px-3 py-1.5 text-[9px] font-semibold uppercase tracking-wide text-slate-500">Price/KG</th>
                  <th class="text-right px-3 py-1.5 text-[9px] font-semibold uppercase tracking-wide text-slate-500">Total</th>
                </tr>
              </thead>
              <tbody>
                <tr class="border-t border-slate-100 hover:bg-slate-50/50">
                  <td class="px-3 py-2 text-slate-800 font-medium">
                    Silage
                    <span class="text-[10px] text-slate-500 ml-1">({{ sale.sale_type }})</span>
                  </td>
                  <td v-if="sale.sale_type !== 'open'" class="px-3 py-2 text-right font-semibold text-slate-800">
                    {{ sale.units > 0 ? numberFormat(sale.units) : '-' }}
                  </td>
                  <td class="px-3 py-2 text-right font-semibold text-slate-800">{{ numberFormat(sale.weight_kg) }}</td>
                  <td class="px-3 py-2 text-right font-medium text-slate-700">Rs. {{ numberFormat(sale.price_per_kg) }}</td>
                  <td class="px-3 py-2 text-right font-bold text-slate-800">Rs. {{ numberFormat(sale.total_price) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ===== TOTALS ===== -->
        <div>
          <h4 class="text-[9px] font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Totals</h4>
          <div class="bg-slate-50 rounded p-3 space-y-0.5">
            <div class="flex justify-between text-[11px]">
              <span class="text-slate-600">Gross Total</span>
              <span class="font-semibold text-slate-800">Rs. {{ numberFormat(sale.total_price) }}</span>
            </div>
            <div v-if="(sale.discount || 0) > 0" class="flex justify-between text-[11px]">
              <span class="text-slate-600">Discount</span>
              <span class="font-semibold text-rose-600">− Rs. {{ numberFormat(sale.discount) }}</span>
            </div>
            <div class="border-t border-slate-200 my-1"></div>
            <div class="flex justify-between text-sm">
              <span class="font-bold text-slate-800">Net Total</span>
              <span class="font-bold text-emerald-600">Rs. {{ numberFormat(sale.total_price - (sale.discount || 0)) }}</span>
            </div>
            <div class="flex justify-between text-[11px]">
              <span class="text-slate-600">Paid Amount</span>
              <span class="font-semibold text-slate-800">Rs. {{ numberFormat(sale.paid_amount || 0) }}</span>
            </div>
            <div class="flex justify-between text-[11px]">
              <span class="text-slate-600">Balance Due</span>
              <span class="font-bold" :class="sale.due_amount > 0 ? 'text-rose-600' : 'text-emerald-600'">
                Rs. {{ numberFormat(sale.due_amount || 0) }}
              </span>
            </div>
          </div>
        </div>

        <!-- ===== PROFIT ANALYSIS ===== -->
        <div>
          <h4 class="text-[9px] font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Profit Analysis</h4>
          <div class="grid grid-cols-2 gap-2">
            <div class="bg-slate-50 rounded p-2.5 text-center">
              <label class="block text-[9px] font-medium text-slate-500">Cost per KG</label>
              <p class="text-xs font-bold text-slate-800 mt-0.5">Rs. {{ numberFormat(sale.cost_per_kg_at_sale) }}</p>
            </div>
            <div class="bg-slate-50 rounded p-2.5 text-center">
              <label class="block text-[9px] font-medium text-slate-500">Total Profit</label>
              <p class="text-xs font-bold mt-0.5" :class="sale.profit >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                Rs. {{ numberFormat(sale.profit) }}
                <span class="text-[10px] font-medium" :class="sale.profit >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                  ({{ sale.profit >= 0 ? '+' : '' }}{{ sale.weight_kg > 0 ? ((sale.profit / sale.weight_kg) || 0).toFixed(2) : '0.00' }}/KG)
                </span>
              </p>
            </div>
          </div>
        </div>

        <!-- ===== ACTIONS ===== -->
        <div class="border-t border-slate-100 pt-3 flex flex-col sm:flex-row justify-end gap-1.5">
          <button 
            @click="printInvoice" 
            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-medium rounded transition-colors flex items-center justify-center gap-1.5 shadow-sm hover:shadow"
          >
            <span>🖨️</span> Print Invoice
          </button>
          <button 
            @click="$emit('close')" 
            class="px-4 py-1.5 border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-medium rounded transition-colors"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  visible: Boolean,
  sale: Object,
})

const emit = defineEmits(['close', 'saved'])

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const numberFormat = (val) => {
  if (val === null || val === undefined || val === '') return '0'
  const num = Number(val)
  if (isNaN(num)) return '0'
  return num.toLocaleString('en-IN', {
    maximumFractionDigits: 2,
    minimumFractionDigits: 0
  })
}

const formatDate = (date) => {
  if (!date) return '-'
  const d = new Date(date)
  return d.toLocaleDateString('en-IN', {
    day: '2-digit',
    month: 'short',
    year: 'numeric'
  })
}

const printInvoice = async () => {
  if (!props.sale?.id) {
    alert('Sale ID not found')
    return
  }

  try {
    const response = await axios.get(`/sales/${props.sale.id}/invoice`, {
      headers: getAuthHeader()
    })
    const newTab = window.open('', '_blank')
    if (newTab) {
      newTab.document.write(response.data)
      newTab.document.close()
    } else {
      alert('Please allow popups for this site')
    }
  } catch (err) {
    console.error('Failed to load invoice:', err)
    alert('Failed to generate invoice')
  }
}
</script>

<style scoped>
/* Smooth scroll */
.max-h-\[90vh\] {
  scroll-behavior: smooth;
}

/* Subtle hover for table */
tbody tr:hover {
  background-color: #f8fafc;
}

/* Status pulse for Due */
.bg-rose-100.text-rose-700 {
  animation: pulse-soft 2s infinite;
}

@keyframes pulse-soft {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.6; }
}

/* Truncate long text */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}
</style>