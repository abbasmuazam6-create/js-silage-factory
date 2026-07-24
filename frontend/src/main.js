import './assets/main.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import axios from 'axios'

// ✅ HARDCODED BACKEND URL (NO ENV VARIABLES)
axios.defaults.baseURL = 'https://js-silage-factory-8xfh-4x6r5n324-abbasmuazam6.vercel.app/api'

// Always attach token if it exists
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.mount('#app')