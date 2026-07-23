import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token') || null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
  },

  actions: {
    setAuth(user, token) {
      this.user = user
      this.token = token
      localStorage.setItem('auth_token', token)
      localStorage.setItem('user', JSON.stringify(user))
      axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
    },

    logout() {
      this.user = null
      this.token = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')
      delete axios.defaults.headers.common['Authorization']
    },

    loadUserFromStorage() {
      const token = localStorage.getItem('auth_token')
      const user = JSON.parse(localStorage.getItem('user') || 'null')
      if (token && user) {
        this.token = token
        this.user = user
        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
      }
    },
  }
})