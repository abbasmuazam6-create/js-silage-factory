<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-200 pb-3">
        <h3 class="text-base font-bold text-slate-700">Purchase Details</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600">✕</button>
      </div>

      <!-- Content -->
      <div v-if="purchase" class="mt-4 space-y-3">
        <!-- Purchase Code & Status -->
        <div class="flex items-center justify-between">
          <span class="text-sm font-semibold text-slate-700">{{ purchase.purchase_code }}</span>
          <span class="text-xs px-2 py-1 rounded-full" :class="purchase.status === 'available' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'">
            {{ purchase.status || 'Available' }}
          </span>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-500">Supplier</label>
            <p class="text-sm font-medium text-slate-700">{{ purchase.supplier?.name || 'Unknown' }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Bunker</label>
            <p class="text-sm font-medium text-slate-700">{{ purchase.bunker?.name || 'Unknown' }}</p>
            <p class="text-xs text-slate-400">{{ purchase.bunker?.location || 'No location' }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Date</label>
            <p class="text-sm font-medium text-slate-700">{{ formatDate(purchase.purchase_date) }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Season</label>
            <p class="text-sm font-medium text-slate-700">{{ purchase.season?.name || 'N/A' }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Weight (KG)</label>
            <p class="text-sm font-medium text-slate-700">{{ numberFormat(purchase.weight_kg) }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Cost</label>
            <p class="text-sm font-medium text-slate-700">Rs. {{ numberFormat(purchase.cost) }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Cost per KG</label>
            <p class="text-sm font-medium text-slate-700">Rs. {{ numberFormat(purchase.cost_per_kg) }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Area (Acres)</label>
            <p class="text-sm font-medium text-slate-700">{{ purchase.area ? numberFormat(purchase.area) + ' acres' : '-' }}</p>
          </div>
          <div class="col-span-2">
            <label class="block text-xs font-medium text-slate-500">Notes</label>
            <p class="text-sm text-slate-700">{{ purchase.notes || 'No notes' }}</p>
          </div>
        </div>

        <!-- Actions – Only Close button -->
        <div class="border-t border-slate-200 pt-3 flex justify-end">
          <button @click="$emit('close')" class="btn-outline px-4 py-2 text-sm">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  visible: Boolean,
  purchase: Object,
})

const emit = defineEmits(['close', 'saved'])

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
</script>