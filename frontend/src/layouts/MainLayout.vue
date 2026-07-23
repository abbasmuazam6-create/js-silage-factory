<template>
  <div class="flex h-screen bg-primary-50">
    <!-- Mobile Overlay -->
    <div
      v-if="isMainSidebarOpen && isMobile"
      class="fixed inset-0 z-40 bg-black/50 lg:hidden"
      @click="closeMainSidebar"
    ></div>

    <!-- ===== MAIN SIDEBAR ===== -->
    <aside
      ref="mainSidebarRef"
      :class="[
        'fixed top-0 left-0 z-50 h-full bg-primary-900 text-white transition-all duration-300 ease-in-out',
        'flex flex-col',
        isMobile ? 'w-64' : (isMainCollapsed ? 'w-20' : 'w-64'),
        isMobile && !isMainSidebarOpen ? '-translate-x-full' : 'translate-x-0',
        !isMobile && 'relative'
      ]"
    >
      <!-- Header -->
      <div class="flex h-16 items-center justify-between border-b border-primary-700 px-4">
        <div class="flex items-center gap-3 overflow-hidden">
          <img
            src="https://i.ibb.co/xKG273Sm/JS-Final-logo.jpg"
            alt="Logo"
            class="h-12 w-12 flex-shrink-0 object-contain"
          />
          <span v-if="!isMainCollapsed || isMobile" class="truncate text-lg font-bold">JS Silage</span>
        </div>
        <button
          v-if="!isMobile"
          @click="toggleMainCollapse"
          class="rounded p-1 text-xl hover:bg-primary-800"
        >
          <span v-if="isMainCollapsed">☰</span>
          <span v-else>✕</span>
        </button>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 overflow-y-auto p-4 space-y-1">
        <!-- Dashboard -->
        <a
          href="#"
          @click.prevent="openTab({ path: 'dashboard', label: 'Dashboard', icon: '📊', component: 'Dashboard' })"
          class="flex items-center gap-3 rounded-lg px-4 py-2 hover:bg-primary-800 transition"
          :class="{ 'bg-primary-800': tabsStore.activeTab?.path === 'dashboard' }"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
          </svg>
          <span v-if="!isMainCollapsed || isMobile">Dashboard</span>
        </a>

        <!-- Modules -->
        <div v-for="module in filteredModules" :key="module.name">
          <div
            v-if="module.children && module.children.length > 0"
            class="flex cursor-pointer items-center gap-3 rounded-lg px-4 py-2 hover:bg-primary-800 transition"
            :class="{ 'bg-primary-800': expandedModules[module.name] }"
            @click="toggleModule(module.name)"
          >
            <span class="text-xl">{{ module.icon }}</span>
            <span v-if="!isMainCollapsed || isMobile" class="flex-1">{{ module.name }}</span>
            <span v-if="!isMainCollapsed || isMobile" class="text-sm transition-transform" :class="{ 'rotate-90': expandedModules[module.name] }">
              ▸
            </span>
          </div>

          <!-- Single module without children (Reports) - Direct link -->
          <a
            v-if="!module.children || module.children.length === 0"
            href="#"
            @click.prevent="openTab({ path: module.path, label: module.name, icon: module.icon, component: module.component })"
            class="flex items-center gap-3 rounded-lg px-4 py-2 hover:bg-primary-800 transition"
            :class="{ 'bg-primary-800': tabsStore.activeTab?.path === module.path }"
          >
            <span class="text-xl">{{ module.icon }}</span>
            <span v-if="!isMainCollapsed || isMobile">{{ module.name }}</span>
          </a>

          <!-- Module Children -->
          <div
            v-if="module.children && expandedModules[module.name] && (!isMainCollapsed || isMobile)"
            class="ml-6 space-y-0.5 border-l border-primary-700 pl-2"
          >
            <a
              v-for="child in module.children"
              :key="child.path"
              href="#"
              @click.prevent="openTab(child)"
              class="block rounded px-4 py-1.5 text-xs text-primary-200 hover:bg-primary-800 hover:text-white transition"
              :class="{ 'bg-primary-800 text-white': tabsStore.activeTab?.path === child.path }"
            >
              {{ child.label }}
            </a>
          </div>
        </div>
      </nav>

      <!-- Logout -->
      <div class="border-t border-primary-700 p-4">
        <a
          href="#"
          @click.prevent="logout"
          class="flex items-center gap-3 rounded-lg px-4 py-2 hover:bg-primary-800 transition"
        >
          <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          <span v-if="!isMainCollapsed || isMobile">Logout</span>
        </a>
      </div>
    </aside>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="flex flex-1 flex-col overflow-hidden">
      <header class="bg-white shadow-sm flex items-center justify-between overflow-x-auto">
        <div class="flex flex-1 items-center gap-1 px-2">
          <button
            @click="toggleMainSidebar"
            class="rounded p-1 hover:bg-primary-100 lg:hidden"
          >
            <span class="text-2xl">☰</span>
          </button>

          <button
            v-for="tab in tabsStore.openTabs"
            :key="tab.path"
            @click="tabsStore.setActiveTab(tab)"
            class="group flex items-center gap-2 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-medium transition"
            :class="tabsStore.activeTab?.path === tab.path ? 'border-primary-500 text-primary-600' : 'border-transparent text-slate-500 hover:border-primary-300'"
          >
            <span>{{ tab.icon }}</span>
            {{ tab.label }}
            <span
              v-if="!tab.permanent"
              @click.stop="tabsStore.closeTab(tab)"
              class="ml-2 rounded-full p-0.5 text-slate-400 hover:bg-primary-100 hover:text-slate-600"
            >
              ✕
            </span>
          </button>
        </div>
        <div class="flex items-center gap-3 px-4">
          <span class="text-sm text-slate-600">{{ user?.name }}</span>
          <span class="text-xs text-slate-400">({{ user?.role }})</span>
        </div>
      </header>

      <main class="flex-1 overflow-y-auto p-4 sm:p-6 bg-primary-50">
        <KeepAlive>
          <component
            :is="tabsStore.activeTabComponent"
            v-if="tabsStore.activeTab"
            :key="tabsStore.activeTab.path"
            v-bind="tabsStore.activeTab.props || {}"
          />
        </KeepAlive>
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, markRaw } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useTabsStore } from '../stores/tabs'
import axios from 'axios'
import Dashboard from '../views/Dashboard.vue'
import Bunkers from '../views/Bunkers.vue'
import BunkerDetail from '../views/BunkerDetail.vue'
import SilagePurchases from '../views/SilagePurchases.vue'
import SilageExpenses from '../views/SilageExpenses.vue'
import Suppliers from '../views/Suppliers.vue'
import Sales from '../views/Sales.vue'
import Customers from '../views/Customers.vue'
import Reports from '../views/reports/Reports.vue'
import Settings from '../views/Settings.vue'
import Users from '../views/Users.vue'

const router = useRouter()
const authStore = useAuthStore()
const tabsStore = useTabsStore()
const user = authStore.user

// Logo URL - Original
const logoUrl = ref('https://i.ibb.co/xKG273Sm/JS-Final-logo.jpg')

// Placeholder
const Placeholder = {
  template: `<div class="p-6 text-slate-600"><h2 class="text-xl font-semibold text-slate-700">Module Under Construction</h2><p>This page will be available soon.</p></div>`
}

// Component map with markRaw
const componentMap = {
  'Dashboard': markRaw(Dashboard),
  'Bunkers': markRaw(Bunkers),
  'BunkerDetail': markRaw(BunkerDetail),
  'SilagePurchases': markRaw(SilagePurchases),
  'SilageExpenses': markRaw(SilageExpenses),
  'Suppliers': markRaw(Suppliers),
  'Sales': markRaw(Sales),
  'Customers': markRaw(Customers),
  'Reports': markRaw(Reports),
  'Settings': markRaw(Settings),
  'Users': markRaw(Users),
  'Placeholder': markRaw(Placeholder),
}

// Mobile detection
const isMobile = ref(window.innerWidth < 1024)

const handleResize = () => {
  isMobile.value = window.innerWidth < 1024
  if (!isMobile.value) {
    isMainSidebarOpen.value = true
  } else {
    isMainSidebarOpen.value = false
  }
}

// Sidebar states
const mainSidebarRef = ref(null)
const isMainSidebarOpen = ref(false)
const isMainCollapsed = ref(false)

const toggleMainSidebar = () => { isMainSidebarOpen.value = !isMainSidebarOpen.value }
const closeMainSidebar = () => { if (isMobile.value) isMainSidebarOpen.value = false }

const toggleMainCollapse = () => {
  if (!isMobile.value) {
    isMainCollapsed.value = !isMainCollapsed.value
  }
}

// ===== MODULES =====
const modules = [
  {
    name: 'Silage',
    icon: '🌾',
    children: [
      { path: 'bunkers', label: 'Bunkers', component: 'Bunkers' },
      { path: 'silage-purchases', label: 'Purchases', component: 'SilagePurchases' },
      { path: 'silage-expenses', label: 'Expenses', component: 'SilageExpenses' },
      { path: 'suppliers', label: 'Suppliers', component: 'Suppliers' },
    ]
  },
  {
    name: 'Sales & Customers',
    icon: '🛒',
    children: [
      { path: 'sales', label: 'Sales', component: 'Sales' },
      { path: 'customers', label: 'Customers', component: 'Customers' },
    ]
  },
  {
    name: 'Reports',
    icon: '📊',
    path: 'reports',
    component: 'Reports'
  },
  {
    name: 'Settings',
    icon: '⚙️',
    children: [
      { path: 'settings', label: 'General', component: 'Settings' },
      { path: 'users', label: 'Users', component: 'Users' },
    ]
  }
]

// Silage expanded by default, all others collapsed
const expandedModules = ref(
  modules.reduce((acc, m) => {
    acc[m.name] = m.name === 'Silage'
    return acc
  }, {})
)

const toggleModule = (name) => {
  expandedModules.value[name] = !expandedModules.value[name]
}

// ✅ Filter modules based on user role - Hide Users for non-admin
const filteredModules = computed(() => {
  const userRole = user?.role
  
  // If user is admin or owner, show all modules
  if (userRole === 'admin' || userRole === 'owner') {
    return modules
  }
  
  // For non-admin users, hide the Users section from Settings
  return modules.map(module => {
    // If this is the Settings module, filter out the Users child
    if (module.name === 'Settings' && module.children) {
      return {
        ...module,
        children: module.children.filter(child => child.path !== 'users')
      }
    }
    return module
  })
})

const getComponent = (comp) => {
  if (typeof comp === 'string') {
    return componentMap[comp] || Placeholder
  }
  return comp || Placeholder
}

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const fetchLogo = async () => {
  try {
    const { data } = await axios.get('/settings', {
      headers: getAuthHeader()
    })
    if (data.logo) {
      logoUrl.value = data.logo
    }
  } catch (err) {
    console.error('Failed to fetch logo:', err)
  }
}

const dashboardTab = {
  path: 'dashboard',
  label: 'Dashboard',
  component: Dashboard,
  permanent: true
}

const openTab = (item) => {
  if (item.path === 'dashboard') {
    tabsStore.openTab(dashboardTab)
    return
  }

  const component = getComponent(item.component)

  tabsStore.openTab({
    path: item.path,
    label: item.label,
    icon: item.icon || '📄',
    component: component,
    permanent: false
  })
}

onMounted(() => {
  window.addEventListener('resize', handleResize)
  handleResize()
  fetchLogo()
  tabsStore.openTab(dashboardTab)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
})

const logout = () => {
  authStore.logout()
  router.push('/')
}
</script>