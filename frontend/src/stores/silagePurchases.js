import { defineStore } from 'pinia'
import axios from 'axios'

export const useSilagePurchasesStore = defineStore('silagePurchases', {
  state: () => ({
    purchases: [],
    currentPurchase: null,
    loading: false,
    error: null,
  }),

  getters: {
    // Returns only purchases with available kg > 0
    availablePurchases: (state) => {
      return state.purchases.filter(p => {
        const available = p.available_kg || 0
        return available > 0
      })
    },
  },

  actions: {
    // Fetch purchases with optional season_id filter
    async fetchPurchases(params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get('/silage-purchases', { params })
        this.purchases = response.data
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load purchases'
        console.error('Fetch purchases error:', err)
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchPurchase(id, params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get(`/silage-purchases/${id}`, { params })
        this.currentPurchase = response.data
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load purchase'
        throw err
      } finally {
        this.loading = false
      }
    },

    async createPurchase(data) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.post('/silage-purchases', data)
        this.purchases.unshift(response.data)
        return response.data
      } catch (err) {
        this.error = err.response?.data?.errors || err.response?.data?.message || 'Failed to create purchase'
        throw err
      } finally {
        this.loading = false
      }
    },

    async updatePurchase(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.put(`/silage-purchases/${id}`, data)
        const index = this.purchases.findIndex(p => p.id === id)
        if (index !== -1) this.purchases[index] = response.data
        this.currentPurchase = response.data
        return response.data
      } catch (err) {
        this.error = err.response?.data?.errors || err.response?.data?.message || 'Failed to update purchase'
        throw err
      } finally {
        this.loading = false
      }
    },

    async deletePurchase(id) {
      this.loading = true
      this.error = null
      try {
        await axios.delete(`/silage-purchases/${id}`)
        this.purchases = this.purchases.filter(p => p.id !== id)
        return true
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete purchase'
        throw err
      } finally {
        this.loading = false
      }
    },

    async deletePurchases(ids) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.post('/silage-purchases/bulk-delete', { ids })
        this.purchases = this.purchases.filter(p => !ids.includes(p.id))
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete purchases'
        throw err
      } finally {
        this.loading = false
      }
    },
  }
})