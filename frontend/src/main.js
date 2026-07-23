import './assets/main.css'
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import axios from 'axios'

// Set base URL
axios.defaults.baseURL = 'http://127.0.0.1:8000/api'

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