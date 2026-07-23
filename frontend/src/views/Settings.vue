<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-bold text-slate-800">⚙️ Settings</h1>

    <div class="flex overflow-x-auto border-b border-slate-200 bg-white rounded-t-lg">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        @click="activeTab = tab.key"
        class="px-4 py-2 text-sm font-medium transition whitespace-nowrap border-b-2"
        :class="activeTab === tab.key ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-700'"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="bg-white rounded-b-lg p-6 shadow-sm">
      <component :is="activeComponent" />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, markRaw } from 'vue'
import BusinessSettings from '../components/settings/BusinessSettings.vue'
import LocationSettings from '../components/settings/LocationSettings.vue'
import SeasonSettings from '../components/settings/SeasonSettings.vue'
import ExpenseCategorySettings from '../components/settings/ExpenseCategorySettings.vue'
import PaymentTypeSettings from '../components/settings/PaymentTypeSettings.vue'

const tabs = [
  { key: 'business', label: '🏢 Business', component: markRaw(BusinessSettings) },
  { key: 'locations', label: '📍 Locations', component: markRaw(LocationSettings) },
  { key: 'seasons', label: '📅 Seasons', component: markRaw(SeasonSettings) },
  { key: 'expense-categories', label: '📂 Expense Categories', component: markRaw(ExpenseCategorySettings) },
  { key: 'payment-types', label: '💳 Payment Types', component: markRaw(PaymentTypeSettings) },
]

const activeTab = ref('business')

const activeComponent = computed(() => {
  const tab = tabs.find(t => t.key === activeTab.value)
  return tab ? tab.component : null
})
</script>