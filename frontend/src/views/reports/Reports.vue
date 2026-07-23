<template>
  <div class="space-y-4">
    <h1 class="text-2xl font-bold text-slate-800">Reports</h1>

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
import BunkersReport from './BunkersReport.vue'
import SeasonalReport from './SeasonalReport.vue'

const tabs = [
  { key: 'bunkers', label: '🏠 Bunkers Report', component: markRaw(BunkersReport) },
  { key: 'seasonal', label: '📅 Seasonal Report', component: markRaw(SeasonalReport) },
]

const activeTab = ref('bunkers')

const activeComponent = computed(() => {
  const tab = tabs.find(t => t.key === activeTab.value)
  return tab ? tab.component : null
})
</script>