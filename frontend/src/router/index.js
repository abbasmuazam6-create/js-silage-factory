import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/Login.vue'
import Dashboard from '../views/Dashboard.vue'
import MainLayout from '../layouts/MainLayout.vue'
import { useAuthStore } from '../stores/auth'
import Users from '../views/Users.vue'

// In routes array:


// Placeholder components for modules we haven't built yet
const Placeholder = {
  template: `<div class="p-6 text-slate-600"><h2 class="text-xl font-semibold text-slate-700">Module Under Construction</h2><p>This page will be available soon.</p></div>`
}

const routes = [
  { path: '/', name: 'login', component: Login },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      { path: 'dashboard', name: 'dashboard', component: Dashboard, meta: { title: 'Dashboard' } },
      { path: 'bunkers', name: 'bunkers', component: () => import('../views/Bunkers.vue'), meta: { title: 'Bunkers' } },
      { path: 'silage-purchases', name: 'silage-purchases', component: () => import('../views/SilagePurchases.vue'), meta: { title: 'Silage Purchases' } },
      { path: 'silage-suppliers', name: 'silage-suppliers', component: () => import('../views/Suppliers.vue'), meta: { title: 'Suppliers' } },
      { path: 'silage-expenses', name: 'silage-expenses', component: () => import('../views/SilageExpenses.vue'), meta: { title: 'Silage Expenses' } },
      { path: 'wanda-production', name: 'wanda-production', component: Placeholder, meta: { title: 'Wanda Production' } },
      { path: 'wanda-stock', name: 'wanda-stock', component: Placeholder, meta: { title: 'Wanda Stock' } },
      { path: 'wanda-raw-materials', name: 'wanda-raw-materials', component: Placeholder, meta: { title: 'Wanda Raw Materials' } },
      { path: 'wanda-expenses', name: 'wanda-expenses', component: Placeholder, meta: { title: 'Wanda Expenses' } },
      { path: 'sales', name: 'sales', component: () => import('../views/Sales.vue'), meta: { title: 'Sales' } },
      { path: 'payments', name: 'payments', component: Placeholder, meta: { title: 'Payments' } },
      { path: 'customers', name: 'customers', component: () => import('../views/Customers.vue'), meta: { title: 'Customers' } },
      { path: 'general-expenses', name: 'general-expenses', component: Placeholder, meta: { title: 'General Expenses' } },
      { path: 'reports', name: 'reports', component: () => import('../views/reports/Reports.vue'), meta: { title: 'Reports' } },
      {
  path: 'users',
  name: 'users',
  component: () => import('../views/Users.vue'),
  meta: { title: 'Users' }
},
      { path: 'settings', name: 'settings', component: () => import('../views/Settings.vue'), meta: { title: 'Settings' } },    ],
  },
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore()
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/')
  } else {
    next()
  }
})


export default router