import { defineStore } from 'pinia'
import axios from 'axios'

export const useSeasonStore = defineStore('season', {
  state: () => ({
    seasons: [],
    loading: false,
  }),
  actions: {
    async fetchSeasons() {
      this.loading = true
      try {
        // ✅ Remove '/api' – baseURL already includes it
        const { data } = await axios.get('/seasons')
        this.seasons = data
      } catch (error) {
        console.error('Failed to fetch seasons:', error)
      } finally {
        this.loading = false
      }
    },
  },
})