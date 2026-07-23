import './assets/main.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import axios from 'axios'

// Set base URL from environment variable, fallback to localhost for development
axios.defaults.baseURL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api'

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