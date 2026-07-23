<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
      <h3 class="mb-4 text-xl font-bold text-slate-700">
        {{ purchase ? 'Edit Purchase' : 'Add Silage Purchase' }}
      </h3>

      <form @submit.prevent="savePurchase" class="space-y-4">
        <!-- Supplier Field with + Button -->
        <div class="grid grid-cols-[1fr_auto] gap-2 items-end">
          <div>
            <label class="block text-sm font-medium text-slate-700">Supplier *</label>
            <select
              v-model="form.supplier_id"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            >
              <option value="">Select Supplier</option>
              <option v-for="supplier in suppliers" :key="supplier.id" :value="supplier.id">
                {{ supplier.name }}
              </option>
            </select>
          </div>
          <button
            type="button"
            @click="openSupplierModal"
            class="btn-primary px-3 py-2 text-sm whitespace-nowrap"
          >
            + Add
          </button>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-slate-700">Purchase Date *</label>
            <input
              v-model="form.purchase_date"
              type="date"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Season *</label>
            <select
              v-model="form.season_id"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            >
              <option value="">Select Season</option>
              <option v-for="season in seasons" :key="season.id" :value="season.id">
                {{ season.name }}
              </option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
          <div>
            <label class="block text-sm font-medium text-slate-700">Area (acres) *</label>
            <input
              v-model.number="form.area"
              type="number"
              step="0.01"
              min="0.01"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Weight (KG) *</label>
            <input
              v-model.number="form.weight_kg"
              type="number"
              step="0.01"
              min="0.01"
              required
              @input="calculateWeights"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
          <div>
            <label class="block text-sm font-medium text-slate-700">Mann (40kg)</label>
            <input
              :value="form.mann || 0"
              type="text"
              disabled
              class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Ton (1000kg)</label>
            <input
              :value="form.ton || 0"
              type="text"
              disabled
              class="w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-700">Cost (PKR) *</label>
            <input
              v-model.number="form.cost"
              type="number"
              step="0.01"
              min="0"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
            />
          </div>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700">Notes</label>
          <textarea
            v-model="form.notes"
            rows="2"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-primary-500 focus:outline-none"
          ></textarea>
        </div>

        <!-- Errors -->
        <div v-if="error" class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
          {{ error }}
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3 border-t border-slate-200 pt-4">
          <button type="button" @click="closeModal" class="btn-outline px-4 py-2 text-sm">
            Cancel
          </button>
          <button
            type="submit"
            class="btn-primary px-4 py-2 text-sm"
            :disabled="loading"
          >
            {{ loading ? 'Saving...' : 'Save' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Supplier Add Modal -->
    <div v-if="supplierModalVisible" class="fixed inset-0 z-60 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
        <h4 class="mb-4 text-lg font-bold text-slate-700">Add New Supplier</h4>
        <form @submit.prevent="saveSupplier">
          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700">Supplier Name *</label>
            <input
              v-model="newSupplier.name"
              type="text"
              required
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            />
          </div>
          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700">Contact Person</label>
            <input
              v-model="newSupplier.contact_person"
              type="text"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            />
          </div>
          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700">Phone</label>
            <input
              v-model="newSupplier.phone"
              type="text"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            />
          </div>
          <div class="mb-3">
            <label class="block text-sm font-medium text-slate-700">Email</label>
            <input
              v-model="newSupplier.email"
              type="email"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            />
          </div>
          <div class="mb-4">
            <label class="block text-sm font-medium text-slate-700">Address</label>
            <textarea
              v-model="newSupplier.address"
              rows="2"
              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            ></textarea>
          </div>
          <div v-if="supplierError" class="mb-3 text-sm text-red-600">{{ supplierError }}</div>
          <div class="flex justify-end gap-3">
            <button type="button" @click="supplierModalVisible = false" class="btn-outline px-4 py-2 text-sm">
              Cancel
            </button>
            <button type="submit" class="btn-primary px-4 py-2 text-sm" :disabled="supplierLoading">
              {{ supplierLoading ? 'Saving...' : 'Add Supplier' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import { useSilagePurchasesStore } from '../stores/silagePurchases'
import { fetchSeasons, getCurrentSeason } from '../utils/seasons'
import axios from 'axios'

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  purchase: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'saved'])

const purchasesStore = useSilagePurchasesStore()

const loading = ref(false)
const error = ref('')
const seasons = ref([])
const suppliers = ref([])

// Supplier modal state
const supplierModalVisible = ref(false)
const supplierLoading = ref(false)
const supplierError = ref('')
const newSupplier = reactive({
  name: '',
  contact_person: '',
  phone: '',
  email: '',
  address: '',
})

// Form data
const form = reactive({
  supplier_id: '',
  purchase_date: new Date().toISOString().split('T')[0],
  area: 0,
  weight_kg: 0,
  cost: 0,
  notes: '',
  season_id: '',
  mann: 0,
  ton: 0,
})

// Reset form
const resetForm = () => {
  form.supplier_id = ''
  form.purchase_date = new Date().toISOString().split('T')[0]
  form.area = 0
  form.weight_kg = 0
  form.cost = 0
  form.notes = ''
  form.season_id = ''
  form.mann = 0
  form.ton = 0
  error.value = ''
}

// Populate form for editing
const populateForm = (purchase) => {
  if (purchase) {
    form.supplier_id = purchase.supplier_id || ''
    form.purchase_date = purchase.purchase_date || new Date().toISOString().split('T')[0]
    form.area = purchase.area || 0
    form.weight_kg = purchase.weight_kg || 0
    form.cost = purchase.cost || 0
    form.notes = purchase.notes || ''
    form.season_id = purchase.season_id || ''
    calculateWeights()
  } else {
    resetForm()
  }
}

// Calculate Mann and Ton
const calculateWeights = () => {
  const kg = form.weight_kg || 0
  form.mann = kg / 40
  form.ton = kg / 1000
}

// Fetch suppliers
const fetchSuppliers = async () => {
  try {
    const res = await axios.get('/suppliers')
    suppliers.value = res.data
  } catch (err) {
    console.error('Failed to fetch suppliers:', err)
  }
}

// Load seasons
const loadSeasons = async () => {
  if (seasons.value.length === 0) {
    const data = await fetchSeasons()
    seasons.value = data
  }
  // Auto-select current season for "Add" mode
  if (!props.purchase && !form.season_id) {
    const current = getCurrentSeason(seasons.value)
    if (current) {
      form.season_id = current.id
    } else if (seasons.value.length > 0) {
      form.season_id = seasons.value[0].id
    }
  }
}

// Watch visibility
watch(() => props.visible, (newVal) => {
  if (newVal) {
    fetchSuppliers()
    loadSeasons()
    if (props.purchase) {
      populateForm(props.purchase)
    } else {
      resetForm()
      // Auto-select season again (in case seasons loaded after)
      const current = getCurrentSeason(seasons.value)
      if (current) form.season_id = current.id
    }
  }
})

// Supplier modal
const openSupplierModal = () => {
  newSupplier.name = ''
  newSupplier.contact_person = ''
  newSupplier.phone = ''
  newSupplier.email = ''
  newSupplier.address = ''
  supplierError.value = ''
  supplierModalVisible.value = true
}

const saveSupplier = async () => {
  if (!newSupplier.name.trim()) {
    supplierError.value = 'Supplier name is required.'
    return
  }
  supplierLoading.value = true
  supplierError.value = ''
  try {
    const res = await axios.post('/suppliers', newSupplier)
    // Add to dropdown
    suppliers.value.push(res.data)
    // Auto-select the new supplier
    form.supplier_id = res.data.id
    supplierModalVisible.value = false
  } catch (err) {
    supplierError.value = err.response?.data?.message || 'Failed to add supplier'
  } finally {
    supplierLoading.value = false
  }
}

// Save purchase
const savePurchase = async () => {
  error.value = ''

  if (!form.supplier_id) {
    error.value = 'Please select a supplier.'
    return
  }
  if (!form.season_id) {
    error.value = 'Please select a season.'
    return
  }
  if (!form.area || form.area <= 0) {
    error.value = 'Area must be greater than 0.'
    return
  }
  if (!form.weight_kg || form.weight_kg <= 0) {
    error.value = 'Weight must be greater than 0.'
    return
  }
  if (!form.cost || form.cost <= 0) {
    error.value = 'Cost must be greater than 0.'
    return
  }

  loading.value = true

  try {
    const payload = {
      supplier_id: form.supplier_id,
      purchase_date: form.purchase_date,
      area: form.area,
      weight_kg: form.weight_kg,
      cost: form.cost,
      notes: form.notes?.trim() || null,
      season_id: form.season_id,
    }

    let response
    if (props.purchase) {
      response = await purchasesStore.updatePurchase(props.purchase.id, payload)
    } else {
      response = await purchasesStore.createPurchase(payload)
    }

    resetForm()
    emit('saved', response)
    emit('close')
  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors
      error.value = Object.values(errors).flat().join(', ')
    } else if (err.response?.data?.message) {
      error.value = err.response.data.message
    } else {
      error.value = err.message || 'Failed to save purchase'
    }
  } finally {
    loading.value = false
  }
}

// Close modal
const closeModal = () => {
  resetForm()
  emit('close')
}

onMounted(() => {
  if (props.visible) {
    fetchSuppliers()
    loadSeasons()
    if (props.purchase) populateForm(props.purchase)
  }
})
</script>