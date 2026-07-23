import { defineStore } from 'pinia'
import axios from 'axios'

export const useBunkerStore = defineStore('bunker', {
  state: () => ({
    bunkers: [],
    currentBunker: null,
    loading: false,
    error: null,
  }),

  getters: {
    activeBunkers: (state) => state.bunkers.filter(b => b.status !== 'empty'),
  },

  actions: {
    async fetchBunkers(params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get('/bunkers', { params })
        this.bunkers = response.data
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load bunkers'
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchBunker(id, params = {}) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get(`/bunkers/${id}`, { params })
        this.currentBunker = response.data
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to load bunker'
        throw err
      } finally {
        this.loading = false
      }
    },

    async createBunker(data) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.post('/bunkers', data)
        this.bunkers.unshift(response.data)
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to create bunker'
        throw err
      } finally {
        this.loading = false
      }
    },

    async updateBunker(id, data) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.put(`/bunkers/${id}`, data)
        const index = this.bunkers.findIndex(b => b.id === id)
        if (index !== -1) this.bunkers[index] = response.data
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to update bunker'
        throw err
      } finally {
        this.loading = false
      }
    },

    async deleteBunker(id) {
      this.loading = true
      this.error = null
      try {
        await axios.delete(`/bunkers/${id}`)
        this.bunkers = this.bunkers.filter(b => b.id !== id)
        return true
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to delete bunker'
        throw err
      } finally {
        this.loading = false
      }
    },

    async markEmpty(id) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.post(`/bunkers/${id}/mark-empty`)
        const index = this.bunkers.findIndex(b => b.id === id)
        if (index !== -1) {
          this.bunkers[index].status = 'empty'
          this.bunkers[index].available_weight = 0
        }
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to mark bunker empty'
        throw err
      } finally {
        this.loading = false
      }
    },

    async reopenBunker(id) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.post(`/bunkers/${id}/reopen`)
        const index = this.bunkers.findIndex(b => b.id === id)
        if (index !== -1) {
          this.bunkers[index].status = 'active'
        }
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Failed to reopen bunker'
        throw err
      } finally {
        this.loading = false
      }
    },
  }
})