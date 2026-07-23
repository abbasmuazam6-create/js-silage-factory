<template>
  <div class="p-3 sm:p-4 space-y-3">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-base font-bold text-slate-700">{{ bunker?.name }}</h2>
        <p class="text-[10px] text-slate-500">{{ bunker?.location || 'No location' }} · {{ bunker?.season?.name || 'No season' }}</p>
      </div>
      <span :class="statusClass(bunker?.status)" class="rounded-full px-2 py-0.5 text-[10px] font-semibold">
        {{ bunker?.status || 'Unknown' }}
      </span>
    </div>

    <!-- Loading / Error -->
    <div v-if="loading" class="text-xs text-slate-500">Loading...</div>
    <div v-else-if="error" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ error }}</div>

    <!-- Content -->
    <template v-else-if="bunker">
      <!-- Summary Grid -->
      <div class="grid grid-cols-2 gap-2">
        <div class="rounded-lg bg-primary-50 p-2 text-center">
          <p class="text-[10px] text-slate-500">Purchased</p>
          <p class="text-sm font-bold text-primary-700">{{ numberFormat(bunker.total_purchased_kg) }} kg</p>
        </div>
        <div class="rounded-lg bg-green-50 p-2 text-center">
          <p class="text-[10px] text-slate-500">Sold</p>
          <p class="text-sm font-bold text-green-700">{{ numberFormat(bunker.total_sold_kg) }} kg</p>
        </div>
        <div class="rounded-lg bg-blue-50 p-2 text-center">
          <p class="text-[10px] text-slate-500">Available</p>
          <p class="text-sm font-bold text-blue-700">{{ numberFormat(bunker.available_weight) }} kg</p>
        </div>
        <div class="rounded-lg bg-purple-50 p-2 text-center">
          <p class="text-[10px] text-slate-500">Cost per KG</p>
          <p class="text-sm font-bold text-purple-700">Rs. {{ numberFormat(bunker.cost_per_kg) }}</p>
        </div>
      </div>

      <!-- Purchases Section -->
      <div class="rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 p-2">
          <h3 class="text-xs font-semibold text-slate-700 cursor-pointer" @click="toggleSection('purchases')">
            Purchases
          </h3>
          <button
            v-if="bunker.status !== 'empty'"
            @click="openAddPurchase"
            class="text-primary-600 hover:text-primary-800 text-sm font-bold"
          >+</button>
        </div>
        <div v-if="sections.purchases" class="p-1.5">
          <table class="w-full text-[10px]">
            <thead>
              <tr class="text-slate-500">
                <th class="px-1.5 py-1 text-left">Supplier</th>
                <th class="px-1.5 py-1 text-left">Date</th>
                <th class="px-1.5 py-1 text-right text-blue-600">KG</th>
                <th class="px-1.5 py-1 text-right text-green-600">Cost</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="purchase in bunker.grouped_purchases || []"
                :key="purchase.supplier_id + purchase.date"
                class="border-t border-slate-100 hover:bg-slate-200 cursor-pointer"
                @dblclick="editPurchaseGroup(purchase)"
              >
                <td class="px-1.5 py-1">{{ purchase.supplier_name }}</td>
                <td class="px-1.5 py-1">{{ formatDate(purchase.date) }}</td>
                <td class="px-1.5 py-1 text-right text-blue-600 font-medium">{{ numberFormat(purchase.total_kg) }}</td>
                <td class="px-1.5 py-1 text-right text-green-600 font-medium">Rs. {{ numberFormat(purchase.total_cost) }}</td>
              </tr>
              <tr v-if="!bunker.grouped_purchases?.length">
                <td colspan="4" class="px-1.5 py-2 text-center text-slate-400">No purchases yet</td>
              </tr>
              <tr v-if="bunker.grouped_purchases?.length" class="border-t-2 border-slate-300 bg-slate-50 font-semibold">
                <td class="px-1.5 py-1 text-slate-700">TOTAL</td>
                <td class="px-1.5 py-1"></td>
                <td class="px-1.5 py-1 text-right text-blue-700">{{ numberFormat(purchasesTotalKg) }}</td>
                <td class="px-1.5 py-1 text-right text-green-700">Rs. {{ numberFormat(purchasesTotalCost) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Expenses Section -->
      <div class="rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 p-2">
          <h3 class="text-xs font-semibold text-slate-700 cursor-pointer" @click="toggleSection('expenses')">
            Expenses
          </h3>
          <button
            v-if="bunker.status !== 'empty'"
            @click="openAddExpense"
            class="text-primary-600 hover:text-primary-800 text-sm font-bold"
          >+</button>
        </div>
        <div v-if="sections.expenses" class="p-1.5">
          <table class="w-full text-[10px]">
            <thead>
              <tr class="text-slate-500">
                <th class="px-1.5 py-1 text-left">Date</th>
                <th class="px-1.5 py-1 text-left">Category</th>
                <th class="px-1.5 py-1 text-right text-red-600">Amount</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="expense in bunker.expenses || []"
                :key="expense.id"
                class="border-t border-slate-100 hover:bg-slate-200 cursor-pointer"
                @dblclick="editExpense(expense)"
              >
                <td class="px-1.5 py-1">{{ formatDate(expense.date) }}</td>
                <td class="px-1.5 py-1">{{ expense.category?.name || 'Uncategorized' }}</td>
                <td class="px-1.5 py-1 text-right text-red-600 font-medium">Rs. {{ numberFormat(expense.amount) }}</td>
              </tr>
              <tr v-if="!bunker.expenses?.length">
                <td colspan="3" class="px-1.5 py-2 text-center text-slate-400">No expenses yet</td>
              </tr>
              <tr v-if="bunker.expenses?.length" class="border-t-2 border-slate-300 bg-slate-50 font-semibold">
                <td class="px-1.5 py-1 text-slate-700">TOTAL</td>
                <td class="px-1.5 py-1"></td>
                <td class="px-1.5 py-1 text-right text-red-700">Rs. {{ numberFormat(expensesTotal) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Sales Section -->
      <div class="rounded-lg border border-slate-200 bg-white">
        <div class="flex items-center justify-between border-b border-slate-200 p-2">
          <h3 class="text-xs font-semibold text-slate-700 cursor-pointer" @click="toggleSection('sales')">
            Sales
          </h3>
          <div class="flex gap-0.5">
            <button @click="openAddSale('open')" class="text-primary-600 hover:text-primary-800 text-[10px] px-1.5 py-0.5 border rounded">Open</button>
            <button @click="openAddSale('bags')" class="text-primary-600 hover:text-primary-800 text-[10px] px-1.5 py-0.5 border rounded">Bags</button>
            <button @click="openAddSale('bales')" class="text-primary-600 hover:text-primary-800 text-[10px] px-1.5 py-0.5 border rounded">Bales</button>
          </div>
        </div>
        <div v-if="sections.sales" class="p-1.5">
          <table class="w-full text-[10px]">
            <thead>
              <tr class="text-slate-500">
                <th class="px-1.5 py-1 text-left">Type</th>
                <th class="px-1.5 py-1 text-right">Units</th>
                <th class="px-1.5 py-1 text-right text-blue-600">KG</th>
                <th class="px-1.5 py-1 text-right text-green-600">Revenue</th>
                <th class="px-1.5 py-1 text-center text-slate-400">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="group in groupedSales"
                :key="group.sale_type"
                class="border-t border-slate-100 hover:bg-slate-200 cursor-pointer"
                @dblclick="openSaleGroupDetail(group)"
              >
                <td class="px-1.5 py-1 capitalize font-medium">{{ group.sale_type }}</td>
                <td class="px-1.5 py-1 text-right">{{ group.total_units || '-' }}</td>
                <td class="px-1.5 py-1 text-right text-blue-600 font-medium">{{ numberFormat(group.total_kg) }}</td>
                <td class="px-1.5 py-1 text-right text-green-600 font-medium">Rs. {{ numberFormat(group.total_revenue) }}</td>
                <td class="px-1.5 py-1 text-center">
                  <button @click.stop="openSaleGroupDetail(group)" class="text-blue-500 hover:text-blue-700 text-xs">📋</button>
                </td>
              </tr>

              <!-- Grand Total -->
              <tr v-if="grandTotalSales.total_kg > 0" class="border-t-2 border-slate-300 bg-slate-50 font-semibold">
                <td class="px-1.5 py-1 text-slate-700">TOTAL</td>
                <td class="px-1.5 py-1 text-right text-slate-700">{{ grandTotalSales.total_units || '-' }}</td>
                <td class="px-1.5 py-1 text-right text-blue-700">{{ numberFormat(grandTotalSales.total_kg) }}</td>
                <td class="px-1.5 py-1 text-right text-green-700">Rs. {{ numberFormat(grandTotalSales.total_revenue) }}</td>
                <td class="px-1.5 py-1"></td>
              </tr>

              <tr v-if="!bunker.sale_items?.length">
                <td colspan="5" class="px-1.5 py-2 text-center text-slate-400">No sales yet</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Bunker Summary Section -->
      <div v-if="bunker.status === 'empty'" class="mt-4 rounded-lg border border-slate-200 bg-white p-3">
        <h3 class="text-xs font-bold text-slate-700 mb-2">📊 Bunker Summary</h3>
        <table class="w-full text-[10px]">
          <tbody>
            <tr class="border-b border-slate-100">
              <td class="py-1.5 text-slate-600">Total Purchased</td>
              <td class="py-1.5 text-right font-medium">{{ numberFormat(bunker.total_purchased_kg) }} kg</td>
            </tr>
            <tr class="border-b border-slate-100">
              <td class="py-1.5 text-slate-600">Total Sold</td>
              <td class="py-1.5 text-right font-medium">{{ numberFormat(bunker.total_sold_kg) }} kg</td>
            </tr>
            <tr class="border-b border-slate-100">
              <td class="py-1.5 text-slate-600">Moisture Loss</td>
              <td class="py-1.5 text-right font-medium">
                {{ numberFormat(bunker.shrinkage_kg) }} kg
                <span class="text-slate-600">({{ numberFormat(bunker.shrinkage_percentage) }}%)</span>
              </td>
            </tr>
            <tr class="border-b border-slate-100">
              <td class="py-1.5 text-slate-600">Total Revenue</td>
              <td class="py-1.5 text-right font-medium text-green-600">Rs. {{ numberFormat(bunker.total_revenue) }}</td>
            </tr>
            <tr class="border-b border-slate-100">
              <td class="py-1.5 text-slate-600">Total Purchase Cost</td>
              <td class="py-1.5 text-right font-medium text-red-600">Rs. {{ numberFormat(bunker.total_purchase_cost) }}</td>
            </tr>
            <tr class="border-b border-slate-100">
              <td class="py-1.5 text-slate-600">Total Expenses</td>
              <td class="py-1.5 text-right font-medium text-red-600">Rs. {{ numberFormat(bunker.total_expenses) }}</td>
            </tr>
            <tr class="border-b border-slate-200">
              <td class="py-1.5 font-semibold text-slate-700">Net Cost (Purchases + Expenses)</td>
              <td class="py-1.5 text-right font-semibold text-slate-700">Rs. {{ numberFormat(bunker.total_cost_with_expenses) }}</td>
            </tr>
            <tr class="border-t-2 border-slate-200 bg-slate-50">
              <td class="py-2 font-bold text-slate-700">Total Profit</td>
              <td class="py-2 text-right font-bold" :class="(bunker.total_profit || 0) >= 0 ? 'text-green-700' : 'text-red-700'">
                Rs. {{ numberFormat(bunker.total_profit) }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Actions -->
      <div class="flex flex-wrap gap-1.5 pt-1">
        <button @click="openEditBunker" class="btn-outline text-[10px] px-2 py-1">Edit</button>
        <button v-if="bunker.status !== 'empty'" @click="markEmpty" class="btn-warning text-[10px] px-2 py-1">Mark Empty</button>
        <button v-if="bunker.status === 'empty'" @click="reopenBunker" class="btn-primary text-[10px] px-2 py-1">Reopen</button>
        <button @click="exportReport" class="btn-outline text-[10px] px-2 py-1">📄 Report</button>
        <button @click="deleteBunker" class="btn-danger text-[10px] px-2 py-1">Delete</button>
      </div>
    </template>

    <!-- Toast -->
    <div v-if="toastMessage" class="fixed bottom-4 right-4 z-50 rounded-lg px-3 py-2 text-white shadow-lg text-sm" :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'">
      {{ toastMessage }}
      <button @click="toastMessage = ''" class="ml-3 opacity-75 hover:opacity-100">✕</button>
    </div>

    <!-- Modals -->
    <AddPurchaseModal
      :visible="purchaseModalVisible"
      :bunker-id="bunker?.id"
      :season-id="bunker?.season_id"
      :edit-data="editingPurchaseGroup"
      :bunker-name="bunker?.name"
      :bunker-location="bunker?.location"
      @close="closePurchaseModal"
      @saved="refreshDetail"
    />
    <AddExpenseModal
      :visible="expenseModalVisible"
      :bunker-id="bunker?.id"
      :season-id="bunker?.season_id"
      :edit-data="editingExpense"
      @close="closeExpenseModal"
      @saved="refreshDetail"
    />
    <AddSaleModal
      :visible="saleModalVisible"
      :bunker-id="bunker?.id"
      :season-id="bunker?.season_id"
      :sale-type="selectedSaleType"
      :edit-data="editingSale"
      @close="closeSaleModal"
      @saved="refreshDetail"
    />
    <SaleGroupDetailModal
      :visible="saleGroupModalVisible"
      :bunker-id="bunker?.id"
      :sale-group="selectedSaleGroup"
      @close="saleGroupModalVisible = false"
      @saved="refreshDetail"
      @editSale="handleEditSale"
    />
    <ConfirmModal
      :visible="confirmModalVisible"
      :title="confirmTitle"
      :message="confirmMessage"
      :confirm-text="confirmButtonText"
      @confirm="executeConfirmedAction"
      @cancel="confirmModalVisible = false"
    />
    <EditBunkerModal
      :visible="editModalVisible"
      :bunker="bunker"
      @close="editModalVisible = false"
      @saved="refreshDetail"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useBunkerStore } from '../stores/bunker'
import { useTabsStore } from '../stores/tabs'
import AddPurchaseModal from '../components/AddPurchaseModal.vue'
import AddExpenseModal from '../components/AddExpenseModal.vue'
import AddSaleModal from '../components/AddSaleModal.vue'
import SaleGroupDetailModal from '../components/SaleGroupDetailModal.vue'
import ConfirmModal from '../components/ConfirmModal.vue'
import EditBunkerModal from '../components/EditBunkerModal.vue'
import axios from 'axios'

const props = defineProps({
  id: { type: String, required: true },
  seasonId: { type: String, default: 'all' }
})

const store = useBunkerStore()
const tabsStore = useTabsStore()

const loading = ref(true)
const error = ref(null)
const bunker = ref(null)

// Sections collapsed by default
const sections = ref({
  purchases: false,
  expenses: false,
  sales: false,
})

// Modal visibility
const purchaseModalVisible = ref(false)
const expenseModalVisible = ref(false)
const saleModalVisible = ref(false)
const saleGroupModalVisible = ref(false)
const confirmModalVisible = ref(false)
const editModalVisible = ref(false)

// Edit data holders
const editingPurchaseGroup = ref(null)
const editingExpense = ref(null)
const editingSale = ref(null)
const selectedSaleType = ref('open')
const selectedSaleGroup = ref(null)

const toastMessage = ref('')
const toastType = ref('success')
const confirmTitle = ref('Confirm')
const confirmMessage = ref('Are you sure?')
const confirmButtonText = ref('Confirm')
let pendingAction = null

// ===== COMPUTED: Grouped Sales =====
const groupedSales = computed(() => {
  const items = bunker.value?.sale_items || []
  if (!items.length) return []

  const groups = {}
  const typeOrder = ['open', 'bags', 'bales']

  items.forEach(sale => {
    const type = sale.sale_type
    if (!groups[type]) {
      groups[type] = {
        sale_type: type,
        total_units: 0,
        total_kg: 0,
        total_revenue: 0,
        sales: []
      }
    }
    groups[type].total_units += Number(sale.units || 0)
    groups[type].total_kg += Number(sale.weight_kg || 0)
    groups[type].total_revenue += Number(sale.total_price || 0)
    groups[type].sales.push(sale)
  })

  const result = []
  typeOrder.forEach(type => {
    if (groups[type]) result.push(groups[type])
  })
  return result
})

// ===== COMPUTED: Grand Total Sales =====
const grandTotalSales = computed(() => {
  const totals = { total_units: 0, total_kg: 0, total_revenue: 0 }
  groupedSales.value.forEach(g => {
    totals.total_units += g.total_units
    totals.total_kg += g.total_kg
    totals.total_revenue += g.total_revenue
  })
  return totals
})

// ===== COMPUTED: Purchases totals (SAFE) =====
const purchasesTotalKg = computed(() => {
  const groups = bunker.value?.grouped_purchases || []
  return groups.reduce((sum, g) => sum + (Number(g.total_kg) || 0), 0)
})

const purchasesTotalCost = computed(() => {
  const groups = bunker.value?.grouped_purchases || []
  return groups.reduce((sum, g) => sum + (Number(g.total_cost) || 0), 0)
})

// ===== COMPUTED: Expenses total (SAFE) =====
const expensesTotal = computed(() => {
  const items = bunker.value?.expenses || []
  return items.reduce((sum, e) => sum + (Number(e.amount) || 0), 0)
})

// ===== FETCH DATA =====
const refreshDetail = async () => {
  loading.value = true
  error.value = null
  try {
    const data = await store.fetchBunker(props.id, { season_id: props.seasonId })
    bunker.value = data
  } catch (err) {
    error.value = err.message || 'Failed to load bunker details'
  } finally {
    loading.value = false
  }
}

// ===== TOGGLE SECTIONS =====
const toggleSection = (name) => {
  sections.value[name] = !sections.value[name]
}

// ===== FORMAT HELPERS (SAFE) =====
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

const statusClass = (status) => ({
  active: 'bg-green-100 text-green-800',
  warning: 'bg-yellow-100 text-yellow-800',
  empty: 'bg-red-100 text-red-800',
  blocked: 'bg-gray-100 text-gray-800'
}[status] || 'bg-gray-100 text-gray-800')

const showToast = (msg, type = 'success') => {
  toastMessage.value = msg
  toastType.value = type
  setTimeout(() => { toastMessage.value = '' }, 3000)
}

// ===== OPEN MODALS =====
const openAddPurchase = () => {
  if (bunker.value?.status === 'empty') {
    showToast('Cannot add purchases to an empty bunker', 'error')
    return
  }
  editingPurchaseGroup.value = null
  purchaseModalVisible.value = true
}

const openAddExpense = () => {
  if (bunker.value?.status === 'empty') {
    showToast('Cannot add expenses to an empty bunker', 'error')
    return
  }
  editingExpense.value = null
  expenseModalVisible.value = true
}

const openAddSale = (type) => {
  selectedSaleType.value = type
  editingSale.value = null
  saleModalVisible.value = true
}

const openEditBunker = () => {
  editModalVisible.value = true
}

// ===== EDIT/DELETE =====
const editPurchaseGroup = (purchase) => {
  if (bunker.value?.status === 'empty') {
    showToast('Cannot edit purchases of an empty bunker', 'error')
    return
  }
  const firstItem = purchase.items?.[0]
  editingPurchaseGroup.value = {
    ...purchase,
    area: firstItem?.area || null,
  }
  purchaseModalVisible.value = true
}

const closePurchaseModal = () => {
  purchaseModalVisible.value = false
  editingPurchaseGroup.value = null
}

const editExpense = (expense) => {
  if (bunker.value?.status === 'empty') {
    showToast('Cannot edit expenses of an empty bunker', 'error')
    return
  }
  editingExpense.value = expense
  expenseModalVisible.value = true
}

const closeExpenseModal = () => {
  expenseModalVisible.value = false
  editingExpense.value = null
}

// ===== SALE GROUP DETAIL =====
const openSaleGroupDetail = (group) => {
  selectedSaleGroup.value = group
  saleGroupModalVisible.value = true
}

const handleEditSale = (sale) => {
  // ✅ CRITICAL FIX: Set the sale type from the sale data
  selectedSaleType.value = sale.sale_type || 'open'
  editingSale.value = sale
  saleModalVisible.value = true
}

const closeSaleModal = () => {
  saleModalVisible.value = false
  editingSale.value = null
}

// ===== MARK EMPTY =====
const markEmpty = () => {
  confirmTitle.value = 'Mark Bunker as Empty'
  confirmMessage.value = `Are you sure you want to mark "${bunker.value.name}" as empty?\n\nAll remaining weight (${numberFormat(bunker.value.available_weight)} kg) will be recorded as moisture loss.`
  confirmButtonText.value = 'Mark Empty'
  pendingAction = 'markEmpty'
  confirmModalVisible.value = true
}

// ===== REOPEN =====
const reopenBunker = async () => {
  try {
    await store.reopenBunker(bunker.value.id)
    showToast('Bunker reopened')
    refreshDetail()
  } catch (err) {
    showToast(err.message, 'error')
  }
}

// ===== DELETE BUNKER =====
const deleteBunker = () => {
  confirmTitle.value = 'Delete Bunker'
  confirmMessage.value = `Are you sure you want to delete "${bunker.value.name}"?`
  confirmButtonText.value = 'Delete'
  pendingAction = 'delete'
  confirmModalVisible.value = true
}

// ===== EXECUTE CONFIRMED ACTION =====
const executeConfirmedAction = async () => {
  confirmModalVisible.value = false
  const action = pendingAction
  pendingAction = null

  if (action === 'markEmpty') {
    try {
      await store.markEmpty(bunker.value.id)
      showToast('Bunker marked as empty')
      refreshDetail()
    } catch (err) {
      showToast(err.message, 'error')
    }
  } else if (action === 'delete') {
    try {
      await store.deleteBunker(bunker.value.id)
      showToast('Bunker deleted')
      const currentTab = tabsStore.openTabs.find(t => t.path === `bunker-detail-${bunker.value.id}`)
      if (currentTab) tabsStore.closeTab(currentTab)
    } catch (err) {
      showToast(err.message, 'error')
    }
  }
}

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

// ===== EXPORT REPORT =====
const exportReport = async () => {
  try {
    const response = await axios.get(`/bunkers/${bunker.value.id}/report`, {
      headers: getAuthHeader()
    })
    const newTab = window.open('', '_blank')
    if (newTab) {
      newTab.document.write(response.data)
      newTab.document.close()
    }
    showToast('Report opening in new tab')
  } catch (error) {
    console.error('Failed to load report:', error)
    showToast('Failed to generate report', 'error')
  }
}

onMounted(() => {
  refreshDetail()
})
</script>