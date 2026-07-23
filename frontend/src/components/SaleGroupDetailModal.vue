<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-lg rounded-lg bg-white shadow-xl max-h-[90vh] flex flex-col">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-200 p-3">
        <h3 class="text-sm font-bold text-slate-700">
          {{ capitalize(saleGroup?.sale_type) }} Sales
        </h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600">✕</button>
      </div>

      <!-- Body -->
      <div class="p-3 overflow-y-auto flex-1">
        <div v-if="loading" class="text-xs text-slate-500">Loading...</div>
        <div v-else-if="error" class="text-xs text-red-600">{{ error }}</div>
        <div v-else>
          <table class="w-full text-[10px]">
            <thead>
              <tr class="text-slate-500 border-b border-slate-200">
                <th class="px-1.5 py-1 text-left">Customer</th>
                <th class="px-1.5 py-1 text-right">KG</th>
                <th class="px-1.5 py-1 text-right">Price/KG</th>
                <th class="px-1.5 py-1 text-right">Total</th>
                <th class="px-1.5 py-1 text-center">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="sale in sales" :key="sale.id" class="border-t border-slate-100">
                <td class="px-1.5 py-1">{{ sale.customer?.name || 'Unknown' }}</td>
                <td class="px-1.5 py-1 text-right">{{ formatNumber(sale.weight_kg) }}</td>
                <td class="px-1.5 py-1 text-right">Rs. {{ formatNumber(sale.price_per_kg) }}</td>
                <td class="px-1.5 py-1 text-right text-green-600 font-medium">Rs. {{ formatNumber(sale.total_price) }}</td>
                <td class="px-1.5 py-1 text-center">
                  <button @click="editSale(sale)" class="text-blue-600 hover:text-blue-800 text-[10px] mr-1">✏️</button>
                  <button @click="confirmDelete(sale)" class="text-red-600 hover:text-red-800 text-[10px]">🗑</button>
                </td>
              </tr>
              <tr v-if="!sales.length">
                <td colspan="5" class="px-1.5 py-2 text-center text-slate-400">No sales in this group</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t border-slate-200 p-3 flex justify-end">
        <button @click="$emit('close')" class="btn-secondary text-xs px-3 py-1">Close</button>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :visible="confirmModalVisible"
      title="Delete Sale"
      message="Are you sure you want to delete this sale?"
      confirm-text="Delete"
      @confirm="executeDelete"
      @cancel="confirmModalVisible = false"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import { useSaleStore } from '../stores/sale'
import ConfirmModal from './ConfirmModal.vue'

const props = defineProps({
  visible: Boolean,
  bunkerId: String,
  saleGroup: Object,
})

const emit = defineEmits(['close', 'saved', 'editSale']) // ✅ added editSale event

const saleStore = useSaleStore()

const loading = ref(false)
const error = ref(null)
const sales = ref([])
const confirmModalVisible = ref(false)
const pendingSaleId = ref(null)

watch(() => props.visible, (val) => {
  if (val && props.saleGroup) {
    sales.value = props.saleGroup.sales || []
  }
}, { immediate: true, deep: true })

const formatNumber = (val) => {
  if (val === null || val === undefined) return '0'
  return Number(val).toLocaleString()
}

const capitalize = (str) => {
  return str ? str.charAt(0).toUpperCase() + str.slice(1) : ''
}

// ✅ Edit – emit event to parent to open AddSaleModal as modal
const editSale = (sale) => {
  emit('editSale', sale)
  emit('close') // close this modal
}

// ✅ Delete – show ConfirmModal instead of browser confirm
const confirmDelete = (sale) => {
  pendingSaleId.value = sale.id
  confirmModalVisible.value = true
}

const executeDelete = async () => {
  confirmModalVisible.value = false
  if (!pendingSaleId.value) return

  loading.value = true
  error.value = null
  try {
    await saleStore.deleteSale(pendingSaleId.value)
    sales.value = sales.value.filter(s => s.id !== pendingSaleId.value)
    emit('saved')
    pendingSaleId.value = null
  } catch (err) {
    error.value = err.message || 'Failed to delete'
  } finally {
    loading.value = false
  }
}
</script>