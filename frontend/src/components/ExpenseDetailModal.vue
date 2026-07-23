<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-slate-200 pb-3">
        <h3 class="text-base font-bold text-slate-700">Expense Details</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600">✕</button>
      </div>

      <!-- Content -->
      <div v-if="expense" class="mt-4 space-y-3">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-medium text-slate-500">Category</label>
            <p class="text-sm font-medium text-slate-700">
              <span
                class="inline-block w-2 h-2 rounded-full mr-1"
                :style="{ backgroundColor: expense.category?.color || '#6B7280' }"
              ></span>
              {{ expense.category?.name || 'Uncategorized' }}
            </p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Amount</label>
            <p class="text-sm font-medium text-slate-700">Rs. {{ numberFormat(expense.amount) }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Date</label>
            <p class="text-sm font-medium text-slate-700">{{ formatDate(expense.date) }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Bunker</label>
            <p class="text-sm font-medium text-slate-700">{{ expense.bunker?.name || 'Unknown' }}</p>
            <p class="text-xs text-slate-400">{{ expense.bunker?.location || 'No location' }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Season</label>
            <p class="text-sm font-medium text-slate-700">{{ expense.season?.name || 'N/A' }}</p>
          </div>
          <div class="col-span-2">
            <label class="block text-xs font-medium text-slate-500">Notes</label>
            <p class="text-sm text-slate-700">{{ expense.notes || 'No notes' }}</p>
          </div>
        </div>

        <!-- Actions -->
        <div class="border-t border-slate-200 pt-3 flex justify-end gap-2">
          <button @click="$emit('close')" class="btn-outline px-4 py-2 text-sm">Close</button>
          <button @click="editExpense" class="btn-primary px-4 py-2 text-sm">✏️ Edit</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useTabsStore } from '../stores/tabs'
import AddExpenseModal from './AddExpenseModal.vue'

const props = defineProps({
  visible: Boolean,
  expense: Object,
})

const emit = defineEmits(['close', 'saved'])

const tabsStore = useTabsStore()

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

const editExpense = () => {
  tabsStore.openTab({
    path: `edit-expense-${props.expense.id}`,
    label: 'Edit Expense',
    icon: '✏️',
    component: AddExpenseModal,
    props: {
      visible: true,
      bunkerId: props.expense.bunker_id,
      seasonId: props.expense.season_id,
      editData: props.expense,
    }
  })
  emit('close')
}
</script>