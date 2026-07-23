<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-sm max-h-[90vh] overflow-y-auto rounded-lg bg-white p-4 shadow-xl">
      <h3 class="mb-3 text-base font-bold text-slate-700">
        {{ isEditing ? 'Edit Sale' : 'Add Sale' }}
        <span v-if="preSelectedBunker || bunkerName" class="text-xs font-normal text-slate-500 block">
          Bunker: {{ preSelectedBunker?.name || bunkerName }}
          <span v-if="preSelectedBunker?.location || bunkerLocation">
            ({{ preSelectedBunker?.location || bunkerLocation || 'No location' }})
          </span>
        </span>
      </h3>
      <p class="text-xs text-slate-500 mb-2">Type: <span class="font-semibold">{{ saleTypeLabel }}</span></p>

      <form @submit.prevent="saveSale" class="space-y-3">
        <!-- Customer -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Customer</label>
          <div class="flex gap-1.5">
            <input
              v-model="customerSearch"
              type="text"
              list="customerList"
              class="flex-1 rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
              placeholder="Search or select customer..."
              @input="onCustomerSearch"
            />
            <datalist id="customerList">
              <option v-for="customer in filteredCustomers" :key="customer.id" :value="customer.name" />
            </datalist>
            <input type="hidden" v-model="form.customer_id" />
            <button type="button" @click="openAddCustomer" class="btn-primary px-2.5 py-1.5 text-xs">+</button>
          </div>
          <p v-if="!form.customer_id && customerSearch" class="mt-0.5 text-[10px] text-amber-600">
            No customer selected. Will be saved as "Walk-in Customer"
          </p>
        </div>

        <!-- Weight -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Weight (KG) *</label>
          <input
            v-model="form.weight"
            type="text"
            inputmode="decimal"
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
            placeholder="Enter weight"
            @input="handleInput('weight')"
          />
        </div>

        <!-- Units (for Bags/Bales) -->
        <div v-if="saleType !== 'open'">
          <label class="block text-xs font-medium text-slate-700">Number of {{ saleType === 'bags' ? 'Bags' : 'Bales' }}</label>
          <input
            v-model="form.units"
            type="text"
            inputmode="numeric"
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
            placeholder="Enter units"
          />
        </div>

        <!-- Price per KG -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Price per KG (Rs.) *</label>
          <input
            v-model="form.price_per_kg"
            type="text"
            inputmode="decimal"
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
            placeholder="Enter price per KG"
            @input="handleInput('price_per_kg')"
          />
        </div>

        <!-- Total Invoice -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Total Invoice (Rs.)</label>
          <input
            v-model="form.total_invoice"
            type="text"
            inputmode="decimal"
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
            placeholder="Enter total invoice"
            @input="handleInput('total_invoice')"
          />
          <p v-if="showCostWarning" class="mt-0.5 text-[10px] text-yellow-600">
            ⚠️ Selling price is below cost price (Rs. {{ costPerKg }})
          </p>
        </div>

        <!-- Discount -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Discount (Rs.)</label>
          <input
            v-model="form.discount"
            type="text"
            inputmode="decimal"
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
            placeholder="Enter discount"
          />
        </div>

        <!-- Paid Amount -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Paid Amount (Rs.)</label>
          <input
            v-model="form.paid_amount"
            type="text"
            inputmode="decimal"
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
            placeholder="Enter paid amount"
          />
          <p v-if="parseFloat(form.paid_amount) > netTotal" class="mt-0.5 text-[10px] text-red-600">
            Paid amount cannot exceed Net Total (Rs. {{ netTotal }})
          </p>
        </div>

        <!-- Payment Type -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Payment Type</label>
          <select
            v-model="form.payment_type_id"
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
          >
            <option value="">Select Payment Type</option>
            <option v-for="pt in paymentTypes" :key="pt.id" :value="pt.id">
              {{ pt.name }}
            </option>
          </select>
        </div>

        <!-- Date -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Date *</label>
          <input
            v-model="form.date"
            type="date"
            required
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
          />
        </div>

        <!-- Print Invoice -->
        <div class="flex items-center gap-1.5">
          <input type="checkbox" v-model="form.print_invoice" id="print_invoice" class="w-3.5 h-3.5" />
          <label for="print_invoice" class="text-xs text-slate-700">Print Invoice</label>
        </div>

        <!-- Errors -->
        <div v-if="error" class="rounded-lg bg-red-50 p-2 text-xs text-red-700">
          {{ error }}
        </div>

        <!-- Buttons -->
        <div class="flex justify-between border-t border-slate-200 pt-3">
          <button
            v-if="isEditing"
            type="button"
            @click="deleteSale"
            class="btn-danger px-3 py-1.5 text-xs"
          >
            Delete
          </button>
          <div class="flex gap-2 ml-auto">
            <button type="button" @click="closeModal" class="btn-outline px-3 py-1.5 text-xs">Cancel</button>
            <button type="submit" class="btn-primary px-3 py-1.5 text-xs" :disabled="loading || parseFloat(form.paid_amount) > netTotal">
              {{ loading ? 'Saving...' : isEditing ? 'Update' : 'Save' }}
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Add Customer Modal -->
    <div v-if="customerModalVisible" class="fixed inset-0 z-60 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-sm max-h-[80vh] overflow-y-auto rounded-lg bg-white p-4 shadow-xl">
        <h4 class="mb-3 text-base font-bold text-slate-700">Add Customer</h4>
        <form @submit.prevent="saveCustomer" class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-slate-700">Name *</label>
            <input v-model="newCustomer.name" type="text" required class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700">Phone</label>
            <input v-model="newCustomer.phone" type="text" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700">Address</label>
            <textarea v-model="newCustomer.address" rows="2" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"></textarea>
          </div>
          <div v-if="customerError" class="text-xs text-red-600">{{ customerError }}</div>
          <div class="flex justify-end gap-2 border-t border-slate-200 pt-3">
            <button type="button" @click="customerModalVisible = false" class="btn-outline px-3 py-1.5 text-xs">Cancel</button>
            <button type="submit" class="btn-primary px-3 py-1.5 text-xs" :disabled="customerLoading">
              {{ customerLoading ? 'Saving...' : 'Add' }}
            </button>
          </div>
        </form>
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
import { ref, reactive, computed, watch, nextTick } from 'vue'
import axios from 'axios'
import ConfirmModal from './ConfirmModal.vue'

const props = defineProps({
  visible: Boolean,
  bunkerId: String,
  seasonId: String,
  saleType: {
    type: String,
    default: 'open',
    validator: (val) => ['open', 'bags', 'bales'].includes(val)
  },
  editData: Object,
  preSelectedBunker: Object,
  bunkerName: String,
  bunkerLocation: String,
})

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const error = ref('')
const customers = ref([])
const paymentTypes = ref([])
const costPerKg = ref(0)

// ===== NEW: Customer Search =====
const customerSearch = ref('')
const filteredCustomers = ref([])

// Filter customers as user types
const onCustomerSearch = () => {
  const search = customerSearch.value.toLowerCase().trim()
  if (!search) {
    filteredCustomers.value = customers.value
    return
  }
  filteredCustomers.value = customers.value.filter(c => 
    c.name.toLowerCase().includes(search)
  )
  // If exact match found, set customer_id
  const match = customers.value.find(c => c.name.toLowerCase() === search)
  if (match) {
    form.customer_id = match.id
  } else {
    // If no exact match, clear customer_id (will be Walk-in)
    form.customer_id = ''
  }
}

// Watch for customer selection from datalist
watch(customerSearch, (val) => {
  if (!val) {
    form.customer_id = ''
    return
  }
  const match = customers.value.find(c => c.name === val)
  if (match) {
    form.customer_id = match.id
  }
})
// ===== END NEW =====

const customerModalVisible = ref(false)
const customerLoading = ref(false)
const customerError = ref('')
const newCustomer = reactive({ name: '', phone: '', address: '' })

const form = reactive({
  customer_id: '',
  payment_type_id: '',
  weight: '',
  units: '',
  price_per_kg: '',
  total_invoice: '',
  discount: '',
  paid_amount: '',
  date: new Date().toISOString().split('T')[0],
  print_invoice: false,
})

const editingId = ref(null)
const isEditing = computed(() => !!props.editData?.id)
const confirmModalVisible = ref(false)

const saleTypeLabel = computed(() => ({
  open: 'Open',
  bags: 'Bags',
  bales: 'Bales'
})[props.saleType] || 'Open')

const toNumber = (val) => {
  if (val === '' || val === null || val === undefined) return 0
  const cleaned = String(val).replace(/,/g, '')
  const num = parseFloat(cleaned)
  return isNaN(num) ? 0 : num
}

const netTotal = computed(() => {
  const total = toNumber(form.total_invoice)
  const discount = toNumber(form.discount)
  return Math.max(0, total - discount)
})

const showCostWarning = computed(() => {
  const price = toNumber(form.price_per_kg)
  const cost = costPerKg.value
  return price > 0 && cost > 0 && price < cost
})

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const fetchCustomers = async () => {
  try {
    const res = await axios.get('/customers', {
      headers: getAuthHeader()
    })
    customers.value = res.data
    // Initialize filteredCustomers
    filteredCustomers.value = res.data
  } catch (err) {
    console.error('Failed to fetch customers:', err)
  }
}

const fetchPaymentTypes = async () => {
  try {
    const res = await axios.get('/payment-types', {
      headers: getAuthHeader()
    })
    paymentTypes.value = res.data
  } catch (err) {
    console.error('Failed to fetch payment types:', err)
  }
}

const fetchCostPerKg = async () => {
  try {
    const res = await axios.get(`/bunkers/${props.bunkerId}`, {
      headers: getAuthHeader()
    })
    costPerKg.value = res.data.cost_per_kg || 0
  } catch (err) {
    console.error('Failed to fetch cost per kg:', err)
  }
}

const openAddCustomer = () => {
  newCustomer.name = ''
  newCustomer.phone = ''
  newCustomer.address = ''
  customerError.value = ''
  customerModalVisible.value = true
}

const saveCustomer = async () => {
  if (!newCustomer.name.trim()) {
    customerError.value = 'Customer name is required'
    return
  }
  customerLoading.value = true
  customerError.value = ''
  try {
    const res = await axios.post('/customers', newCustomer, {
      headers: getAuthHeader()
    })
    customers.value.push(res.data)
    filteredCustomers.value = customers.value
    form.customer_id = res.data.id
    customerSearch.value = res.data.name
    customerModalVisible.value = false
  } catch (err) {
    customerError.value = err.response?.data?.message || 'Failed to add customer'
  } finally {
    customerLoading.value = false
  }
}

const resetForm = () => {
  form.customer_id = ''
  customerSearch.value = ''
  filteredCustomers.value = []
  form.payment_type_id = ''
  form.weight = ''
  form.units = ''
  form.price_per_kg = ''
  form.total_invoice = ''
  form.discount = ''
  form.paid_amount = ''
  form.date = new Date().toISOString().split('T')[0]
  form.print_invoice = false
  editingId.value = null
  error.value = ''
  confirmModalVisible.value = false
}

const calculateFields = (changedField) => {
  // ✅ Skip calculation if changed field is 'units'
  if (changedField === 'units') return

  const weight = toNumber(form.weight)
  const price = toNumber(form.price_per_kg)
  const total = toNumber(form.total_invoice)

  if (changedField === 'weight' && form.weight === '') {
    form.price_per_kg = ''
    form.total_invoice = ''
    return
  }

  if (changedField === 'price_per_kg' && form.price_per_kg === '') {
    form.total_invoice = ''
    return
  }

  if (changedField === 'total_invoice' && form.total_invoice === '') {
    form.price_per_kg = ''
    return
  }

  if (changedField === 'weight' || changedField === 'total_invoice') {
    if (weight > 0 && total > 0) {
      const calculatedPrice = total / weight
      form.price_per_kg = String(calculatedPrice)
      return
    }
  }

  if (changedField === 'weight' || changedField === 'price_per_kg') {
    if (weight > 0 && price > 0) {
      const calculatedTotal = weight * price
      form.total_invoice = String(calculatedTotal)
      return
    }
  }
}

const handleInput = (field) => {
  let value = form[field]
  value = value.replace(/[^0-9.]/g, '')
  
  const parts = value.split('.')
  if (parts.length > 2) {
    value = parts[0] + '.' + parts.slice(1).join('')
  }
  
  form[field] = value
  
  nextTick(() => {
    calculateFields(field)
  })
}

const saveSale = async () => {
  error.value = ''
  
  let customerId = form.customer_id ? form.customer_id.trim() : ''
  const weight = toNumber(form.weight)
  const price = toNumber(form.price_per_kg)
  const totalInvoice = toNumber(form.total_invoice)

  // === NEW: Handle Walk-in Customer ===
  if (!customerId) {
    // Check if "Walk-in Customer" exists
    let walkin = customers.value.find(c => c.name === 'Walk-in Customer')
    if (!walkin) {
      try {
        const res = await axios.post('/customers', { 
          name: 'Walk-in Customer', 
          is_active: true 
        }, {
          headers: getAuthHeader()
        })
        walkin = res.data
        customers.value.push(walkin)
        filteredCustomers.value = customers.value
      } catch (err) {
        error.value = 'Failed to create Walk-in Customer. Please try again.'
        return
      }
    }
    customerId = walkin.id
    form.customer_id = customerId
  }
  // === END NEW ===

  if (weight <= 0) {
    error.value = 'Please enter a valid weight'
    return
  }
  if (price <= 0) {
    error.value = 'Please enter a valid price'
    return
  }
  if (totalInvoice <= 0) {
    error.value = 'Please enter a valid total invoice'
    return
  }

  if (!isEditing.value) {
    try {
      const res = await axios.get(`/bunkers/${props.bunkerId}`, {
        headers: getAuthHeader()
      })
      const available = res.data.available_weight || 0
      if (weight > available) {
        error.value = `Insufficient stock. Available: ${available.toFixed(2)} kg`
        return
      }
    } catch (err) {
      // Continue if we can't check stock
    }
  }

  const paidAmount = toNumber(form.paid_amount)
  if (paidAmount > netTotal.value) {
    error.value = 'Paid amount cannot exceed Net Total (Rs. ' + netTotal.value + ')'
    return
  }

  loading.value = true
  try {
    const payload = {
      bunker_id: props.bunkerId,
      customer_id: customerId,
      payment_type_id: form.payment_type_id || null,
      season_id: props.seasonId,
      sale_type: props.saleType,
      weight_kg: weight,
      units: toNumber(form.units),
      price_per_kg: price,
      total_price: totalInvoice,
      discount: toNumber(form.discount),
      paid_amount: paidAmount,
      date: form.date,
      print_invoice: form.print_invoice,
    }

    let response
    if (isEditing.value) {
      await axios.put(`/sales/${editingId.value}`, payload, {
        headers: getAuthHeader()
      })
    } else {
      response = await axios.post('/sales', payload, {
        headers: getAuthHeader()
      })
    }

    if (form.print_invoice && response?.data?.sale?.id) {
      try {
        const invoiceResponse = await axios.get(`/sales/${response.data.sale.id}/invoice`, {
          headers: getAuthHeader()
        })
        const newTab = window.open('', '_blank')
        if (newTab) {
          newTab.document.write(invoiceResponse.data)
          newTab.document.close()
        }
      } catch (err) {
        console.error('Failed to load invoice:', err)
        const token = localStorage.getItem('token')
        const baseUrl = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'
        const invoiceUrl = `${baseUrl}/sales/${response.data.sale.id}/invoice?token=${token}`
        window.open(invoiceUrl, '_blank')
      }
    }

    emit('saved')
    closeModal()
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to save sale'
  } finally {
    loading.value = false
  }
}

const deleteSale = () => {
  confirmModalVisible.value = true
}

const executeDelete = async () => {
  confirmModalVisible.value = false
  loading.value = true
  error.value = ''
  try {
    await axios.delete(`/sales/${editingId.value}`, {
      headers: getAuthHeader()
    })
    emit('saved')
    closeModal()
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to delete'
  } finally {
    loading.value = false
  }
}

const closeModal = () => {
  resetForm()
  emit('close')
}

watch(() => props.editData, (data) => {
  if (data && data.id) {
    editingId.value = data.id
    form.customer_id = data.customer_id || ''
    // Set customer search if customer exists
    if (data.customer_id) {
      const customer = customers.value.find(c => c.id === data.customer_id)
      customerSearch.value = customer ? customer.name : ''
    }
    form.payment_type_id = data.payment_type_id || ''
    form.weight = data.weight_kg ? String(data.weight_kg) : ''
    form.units = data.units !== undefined && data.units !== null ? String(data.units) : ''
    form.price_per_kg = data.price_per_kg ? String(data.price_per_kg) : ''
    form.total_invoice = data.total_price ? String(data.total_price) : ''
    form.discount = data.discount ? String(data.discount) : ''
    form.paid_amount = data.paid_amount ? String(data.paid_amount) : ''
    form.date = data.date ? new Date(data.date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0]
    form.print_invoice = false
  } else {
    resetForm()
  }
}, { immediate: true })

watch(() => props.visible, (val) => {
  if (val) {
    fetchCustomers()
    fetchPaymentTypes()
    fetchCostPerKg()
  }
})
</script>