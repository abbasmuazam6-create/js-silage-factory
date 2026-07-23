<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md max-h-[90vh] overflow-y-auto rounded-lg bg-white p-4 shadow-xl">
      <h3 class="mb-3 text-base font-bold text-slate-700">
        {{ isEditing ? 'Edit Purchase' : 'Add Purchase' }}
        <span v-if="preSelectedBunker || bunkerName" class="text-xs font-normal text-slate-500 block">
          Bunker: {{ preSelectedBunker?.name || bunkerName }}
          <span v-if="preSelectedBunker?.location || bunkerLocation">
            ({{ preSelectedBunker?.location || bunkerLocation || 'No location' }})
          </span>
        </span>
      </h3>

      <form @submit.prevent="savePurchase" class="space-y-3">
        <!-- Supplier -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Supplier *</label>
          <div class="flex gap-1.5">
            <input
              v-model="supplierSearch"
              type="text"
              list="supplierList"
              class="flex-1 rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
              placeholder="Search or select supplier..."
              @input="onSupplierSearch"
            />
            <datalist id="supplierList">
              <option v-for="supplier in filteredSuppliers" :key="supplier.id" :value="supplier.name" />
            </datalist>
            <input type="hidden" v-model="form.supplier_id" />
            <button type="button" @click="openAddSupplier" class="btn-primary px-2.5 py-1.5 text-xs">+</button>
          </div>
          <p v-if="!form.supplier_id && supplierSearch" class="mt-0.5 text-[10px] text-amber-600">
            No supplier selected. Please select from the list.
          </p>
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

        <!-- Area (Acres) -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Area (Acres)</label>
          <input
            v-model.number="form.area"
            type="number"
            step="0.01"
            min="0"
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
          />
        </div>

        <!-- Load Rows -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Loads (Weight in KG) *</label>
          <div v-for="(load, index) in form.loads" :key="index" class="flex items-center gap-1.5 mt-1">
            <input
              v-model.number="load.weight"
              type="number"
              step="0.01"
              min="0.01"
              placeholder="KG"
              class="flex-1 rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
              required
            />
            <button type="button" @click="removeLoad(index)" class="text-red-500 hover:text-red-700 text-sm">✕</button>
          </div>
          <button type="button" @click="addLoad" class="mt-1 text-xs text-primary-600 hover:underline">+ Add Load</button>
        </div>

        <!-- Total KG -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Total KG</label>
          <input
            :value="totalWeight"
            type="text"
            disabled
            class="w-full rounded-lg border border-slate-300 bg-slate-50 px-2 py-1.5 text-xs"
          />
        </div>

        <!-- Total Cost -->
        <div>
          <label class="block text-xs font-medium text-slate-700">Total Cost (Rs.) *</label>
          <input
            v-model.number="form.total_cost"
            type="number"
            step="0.01"
            min="0"
            required
            class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"
          />
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
            @click="deleteGroup"
            class="btn-danger px-3 py-1.5 text-xs"
          >
            Delete Group
          </button>
          <div class="flex gap-2 ml-auto">
            <button type="button" @click="closeModal" class="btn-outline px-3 py-1.5 text-xs">Cancel</button>
            <button type="submit" class="btn-primary px-3 py-1.5 text-xs" :disabled="loading">
              {{ loading ? 'Saving...' : isEditing ? 'Update' : 'Save' }}
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Add Supplier Modal -->
    <div v-if="supplierModalVisible" class="fixed inset-0 z-60 flex items-center justify-center bg-black/50 p-4">
      <div class="w-full max-w-sm max-h-[80vh] overflow-y-auto rounded-lg bg-white p-4 shadow-xl">
        <h4 class="mb-3 text-base font-bold text-slate-700">Add Supplier</h4>
        <form @submit.prevent="saveSupplier" class="space-y-3">
          <div>
            <label class="block text-xs font-medium text-slate-700">Name *</label>
            <input v-model="newSupplier.name" type="text" required class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700">Phone</label>
            <input v-model="newSupplier.phone" type="text" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs" />
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-700">Address</label>
            <textarea v-model="newSupplier.address" rows="2" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-xs"></textarea>
          </div>
          <div v-if="supplierError" class="text-xs text-red-600">{{ supplierError }}</div>
          <div class="flex justify-end gap-2 border-t border-slate-200 pt-3">
            <button type="button" @click="supplierModalVisible = false" class="btn-outline px-3 py-1.5 text-xs">Cancel</button>
            <button type="submit" class="btn-primary px-3 py-1.5 text-xs" :disabled="supplierLoading">
              {{ supplierLoading ? 'Saving...' : 'Add' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :visible="confirmModalVisible"
      title="Delete Purchase Group"
      message="Are you sure you want to delete this entire purchase group?"
      confirm-text="Delete"
      @confirm="executeDelete"
      @cancel="confirmModalVisible = false"
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import axios from 'axios'
import ConfirmModal from './ConfirmModal.vue'

const props = defineProps({
  visible: Boolean,
  bunkerId: String,
  seasonId: String,
  editData: Object,
  preSelectedBunker: Object,
  bunkerName: String,
  bunkerLocation: String,
})

const emit = defineEmits(['close', 'saved'])

const loading = ref(false)
const error = ref('')
const suppliers = ref([])
const bunkers = ref([])

// ===== NEW: Supplier Search =====
const supplierSearch = ref('')
const filteredSuppliers = ref([])

// Filter suppliers as user types
const onSupplierSearch = () => {
  const search = supplierSearch.value.toLowerCase().trim()
  if (!search) {
    filteredSuppliers.value = suppliers.value
    return
  }
  filteredSuppliers.value = suppliers.value.filter(s => 
    s.name.toLowerCase().includes(search)
  )
  // If exact match found, set supplier_id
  const match = suppliers.value.find(s => s.name.toLowerCase() === search)
  if (match) {
    form.supplier_id = match.id
  } else {
    // If no exact match, clear supplier_id
    form.supplier_id = ''
  }
}

// Watch for supplier selection from datalist
watch(supplierSearch, (val) => {
  if (!val) {
    form.supplier_id = ''
    return
  }
  const match = suppliers.value.find(s => s.name === val)
  if (match) {
    form.supplier_id = match.id
  }
})
// ===== END NEW =====

const supplierModalVisible = ref(false)
const supplierLoading = ref(false)
const supplierError = ref('')
const newSupplier = reactive({ name: '', phone: '', address: '' })

const form = reactive({
  supplier_id: '',
  date: new Date().toISOString().split('T')[0],
  loads: [{ weight: null }],
  total_cost: 0,
  area: null,
})

const editingIds = ref([])
const isEditing = computed(() => !!props.editData?.supplier_id)
const confirmModalVisible = ref(false)

const totalWeight = computed(() => {
  return form.loads.reduce((sum, l) => sum + (l.weight || 0), 0)
})

// Set bunker_id from props
watch(() => props.bunkerId, (id) => {
  if (id) {
    form.bunker_id = id
  }
}, { immediate: true })

watch(() => props.preSelectedBunker, (bunker) => {
  if (bunker) {
    form.bunker_id = bunker.id
  }
}, { immediate: true })

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const fetchSuppliers = async () => {
  try {
    const res = await axios.get('/suppliers', {
      headers: getAuthHeader()
    })
    suppliers.value = res.data
    filteredSuppliers.value = res.data
  } catch (err) {
    console.error('Failed to fetch suppliers:', err)
  }
}

const addLoad = () => {
  form.loads.push({ weight: null })
}

const removeLoad = (index) => {
  if (form.loads.length > 1) {
    form.loads.splice(index, 1)
  }
}

const resetForm = () => {
  form.supplier_id = ''
  supplierSearch.value = ''
  filteredSuppliers.value = []
  form.date = new Date().toISOString().split('T')[0]
  form.loads = [{ weight: null }]
  form.total_cost = 0
  form.area = null
  editingIds.value = []
  error.value = ''
  confirmModalVisible.value = false
}

const openAddSupplier = () => {
  newSupplier.name = ''
  newSupplier.phone = ''
  newSupplier.address = ''
  supplierError.value = ''
  supplierModalVisible.value = true
}

const saveSupplier = async () => {
  if (!newSupplier.name.trim()) {
    supplierError.value = 'Supplier name is required'
    return
  }
  supplierLoading.value = true
  supplierError.value = ''
  try {
    const res = await axios.post('/suppliers', newSupplier, {
      headers: getAuthHeader()
    })
    suppliers.value.push(res.data)
    filteredSuppliers.value = suppliers.value
    form.supplier_id = res.data.id
    supplierSearch.value = res.data.name
    supplierModalVisible.value = false
  } catch (err) {
    supplierError.value = err.response?.data?.message || 'Failed to add supplier'
  } finally {
    supplierLoading.value = false
  }
}

const savePurchase = async () => {
  error.value = ''
  
  // Check if supplier is selected
  const supplierId = form.supplier_id
  if (!supplierId) {
    error.value = 'Please select a supplier'
    return
  }
  if (!props.bunkerId && !form.bunker_id) {
    error.value = 'Please select a bunker'
    return
  }
  if (totalWeight.value <= 0) {
    error.value = 'Please add at least one load with weight'
    return
  }
  if (!form.total_cost || form.total_cost <= 0) {
    error.value = 'Please enter total cost'
    return
  }

  const bunkerId = form.bunker_id || props.bunkerId

  loading.value = true
  try {
    if (isEditing.value) {
      const [firstId, ...restIds] = editingIds.value
      
      for (const id of restIds) {
        await axios.delete(`/silage-purchases/${id}`, {
          headers: getAuthHeader()
        })
      }
      
      await axios.put(`/silage-purchases/${firstId}`, {
        supplier_id: supplierId,
        purchase_date: form.date,
        weight_kg: totalWeight.value,
        cost: form.total_cost,
        area: form.area,
        notes: null,
      }, {
        headers: getAuthHeader()
      })
    } else {
      await axios.post('/silage-purchases', {
        supplier_id: supplierId,
        bunker_id: bunkerId,
        season_id: props.seasonId || null,
        purchase_date: form.date,
        weight_kg: totalWeight.value,
        cost: form.total_cost,
        area: form.area,
        notes: null,
      }, {
        headers: getAuthHeader()
      })
    }

    emit('saved')
    closeModal()
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Failed to save purchase'
  } finally {
    loading.value = false
  }
}

const deleteGroup = () => {
  confirmModalVisible.value = true
}

const executeDelete = async () => {
  confirmModalVisible.value = false
  loading.value = true
  error.value = ''
  try {
    for (const id of editingIds.value) {
      await axios.delete(`/silage-purchases/${id}`, {
        headers: getAuthHeader()
      })
    }
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
  if (data && data.supplier_id) {
    form.supplier_id = data.supplier_id
    // Set supplier search value
    const supplier = suppliers.value.find(s => s.id === data.supplier_id)
    supplierSearch.value = supplier ? supplier.name : ''
    
    form.date = data.date ? new Date(data.date).toISOString().split('T')[0] : new Date().toISOString().split('T')[0]
    form.loads = [{ weight: data.total_kg || 0 }]
    form.total_cost = data.total_cost || 0
    form.area = data.area || null
    editingIds.value = data.items?.map(p => p.id) || []
  } else {
    resetForm()
  }
}, { immediate: true })

watch(() => props.visible, (val) => {
  if (val) {
    fetchSuppliers()
  }
})
</script>