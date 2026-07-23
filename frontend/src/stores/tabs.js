import { defineStore } from 'pinia'
import { ref, computed, shallowRef } from 'vue'

export const useTabsStore = defineStore('tabs', () => {
  const openTabs = ref([])
  const activeTab = ref(null)

  const activeTabComponent = computed(() => {
    return activeTab.value?.component || null
  })

  function openTab(tab) {
    // ✅ If component is a string, keep it as string – we'll resolve it in the layout
    const existing = openTabs.value.find(t => t.path === tab.path)
    if (existing) {
      activeTab.value = existing
      return
    }

    const newTab = {
      ...tab,
      permanent: tab.permanent || false,
    }
    openTabs.value.push(newTab)
    activeTab.value = newTab

    console.log('📂 Tab opened:', newTab.path, 'Component:', typeof newTab.component)
  }

  function closeTab(tab) {
    const index = openTabs.value.indexOf(tab)
    if (index > -1) {
      openTabs.value.splice(index, 1)
      if (activeTab.value === tab) {
        activeTab.value = openTabs.value[openTabs.value.length - 1] || null
      }
    }
  }

  function setActiveTab(tab) {
    activeTab.value = tab
  }

  return {
    openTabs,
    activeTab,
    activeTabComponent,
    openTab,
    closeTab,
    setActiveTab,
  }
})