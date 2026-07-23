import { defineStore } from 'pinia'
import axios from 'axios'

export const useBunkerFormationStore = defineStore('bunkerFormation', {
  state: () => ({
    loading: false,
    error: null,
  }),

  actions: {
    async formBunker(data) {
      this.loading = true
      this.error = null
      try {
        const response = await axios.post('/bunkers/formation', data)
        return response.data
      } catch (err) {
        this.error = err.response?.data?.errors || err.response?.data?.message || 'Failed to form bunker'
        throw err
      } finally {
        this.loading = false
      }
    },
  }
})