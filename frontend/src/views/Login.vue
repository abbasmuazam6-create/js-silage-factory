<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-primary-100 p-4">
    <div class="w-full max-w-sm sm:max-w-md">
      <!-- Logo Section -->
      <div class="text-center mb-6 sm:mb-8">
        <img
          src="https://i.ibb.co/xKG273Sm/JS-Final-logo.jpg"
          alt="JS Silage & Wanda Factory"
          class="w-24 h-24 sm:w-32 sm:h-32 object-contain mx-auto mb-3 sm:mb-4"
        />
        <h1 class="text-lg sm:text-2xl font-bold text-primary-800 tracking-tight leading-tight">
          JS Silage &amp; Wanda Factory
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5 sm:mt-1">Export Quality Products</p>
      </div>

      <!-- Login Card -->
      <div class="bg-white rounded-2xl shadow-lg p-6 sm:p-8 border border-primary-100">
        <div class="mb-5 sm:mb-6">
          <h2 class="text-xl sm:text-2xl font-semibold text-slate-800">Login</h2>
          <p class="text-xs sm:text-sm text-slate-500">Sign in to access your dashboard</p>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4 sm:space-y-5">
          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
            <input
              v-model="email"
              type="email"
              class="w-full px-4 py-3 sm:py-3.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-200 outline-none text-base"
              placeholder="admin@jssilage.com"
              required
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
            <div class="relative">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                class="w-full px-4 py-3 sm:py-3.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition duration-200 outline-none text-base pr-12"
                placeholder="Enter your password"
                required
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xl"
              >
                <span v-if="showPassword">👁️</span>
                <span v-else>👁️‍🗨️</span>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-between">
            <label class="flex items-center text-sm text-slate-600 cursor-pointer">
              <input
                v-model="rememberMe"
                type="checkbox"
                class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 w-4 h-4 mr-2"
              />
              <span>Remember me</span>
            </label>
          </div>

          <button
            type="submit"
            class="w-full bg-primary-600 hover:bg-primary-700 text-white font-medium py-3.5 sm:py-4 rounded-xl transition duration-200 shadow-sm hover:shadow-md text-base sm:text-lg"
            :disabled="loading"
          >
            {{ loading ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>

        <div v-if="error" class="mt-4 p-3 bg-red-50 border border-red-200 rounded-xl">
          <p class="text-red-600 text-sm">{{ error }}</p>
        </div>

        <div class="mt-6 text-center">
          <p class="text-xs sm:text-sm text-slate-500">
            Don't have an account?
            <a href="#" class="text-primary-600 hover:text-primary-700 font-medium hover:underline">Contact Admin</a>
          </p>
        </div>
      </div>

      <p class="text-center text-[10px] sm:text-xs text-slate-400 mt-5 sm:mt-6">
        &copy; 2026 JS Silage &amp; Wanda Factory. All rights reserved.
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import axios from 'axios'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const rememberMe = ref(false)
const error = ref('')
const loading = ref(false)

const handleLogin = async () => {
  error.value = ''
  loading.value = true

  try {
    const response = await axios.post('/login', {
      email: email.value,
      password: password.value,
    })
    const { user, token } = response.data
    authStore.setAuth(user, token)

    if (rememberMe.value) {
      localStorage.setItem('remembered_email', email.value)
    } else {
      localStorage.removeItem('remembered_email')
    }

    router.push('/dashboard')
  } catch (err) {
    console.error('Login error:', err)
    if (err.response) {
      error.value = err.response.data?.message || 'Invalid credentials. Please try again.'
    } else if (err.request) {
      error.value = 'No response from server. Is the backend running?'
    } else {
      error.value = err.message
    }
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  const rememberedEmail = localStorage.getItem('remembered_email')
  if (rememberedEmail) {
    email.value = rememberedEmail
    rememberMe.value = true
  }
})
</script>